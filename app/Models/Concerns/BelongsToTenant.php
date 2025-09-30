<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model) {
            if (!$model->tenant_id && ($tenantId = static::resolveTenantId())) {
                $model->tenant_id = $tenantId;
            }
        });

        // Keep tenant scoping optional so single-tenant deployments work without extra config.
        if (!config('app.tenant_enforced', false)) {
            return;
        }

        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($tenantId = static::resolveTenantId()) {
                $builder->where(static::qualifyTenantColumn(), $tenantId);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function resolveTenantId(): ?int
    {
        $user = Auth::user();

        if ($user && $user->tenant_id) {
            return (int) $user->tenant_id;
        }

        return null;
    }

    protected static function qualifyTenantColumn(): string
    {
        $instance = new static;

        return $instance->getTable() . '.tenant_id';
    }
}
