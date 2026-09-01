<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $modulePath = app_path('Modules');

        if (!File::exists($modulePath)) {
            return;
        }

        $modules = array_map(function ($path) {
            return basename($path);
        }, File::directories($modulePath));

        foreach ($modules as $module) {
            $this->loadModule($module);
        }
    }

    /**
     * Load components for a specific module.
     */
    private function loadModule(string $module): void
    {
        $modulePath = app_path("Modules/{$module}");

        // Load Routes
        if (File::exists("{$modulePath}/Routes/web.php")) {
            Route::middleware('web')
                ->group("{$modulePath}/Routes/web.php");
        }

        if (File::exists("{$modulePath}/Routes/api.php")) {
            Route::middleware('api')
                ->prefix('api')
                ->group("{$modulePath}/Routes/api.php");
        }

        // Load Migrations
        if (File::exists("{$modulePath}/Migrations")) {
            $this->loadMigrationsFrom("{$modulePath}/Migrations");
        }

        // Load Views
        if (File::exists("{$modulePath}/Views")) {
            $this->loadViewsFrom("{$modulePath}/Views", strtolower($module));
        }

        // Load Translations
        if (File::exists("{$modulePath}/Lang")) {
            $this->loadTranslationsFrom("{$modulePath}/Lang", strtolower($module));
        }
    }
}
