<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('almacen_movements', function (Blueprint $table) {
            $table->id();
            $table->enum('movement_type', ['SALIDA', 'ENTRADA']);
            $table->foreignId('equipment_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('inventory_id')->nullable()->constrained('inventory')->nullOnDelete();
            $table->string('person_name', 120);
            $table->text('reason');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['movement_type', 'created_at']);
            $table->index('person_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('almacen_movements');
    }
};
