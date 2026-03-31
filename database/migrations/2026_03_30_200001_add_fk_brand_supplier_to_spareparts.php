<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spareparts', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('brand')
                  ->constrained('brands')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->after('supplier')
                  ->constrained('suppliers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('spareparts', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['brand_id', 'supplier_id']);
        });
    }
};
