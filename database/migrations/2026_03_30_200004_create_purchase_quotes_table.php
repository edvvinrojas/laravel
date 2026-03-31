<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->string('supplier_name', 255);
            $table->decimal('cost', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'supplier1_name', 'supplier1_cost',
                'supplier2_name', 'supplier2_cost',
                'supplier3_name', 'supplier3_cost',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_quotes');

        Schema::table('purchases', function (Blueprint $table) {
            $table->string('supplier1_name')->nullable();
            $table->decimal('supplier1_cost', 10, 2)->nullable();
            $table->string('supplier2_name')->nullable();
            $table->decimal('supplier2_cost', 10, 2)->nullable();
            $table->string('supplier3_name')->nullable();
            $table->decimal('supplier3_cost', 10, 2)->nullable();
        });
    }
};
