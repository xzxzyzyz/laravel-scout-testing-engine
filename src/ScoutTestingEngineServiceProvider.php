<?php

namespace Xzxzyzyz\Laravel\ScoutTestingEngine;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Laravel\Scout\EngineManager;

class ScoutTestingEngineServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(Engine::class, function ($app) {
            return new Engine(new Filesystem, $app['config']->get('scout'));
        });

        $this->app->alias(Engine::class, 'laravel.scout.testing.engine');

        $this->app[EngineManager::class]->extend('testing', function($app) {
            return $app->make('laravel.scout.testing.engine');
        });
    }
}
