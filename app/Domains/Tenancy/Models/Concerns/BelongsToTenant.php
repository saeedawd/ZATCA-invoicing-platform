<?php

namespace App\Domains\Tenancy\Models\Concerns;

use App\Domains\Tenancy\Models\Tenant;
use App\Domains\Tenancy\Scopes\TenantScope;
use App\Domains\Tenancy\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            if (! empty($model->tenant_id)) {
                return;
            }

            $tenantId = TenantContext::id() ?? auth()->user()?->tenant_id;

            if ($tenantId) {
                $model->tenant_id = $tenantId;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $field ??= $this->getRouteKeyName();

        $query = static::query()->where($field, $value);

        if (! TenantContext::id() && auth()->check()) {
            $query->where($this->getTable().'.tenant_id', auth()->user()->tenant_id);
        }

        return $query->first();
    }
}
