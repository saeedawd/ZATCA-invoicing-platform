<?php

namespace Tests\Feature;

use App\Domains\Catalog\Models\Party;
use App\Domains\Tenancy\Support\TenantContext;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    public function test_party_queries_are_scoped_to_current_tenant(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        TenantContext::set($userA->tenant);
        Party::create([
            'tenant_id' => $userA->tenant_id,
            'type' => 'customer',
            'name' => 'عميل أ',
        ]);

        TenantContext::set($userB->tenant);
        Party::create([
            'tenant_id' => $userB->tenant_id,
            'type' => 'customer',
            'name' => 'عميل ب',
        ]);

        TenantContext::set($userA->tenant);
        $this->assertSame(1, Party::count());
        $this->assertSame('عميل أ', Party::first()->name);

        TenantContext::set($userB->tenant);
        $this->assertSame(1, Party::count());
        $this->assertSame('عميل ب', Party::first()->name);
    }
}
