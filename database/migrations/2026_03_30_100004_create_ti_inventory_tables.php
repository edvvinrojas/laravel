<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Equipos internos de la empresa (PCs, laptops, etc.)
        Schema::create('ti_equipment', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_interno', 50)->unique();
            $table->string('marca', 100);
            $table->string('modelo', 150);
            $table->string('numero_serie', 100)->nullable()->unique();
            $table->enum('tipo', ['PC','LAPTOP','SERVIDOR','IMPRESORA','TELEFONO','TABLET','SWITCH','ROUTER','OTRO']);
            $table->string('procesador', 100)->nullable();
            $table->string('ram', 50)->nullable();
            $table->string('almacenamiento', 100)->nullable();
            $table->string('sistema_operativo', 100)->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ubicacion', 200)->nullable();
            $table->enum('status', ['ACTIVO','BAJA','REPARACION','BODEGA'])->default('ACTIVO');
            $table->date('fecha_compra')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Periféricos relacionados con un equipo TI
        Schema::create('ti_peripherals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ti_equipment_id')->constrained('ti_equipment')->cascadeOnDelete();
            $table->enum('tipo', ['MONITOR','TECLADO','MOUSE','CARGADOR','DOCKING','HEADSET','CAMARA','OTRO']);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('numero_serie', 100)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        // Licencias de software
        Schema::create('ti_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('software', 150);
            $table->enum('tipo', ['OFFICE','ANTIVIRUS','OS','OTRO']);
            $table->string('clave_licencia', 255)->nullable();
            $table->string('proveedor', 150)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->integer('cantidad_licencias')->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('notas')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Relación equipo TI ↔ licencias
        Schema::create('ti_equipment_license', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ti_equipment_id')->constrained('ti_equipment')->cascadeOnDelete();
            $table->foreignId('ti_license_id')->constrained('ti_licenses')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ti_equipment_license');
        Schema::dropIfExists('ti_licenses');
        Schema::dropIfExists('ti_peripherals');
        Schema::dropIfExists('ti_equipment');
    }
};
