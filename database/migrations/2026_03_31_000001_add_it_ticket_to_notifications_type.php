<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const VALUES = [
        'COBRANZA_VENCIDA', 'COBRANZA_POR_VENCER', 'TICKET_URGENTE',
        'COMPRA_PENDIENTE', 'VACACION_PENDIENTE', 'RENTA_POR_VENCER',
        'SISTEMA', 'INFO', 'IT_TICKET',
    ];

    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // Drop the old CHECK constraint and add a new one with IT_TICKET
            DB::statement('ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check');
            $list = implode("','", self::VALUES);
            DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_type_check CHECK (type IN ('{$list}'))");
        } else {
            $list = "'" . implode("','", self::VALUES) . "'";
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM({$list}) NOT NULL DEFAULT 'INFO'");
        }
    }

    public function down(): void
    {
        $original = [
            'COBRANZA_VENCIDA', 'COBRANZA_POR_VENCER', 'TICKET_URGENTE',
            'COMPRA_PENDIENTE', 'VACACION_PENDIENTE', 'RENTA_POR_VENCER',
            'SISTEMA', 'INFO',
        ];

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check');
            $list = implode("','", $original);
            DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_type_check CHECK (type IN ('{$list}'))");
        } else {
            $list = "'" . implode("','", $original) . "'";
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM({$list}) NOT NULL DEFAULT 'INFO'");
        }
    }
};
