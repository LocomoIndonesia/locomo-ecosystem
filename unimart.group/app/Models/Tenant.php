<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'wifi_radius_secret',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Relasi ke Users.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relasi ke Products.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relasi ke Orders.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
