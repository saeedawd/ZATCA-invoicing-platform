<?php

namespace App\Domains\Tenancy\Models;

use App\Domains\Billing\Models\Plan;
use App\Domains\Billing\Models\Subscription;
use App\Domains\Organization\Models\Organization;
use App\Models\User;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name_ar',
        'name_en',
        'slug',
        'status',
        'plan_id',
        'locale',
        'timezone',
        'currency',
    ];

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant): void {
            $tenant->uuid ??= (string) Str::uuid();
            $tenant->slug ??= Str::slug($tenant->name_en ?: $tenant->name_ar).'-'.Str::lower(Str::random(4));
            $tenant->locale ??= 'ar';
            $tenant->timezone ??= 'Asia/Riyadh';
            $tenant->currency ??= 'SAR';
            $tenant->status ??= 'trial';
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function organization(): HasOne
    {
        return $this->hasOne(Organization::class)->withoutGlobalScopes();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany()->whereIn('status', ['trialing', 'active']);
    }
}
