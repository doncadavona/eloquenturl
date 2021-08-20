<?php

namespace Doncadavona\Eloquenturl;

use Illuminate\Support\ServiceProvider;

class EloquenturlServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'doncadavona');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'doncadavona');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/eloquenturl.php', 'eloquenturl');

        // Register the service the package provides.
        $this->app->singleton('eloquenturl', function ($app) {
            return new Eloquenturl;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['eloquenturl'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/eloquenturl.php' => config_path('eloquenturl.php'),
        ], 'eloquenturl.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/doncadavona'),
        ], 'eloquenturl.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/doncadavona'),
        ], 'eloquenturl.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/doncadavona'),
        ], 'eloquenturl.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
