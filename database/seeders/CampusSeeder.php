<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Sede Central', 'address' => null, 'status' => true],
            ['name' => 'Sede Norte', 'address' => null, 'status' => true],
        ];

        foreach ($rows as $row) {
            Campus::query()->updateOrCreate(
                ['name' => $row['name']],
                $row
            );
        }
    }
}
