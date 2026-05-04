<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spareparts', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->nullable()->after('supplier');
            $table->decimal('total_price', 10, 2)->nullable()->after('unit_price');
            $table->string('invoice_number', 100)->nullable()->after('total_price');
        });
    }

    public function down(): void
    {
        Schema::table('spareparts', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'total_price', 'invoice_number']);
        });
    }
};
