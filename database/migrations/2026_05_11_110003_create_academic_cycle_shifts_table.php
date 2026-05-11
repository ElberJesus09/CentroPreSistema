<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_cycle_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_cycle_id')->constrained('academic_cycles')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('enrolled')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['academic_cycle_id', 'campus_id', 'shift_id'], 'academic_cycle_shifts_cycle_campus_shift_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_cycle_shifts');
    }
};
