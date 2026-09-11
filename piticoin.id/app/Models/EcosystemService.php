<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EcosystemService extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'code',
        'icon',
        'category',
        'description',
        'wizard_schema',
        'price_gold',
        'price_fiat',
        'service_url',
        'webhook_url',
        'status',
        'is_active',
    ];

    protected $casts = [
        'wizard_schema' => 'array',
        'price_gold' => 'integer',
        'price_fiat' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function licenses(): HasMany
    {
        return $this->hasMany(ServiceLicense::class, 'service_id');
    }
}
