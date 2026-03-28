<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->enum('report_status', [
                'PENDIENTE', 'LISTO', 'URGENTE', 'PROGRAMADO',
                'INFORMATIVO', 'NO_QUEDO_EN_LA_VISITA', 'ATENCION'
            ])->default('PENDIENTE');
            $table->enum('report_type', [
                'CONECTIVIDAD', 'ATASCO', 'TONER', 'QUEJAS',
                'COPIA', 'RUIDOS', 'IMPRESION', 'OTROS'
            ]);
            $table->text('description');
            $table->string('evidence', 500)->nullable();
            $table->text('corrective_action')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
