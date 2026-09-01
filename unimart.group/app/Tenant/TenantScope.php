<?php

namespace App\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Terapkan scope untuk menyaring query berdasarkan tenant_id aktif.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->bound('tenant_id') && app('tenant_id') !== null) {
            $builder->where($model->getTable() . '.tenant_id', app('tenant_id'));
        }
    }
}
