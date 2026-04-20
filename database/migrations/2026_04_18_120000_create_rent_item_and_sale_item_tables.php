<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rent_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rent_id')->constrained('rents')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['rent_id', 'item_id']);
            $table->index('item_id');
        });

        Schema::create('sale_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['sale_id', 'item_id']);
            $table->index('item_id');
        });

        DB::statement('INSERT INTO rent_item (rent_id, item_id, created_at, updated_at) SELECT id, item_id, NOW(), NOW() FROM rents WHERE item_id IS NOT NULL');
        DB::statement('INSERT INTO sale_item (sale_id, item_id, created_at, updated_at) SELECT id, item_id, NOW(), NOW() FROM sales WHERE item_id IS NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_item');
        Schema::dropIfExists('rent_item');
    }
};
