<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_requests', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 20)->unique();
            $table->foreignId('user_id')->constrained('users');                          // solicitante
            $table->enum('category', [
                'EMAIL', 'INTERNET', 'HARDWARE', 'SOFTWARE',
                'IMPRESORA', 'TELEFONO', 'ACCESOS', 'OTRO'
            ]);
            $table->string('title', 255);
            $table->text('description');
            $table->enum('priority', ['BAJA', 'MEDIA', 'ALTA', 'URGENTE'])->default('MEDIA');
            $table->enum('status', ['ABIERTO', 'EN_PROCESO', 'RESUELTO', 'CERRADO'])->default('ABIERTO');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_requests');
    }
};
