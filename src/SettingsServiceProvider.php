<?php

namespace Hogus\LaravelSettings;

use Hogus\LaravelSettings\Console\SettingsTableCommand;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $configPath = __DIR__.'/config.php';

        $this->publishes([$configPath => config_path('settings.php')], 'config');

        $this->mergeConfigFrom(__DIR__ . '/config.php', 'settings');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('settings', function ($app) {
            $settings = new Settings(
                (new Factory($app))->driver()
            );

            $settings->setCache($app['cache.store']);

            $app['config']['settings.cache'] ? $settings->enableCache() : $settings->disableCache();

            return $settings;
        });

        $this->app->alias('settings', Settings::class);

        $this->registerCommands();
    }


    /**
     * Register the settings related console commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->app->singleton('command.settings.table', function ($app) {
            return new SettingsTableCommand($app['files'], $app['composer']);
        });

        $this->commands('command.settings.table');
    }
}
