<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MariaDB no permite CHECK constraints sobre columnas con FK (error 1901).
        // La integridad se garantiza con triggers BEFORE INSERT y BEFORE UPDATE.
        DB::unprepared("
            CREATE TRIGGER trg_billings_bi
            BEFORE INSERT ON billings
            FOR EACH ROW
            BEGIN
                IF NOT (
                    (NEW.billing_type = 'RENTA' AND NEW.rent_id IS NOT NULL AND NEW.sale_id IS NULL) OR
                    (NEW.billing_type = 'VENTA' AND NEW.sale_id IS NOT NULL AND NEW.rent_id IS NULL)
                ) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'billing_type must match exactly one of rent_id or sale_id';
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_billings_bu
            BEFORE UPDATE ON billings
            FOR EACH ROW
            BEGIN
                IF NOT (
                    (NEW.billing_type = 'RENTA' AND NEW.rent_id IS NOT NULL AND NEW.sale_id IS NULL) OR
                    (NEW.billing_type = 'VENTA' AND NEW.sale_id IS NOT NULL AND NEW.rent_id IS NULL)
                ) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'billing_type must match exactly one of rent_id or sale_id';
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_billings_bi');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_billings_bu');
    }
};
