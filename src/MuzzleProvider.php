<?php

namespace Muzzle;

use Illuminate\Support\ServiceProvider;

/**
 * @codeCoverageIgnore
 */
class MuzzleProvider extends ServiceProvider
{

    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('muzzle.php'),
            ]);
        }
    }

    public function register()
    {

        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'muzzle');

        ResponseBuilder::setFixtureDirectory($this->app['config']->get('muzzle.fixture_path'));
    }
}
