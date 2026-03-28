<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('item_name')->unique()->index();
            $table->text('description')->nullable();
            $table->enum('item_type', ['TONER', 'REFACCION']);
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->enum('color', ['K', 'C', 'M', 'Y'])->nullable();
            $table->text('usage')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shelves', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->enum('section', ['SECCION_1', 'SECCION_2', 'SECCION_3', 'SECCION_4', 'SECCION_5', 'SECCION_6']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 100)->unique()->index();
            $table->foreignId('catalog_id')->constrained('item_catalog')->cascadeOnDelete();
            $table->enum('section', ['SECCION_1', 'SECCION_2', 'SECCION_3', 'SECCION_4', 'SECCION_5', 'SECCION_6']);
            $table->foreignId('shelf_id')->nullable()->constrained('shelves')->nullOnDelete();
            $table->enum('quality', ['ORIGINAL', 'GENERICO', 'REPARADO', 'NUEVA', 'USADO', 'NA']);
            $table->timestamp('entry_date')->useCurrent();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('invoice', 100)->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->text('comments')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('item_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_id')->unique()->constrained('item_catalog')->cascadeOnDelete();
            $table->integer('stock_min')->default(0);
            $table->integer('stock_max')->default(0);
            $table->timestamps();
        });

        Schema::create('inventory_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 10)->unique();
            $table->integer('current_value')->default(0);
            $table->timestamps();
        });

        Schema::create('inventory_equipment', function (Blueprint $table) {
            $table->foreignId('inventory_id')->constrained('inventory')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->primary(['inventory_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_equipment');
        Schema::dropIfExists('inventory_sequences');
        Schema::dropIfExists('item_stock');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('shelves');
        Schema::dropIfExists('item_catalog');
    }
};
