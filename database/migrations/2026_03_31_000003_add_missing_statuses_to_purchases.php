<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ALL_VALUES = [
        // originales
        'PAUSADO_BACK_ORDERS', 'EN_TRANSITO', 'SOLICITUD_GUIA_ALMACEN',
        'FALTA_PAGO_PROVEEDOR', 'FALTA_FACTURA', 'EN_CURSO', 'POR_REVISAR',
        'FALTA_AUTORIZACION', 'RECHAZADO', 'FALTA_ORDEN_SERVICIO', 'CONCLUIDO',
        // nuevos
        'SOLICITADO', 'AUTORIZADO', 'PEDIDO', 'LLEGO', 'ENTREGADO',
    ];

    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE purchases DROP CONSTRAINT IF EXISTS purchases_status_check');
            $list = implode("','", self::ALL_VALUES);
            DB::statement("ALTER TABLE purchases ADD CONSTRAINT purchases_status_check CHECK (status IN ('{$list}'))");
        } else {
            $list = "'" . implode("','", self::ALL_VALUES) . "'";
            DB::statement("ALTER TABLE purchases MODIFY COLUMN status ENUM({$list}) NOT NULL DEFAULT 'SOLICITADO'");
        }
    }

    public function down(): void
    {
        $original = [
            'PAUSADO_BACK_ORDERS', 'EN_TRANSITO', 'SOLICITUD_GUIA_ALMACEN',
            'FALTA_PAGO_PROVEEDOR', 'FALTA_FACTURA', 'EN_CURSO', 'POR_REVISAR',
            'FALTA_AUTORIZACION', 'RECHAZADO', 'FALTA_ORDEN_SERVICIO', 'CONCLUIDO',
        ];

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE purchases DROP CONSTRAINT IF EXISTS purchases_status_check');
            $list = implode("','", $original);
            DB::statement("ALTER TABLE purchases ADD CONSTRAINT purchases_status_check CHECK (status IN ('{$list}'))");
        } else {
            $list = "'" . implode("','", $original) . "'";
            DB::statement("ALTER TABLE purchases MODIFY COLUMN status ENUM({$list}) NOT NULL");
        }
    }
};
