<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Categorías de equipo ──────────────────────────────────────────
        Schema::create('categorias_equipo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();          // Copiadora, Impresora, MFP…
            $table->string('codigo', 20)->unique();           // COPIA, IMPRE, MFP, SCAN, FAX
            $table->text('descripcion')->nullable();
            $table->boolean('es_activo')->default(true);
            $table->timestamps();
        });

        // ── 2. Catálogo de modelos de equipo ─────────────────────────────────
        Schema::create('modelos_equipo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marca_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias_equipo')->cascadeOnDelete();
            $table->string('nombre_modelo', 255);             // MP 2554, ECOSYS M3145idn
            $table->string('nombre_comercial', 255)->nullable();
            $table->enum('tipo_color', ['monocromo', 'color'])->default('monocromo');
            $table->enum('tecnologia', ['laser', 'inkjet', 'led', 'thermal'])->default('laser');
            $table->enum('formato_max', ['A4', 'A3', 'A2'])->default('A4');
            $table->unsignedSmallInteger('velocidad_bn_ppm')->nullable();   // páginas/min B/N
            $table->unsignedSmallInteger('velocidad_color_ppm')->nullable();
            $table->unsignedInteger('vida_util_paginas')->nullable();
            $table->boolean('tiene_escaner')->default(false);
            $table->boolean('tiene_fax')->default(false);
            $table->boolean('tiene_duplex')->default(true);
            $table->boolean('tiene_red')->default(true);
            $table->boolean('tiene_wifi')->default(false);
            $table->text('descripcion')->nullable();
            $table->boolean('es_activo')->default(true);
            $table->timestamps();

            $table->index(['marca_id', 'nombre_modelo']);
        });

        // ── 3. Tipos de consumible ───────────────────────────────────────────
        Schema::create('tipos_consumible', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();     // Tóner, Tambor, Kit Mantenimiento…
            $table->string('codigo', 30)->unique();      // TONER, TAMBOR, KIT_MTO, FUSOR…
            $table->text('descripcion')->nullable();
        });

        // ── 4. Catálogo de consumibles ───────────────────────────────────────
        Schema::create('catalogo_consumibles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_id')->constrained('tipos_consumible')->cascadeOnDelete();
            $table->foreignId('marca_id')->constrained('brands')->cascadeOnDelete();
            $table->string('codigo_oem', 100);               // TK-1175, DR-3455, MK-1170
            $table->string('codigo_alternativo', 100)->nullable();
            $table->string('nombre', 255);
            $table->enum('color', ['K', 'C', 'M', 'Y', 'CMYK', 'NA'])->default('K');
            $table->unsignedInteger('rendimiento_paginas')->nullable();     // yield OEM
            $table->unsignedInteger('rendimiento_paginas_alt')->nullable(); // yield genérico
            $table->boolean('es_original')->default(true);                 // OEM vs genérico
            $table->text('descripcion')->nullable();
            $table->boolean('es_activo')->default(true);
            $table->timestamps();

            $table->index('codigo_oem');
            $table->index(['tipo_id', 'marca_id']);
        });

        // ── 5. Compatibilidad consumible ↔ modelo ───────────────────────────
        Schema::create('compatibilidad_consumible_modelo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumible_id')->constrained('catalogo_consumibles')->cascadeOnDelete();
            $table->foreignId('modelo_id')->constrained('modelos_equipo')->cascadeOnDelete();
            $table->boolean('es_oficial')->default(true);   // OEM confirmado vs equivalente
            $table->string('notas', 255)->nullable();
            $table->timestamps();

            $table->unique(['consumible_id', 'modelo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compatibilidad_consumible_modelo');
        Schema::dropIfExists('catalogo_consumibles');
        Schema::dropIfExists('tipos_consumible');
        Schema::dropIfExists('modelos_equipo');
        Schema::dropIfExists('categorias_equipo');
    }
};
