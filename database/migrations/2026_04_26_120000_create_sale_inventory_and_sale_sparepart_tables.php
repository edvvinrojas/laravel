<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_inventory', function (Blueprint $table) {
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained('inventory')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['sale_id', 'inventory_id']);
        });

        Schema::create('sale_sparepart', function (Blueprint $table) {
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('sparepart_id')->constrained('spareparts')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['sale_id', 'sparepart_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_sparepart');
        Schema::dropIfExists('sale_inventory');
    }
};
