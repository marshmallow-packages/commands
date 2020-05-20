<?php

namespace Marshmallow\Commands;

use Illuminate\Support\ServiceProvider;
use Marshmallow\Commands\Console\Commands\EnvironmentCommand;
use Marshmallow\Commands\Console\Commands\Nova\ResourceCommand;

class CommandsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Commands
         */
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                EnvironmentCommand::class,
                ResourceCommand::class,
                ]
            );
        }
    }
}
