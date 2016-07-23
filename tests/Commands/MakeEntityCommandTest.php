<?php

namespace Somnambulist\Tests\Doctrine\Commands;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Somnambulist\Doctrine\Commands\MakeEntityCommand;
use Somnambulist\Doctrine\Contracts\GloballyTrackable;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class TestContainer extends Container
{
    /**
     * Needed because this is called within Console\Command but it exists on Foundation\Application
     */
    public function getNamespace()
    {
        return 'App\\';
    }
}

/**
 * Class MakeEntityTest
 *
 * @author Dave Redfern
 */
class MakeEntityCommandTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var vfsStreamDirectory
     */
    protected $root;

    /**
     * @var MakeEntityCommand
     */
    protected $command;

    protected function setUp()
    {
        $this->root = vfsStream::setup('app');

        $config = [
            'doctrine_behaviours' => include __DIR__ . '/../../config/doctrine_behaviours.php',
        ];

        $container = new TestContainer();
        $container->instance('path', vfsStream::url('app'));

        $this->command = new MakeEntityCommand(
            new Filesystem(), new Repository($config)
        );
        $this->command->setLaravel($container);
    }

    protected function tearDown()
    {
        $this->command = null;
        $this->root    = null;
    }



    public function testCanGetHelpInformation()
    {
        $output = new BufferedOutput();

        $helper = new DescriptorHelper();
        $helper->describe($output, $this->command, array(
            'format' => 'txt',
            'raw_text' => null,
        ));

        $output = $output->fetch();

        $this->assertContains('-g, --globally_trackable', $output);
        $this->assertContains('-i, --identifiable', $output);
        $this->assertContains('-y, --timestampable', $output);
    }

    public function testThatAliasesAreOptional()
    {
        $config = [
            'doctrine_behaviours' => [
                'behaviour_mappings' => [
                    'globally_trackable' => [
                        'contract' => GloballyTrackable::class,
                        'trait'    => GloballyTrackable::class,
                    ],
                ],
            ],
        ];

        $command = new MakeEntityCommand(
            new Filesystem(), new Repository($config)
        );

        $output = new BufferedOutput();
        $helper = new DescriptorHelper();
        $helper->describe($output, $command, array(
            'format' => 'txt',
            'raw_text' => null,
        ));

        $output = $output->fetch();

        $this->assertContains('--globally_trackable', $output);
        $this->assertNotContains('-g, --glo', $output);
    }

    public function testCanMakeEntity()
    {
        $input = new ArrayInput([
            'name'    => 'Bob',
            '--debug' => true,
        ]);
        $output = new BufferedOutput();

        $this->command->run($input, $output);

        $output = $output->fetch();

        $this->assertContains('File: vfs://app/Bob.php', $output);
        $this->assertContains('namespace App', $output);
        $this->assertContains('class Bob', $output);
    }

    public function testCanMakeEntityWithRepository()
    {
        $input  = new ArrayInput([
            'name'         => 'Bob',
            '--debug'      => true,
            '--repository' => true,
        ]);
        $output = new BufferedOutput();

        $this->command->run($input, $output);

        $output = $output->fetch();

        $this->assertContains('File: vfs://app/Repositories/BobRepository.php', $output);
        $this->assertContains('namespace App\Repositories', $output);
        $this->assertContains('class BobRepository', $output);
        $this->assertContains('File: vfs://app/Contracts/Repository/BobRepository.php', $output);
        $this->assertContains('namespace App\Contracts\Repository', $output);
        $this->assertContains('interface BobRepository', $output);
        $this->assertContains('File: vfs://app/Support/AppEntityRepository.php', $output);
        $this->assertContains('namespace App\Support', $output);
        $this->assertContains('class AppEntityRepository', $output);
    }

    public function testCanMakeEntityWithOptions()
    {
        $input  = new ArrayInput([
            'name'            => 'Bob',
            '--debug'         => true,
            '--identifiable'  => true,
            '--nameable'      => true,
            '--timestampable' => true,
            '--versionable'   => true,
        ]);
        $output = new BufferedOutput();

        $this->command->run($input, $output);

        $output = $output->fetch();

        $this->assertContains('namespace App', $output);
        $this->assertContains('class Bob', $output);
        $this->assertContains('IdentifiableContract', $output);
        $this->assertContains('NameableContract', $output);
        $this->assertContains('TimestampableContract', $output);
        $this->assertContains('VersionableContract', $output);
        $this->assertContains('use Identifiable', $output);
        $this->assertContains('use Nameable', $output);
        $this->assertContains('use Timestampable', $output);
        $this->assertContains('use Versionable', $output);
    }

    public function testThatBlockedOptionsRaiseError()
    {
        $input  = new ArrayInput([
            'name'           => 'Bob',
            '--debug'        => true,
            '--trackable'    => true,
            '--identifiable' => true,
        ]);
        $output = new BufferedOutput();

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Option "trackable" cannot be used with "identifiable"');
        $this->command->run($input, $output);
    }

    public function testThatContractAndTraitAreRequiredInSettings()
    {
        $config = [
            'doctrine_behaviours' => [
                'behaviour_mappings' => [
                    'globally_trackable' => [
                        'alias'      => 'g',
                        'contract'   => GloballyTrackable::class,
                    ],
                ],
            ],
        ];

        $container = new TestContainer();
        $container->instance('path', vfsStream::url('app'));

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Missing required parameters in option mappings: trait|contract');

        $this->command = new MakeEntityCommand(
            new Filesystem(), new Repository($config)
        );
    }

    public function testWillCreateFilesAndFoldersInAppFolder()
    {
        $input  = new ArrayInput([
            'name'            => 'Bob',
            '--repository'    => true,
            '--identifiable'  => true,
            '--nameable'      => true,
            '--timestampable' => true,
            '--versionable'   => true,
        ]);
        $output = new BufferedOutput();

        $this->command->run($input, $output);

        $this->assertTrue($this->root->hasChild('Bob.php'));
        $this->assertTrue($this->root->hasChild('Repositories/BobRepository.php'));
        $this->assertTrue($this->root->hasChild('Contracts/Repository/BobRepository.php'));
        $this->assertTrue($this->root->hasChild('Support/AppEntityRepository.php'));
    }

    public function testDoesNotOverwriteExistingFiles()
    {
        $input  = new ArrayInput([
            'name' => 'Bob',
        ]);
        $output = new BufferedOutput();

        $this->command->run($input, $output);

        $this->assertTrue($this->root->hasChild('Bob.php'));

        $input  = new ArrayInput([
            'name'         => 'Bob',
            '--repository' => true,
        ]);
        $output = new BufferedOutput();

        $this->command->run($input, $output);

        $this->assertContains('Skipping vfs://app/Bob.php -- exists', $output->fetch());
        $this->assertTrue($this->root->hasChild('Repositories/BobRepository.php'));
        $this->assertTrue($this->root->hasChild('Contracts/Repository/BobRepository.php'));
        $this->assertTrue($this->root->hasChild('Support/AppEntityRepository.php'));

        $input  = new ArrayInput([
            'name'         => 'Sally',
            '--repository' => true,
        ]);
        $output = new BufferedOutput();

        $this->command->run($input, $output);

        $this->assertContains('Skipping vfs://app/Support/AppEntityRepository.php -- exists', $output->fetch());
        $this->assertTrue($this->root->hasChild('Sally.php'));
        $this->assertTrue($this->root->hasChild('Contracts/Repository/SallyRepository.php'));
    }

    public function testPrependsNamespaceIfNotSpecified()
    {
        $input  = new ArrayInput([
            'name' => 'This\Is\My\Bob',
            '--debug' => true,
        ]);
        $output = new BufferedOutput();

        $this->command->run($input, $output);

        $this->assertContains('namespace App\This\Is\My', $output->fetch());
    }
}
