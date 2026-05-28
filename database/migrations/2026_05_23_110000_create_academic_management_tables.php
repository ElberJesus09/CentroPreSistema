<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('classrooms')) {
            Schema::create('classrooms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('academic_cycle_id')->constrained('academic_cycles')->cascadeOnUpdate()->restrictOnDelete();
                $table->string('name');
                $table->string('code', 32);
                $table->unsignedSmallInteger('floor')->default(1);
                $table->unsignedSmallInteger('capacity');
                $table->boolean('status')->default(true);
                $table->unsignedSmallInteger('academic_priority');
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['academic_cycle_id', 'code'], 'classrooms_cycle_code_unique');
                $table->index(['academic_cycle_id', 'status', 'academic_priority'], 'classrooms_cycle_status_priority_index');
            });
        }

        if (! Schema::hasTable('student_classroom_assignments')) {
            Schema::create('student_classroom_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('academic_cycle_id')->constrained('academic_cycles')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->cascadeOnUpdate()->restrictOnDelete();
                $table->decimal('placement_score', 5, 2)->nullable();
                $table->boolean('distribution_locked')->default(false);
                $table->foreignId('assigned_by')->nullable()->constrained('staff')->nullOnDelete();
                $table->timestamp('assigned_at')->nullable();
                $table->timestamps();

                $table->unique(['student_id', 'academic_cycle_id'], 'sca_student_cycle_unique');
                $table->index(['academic_cycle_id', 'classroom_id'], 'sca_cycle_classroom_index');
                $table->index(['academic_cycle_id', 'placement_score'], 'sca_cycle_score_index');
            });
        }

        if (! Schema::hasTable('classroom_movements')) {
            Schema::create('classroom_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('academic_cycle_id')->constrained('academic_cycles')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreignId('from_classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
                $table->foreignId('to_classroom_id')->constrained('classrooms')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreignId('moved_by')->nullable()->constrained('staff')->nullOnDelete();
                $table->string('reason', 500)->nullable();
                $table->timestamps();

                $table->index(['academic_cycle_id', 'student_id', 'created_at'], 'cm_cycle_student_created_index');
            });
        }

        if (! Schema::hasTable('evaluations')) {
            Schema::create('evaluations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('academic_cycle_id')->constrained('academic_cycles')->cascadeOnUpdate()->restrictOnDelete();
                $table->string('name');
                $table->string('type', 40)->default('regular');
                $table->decimal('weight', 6, 2)->default(1);
                $table->boolean('counts_for_average')->default(true);
                $table->unsignedTinyInteger('rounding_decimals')->default(2);
                $table->boolean('status')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('staff')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['academic_cycle_id', 'name'], 'evaluations_cycle_name_unique');
                $table->index(['academic_cycle_id', 'type', 'status'], 'evaluations_cycle_type_status_index');
            });
        }

        if (! Schema::hasTable('grades')) {
            Schema::create('grades', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('evaluation_id')->constrained('evaluations')->cascadeOnUpdate()->cascadeOnDelete();
                $table->decimal('score', 5, 2);
                $table->string('observations', 500)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('staff')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['student_id', 'evaluation_id']);
                $table->index(['evaluation_id', 'score']);
                $table->index(['student_id', 'score']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('classroom_movements');
        Schema::dropIfExists('student_classroom_assignments');
        Schema::dropIfExists('classrooms');
    }
};
