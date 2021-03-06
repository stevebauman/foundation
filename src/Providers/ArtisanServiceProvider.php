<?php namespace Orchestra\Foundation\Providers;

use Illuminate\Contracts\Foundation\Application;
use Orchestra\Config\Console\ConfigCacheCommand;
use Illuminate\Foundation\Providers\ArtisanServiceProvider as ServiceProvider;

class ArtisanServiceProvider extends ServiceProvider
{
    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerConfigCacheCommand()
    {
        $this->app->singleton('command.config.cache', function (Application $app) {
            return new ConfigCacheCommand($app->make('files'));
        });
    }
}
