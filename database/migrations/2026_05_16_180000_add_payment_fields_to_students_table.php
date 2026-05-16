<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('payment_voucher_number', 40)->nullable()->after('email');
            $table->string('payment_agency_number', 4)->nullable()->after('payment_voucher_number');
            $table->date('payment_date')->nullable()->after('payment_agency_number');

            $table->unique('payment_voucher_number', 'students_payment_voucher_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_payment_voucher_number_unique');
            $table->dropColumn([
                'payment_voucher_number',
                'payment_agency_number',
                'payment_date',
            ]);
        });
    }
};
