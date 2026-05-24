<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_temporary_permission_grants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('granted_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->unique(['staff_id', 'permission_id']);
            $table->index(['staff_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_temporary_permission_grants');
    }
};
