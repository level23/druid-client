<?php
declare(strict_types=1);

namespace Level23\Druid;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Foundation\Application as LaravelApplication;

/**
 * Class DruidServiceProvider
 *
 * @package Level23\Druid
 * @codeCoverageIgnore
 */
class DruidServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->registerDruidClient();
    }

    /**
     * Publish and merge the config
     */
    protected function setupConfig(): void
    {
        $source = (string)realpath(__DIR__ . '/../config/config.php');

        if ($this->app instanceof LaravelApplication) {
            $this->publishes([$source => config_path('druid.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('druid');
        }

        $this->mergeConfigFrom($source, 'druid');
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    protected function registerDruidClient(): void
    {
        $this->setupConfig();

        $this->app->singleton(DruidClient::class, function () {
            $client = new DruidClient($this->app['config']['druid']); // @phpstan-ignore-line
            $client->setLogger($this->app['log']); // @phpstan-ignore-line

            return $client;
        });

        $this->app->bind('druid', DruidClient::class);
    }
}