<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'bank_account_info',
        'current_debt',
        'is_active',
    ];

    protected $casts = [
        'current_debt' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function accountsPayable()
    {
        return $this->hasMany(AccountPayable::class);
    }
}
