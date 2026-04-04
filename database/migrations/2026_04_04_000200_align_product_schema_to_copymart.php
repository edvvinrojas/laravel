<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Regresar items al esquema de CopyMartERP (sin producto_id).
        if (Schema::hasTable('items') && Schema::hasColumn('items', 'producto_id')) {
            Schema::table('items', function (Blueprint $table) {
                try {
                    $table->dropForeign(['producto_id']);
                } catch (\Throwable) {
                    // Ignorar si la FK no existe por estado previo.
                }

                $table->dropColumn('producto_id');
            });
        }

        // Eliminar pivotes agregados en Laravel que no existen en CopyMartERP.
        Schema::dropIfExists('rent_consumible');
        Schema::dropIfExists('rent_accesorio');
        Schema::dropIfExists('sale_consumible');
        Schema::dropIfExists('sale_accesorio');

        Schema::dropIfExists('producto_consumible');
        Schema::dropIfExists('producto_accesorio');

        // Eliminar tablas de catalogo de producto agregadas en Laravel
        // que no existen en CopyMartERP.
        Schema::dropIfExists('stock');
        Schema::dropIfExists('consumibles');
        Schema::dropIfExists('accesorios');
        Schema::dropIfExists('productos');
    }

    public function down(): void
    {
        // Rollback minimo: restaurar solo la columna en items.
        // Las tablas eliminadas se recrean con sus migraciones originales si se requiere.
        if (Schema::hasTable('items') && !Schema::hasColumn('items', 'producto_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->unsignedBigInteger('producto_id')->nullable()->after('brand_id');
            });
        }
    }
};
