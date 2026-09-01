<?php

namespace App\Tenant;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot trait untuk mendaftarkan global scope dan event creating.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (app()->bound('tenant_id') && app('tenant_id') !== null) {
                if (empty($model->tenant_id)) {
                    $model->tenant_id = app('tenant_id');
                }
            }
        });
    }

    /**
     * Relasi ke model Tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
