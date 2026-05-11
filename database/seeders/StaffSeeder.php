<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::query()->where('name', Role::NAME_SUPER_ADMIN)->firstOrFail();

        // Plain password; el modelo aplica cast hashed al persistir.
        $password = (string) (env('SUPERADMIN_INITIAL_PASSWORD') ?: 'CentroPre!2026Secure');

        Staff::query()->firstOrCreate(
            ['username' => 'superadmin'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'mother_last_name' => 'System',
                'dni' => '00000001',
                'phone' => '999999999',
                'email' => 'superadmin@system.local',
                'password' => $password,
                'role_id' => $role->id,
                'status' => true,
            ]
        );
    }
}
