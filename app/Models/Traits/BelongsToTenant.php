<?php

namespace App\Models\Traits;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($tenantId = TenantContext::id()) {
                $builder->where(static::getTenantColumn(), $tenantId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->{static::getTenantColumn()}) && $tenantId = TenantContext::id()) {
                $model->{static::getTenantColumn()} = $tenantId;
            }
        });
    }

    protected static function getTenantColumn(): string
    {
        return 'tenant_id';
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->withoutGlobalScope('tenant')->where(static::getTenantColumn(), $tenantId);
    }
}
