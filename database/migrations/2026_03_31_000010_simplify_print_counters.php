<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_counters', function (Blueprint $table) {
            // Columnas derivadas del original (se calculan en PHP desde la renta)
            $table->dropColumn([
                'bn_printed', 'bn_included', 'bn_excess',
                'bn_cost_per_page', 'bn_excess_amount',
                'color_printed', 'color_included', 'color_excess',
                'color_cost_per_page', 'color_excess_amount',
                'total_excess_amount',
            ]);
        });

        // Columnas del extender migration que no se pidieron
        if (Schema::hasColumn('print_counters', 'verificado_por')) {
            Schema::table('print_counters', function (Blueprint $table) {
                $table->dropForeign(['verificado_por']);
            });
        }

        $extraCols = [
            'bn_a3_anterior','bn_a3_actual','bn_a3_impreso','bn_a3_incluido',
            'bn_a3_exceso','bn_a3_costo_por_pagina','bn_a3_monto_exceso',
            'color_a3_anterior','color_a3_actual','color_a3_impreso','color_a3_incluido',
            'color_a3_exceso','color_a3_costo_por_pagina','color_a3_monto_exceso',
            'scan_anterior','scan_actual','scan_total',
            'metodo_lectura','verificado','verificado_por',
        ];

        $existing = array_filter($extraCols, fn($c) => Schema::hasColumn('print_counters', $c));
        if ($existing) {
            Schema::table('print_counters', fn(Blueprint $t) => $t->dropColumn(array_values($existing)));
        }
    }

    public function down(): void
    {
        // No reversible — no se reconstruyen columnas derivadas a propósito
    }
};
