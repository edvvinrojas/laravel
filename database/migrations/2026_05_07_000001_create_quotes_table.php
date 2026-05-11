<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('quote_number', 50)->unique()->index();
            $table->enum('status', ['BORRADOR', 'ENVIADA', 'APROBADA', 'RECHAZADA'])->default('BORRADOR');
            $table->text('notes')->nullable();
            $table->date('valid_until')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('quote_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->enum('product_type', ['item', 'sparepart', 'inventory'])->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('description', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['product_type', 'product_id']);
        });

        // Agregar COTIZACION al enum de notificaciones
        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
                'COBRANZA_VENCIDA','COBRANZA_POR_VENCER','TICKET_URGENTE',
                'COMPRA_PENDIENTE','VACACION_PENDIENTE','RENTA_POR_VENCER',
                'SISTEMA','INFO','IT_TICKET','compra','ausentismo','vacaciones',
                'COTIZACION'
            ) NOT NULL DEFAULT 'INFO'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_lines');
        Schema::dropIfExists('quotes');
    }
};
