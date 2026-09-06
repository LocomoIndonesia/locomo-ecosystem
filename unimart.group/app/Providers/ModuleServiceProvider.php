<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\ModuleManager::class, function () {
            return new \App\Services\ModuleManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $modulesPath = app_path('Modules');

        if (!File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $module) {
            $moduleName = basename($module);
            $moduleLower = strtolower($moduleName);

            // 1. Daftarkan Routes jika berkas routes.php tersedia
            $routesPath = $module . '/routes.php';
            if (File::exists($routesPath)) {
                Route::middleware('web')
                    ->group($routesPath);
            }

            // 2. Daftarkan Views dengan namespace (misal: caffe::pos, grosir::pos, market::home)
            $viewsPath = $module . '/Views';
            if (File::exists($viewsPath)) {
                $this->loadViewsFrom($viewsPath, $moduleLower);
            }

            // 3. Daftarkan Migrations jika modul memiliki tabel spesifik
            $migrationsPath = $module . '/Database/Migrations';
            if (File::exists($migrationsPath)) {
                $this->loadMigrationsFrom($migrationsPath);
            }
        }
    }
}
