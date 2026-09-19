<?php

namespace Database\Factories;

use App\Domains\Billing\Models\Plan;
use App\Domains\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'uuid' => (string) Str::uuid(),
            'name_ar' => $name,
            'name_en' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'status' => 'trial',
            'plan_id' => Plan::query()->first()?->id,
            'locale' => 'ar',
            'timezone' => 'Asia/Riyadh',
            'currency' => 'SAR',
        ];
    }
}
