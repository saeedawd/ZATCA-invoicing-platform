<?php

namespace Tests\Feature\Auth;

use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        $response = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('company_name', 'Test Company')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('organization.edit', absolute: false));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('tenants', ['name_ar' => 'Test Company']);
        $this->assertDatabaseHas('organizations', ['legal_name_ar' => 'Test Company']);
    }
}
