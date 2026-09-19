<?php

namespace App\Domains\Tenancy\Services;

use App\Domains\Billing\Models\Plan;
use App\Domains\Billing\Models\Subscription;
use App\Domains\Organization\Models\Organization;
use App\Domains\Tenancy\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantRegistrar
{
    /**
     * @param  array{name:string,email:string,password:string,company_name:string,phone?:string}  $data
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $plan = Plan::query()->where('code', 'starter')->first()
                ?? Plan::query()->where('is_active', true)->first();

            $tenant = Tenant::create([
                'name_ar' => $data['company_name'],
                'name_en' => $data['company_name'],
                'plan_id' => $plan?->id,
                'status' => 'trial',
            ]);

            Organization::create([
                'tenant_id' => $tenant->id,
                'legal_name_ar' => $data['company_name'],
                'country' => 'SA',
                'is_vat_registered' => true,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            if ($plan) {
                Subscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'status' => 'trialing',
                    'starts_at' => now(),
                    'trial_ends_at' => now()->addDays(14),
                ]);
            }

            return User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'role' => 'owner',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
        });
    }
}
