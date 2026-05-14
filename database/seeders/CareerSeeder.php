<?php

namespace Database\Seeders;

use App\Models\Career;
use Illuminate\Database\Seeder;

/**
 * Catálogo oficial de carreras (UNPRG / CPU). Idempotente por código.
 */
class CareerSeeder extends Seeder
{
    public function run(): void
    {
        $rows = $this->officialCareers();
        $codes = array_column($rows, 'code');

        foreach ($rows as $row) {
            Career::query()->updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'status' => true,
                ]
            );
        }

        // Quita códigos que ya no pertenecen al catálogo oficial (p. ej. semillas de demo), si no tienen postulantes.
        Career::query()
            ->whereNotIn('code', $codes)
            ->whereDoesntHave('students')
            ->delete();
    }

    /**
     * @return list<array{code: string, name: string}>
     */
    private function officialCareers(): array
    {
        return [
            ['code' => 'ADM', 'name' => 'ADMINISTRACIÓN'],
            ['code' => 'AGR', 'name' => 'AGRONOMÍA'],
            ['code' => 'ARK', 'name' => 'ARQUEOLOGÍA'],
            ['code' => 'ARC', 'name' => 'ARQUITECTURA'],
            ['code' => 'ARP', 'name' => 'ARTE - ARTES PLÁSTICAS'],
            ['code' => 'ARD', 'name' => 'ARTE - DANZAS'],
            ['code' => 'ARM', 'name' => 'ARTE - MÚSICA'],
            ['code' => 'ART', 'name' => 'ARTE - TEATRO'],
            ['code' => 'CPO', 'name' => 'CIENCIA POLÍTICA'],
            ['code' => 'CBB', 'name' => 'CIENCIAS BIOLÓGICAS - BIOLOGÍA'],
            ['code' => 'CBT', 'name' => 'CIENCIAS BIOLÓGICAS - BOTÁNICA'],
            ['code' => 'CBM', 'name' => 'CIENCIAS BIOLÓGICAS - MICROBIOLOGÍA - PARASITOLOGÍA'],
            ['code' => 'CBP', 'name' => 'CIENCIAS BIOLÓGICAS - PESQUERÍA'],
            ['code' => 'CDC', 'name' => 'CIENCIAS DE LA COMUNICACIÓN'],
            ['code' => 'CNI', 'name' => 'COMERCIO Y NEGOCIOS INTERNACIONALES'],
            ['code' => 'CON', 'name' => 'CONTABILIDAD'],
            ['code' => 'DER', 'name' => 'DERECHO'],
            ['code' => 'ECO', 'name' => 'ECONOMÍA'],
            ['code' => 'EHS', 'name' => 'EDUCACIÓN - CIENCIAS HIST. SOC. Y FILOSOFÍA'],
            ['code' => 'ECN', 'name' => 'EDUCACIÓN - CIENCIAS NATURALES'],
            ['code' => 'EEF', 'name' => 'EDUCACIÓN - EDUCACIÓN FÍSICA'],
            ['code' => 'EIE', 'name' => 'EDUCACIÓN - IDIOMAS EXTRANJEROS'],
            ['code' => 'EIN', 'name' => 'EDUCACIÓN - INICIAL'],
            ['code' => 'ELL', 'name' => 'EDUCACIÓN - LENGUA Y LITERATURA'],
            ['code' => 'EMC', 'name' => 'EDUCACIÓN - MATEMÁTICA Y COMPUTACIÓN'],
            ['code' => 'EPR', 'name' => 'EDUCACIÓN - PRIMARIA'],
            ['code' => 'ENF', 'name' => 'ENFERMERÍA'],
            ['code' => 'EST', 'name' => 'ESTADÍSTICA'],
            ['code' => 'FIS', 'name' => 'FÍSICA'],
            ['code' => 'IAG', 'name' => 'INGENIERÍA AGRÍCOLA'],
            ['code' => 'ICV', 'name' => 'INGENIERÍA CIVIL'],
            ['code' => 'IIA', 'name' => 'INGENIERÍA DE INDUSTRIAS ALIMENTARIAS'],
            ['code' => 'ISI', 'name' => 'INGENIERÍA DE SISTEMAS'],
            ['code' => 'IEL', 'name' => 'INGENIERÍA ELECTRÓNICA'],
            ['code' => 'ICI', 'name' => 'INGENIERÍA EN COMPUTACIÓN E INFORMÁTICA'],
            ['code' => 'IME', 'name' => 'INGENIERÍA MECÁNICA Y ELÉCTRICA'],
            ['code' => 'IQU', 'name' => 'INGENIERÍA QUÍMICA'],
            ['code' => 'IZO', 'name' => 'INGENIERÍA ZOOTECNIA'],
            ['code' => 'MAT', 'name' => 'MATEMÁTICAS'],
            ['code' => 'MED', 'name' => 'MEDICINA HUMANA'],
            ['code' => 'MVE', 'name' => 'MEDICINA VETERINARIA'],
            ['code' => 'PSI', 'name' => 'PSICOLOGÍA'],
            ['code' => 'SOC', 'name' => 'SOCIOLOGÍA'],
        ];
    }
}
