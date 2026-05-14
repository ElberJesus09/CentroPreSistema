<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'Morning' => 'Mañana',
            'Afternoon' => 'Tarde',
            'Evening' => 'Noche',
        ];

        foreach ($map as $legacy => $label) {
            $row = Shift::query()->where('name', $legacy)->first();
            if ($row !== null) {
                $row->update(['name' => $label, 'status' => true]);

                continue;
            }
            Shift::query()->updateOrCreate(
                ['name' => $label],
                ['name' => $label, 'status' => true]
            );
        }
    }
}
