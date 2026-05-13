<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_mail_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('channel', 64);
            $table->string('status', 32);
            $table->text('error_message')->nullable();
            $table->foreignId('triggered_by_staff_id')->nullable()->constrained('staff')->nullOnDelete()->cascadeOnUpdate();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_mail_logs');
    }
};
