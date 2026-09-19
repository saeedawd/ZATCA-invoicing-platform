<?php

namespace Database\Seeders;

use App\Domains\Tenancy\Services\TenantRegistrar;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PlanSeeder::class);

        app(TenantRegistrar::class)->register([
            'name' => 'مدير تجريبي',
            'company_name' => 'شركة تجريبية',
            'email' => 'admin@zatca.test',
            'phone' => '0500000000',
            'password' => 'password',
        ]);
    }
}
