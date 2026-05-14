<?php

namespace Database\Seeders;

use App\Models\AcademicCycle;
use App\Models\AcademicCycleShift;
use App\Models\Campus;
use App\Models\Shift;
use Illuminate\Database\Seeder;

/**
 * Ciclo y programación mínima para demos (cupos altos para semillas de alumnos).
 */
class AcademicDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $cycle = AcademicCycle::query()->updateOrCreate(
            ['name' => 'CEPRE Demo 2026'],
            [
                'status' => true,
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
            ]
        );

        $campus = Campus::query()->orderBy('id')->first();
        $shift = Shift::query()->orderBy('id')->first();

        if ($campus === null || $shift === null) {
            return;
        }

        AcademicCycleShift::query()->updateOrCreate(
            [
                'academic_cycle_id' => $cycle->id,
                'campus_id' => $campus->id,
                'shift_id' => $shift->id,
            ],
            [
                'capacity' => 10_000,
                'enrolled' => 0,
                'status' => true,
            ]
        );
    }
}
