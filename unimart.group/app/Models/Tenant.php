<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'business_type',
        'owner_name',
        'owner_email',
        'owner_phone',
        'status',
        'trial_ends_at',
        'subscription_ends_at',
        'is_marketplace_enabled',
        'city',
        'address',
        'wifi_radius_secret',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'is_marketplace_enabled' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function accountsPayable(): HasMany
    {
        return $this->hasMany(AccountPayable::class);
    }

    public function accountsReceivable(): HasMany
    {
        return $this->hasMany(AccountReceivable::class);
    }

    /**
     * Dapatkan domain lengkap tenant (misal: cafe-a.unimart.group atau cafe-a.unimart.group.localhost).
     */
    public function getFullDomainAttribute(): string
    {
        $host = request() ? request()->getHost() : 'localhost';
        $isLocal = str_ends_with($host, '.localhost') || $host === 'localhost' || $host === '127.0.0.1';
        $baseDomain = $isLocal ? env('APP_LOCAL_DOMAIN', 'unimart.group.localhost') : env('APP_BASE_DOMAIN', 'unimart.group');

        return "{$this->domain}.{$baseDomain}";
    }

    /**
     * Dapatkan URL lengkap toko tenant menggunakan path-based URL otonom (/s/{domain}).
     */
    public function getStoreUrlAttribute(): string
    {
        $scheme = (request() && request()->isSecure()) ? 'https' : 'http';
        $host = request() ? request()->getHost() : 'localhost';
        $isLocal = str_ends_with($host, '.localhost') || $host === 'localhost' || $host === '127.0.0.1';
        $baseDomain = $isLocal ? env('APP_LOCAL_DOMAIN', 'unimart.group.localhost') : env('APP_BASE_DOMAIN', 'unimart.group');

        return "{$scheme}://{$baseDomain}/s/{$this->domain}";
    }

    /**
     * Dapatkan URL subdomain jika wildcard DNS/hosting aktif.
     */
    public function getSubdomainUrlAttribute(): string
    {
        $scheme = (request() && request()->isSecure()) ? 'https' : 'http';
        return "{$scheme}://{$this->full_domain}";
    }
}
