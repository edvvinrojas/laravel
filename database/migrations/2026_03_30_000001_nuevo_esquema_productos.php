<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Quitar FKs y columnas del esquema anterior en items
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'categoria_id')) {
                try { $table->dropForeign(['categoria_id']); } catch (\Throwable) {}
                $table->dropColumn('categoria_id');
            }
            if (Schema::hasColumn('items', 'modelo_id')) {
                try { $table->dropForeign(['modelo_id']); } catch (\Throwable) {}
                $table->dropColumn('modelo_id');
            }
            if (Schema::hasColumn('items', 'tipo_equipo')) {
                $table->dropColumn('tipo_equipo');
            }
            if (Schema::hasColumn('items', 'formato_max')) {
                $table->dropColumn('formato_max');
            }
        });

        // 2. Eliminar tablas del esquema anterior (orden: hijos primero)
        Schema::dropIfExists('compatibilidad_consumible_modelo');
        Schema::dropIfExists('catalogo_consumibles');
        Schema::dropIfExists('tipos_consumible');
        Schema::dropIfExists('modelos_equipo');
        Schema::dropIfExists('categorias_equipo');

        // 3. Crear tabla PRODUCTOS (catálogo maestro)
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('codigo', 50)->unique();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->enum('categoria', [
                'COPIADORA','IMPRESORA','MFP','ESCANER','FAX','PLOTTER','OTRO'
            ])->default('OTRO');
            $table->enum('tipo_color', ['MONOCROMO','COLOR','AMBOS'])->nullable();
            $table->enum('formato_max', ['A4','A3','CARTA','OFICIO','A2','A1','A0'])->nullable();
            $table->text('descripcion')->nullable();
            $table->decimal('precio_venta', 10, 2)->nullable();
            $table->decimal('precio_renta', 10, 2)->nullable();
            $table->boolean('es_activo')->default(true);
            $table->timestamps();
        });

        // 4. Agregar producto_id a items (equipos físicos)
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('producto_id')->nullable()->after('brand_id')
                  ->constrained('productos')->nullOnDelete();
        });

        // 5. Crear tabla ACCESORIOS (componentes opcionales del producto)
        Schema::create('accesorios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('codigo', 50)->unique();
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2)->nullable();
            $table->boolean('es_activo')->default(true);
            $table->timestamps();
        });

        // 6. Pivot producto ↔ accesorio
        Schema::create('producto_accesorio', function (Blueprint $table) {
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('accesorio_id')->constrained('accesorios')->cascadeOnDelete();
            $table->boolean('es_incluido')->default(false)->comment('viene incluido de fábrica');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->primary(['producto_id', 'accesorio_id']);
        });

        // 7. Crear tabla CONSUMIBLES (tóner, tambores, etc.)
        Schema::create('consumibles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('codigo_oem', 100)->unique();
            $table->string('codigo_alternativo', 100)->nullable();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->enum('tipo', [
                'TONER','DRUM','KIT_MANTENIMIENTO','FUSOR','RODILLO','TINTA','OTRO'
            ])->default('TONER');
            $table->enum('color', [
                'NEGRO','CYAN','MAGENTA','AMARILLO','TRICOLOR','NA'
            ])->nullable();
            $table->integer('rendimiento_paginas')->nullable();
            $table->boolean('es_original')->default(true);
            $table->text('descripcion')->nullable();
            $table->boolean('es_activo')->default(true);
            $table->timestamps();
        });

        // 8. Pivot producto ↔ consumible
        Schema::create('producto_consumible', function (Blueprint $table) {
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('consumible_id')->constrained('consumibles')->cascadeOnDelete();
            $table->boolean('es_oficial')->default(true);
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->primary(['producto_id', 'consumible_id']);
        });

        // 9. Crear tabla STOCK (disponibilidad separada del registro del producto)
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['PRODUCTO', 'ACCESORIO', 'CONSUMIBLE']);
            $table->unsignedBigInteger('referencia_id');
            $table->integer('cantidad_disponible')->default(0);
            $table->integer('cantidad_minima')->default(0);
            $table->decimal('costo', 10, 2)->nullable();
            $table->string('ubicacion', 200)->nullable();
            $table->timestamps();
            $table->unique(['tipo', 'referencia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock');
        Schema::dropIfExists('producto_consumible');
        Schema::dropIfExists('consumibles');
        Schema::dropIfExists('producto_accesorio');
        Schema::dropIfExists('accesorios');

        Schema::table('items', function (Blueprint $table) {
            try { $table->dropForeign(['producto_id']); } catch (\Throwable) {}
            if (Schema::hasColumn('items', 'producto_id')) {
                $table->dropColumn('producto_id');
            }
        });

        Schema::dropIfExists('productos');
    }
};
