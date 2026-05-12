<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Indices para listados y consultas publicas de cupos. */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->index('academic_cycle_shift_id');
            $table->index('career_id');
        });

        Schema::table('academic_cycle_shifts', function (Blueprint $table) {
            $table->index(['status', 'academic_cycle_id']);
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['academic_cycle_shift_id']);
            $table->dropIndex(['career_id']);
        });

        Schema::table('academic_cycle_shifts', function (Blueprint $table) {
            $table->dropIndex(['status', 'academic_cycle_id']);
        });
    }
};
