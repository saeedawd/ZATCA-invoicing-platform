<?php

namespace Database\Factories;

use App\Domains\Billing\Models\Plan;
use App\Domains\Organization\Models\Organization;
use App\Domains\Tenancy\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'owner',
            'status' => 'active',
            'remember_token' => Str::random(10),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            if (! $user->tenant->organization) {
                Organization::create([
                    'tenant_id' => $user->tenant_id,
                    'legal_name_ar' => $user->tenant->name_ar,
                    'country' => 'SA',
                    'is_vat_registered' => true,
                ]);

                $user->tenant->unsetRelation('organization');
            }
        });
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
