<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sku_formats', function (Blueprint $table) {
            $table->id();
            $table->string('category', 30)->unique();
            $table->string('label', 50);
            $table->string('prefix', 20)->default('');
            $table->tinyInteger('pad')->unsigned()->default(3);
            $table->integer('last_number')->unsigned()->default(0);
            $table->timestamps();
        });

        // Seed default categories
        $now = now();
        DB::table('sku_formats')->insert([
            ['category' => 'EQUIPO',        'label' => 'Equipos',        'prefix' => 'EQ-',  'pad' => 3, 'last_number' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['category' => 'PRODUCTO',      'label' => 'Productos',      'prefix' => 'PRD-', 'pad' => 3, 'last_number' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['category' => 'ACCESORIO',     'label' => 'Accesorios',     'prefix' => 'ACC-', 'pad' => 3, 'last_number' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['category' => 'CONSUMIBLE',    'label' => 'Consumibles',    'prefix' => 'CON-', 'pad' => 3, 'last_number' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['category' => 'REFACCION',     'label' => 'Refacciones',    'prefix' => 'REF-', 'pad' => 3, 'last_number' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['category' => 'TI_EQUIPO',     'label' => 'Equipos TI',     'prefix' => 'TI-',  'pad' => 3, 'last_number' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['category' => 'TI_PERIFERICO', 'label' => 'Periféricos TI', 'prefix' => 'PER-', 'pad' => 3, 'last_number' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['category' => 'OTRO',          'label' => 'Otro',           'prefix' => 'OTR-', 'pad' => 3, 'last_number' => 0, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::dropIfExists('skus');
    }

    public function down(): void
    {
        Schema::dropIfExists('sku_formats');
    }
};
