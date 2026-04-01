<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skus', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->string('description', 255)->nullable();
            $table->enum('category', [
                'EQUIPO', 'PRODUCTO', 'ACCESORIO', 'CONSUMIBLE',
                'REFACCION', 'TI_EQUIPO', 'TI_PERIFERICO', 'OTRO',
            ])->default('EQUIPO');
            $table->boolean('is_used')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skus');
    }
};
