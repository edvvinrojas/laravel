<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items');
            $table->string('model');
            $table->string('serie');
            $table->string('model_toner');
            $table->timestamp('fecha_alta')->useCurrent();
            $table->enum('procedencia', ['BODEGA', 'ASIGNADO', 'VENDIDO', 'DESCONOCIDO']);
            $table->enum('estado_taller', ['PENDIENTE', 'PAUSADO', 'LISTO'])->default('PENDIENTE');
            $table->timestamp('fecha_conclusion')->nullable();
            $table->string('folio_escaneado')->nullable();
            $table->string('foto_evidencia')->nullable();
            $table->enum('ubicacion', ['ZONA_1', 'ZONA_2', 'ZONA_3', 'ZONA_4', 'BASURA'])->nullable();
            $table->enum('proceso', ['DESCONOCIDO', 'PROCESO_1', 'PROCESO_2', 'PROCESO_3'])->default('DESCONOCIDO');
            $table->enum('estatus', [
                'EN_ESPERA_AUTORIZACION', 'EN_ESPERA_PIEZA', 'PAUSADO', 'LISTO'
            ])->default('EN_ESPERA_AUTORIZACION');
            $table->text('diagnostico_inicial')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
