<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engineer_id')->constrained('users');
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->enum('tipo_orden', ['PREVENTIVO','CORRECTIVO','ENTREGA','INSTALACION','CAMBIO_EQUIPO','DIGITALIZACION','INSTALACION_DRIVERS']);
            $table->json('se_reviso')->nullable();
            $table->text('diagnostico_accion')->nullable();
            $table->boolean('entrego_toner')->default(false);
            $table->string('codigos_toner')->nullable();
            $table->unsignedTinyInteger('pct_toner_negro')->nullable();
            $table->unsignedTinyInteger('pct_toner_cyan')->nullable();
            $table->unsignedTinyInteger('pct_toner_magenta')->nullable();
            $table->unsignedTinyInteger('pct_toner_amarillo')->nullable();
            $table->string('evidencia_foto')->nullable();
            $table->text('pendiente_material')->nullable();
            $table->boolean('tiene_stock')->default(false);
            $table->string('foto_stock')->nullable();
            $table->string('firma_nombre')->nullable();
            $table->text('firma_imagen')->nullable();
            $table->boolean('queda_pendiente')->default(false);
            $table->text('descripcion_pendiente')->nullable();
            $table->string('pagina_estado_foto')->nullable();
            $table->enum('status', ['PENDIENTE','COMPLETADO'])->default('PENDIENTE');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
