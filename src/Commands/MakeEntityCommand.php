<?php

namespace Somnambulist\Doctrine\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MakeEntityCommand
 *
 * @package    Somnambulist\Doctrine\Commands
 * @subpackage Somnambulist\Doctrine\Commands\MakeEntityCommand
 * @author     Dave Redfern
 */
class MakeEntityCommand extends GeneratorCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:entity
                            {name           : The name of the entity including namespace}
                            
                            {--r|repository : Make a repository for the entity}
                            {--debug        : Display generated classes without saving them}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates an empty entity, optionally implementing behaviours or with a repository.';

    /**
     * Maps the behaviour option flags to classes
     *
     * @var array
     */
    protected $behaviourOptionMappings = [];

    /**
     * @var string
     */
    protected $appNamespace = 'App';

    /**
     * @var string
     */
    protected $entityNamespace = 'App\\Entities';

    /**
     * @var string
     */
    protected $repositoryNamespace = 'App\\Repositories';

    /**
     * @var string
     */
    protected $contractNamespace = 'App\\Contracts\\Repository';



    /**
     * Constructor.
     *
     * @param Filesystem $files
     * @param Repository $config
     */
    public function __construct(Filesystem $files, Repository $config)
    {
        parent::__construct($files);

        $this->behaviourOptionMappings = $config->get('doctrine_behaviours.behaviour_mappings');
        $this->specifyParameters();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return '';
    }

    /**
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $options = [];

        foreach ($this->behaviourOptionMappings as $option => $mapping) {
            $this->assertMappingContainsContractAndTrait($mapping);

            $description = 'Make the entity ' . $this->getShortNameFromClass($mapping['trait']);

            $options[] = [$option, (isset($mapping['alias']) ? $mapping['alias'] : null), InputOption::VALUE_NONE, $description];
        }

        return $options;
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function fire()
    {
        $entityName      = $this->parseName($this->argument('name'));
        $pieces          = explode('\\', $entityName);
        $entityShortName = array_pop($pieces);

        $this->appNamespace        = $this->laravel->getNamespace();
        $this->entityNamespace     = implode('\\', $pieces);
        $this->contractNamespace   = $this->appNamespace . 'Contracts\Repository';
        $this->repositoryNamespace = $this->appNamespace . 'Repositories';

        $repoName         = $entityShortName . 'Repository';
        $repoAppEntity    = $this->appNamespace . 'Support\AppEntityRepository';
        $repoContractName = sprintf('%s\%s', $this->contractNamespace, $repoName);
        $repoConcreteName = sprintf('%s\%s', $this->repositoryNamespace, $repoName);

        $traits     = new Collection();
        $interfaces = new Collection();

        $this->buildBehaviourOptions($traits, $interfaces);

        $this->createFile($entityName, $this->entityClassStub($entityShortName, $interfaces, $traits));

        if ($this->option('repository')) {
            $this->createFile($repoAppEntity,    $this->appRepositoryClassStub());
            $this->createFile($repoConcreteName, $this->repositoryClassStub($repoName));
            $this->createFile($repoContractName, $this->repositoryInterfaceStub($repoName));
        }

        return 0;
    }

    /**
     * @param array $mappings
     */
    private function assertMappingContainsContractAndTrait(array $mappings)
    {
        if (!isset($mappings['trait']) || !isset($mappings['contract'])) {
            throw new \RuntimeException('Missing required parameters in option mappings: trait|contract');
        }
    }

    /**
     * @param string $class
     *
     * @return string
     */
    private function getShortNameFromClass($class)
    {
        return (new \ReflectionClass($class))->getShortName();
    }

    /**
     * @param string $name
     * @param string $content
     */
    private function createFile($name, $content)
    {
        $file = $this->getPath($name);

        if ($this->option('debug')) {
            $this->comment("File: <info>$file</info>");
            $this->line($content);
            return;
        }

        if (!$this->files->isDirectory(dirname($file))) {
            $this->files->makeDirectory(dirname($file), 0755, true, true);
        }

        if ($this->files->exists($file)) {
            $this->line("Skipping <info>$file</info> -- exists");
            return;
        }

        if ($this->files->put($file, $content)) {
            $this->line("Successfully created <info>$file</info>");
        }
    }

    /**
     * @param Collection $traits
     * @param Collection $interfaces
     */
    private function buildBehaviourOptions(Collection $traits, Collection $interfaces)
    {
        foreach ($this->behaviourOptionMappings as $option => $mappings) {
            if (!$this->option($option)) {
                continue;
            }

            $this->assertMappingContainsContractAndTrait($mappings);

            if (isset($mappings['blocked_by'])) {
                foreach ($mappings['blocked_by'] as $blocker) {
                    if ($this->option($blocker)) {
                        throw new \RuntimeException(sprintf('Option "%s" cannot be used with "%s"', $option, $blocker));
                    }
                }
            }

            $this->addTraitAndContractDefinition($traits, $interfaces, $mappings);
        }
    }

    /**
     * @param Collection $traits
     * @param Collection $interfaces
     * @param array      $mappings
     */
    private function addTraitAndContractDefinition(Collection $traits, Collection $interfaces, array $mappings)
    {
        $traits->push($mappings['trait']);
        $interfaces->push([
            'class' => $mappings['contract'],
            'alias' => $this->getShortNameFromClass($mappings['contract']) . 'Contract',
        ]);
    }

    /**
     * @param Collection  $collection
     * @param string      $separator
     * @param null|string $prefix
     * @param null|string $suffix
     *
     * @return null|string
     */
    private function collectionToString(Collection $collection, $separator = "\n", $prefix = null, $suffix = null)
    {
        $return = null;

        if ($collection->count()) {
            $return = $prefix . $collection->sort()->implode($separator) . $suffix;
        }

        return $return;
    }

    /**
     * @param string     $name
     * @param Collection $interfaces
     * @param Collection $traits
     *
     * @return string
     */
    private function entityClassStub($name, Collection $interfaces, Collection $traits)
    {
        $useString       = new Collection();
        $traitString     = new Collection();
        $interfaceString = $interfaces->count() ? ' implements ' . $interfaces->pluck('alias')->implode(', ') : '';

        foreach ($interfaces as $int) {
            $useString[] = "use {$int['class']}" . (isset($int['alias']) ? ' as ' . $int['alias'] : '') . ';';
        }
        foreach ($traits as $trait) {
            $ref = new \ReflectionClass($trait);
            $useString[]   = "use {$trait};";
            $traitString[] = "    use {$ref->getShortName()};";
        }

        $useString   = $this->collectionToString($useString, "\n", "\n", "\n");
        $traitString = $this->collectionToString($traitString, "\n", null, "\n");

        return <<<CLASS
<?php

namespace $this->entityNamespace;
$useString
/**
 * Class $name
 *
 * @package    $this->entityNamespace
 * @subpackage $this->entityNamespace\\{$name}
 */
class $name$interfaceString
{

$traitString
}

CLASS;

    }

    /**
     * @param string $repository
     *
     * @return string
     */
    private function repositoryInterfaceStub($repository)
    {
        return <<<INT
<?php

namespace {$this->contractNamespace};

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Interface {$repository}
 *
 * @package    {$this->contractNamespace}
 * @subpackage {$this->contractNamespace}\\{$repository}
 */
interface {$repository} extends ObjectRepository
{

}

INT;

    }

    /**
     * @return string
     */
    private function appRepositoryClassStub()
    {
        return <<<CLASS
<?php

namespace {$this->appNamespace}Support;

use Doctrine\ORM\EntityRepository;

/**
 * Class AppEntityRepository
 *
 * @package    {$this->appNamespace}Support
 * @subpackage {$this->appNamespace}Support\\AppEntityRepository
 */
abstract class AppEntityRepository extends EntityRepository
{

}

CLASS;

    }

    /**
     * @param string $repository
     *
     * @return string
     */
    private function repositoryClassStub($repository)
    {
        return <<<CLASS
<?php

namespace {$this->repositoryNamespace};

use {$this->contractNamespace}\\{$repository} as {$repository}Contract;
use {$this->appNamespace}Support\\AppEntityRepository;

/**
 * Class {$repository}
 *
 * @package    {$this->repositoryNamespace}
 * @subpackage {$this->repositoryNamespace}\\{$repository}
 */
class {$repository} extends AppEntityRepository implements {$repository}Contract
{

}

CLASS;

    }
}
