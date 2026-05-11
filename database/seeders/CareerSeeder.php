<?php

namespace Database\Seeders;

use App\Models\Career;
use Illuminate\Database\Seeder;

class CareerSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Engineering', 'code' => 'ENG', 'status' => true],
            ['name' => 'Medicine', 'code' => 'MED', 'status' => true],
            ['name' => 'Architecture', 'code' => 'ARC', 'status' => true],
        ];

        foreach ($rows as $row) {
            Career::query()->updateOrCreate(
                ['code' => $row['code']],
                $row
            );
        }
    }
}
