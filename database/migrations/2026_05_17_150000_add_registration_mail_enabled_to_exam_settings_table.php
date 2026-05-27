<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_settings', function (Blueprint $table): void {
            $table->boolean('registration_mail_enabled')->default(false)->after('institutional_message');
        });
    }

    public function down(): void
    {
        Schema::table('exam_settings', function (Blueprint $table): void {
            $table->dropColumn('registration_mail_enabled');
        });
    }
};
