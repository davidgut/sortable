<?php

namespace DavidGut\Sortable;

use Illuminate\Support\ServiceProvider;

class SortableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/sortable.php' => config_path('sortable.php'),
            ], 'sortable-config');

            $this->publishes([
                __DIR__ . '/../resources/js' => resource_path('js/vendor/sortable'),
            ], 'sortable-assets');

            // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sortable.php', 'sortable');
    }
}
