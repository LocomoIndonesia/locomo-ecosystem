<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Tenant\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'name',
        'phone',
        'mac_address',
        'visit_count',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
