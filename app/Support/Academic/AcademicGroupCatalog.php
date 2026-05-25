<?php

namespace App\Support\Academic;

class AcademicGroupCatalog
{
    /** @return array<string, string> */
    public static function groups(): array
    {
        return [
            'economic' => 'Ciencias Economicas',
            'medical' => 'Ciencias Medicas',
            'engineering' => 'Ciencias e Ingenierias',
            'agricultural' => 'Ingenierias Agropecuarias',
            'social' => 'Ciencias Sociales',
            'law_politics' => 'Ciencia Politica y Derecho',
        ];
    }

    /** @return array<string, list<string>> */
    public static function careerCodesByGroup(): array
    {
        return [
            'economic' => ['ADM', 'CON', 'ECO', 'CNI'],
            'medical' => ['CBB', 'CBT', 'CBM', 'CBP', 'ENF', 'MED', 'MVE'],
            'engineering' => ['ICV', 'ISI', 'ARC', 'MAT', 'FIS', 'EST', 'IEL', 'ICI', 'IME', 'IQU', 'IIA'],
            'agricultural' => ['AGR', 'IAG', 'IZO'],
            'social' => ['EIN', 'EPR', 'EHS', 'ECN', 'EMC', 'ELL', 'EIE', 'EEF', 'ARP', 'ARD', 'ARM', 'ART', 'SOC', 'CDC', 'PSI', 'ARK'],
            'law_politics' => ['DER', 'CPO'],
        ];
    }

    public static function groupForCareerCode(?string $code): ?string
    {
        if ($code === null || $code === '') {
            return null;
        }

        $code = mb_strtoupper($code);
        foreach (self::careerCodesByGroup() as $group => $codes) {
            if (in_array($code, $codes, true)) {
                return $group;
            }
        }

        return null;
    }

    public static function groupNameForCareerCode(?string $code): string
    {
        $group = self::groupForCareerCode($code);

        return $group === null ? 'Sin grupo academico' : self::groups()[$group];
    }
}
