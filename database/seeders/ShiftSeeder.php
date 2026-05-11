<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Morning', 'Afternoon', 'Evening'];

        foreach ($names as $name) {
            Shift::query()->updateOrCreate(
                ['name' => $name],
                ['name' => $name, 'status' => true]
            );
        }
    }
}
