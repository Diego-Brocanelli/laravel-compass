<?php

namespace Davidhsianturi\Compass;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Davidhsianturi\Compass\Contracts\DocsRepository;
use Davidhsianturi\Compass\Contracts\RoutesRepository;
use Davidhsianturi\Compass\Storage\DatabaseDocsRepository;
use Davidhsianturi\Compass\Storage\DatabaseRoutesRepository;

class CompassServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerPublishing();

        $this->loadViewsFrom(
            __DIR__.'/../resources/views', 'compass'
        );
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::namespace('Davidhsianturi\Compass\Http\Controllers')
            ->as('compass.')
            ->prefix(config('compass.path'))
            ->group(function () {
                $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
            });
    }

    /**
     * Register the package's migrations.
     *
     * @return void
     */
    private function registerMigrations()
    {
        if ($this->app->runningInConsole() && $this->shouldMigrate()) {
            $this->loadMigrationsFrom(__DIR__.'/Storage/migrations');
        }
    }

    /**
     * Determine if we should register the migrations.
     *
     * @return void
     */
    protected function shouldMigrate()
    {
        return Compass::$runsMigrations && config('compass.driver') === 'database';
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/Storage/migrations' => database_path('migrations'),
            ], 'compass-migrations');

            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/compass'),
            ], 'compass-assets');

            $this->publishes([
                __DIR__.'/../config/compass.php' => config_path('compass.php'),
            ], 'compass-config');
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/compass.php', 'compass'
        );

        $this->registerStorageDriver();

        $this->commands([
            Console\InstallCommand::class,
            Console\PublishCommand::class,
        ]);
    }

    /**
     * Register the package storage driver.
     *
     * @return void
     */
    protected function registerStorageDriver()
    {
        $driver = config('compass.driver');

        if (method_exists($this, $method = 'register'.ucfirst($driver).'Driver')) {
            return $this->$method();
        }
    }

    /**
     * Register the package database storage driver.
     *
     * @return void
     */
    protected function registerDatabaseDriver()
    {
        $this->app->singleton(
            RoutesRepository::class, DatabaseRoutesRepository::class
        );

        $this->app->singleton(
            DocsRepository::class, DatabaseDocsRepository::class
        );

        $this->app->when(DatabaseRoutesRepository::class)
            ->needs('$connection')
            ->give(config('compass.storage.database.connection'));
    }
}