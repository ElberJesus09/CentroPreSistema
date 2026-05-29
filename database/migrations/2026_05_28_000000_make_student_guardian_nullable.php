<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['guardian_id']);
            $table->foreignId('guardian_id')->nullable()->change();
            $table->foreign('guardian_id')->references('id')->on('guardians')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['guardian_id']);
            $table->foreignId('guardian_id')->nullable(false)->change();
            $table->foreign('guardian_id')->references('id')->on('guardians')->cascadeOnUpdate()->restrictOnDelete();
        });
    }
};
