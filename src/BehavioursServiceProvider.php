<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace Somnambulist\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

/**
 * Class BehavioursServiceProvider
 *
 * @package    Somnambulist\Doctrine
 * @subpackage Somnambulist\Doctrine\BehavioursServiceProvider
 * @author     Dave Redfern
 */
class BehavioursServiceProvider extends ServiceProvider
{

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes($this->getConfigPaths(), 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();

        $config = $this->app->make('config');

        $this->registerRepositories($config);
        $this->registerCommands($config);
    }



    /**
     * Merge config
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom($this->getBehavioursConfigPath(), 'doctrine_behaviours');
        $this->mergeConfigFrom($this->getRepositoryConfigPath(), 'doctrine_repositories');
    }

    /**
     * Register any bound tenant aware repositories
     *
     * @param Repository $config
     *
     * @return void
     */
    protected function registerRepositories(Repository $config)
    {
        $this->app->afterResolving('registry', function (ManagerRegistry $registry, Container $container) use ($config) {
            foreach ($config->get('doctrine_repositories.repositories', []) as $details) {
                if (!isset($details['repository']) && !isset($details['entity'])) {
                    throw new \InvalidArgumentException(
                        sprintf('Failed to process repository data: missing repository/entity from definition')
                    );
                }

                $this->app->singleton($details['repository'], function ($app) use ($details, $registry) {
                    $class = $details['repository'];
                    $em    = isset($details['em']) ? $registry->getManager($details['em']) : $registry->getManager();

                    return new $class($em, $em->getClassMetaData($details['entity']));
                });

                if (isset($details['alias'])) {
                    $this->app->alias($details['repository'], $details['alias']);
                }
                if (isset($details['tags'])) {
                    $this->app->tag($details['repository'], $details['tags']);
                }
            }
        });
    }

    /**
     * Register the CLI commands with console
     *
     * @param Repository $config
     */
    protected function registerCommands(Repository $config)
    {
        $this->commands([
            Commands\MakeEntityCommand::class,
        ]);
    }

    /**
     * @return string
     */
    protected function getConfigPaths()
    {
        return [
            $this->getRepositoryConfigPath() => config_path('doctrine_repositories.php'),
            $this->getBehavioursConfigPath() => config_path('doctrine_behaviours.php'),
        ];
    }

    /**
     * @return string
     */
    protected function getRepositoryConfigPath()
    {
        return __DIR__ . '/../config/doctrine_repositories.php';
    }

    /**
     * @return string
     */
    protected function getBehavioursConfigPath()
    {
        return __DIR__ . '/../config/doctrine_behaviours.php';
    }

    /**
     * @return array
     */
    public static function compiles()
    {
        return [
            __DIR__ . '/Contracts/Activatable.php',
            __DIR__ . '/Contracts/Blamable.php',
            __DIR__ . '/Contracts/CanRenumberCollection.php',
            __DIR__ . '/Contracts/Identifiable.php',
            __DIR__ . '/Contracts/Nameable.php',
            __DIR__ . '/Contracts/NumericallySortable.php',
            __DIR__ . '/Contracts/Publishable.php',
            __DIR__ . '/Contracts/Sluggable.php',
            __DIR__ . '/Contracts/Timestampable.php',
            __DIR__ . '/Contracts/Versionable.php',
            __DIR__ . '/Contracts/UniversallyIdentifiable.php',
            __DIR__ . '/Contracts/GloballyTrackable.php',
            __DIR__ . '/Contracts/Trackable.php',
            __DIR__ . '/EventSubscribers/BlamableEventSubscriber.php',
            __DIR__ . '/EventSubscribers/SluggableEventSubscriber.php',
            __DIR__ . '/EventSubscribers/TimestampableEventSubscriber.php',
            __DIR__ . '/EventSubscribers/UuidEventSubscriber.php',
            __DIR__ . '/EventSubscribers/VersionableEventSubscriber.php',
            __DIR__ . '/Traits/Activatable.php',
            __DIR__ . '/Traits/Blamable.php',
            __DIR__ . '/Traits/CanRenumberCollection.php',
            __DIR__ . '/Traits/Identifiable.php',
            __DIR__ . '/Traits/Nameable.php',
            __DIR__ . '/Traits/NumericallySortable.php',
            __DIR__ . '/Traits/Publishable.php',
            __DIR__ . '/Traits/Sluggable.php',
            __DIR__ . '/Traits/Timestampable.php',
            __DIR__ . '/Traits/UniversallyIdentifiable.php',
            __DIR__ . '/Traits/Versionable.php',
            __DIR__ . '/Traits/GloballyTrackable.php',
            __DIR__ . '/Traits/Trackable.php',
            __DIR__ . '/Types/DateTimeType.php',
            __DIR__ . '/Types/DateTimeTzType.php',
            __DIR__ . '/Types/DateType.php',
            __DIR__ . '/Types/TimeType.php',
        ];
    }
}
