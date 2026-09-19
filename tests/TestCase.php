<?php

namespace Tests;

use App\Domains\Tenancy\Support\TenantContext;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::clear();

        if ($this->app->runningUnitTests()) {
            try {
                $this->seed(PlanSeeder::class);
            } catch (\Throwable) {
                // Migrations may not have run yet for some unit tests.
            }
        }
    }

    protected function tearDown(): void
    {
        TenantContext::clear();
        parent::tearDown();
    }
}
