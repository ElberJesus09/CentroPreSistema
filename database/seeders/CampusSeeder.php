<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        Campus::query()->updateOrCreate(
            ['name' => 'Centro Preuniversitario Juan Francisco Aguinaga Castro'],
            [
                'address' => 'Av. José Leonardo Ortiz 405, Chiclayo, Peru',
                'status' => true,
            ],
        );
    }
}
