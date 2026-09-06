<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Tenant\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'email',
        'address',
        'credit_limit',
        'current_debt',
        'points',
        'mac_address',
        'visit_count',
    ];

    protected $casts = [
        'credit_limit' => 'integer',
        'current_debt' => 'integer',
        'points' => 'integer',
        'visit_count' => 'integer',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function accountsReceivable(): HasMany
    {
        return $this->hasMany(AccountReceivable::class);
    }
}
