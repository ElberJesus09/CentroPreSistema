<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('mother_last_name');
            $table->string('dni', 8);
            $table->date('birth_date');
            $table->string('gender', 16);
            $table->string('phone', 9);
            $table->text('address');
            $table->string('email');
            $table->date('registration_date');
            $table->foreignId('guardian_id')->constrained('guardians')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('career_id')->constrained('careers')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('academic_cycle_id')->constrained('academic_cycles')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('academic_cycle_shift_id')->constrained('academic_cycle_shifts')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('status', 32)->default('pending');
            $table->timestamps();

            $table->unique(['dni', 'academic_cycle_id'], 'students_dni_academic_cycle_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
