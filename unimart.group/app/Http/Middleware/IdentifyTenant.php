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
        $rawHost = $request->getHost();
        $host = explode(':', $rawHost)[0];

        $baseDomain = env('APP_LOCAL_DOMAIN', 'unimart.group.localhost');
        $onlineDomain = env('APP_BASE_DOMAIN', 'unimart.group');

        $isMarketplace = str_starts_with($host, 'marketplace.') || $request->is('marketplace*');
        app()->instance('is_marketplace', $isMarketplace);

        $isRootDomain = in_array($host, [
            $baseDomain, 
            $onlineDomain, 
            'localhost', 
            '127.0.0.1', 
            'www.' . $baseDomain, 
            'www.' . $onlineDomain
        ]);
        app()->instance('is_root_domain', $isRootDomain);

        $tenant = null;

        // 1. Jika rute memiliki parameter alias (misal /caffe/{alias} atau /grosir/{alias})
        if ($request->route('alias')) {
            $tenant = Tenant::where('domain', $request->route('alias'))->first();
        }

        // 2. Jika bukan root domain dan bukan marketplace, cari tenant berdasarkan subdomain host
        if (!$isRootDomain && !$isMarketplace && !$tenant) {
            $subdomain = null;

            if (str_ends_with($host, '.' . $baseDomain)) {
                $subdomain = substr($host, 0, -strlen('.' . $baseDomain));
            } elseif (str_ends_with($host, '.' . $onlineDomain)) {
                $subdomain = substr($host, 0, -strlen('.' . $onlineDomain));
            }

            if ($subdomain && !in_array($subdomain, ['www', 'app', 'admin', 'marketplace'])) {
                $tenant = Tenant::where('domain', $subdomain)->first();
            }

            if (!$tenant) {
                // Cek domain mappings (custom domain)
                if (\Illuminate\Support\Facades\Schema::hasTable('domain_mappings')) {
                    $mapping = \App\Models\DomainMapping::where('custom_domain', $host)->where('is_verified', true)->first();
                    if ($mapping) {
                        $tenant = $mapping->tenant;
                    }
                }

                if (!$tenant) {
                    $tenant = Tenant::where('domain', $host)->first();
                }
            }
        }

        // 3. Jika user login (untuk akses dashboard /owner atau /staff), resolve tenant milik user
        if (!$tenant && Auth::check() && Auth::user()->tenant_id) {
            $tenant = Tenant::find(Auth::user()->tenant_id);
        }

        // 4. Fallback untuk environment testing non-root domain
        if (!$isRootDomain && !$isMarketplace && !$tenant) {
            if (app()->environment('testing')) {
                $tenant = Tenant::first();
            }
        }

        if ($tenant) {
            app()->instance('tenant_id', $tenant->id);
            app()->instance('tenant', $tenant);
            app()->instance('business_type', $tenant->business_type ?? 'caffe');

            session([
                'tenant_id' => $tenant->id,
                'business_type' => $tenant->business_type ?? 'caffe'
            ]);
        } else {
            app()->forgetInstance('tenant_id');
            app()->forgetInstance('tenant');
            app()->forgetInstance('business_type');
            session()->forget(['tenant_id', 'business_type']);
        }

        return $next($request);
    }
}
