<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CanvasTenantMiddleware
 *
 * Middleware untuk Canvas Module.
 * Memastikan user yang masuk ke /canvas sudah terikat ke tenant-nya.
 * Mengambil tenant dari konteks app() yang di-set oleh IdentifyTenant,
 * lalu meng-inject data canvas boot ke request agar CanvasController
 * bisa langsung menggunakannya tanpa query ulang.
 */
class CanvasTenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Pastikan user sudah login (redundant safety check setelah 'auth')
        if (!$user) {
            return redirect()->route('login');
        }

        // Ambil tenant dari konteks app() yang sudah di-set IdentifyTenant
        $tenant = app()->bound('tenant') ? app('tenant') : null;

        // Jika tidak ada tenant terikat (root domain atau subdomain tidak valid)
        // coba resolve dari user langsung
        if (!$tenant && $user->tenant_id) {
            $tenant = \App\Models\Tenant::find($user->tenant_id);
            if ($tenant) {
                app()->instance('tenant', $tenant);
            }
        }

        // Jika masih tidak ada tenant → redirect ke halaman owner register
        if (!$tenant) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Tenant tidak ditemukan. Silakan daftar toko terlebih dahulu.',
                    'action'  => 'redirect',
                    'url'     => route('owner.register'),
                ], 403);
            }
            return redirect()->route('owner.register')
                ->with('error', 'Tenant tidak ditemukan. Silakan daftar toko terlebih dahulu.');
        }

        // Inject canvas boot data ke request agar controller bisa langsung pakai
        $request->attributes->set('canvas_tenant', $tenant);
        $request->attributes->set('canvas_role', $user->role ?? 'staff');

        return $next($request);
    }
}
