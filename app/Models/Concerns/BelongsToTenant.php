<?php

namespace App\Models\Concerns;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            if (app()->bound('tenant.id')) {
                $query->where($query->getModel()->getTable().'.tenant_id', app('tenant.id'));
            }
        });

        static::creating(function ($model) {
            if (app()->bound('tenant.id') && empty($model->tenant_id)) {
                $model->tenant_id = app('tenant.id');
            }
        });
    }
}

