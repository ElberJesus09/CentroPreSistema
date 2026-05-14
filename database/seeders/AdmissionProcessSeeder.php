<?php

namespace Database\Seeders;

use App\Models\AdmissionProcess;
use Illuminate\Database\Seeder;

/**
 * Procesos de inscripción institucionales (histórico y vigente).
 */
class AdmissionProcessSeeder extends Seeder
{
    public function run(): void
    {
        AdmissionProcess::query()->updateOrCreate(
            ['name' => 'Proceso de Inscripción CEPRE 2025-I'],
            [
                'start_date' => '2025-01-10',
                'end_date' => '2025-03-20',
                'status' => AdmissionProcess::STATUS_FINALIZADO,
                'description' => 'Proceso ordinario de inscripción correspondiente al ciclo 2025-I del Centro Preuniversitario Juan Francisco Aguinaga Castro.',
            ]
        );

        AdmissionProcess::query()->updateOrCreate(
            ['name' => 'Proceso de Inscripción CEPRE 2025-II'],
            [
                'start_date' => '2025-04-01',
                'end_date' => '2025-06-30',
                'status' => AdmissionProcess::STATUS_FINALIZADO,
                'description' => 'Segundo proceso ordinario de inscripción del año 2025 (CEPRE 2025-II), Centro Preuniversitario Juan Francisco Aguinaga Castro.',
            ]
        );

        AdmissionProcess::query()->updateOrCreate(
            ['name' => 'Proceso de Inscripción CEPRE 2026-I'],
            [
                'start_date' => '2026-05-18',
                'end_date' => '2026-08-10',
                'status' => AdmissionProcess::STATUS_ACTIVO,
                'description' => 'Proceso oficial de inscripción para postulantes al Centro Preuniversitario Juan Francisco Aguinaga Castro de la Universidad Nacional Pedro Ruiz Gallo.',
            ]
        );
    }
}
