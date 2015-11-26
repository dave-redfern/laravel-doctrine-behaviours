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

namespace Somnambulist\Doctrine\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class RepositoryServiceProvider
 *
 * Define repository definitions as an array and pass to registerRepositories:
 *
 * <code>
 * public function register()
 * {
 *     $repositories = [
 *         // [repository => '', entity => '', alias => '', 'tags' => [tags]],
 *         [
 *             'repository' => Repository\CustomerRepository::class,
 *             'entity'     => Repository\Customer::class,
 *             'alias'      => 'app.repository.customer',
 *             'tags'       => ['repository'],
 *         ],
 *     ];
 *     $this->registerRepositories($repositories);
 * }
 * </code>
 *
 * @package    App\Service\Tenant\Providers
 * @subpackage App\Service\Tenant\Providers\RepositoryServiceProvider
 * @author     Dave Redfern
 */
abstract class RepositoryServiceProvider extends ServiceProvider
{

    /**
     * Processes definitions to the IoC
     *
     * @param array $definitions
     */
    protected function registerRepositories(array $definitions = [])
    {
        foreach ($definitions as $details) {
            if (!isset($details['repository']) && !isset($details['entity'])) {
                throw new \InvalidArgumentException(
                    sprintf('Failed to process repository data: missing repository/entity from definition')
                );
            }

            $this->app->singleton($details['repository'], function ($app) use ($details) {
                $class = $details['repository'];
                return new $class($app['em'], $app['em']->getClassMetaData($details['entity']));
            });

            if (isset($details['alias'])) {
                $this->app->alias($details['repository'], $details['alias']);
            }
            if (isset($details['tags'])) {
                $this->app->tag($details['repository'], $details['tags']);
            }
        }
    }
}