<?php

use App\Providers\AppServiceProvider;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Tighten\Ziggy\ZiggyServiceProvider;

return [
    AppServiceProvider::class,
    InertiaServiceProvider::class,
    ZiggyServiceProvider::class,
];
