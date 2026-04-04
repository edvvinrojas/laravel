<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                $cols = [
                    'fecha_compra',
                    'fecha_instalacion',
                    'fecha_garantia_fin',
                    'ubicacion_fisica',
                    'contador_inicial_bn',
                    'contador_inicial_color',
                    'contador_inicial_scan',
                    'direccion_ip',
                    'mac_address',
                ];

                $existing = array_filter($cols, fn ($c) => Schema::hasColumn('items', $c));
                if (!empty($existing)) {
                    $table->dropColumn($existing);
                }
            });
        }

        if (Schema::hasTable('spareparts')) {
            Schema::table('spareparts', function (Blueprint $table) {
                foreach (['brand_id', 'supplier_id', 'shelf_id'] as $fk) {
                    if (Schema::hasColumn('spareparts', $fk)) {
                        try {
                            $table->dropForeign([$fk]);
                        } catch (\Throwable) {
                            // Ignorar si no existe la FK por estado previo.
                        }
                    }
                }

                $cols = ['brand_id', 'supplier_id', 'internal_code', 'shelf_id'];
                $existing = array_filter($cols, fn ($c) => Schema::hasColumn('spareparts', $c));
                if (!empty($existing)) {
                    $table->dropColumn($existing);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (!Schema::hasColumn('items', 'fecha_compra')) {
                    $table->date('fecha_compra')->nullable()->after('cost');
                }
                if (!Schema::hasColumn('items', 'fecha_instalacion')) {
                    $table->date('fecha_instalacion')->nullable()->after('fecha_compra');
                }
                if (!Schema::hasColumn('items', 'fecha_garantia_fin')) {
                    $table->date('fecha_garantia_fin')->nullable()->after('fecha_instalacion');
                }
                if (!Schema::hasColumn('items', 'ubicacion_fisica')) {
                    $table->string('ubicacion_fisica', 255)->nullable()->after('location_status');
                }
                if (!Schema::hasColumn('items', 'contador_inicial_bn')) {
                    $table->unsignedInteger('contador_inicial_bn')->default(0)->after('ubicacion_fisica');
                }
                if (!Schema::hasColumn('items', 'contador_inicial_color')) {
                    $table->unsignedInteger('contador_inicial_color')->default(0)->after('contador_inicial_bn');
                }
                if (!Schema::hasColumn('items', 'contador_inicial_scan')) {
                    $table->unsignedInteger('contador_inicial_scan')->default(0)->after('contador_inicial_color');
                }
                if (!Schema::hasColumn('items', 'direccion_ip')) {
                    $table->string('direccion_ip', 45)->nullable()->after('contador_inicial_scan');
                }
                if (!Schema::hasColumn('items', 'mac_address')) {
                    $table->string('mac_address', 17)->nullable()->after('direccion_ip');
                }
            });
        }

        if (Schema::hasTable('spareparts')) {
            Schema::table('spareparts', function (Blueprint $table) {
                if (!Schema::hasColumn('spareparts', 'brand_id')) {
                    $table->unsignedBigInteger('brand_id')->nullable()->after('brand');
                }
                if (!Schema::hasColumn('spareparts', 'internal_code')) {
                    $table->string('internal_code', 120)->nullable()->unique()->after('code');
                }
                if (!Schema::hasColumn('spareparts', 'supplier_id')) {
                    $table->unsignedBigInteger('supplier_id')->nullable()->after('supplier');
                }
                if (!Schema::hasColumn('spareparts', 'shelf_id')) {
                    $table->unsignedBigInteger('shelf_id')->nullable()->after('internal_code');
                }
            });
        }
    }
};
