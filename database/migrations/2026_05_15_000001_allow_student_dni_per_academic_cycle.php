<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'academic_cycle_id')) {
                $table->foreignId('academic_cycle_id')
                    ->nullable()
                    ->after('career_id')
                    ->constrained('academic_cycles')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            }
        });

        DB::table('students')
            ->whereNull('academic_cycle_id')
            ->update([
                'academic_cycle_id' => DB::raw(
                    '(select academic_cycle_shifts.academic_cycle_id from academic_cycle_shifts where academic_cycle_shifts.id = students.academic_cycle_shift_id)'
                ),
            ]);

        Schema::table('students', function (Blueprint $table) {
            if ($this->indexExists('students', 'students_dni_unique')) {
                $table->dropUnique('students_dni_unique');
            }

            if (! $this->indexExists('students', 'students_dni_index')) {
                $table->index('dni', 'students_dni_index');
            }

            if (! $this->indexExists('students', 'students_dni_academic_cycle_unique')) {
                $table->unique(['dni', 'academic_cycle_id'], 'students_dni_academic_cycle_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if ($this->indexExists('students', 'students_dni_academic_cycle_unique')) {
                $table->dropUnique('students_dni_academic_cycle_unique');
            }

            if ($this->indexExists('students', 'students_dni_index')) {
                $table->dropIndex('students_dni_index');
            }

            $table->unique('dni', 'students_dni_unique');
            $table->dropConstrainedForeignId('academic_cycle_id');
        });
    }

    private function indexExists(string $table, string $name): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['name'] ?? null) === $name) {
                return true;
            }
        }

        return false;
    }
};
