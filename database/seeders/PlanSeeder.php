<?php

namespace Database\Seeders;

use App\Domains\Billing\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::query()->upsert([
            [
                'code' => 'starter',
                'name_ar' => 'الباقة الأساسية',
                'name_en' => 'Starter',
                'price_monthly' => 99,
                'max_users' => 3,
                'max_invoices_monthly' => 200,
                'max_devices' => 1,
                'is_active' => true,
                'features' => json_encode(['sales', 'purchases', 'zatca_sandbox']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'business',
                'name_ar' => 'باقة الأعمال',
                'name_en' => 'Business',
                'price_monthly' => 249,
                'max_users' => 10,
                'max_invoices_monthly' => 2000,
                'max_devices' => 3,
                'is_active' => true,
                'features' => json_encode(['sales', 'purchases', 'zatca_production', 'reports']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['code'], [
            'name_ar', 'name_en', 'price_monthly', 'max_users', 'max_invoices_monthly',
            'max_devices', 'is_active', 'features', 'updated_at',
        ]);
    }
}
