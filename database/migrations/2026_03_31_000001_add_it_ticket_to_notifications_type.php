<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TYPE notifications_type ADD VALUE IF NOT EXISTS 'IT_TICKET'");
        } else {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
                'COBRANZA_VENCIDA','COBRANZA_POR_VENCER','TICKET_URGENTE',
                'COMPRA_PENDIENTE','VACACION_PENDIENTE','RENTA_POR_VENCER',
                'SISTEMA','INFO','IT_TICKET'
            ) NOT NULL");
        }
    }

    public function down(): void
    {
        // Removing an ENUM value safely requires a full column rebuild; skipped.
    }
};
