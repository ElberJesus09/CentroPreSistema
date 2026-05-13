<?php

namespace Database\Seeders;

use App\Models\ExamSetting;
use Illuminate\Database\Seeder;

class ExamSettingSeeder extends Seeder
{
    public function run(): void
    {
        ExamSetting::singleton();
    }
}
