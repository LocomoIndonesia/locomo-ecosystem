<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;

        // 1. Jika user sudah login, prioritaskan tenant milik user tersebut
        if (Auth::check() && Auth::user()->tenant_id) {
            $tenant = Tenant::find(Auth::user()->tenant_id);
        }

        // 2. Jika mengakses rute dengan parameter alias (caffe atau hotspot)
        if (!$tenant && $request->route('alias')) {
            $tenant = Tenant::where('domain', $request->route('alias'))->first();
        }

        // 3. Cari tenant berdasarkan host domain
        if (!$tenant) {
            $host = $request->getHost();
            $baseDomain = 'unimart.group.localhost';
            $onlineDomain = 'unimart.group';
            if (str_ends_with($host, '.' . $baseDomain)) {
                $subdomain = str_replace('.' . $baseDomain, '', $host);
                $tenant = Tenant::where('domain', $subdomain)->first();
            } elseif (str_ends_with($host, '.' . $onlineDomain)) {
                $subdomain = str_replace('.' . $onlineDomain, '', $host);
                $tenant = Tenant::where('domain', $subdomain)->first();
            }
            if (!$tenant) {
                $tenant = Tenant::where('domain', $host)->first();
            }
        }

        // 4. Fallback untuk testing/lokal
        if (!$tenant) {
            if (app()->environment('testing')) {
                $tenant = Tenant::first();
            } elseif (app()->environment('local')) {
                $host = $request->getHost();
                $baseDomain = 'unimart.group.localhost';
                $onlineDomain = 'unimart.group';
                if ($host !== $baseDomain && $host !== $onlineDomain && $host !== 'localhost' && $host !== '127.0.0.1') {
                    $tenant = Tenant::first();
                }
            }
        }

        if ($tenant) {
            app()->instance('tenant_id', $tenant->id);
            app()->instance('tenant', $tenant);
            
            // Simpan di session agar mudah diakses
            session(['tenant_id' => $tenant->id]);
        }

        return $next($request);
    }
}
