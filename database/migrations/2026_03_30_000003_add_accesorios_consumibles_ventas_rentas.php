<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_accesorio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('accesorio_id')->constrained('accesorios')->cascadeOnDelete();
            $table->integer('cantidad')->default(1);
            $table->string('notas', 255)->nullable();
            $table->timestamps();
            $table->unique(['sale_id', 'accesorio_id']);
        });

        Schema::create('sale_consumible', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('consumible_id')->constrained('consumibles')->cascadeOnDelete();
            $table->integer('cantidad')->default(1);
            $table->string('notas', 255)->nullable();
            $table->timestamps();
            $table->unique(['sale_id', 'consumible_id']);
        });

        Schema::create('rent_accesorio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rent_id')->constrained('rents')->cascadeOnDelete();
            $table->foreignId('accesorio_id')->constrained('accesorios')->cascadeOnDelete();
            $table->integer('cantidad')->default(1);
            $table->string('notas', 255)->nullable();
            $table->timestamps();
            $table->unique(['rent_id', 'accesorio_id']);
        });

        Schema::create('rent_consumible', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rent_id')->constrained('rents')->cascadeOnDelete();
            $table->foreignId('consumible_id')->constrained('consumibles')->cascadeOnDelete();
            $table->integer('cantidad')->default(1);
            $table->string('notas', 255)->nullable();
            $table->timestamps();
            $table->unique(['rent_id', 'consumible_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_consumible');
        Schema::dropIfExists('rent_accesorio');
        Schema::dropIfExists('sale_consumible');
        Schema::dropIfExists('sale_accesorio');
    }
};
