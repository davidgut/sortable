<?php

namespace DavidGut\Sortable;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class SortableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/sortable.php');

        Blade::directive('sortableUrl', function (string $expression) {
            return "data-sortable-update-url=\"<?php echo e({$expression}->sortableUrl()); ?>\"";
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/sortable.php' => config_path('sortable.php'),
            ], 'sortable-config');

            $this->publishes([
                __DIR__ . '/../routes/sortable.php' => base_path('routes/sortable.php'),
            ], 'sortable-routes');

            $this->publishes([
                __DIR__ . '/../resources/js' => resource_path('js/vendor/sortable'),
            ], 'sortable-assets');
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
