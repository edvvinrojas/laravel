<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // CHECK: según billing_type, exactamente uno de rent_id/sale_id debe tener valor.
        // Sintaxis compatible con MariaDB 10.2+ y MySQL 8.0.16+
        DB::statement("
            ALTER TABLE billings
            ADD CONSTRAINT chk_billing_type_fk
            CHECK (
                (billing_type = 'RENTA' AND rent_id IS NOT NULL AND sale_id IS NULL) OR
                (billing_type = 'VENTA' AND sale_id IS NOT NULL AND rent_id IS NULL)
            )
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE billings DROP CONSTRAINT chk_billing_type_fk');
    }
};
