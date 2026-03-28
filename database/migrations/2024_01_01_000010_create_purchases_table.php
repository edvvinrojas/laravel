<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sparepart_id')->nullable()->constrained('spareparts')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('name');
            $table->integer('amount');
            $table->integer('authorized_amount')->nullable();
            $table->string('quality', 100)->nullable();
            $table->text('justification')->nullable();
            $table->enum('type', ['INTERNA', 'VENTA'])->default('INTERNA');
            $table->string('supplier1_name')->nullable();
            $table->decimal('supplier1_cost', 10, 2)->nullable();
            $table->string('supplier2_name')->nullable();
            $table->decimal('supplier2_cost', 10, 2)->nullable();
            $table->string('supplier3_name')->nullable();
            $table->decimal('supplier3_cost', 10, 2)->nullable();
            $table->foreignId('authorized_by_area_chief_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('authorized_by_area_chief_date')->nullable();
            $table->foreignId('authorized_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('authorized_by_admin_date')->nullable();
            $table->string('quotation_file', 500)->nullable();
            $table->string('supplier_payment_file', 500)->nullable();
            $table->string('supplier_invoice_file', 500)->nullable();
            $table->boolean('is_paid')->nullable()->default(null);
            $table->string('shipping_method', 100)->nullable();
            $table->decimal('shipping_cost', 10, 2)->nullable();
            $table->string('shipping_code', 100)->nullable()->index();
            $table->enum('status', [
                'PAUSADO_BACK_ORDERS', 'EN_TRANSITO', 'SOLICITUD_GUIA_ALMACEN',
                'FALTA_PAGO_PROVEEDOR', 'FALTA_FACTURA', 'EN_CURSO', 'POR_REVISAR',
                'FALTA_AUTORIZACION', 'RECHAZADO', 'FALTA_ORDEN_SERVICIO', 'CONCLUIDO'
            ])->default('EN_CURSO')->index();
            $table->text('comments')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
