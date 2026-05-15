<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Indices para listados administrativos con miles de alumnos. */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! $this->indexExists('students', 'students_registration_date_index')) {
                $table->index('registration_date', 'students_registration_date_index');
            }

            if (! $this->indexExists('students', 'students_admin_process_recent_index')) {
                $table->index(
                    ['admission_process_id', 'registration_date', 'id'],
                    'students_admin_process_recent_index'
                );
            }

            if (! $this->indexExists('students', 'students_admin_cycle_recent_index')) {
                $table->index(
                    ['academic_cycle_id', 'registration_date', 'id'],
                    'students_admin_cycle_recent_index'
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if ($this->indexExists('students', 'students_admin_cycle_recent_index')) {
                $table->dropIndex('students_admin_cycle_recent_index');
            }

            if ($this->indexExists('students', 'students_admin_process_recent_index')) {
                $table->dropIndex('students_admin_process_recent_index');
            }

            if ($this->indexExists('students', 'students_registration_date_index')) {
                $table->dropIndex('students_registration_date_index');
            }
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
