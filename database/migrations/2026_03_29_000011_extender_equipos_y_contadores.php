<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Extender tabla items (equipos) ───────────────────────────────────
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('categoria_id')->nullable()->after('brand_id')
                  ->constrained('categorias_equipo')->nullOnDelete();
            $table->foreignId('modelo_id')->nullable()->after('categoria_id')
                  ->constrained('modelos_equipo')->nullOnDelete();
            $table->enum('tipo_equipo', ['copiadora','impresora','mfp','escaner','fax','plotter'])
                  ->default('mfp')->after('type');
            $table->enum('formato_max', ['A4','A3'])->default('A4')->after('tipo_equipo');
            $table->date('fecha_compra')->nullable()->after('cost');
            $table->date('fecha_instalacion')->nullable()->after('fecha_compra');
            $table->date('fecha_garantia_fin')->nullable()->after('fecha_instalacion');
            $table->string('ubicacion_fisica', 255)->nullable()->after('location_status');
            $table->unsignedInteger('contador_inicial_bn')->default(0)->after('ubicacion_fisica');
            $table->unsignedInteger('contador_inicial_color')->default(0)->after('contador_inicial_bn');
            $table->unsignedInteger('contador_inicial_scan')->default(0)->after('contador_inicial_color');
        });

        // ── Extender contadores de impresión ────────────────────────────────
        Schema::table('print_counters', function (Blueprint $table) {
            // A3 B/N
            $table->integer('bn_a3_anterior')->default(0)->after('bn_excess_amount');
            $table->integer('bn_a3_actual')->default(0)->after('bn_a3_anterior');
            $table->integer('bn_a3_impreso')->default(0)->after('bn_a3_actual');
            $table->unsignedInteger('bn_a3_incluido')->default(0)->after('bn_a3_impreso');
            $table->integer('bn_a3_exceso')->default(0)->after('bn_a3_incluido');
            $table->decimal('bn_a3_costo_por_pagina', 10, 4)->default(0)->after('bn_a3_exceso');
            $table->decimal('bn_a3_monto_exceso', 10, 2)->default(0)->after('bn_a3_costo_por_pagina');
            // A3 Color
            $table->integer('color_a3_anterior')->default(0)->after('color_excess_amount');
            $table->integer('color_a3_actual')->default(0)->after('color_a3_anterior');
            $table->integer('color_a3_impreso')->default(0)->after('color_a3_actual');
            $table->unsignedInteger('color_a3_incluido')->default(0)->after('color_a3_impreso');
            $table->integer('color_a3_exceso')->default(0)->after('color_a3_incluido');
            $table->decimal('color_a3_costo_por_pagina', 10, 4)->default(0)->after('color_a3_exceso');
            $table->decimal('color_a3_monto_exceso', 10, 2)->default(0)->after('color_a3_costo_por_pagina');
            // Escaneos
            $table->integer('scan_anterior')->default(0)->after('color_a3_monto_exceso');
            $table->integer('scan_actual')->default(0)->after('scan_anterior');
            $table->integer('scan_total')->default(0)->after('scan_actual');
            // Control de lectura
            $table->enum('metodo_lectura', ['presencial','email','snmp','cliente','estimado'])
                  ->default('presencial')->after('scan_total');
            $table->boolean('verificado')->default(false)->after('metodo_lectura');
            $table->foreignId('verificado_por')->nullable()->after('verificado')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('print_counters', function (Blueprint $table) {
            $table->dropForeign(['verificado_por']);
            $table->dropColumn([
                'bn_a3_anterior','bn_a3_actual','bn_a3_impreso','bn_a3_incluido',
                'bn_a3_exceso','bn_a3_costo_por_pagina','bn_a3_monto_exceso',
                'color_a3_anterior','color_a3_actual','color_a3_impreso','color_a3_incluido',
                'color_a3_exceso','color_a3_costo_por_pagina','color_a3_monto_exceso',
                'scan_anterior','scan_actual','scan_total',
                'metodo_lectura','verificado','verificado_por',
            ]);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropForeign(['modelo_id']);
            $table->dropColumn([
                'categoria_id','modelo_id','tipo_equipo','formato_max',
                'fecha_compra','fecha_instalacion','fecha_garantia_fin',
                'ubicacion_fisica',
                'contador_inicial_bn','contador_inicial_color','contador_inicial_scan',
            ]);
        });
    }
};
