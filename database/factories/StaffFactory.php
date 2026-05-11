<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Staff>
 */
class StaffFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'mother_last_name' => fake()->lastName(),
            'dni' => fake()->unique()->numerify('########'),
            'phone' => fake()->numerify('9########'),
            'email' => fake()->unique()->safeEmail(),
            'username' => fake()->unique()->userName(),
            'password' => 'password',
            'last_login_at' => null,
            'role_id' => Role::query()->value('id')
                ?? Role::query()->create(['name' => Role::NAME_TRABAJADOR, 'status' => true])->id,
            'status' => true,
            'remember_token' => Str::random(10),
        ];
    }
}
