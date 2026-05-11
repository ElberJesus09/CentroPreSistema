<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (
            [
                Role::NAME_SUPER_ADMIN,
                Role::NAME_ADMIN,
                Role::NAME_TRABAJADOR,
            ] as $name
        ) {
            Role::query()->firstOrCreate(
                ['name' => $name],
                ['status' => true]
            );
        }
    }
}
