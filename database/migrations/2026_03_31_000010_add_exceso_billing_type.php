<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: solo agregar valor al enum si el tipo existe
            $typeExists = DB::selectOne("SELECT 1 FROM pg_type WHERE typname = 'billing_type_enum'");
            if ($typeExists) {
                DB::unprepared("ALTER TYPE billing_type_enum ADD VALUE IF NOT EXISTS 'EXCESO'");
            }

            // Verificar si la columna usa CHECK constraint en vez de enum type
            $checkExists = DB::selectOne("
                SELECT 1 FROM information_schema.check_constraints
                WHERE constraint_name LIKE '%billing_type%'
            ");
            if ($checkExists) {
                // Reemplazar CHECK constraint para incluir EXCESO
                $constraintName = DB::selectOne("
                    SELECT con.conname FROM pg_constraint con
                    JOIN pg_class rel ON rel.oid = con.conrelid
                    WHERE rel.relname = 'billings' AND con.contype = 'c'
                    AND pg_get_constraintdef(con.oid) LIKE '%billing_type%'
                ")?->conname;

                if ($constraintName) {
                    DB::unprepared("ALTER TABLE billings DROP CONSTRAINT \"{$constraintName}\"");
                    DB::unprepared("ALTER TABLE billings ADD CONSTRAINT \"{$constraintName}\" CHECK (billing_type::text = ANY (ARRAY['RENTA','VENTA','EXCESO']))");
                }
            }

            // Recrear función de trigger para permitir EXCESO
            DB::unprepared("
                CREATE OR REPLACE FUNCTION fn_check_billing_type_fk()
                RETURNS TRIGGER AS \$\$
                BEGIN
                    IF NOT (
                        (NEW.billing_type = 'RENTA'  AND NEW.rent_id IS NOT NULL AND NEW.sale_id IS NULL)  OR
                        (NEW.billing_type = 'VENTA'  AND NEW.sale_id IS NOT NULL AND NEW.rent_id IS NULL)  OR
                        (NEW.billing_type = 'EXCESO' AND NEW.rent_id IS NOT NULL AND NEW.sale_id IS NULL)
                    ) THEN
                        RAISE EXCEPTION 'billing_type must match its required FK';
                    END IF;
                    RETURN NEW;
                END;
                \$\$ LANGUAGE plpgsql;
            ");
        } else {
            // MySQL / MariaDB: modificar el ENUM
            DB::unprepared("ALTER TABLE billings MODIFY COLUMN billing_type ENUM('RENTA','VENTA','EXCESO') NOT NULL");

            // Eliminar triggers viejos y recrear con soporte EXCESO
            DB::unprepared('DROP TRIGGER IF EXISTS trg_billings_bi');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_billings_bu');

            DB::unprepared("
                CREATE TRIGGER trg_billings_bi
                BEFORE INSERT ON billings
                FOR EACH ROW
                BEGIN
                    IF NOT (
                        (NEW.billing_type = 'RENTA'  AND NEW.rent_id IS NOT NULL AND NEW.sale_id IS NULL)  OR
                        (NEW.billing_type = 'VENTA'  AND NEW.sale_id IS NOT NULL AND NEW.rent_id IS NULL)  OR
                        (NEW.billing_type = 'EXCESO' AND NEW.rent_id IS NOT NULL AND NEW.sale_id IS NULL)
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'billing_type must match its required FK';
                    END IF;
                END
            ");

            DB::unprepared("
                CREATE TRIGGER trg_billings_bu
                BEFORE UPDATE ON billings
                FOR EACH ROW
                BEGIN
                    IF NOT (
                        (NEW.billing_type = 'RENTA'  AND NEW.rent_id IS NOT NULL AND NEW.sale_id IS NULL)  OR
                        (NEW.billing_type = 'VENTA'  AND NEW.sale_id IS NOT NULL AND NEW.rent_id IS NULL)  OR
                        (NEW.billing_type = 'EXCESO' AND NEW.rent_id IS NOT NULL AND NEW.sale_id IS NULL)
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'billing_type must match its required FK';
                    END IF;
                END
            ");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // No se puede revertir fácilmente un ADD VALUE en PostgreSQL
        } else {
            DB::unprepared("ALTER TABLE billings MODIFY COLUMN billing_type ENUM('RENTA','VENTA') NOT NULL");

            DB::unprepared('DROP TRIGGER IF EXISTS trg_billings_bi');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_billings_bu');

            DB::unprepared("
                CREATE TRIGGER trg_billings_bi BEFORE INSERT ON billings FOR EACH ROW
                BEGIN
                    IF NOT (
                        (NEW.billing_type = 'RENTA' AND NEW.rent_id IS NOT NULL AND NEW.sale_id IS NULL) OR
                        (NEW.billing_type = 'VENTA' AND NEW.sale_id IS NOT NULL AND NEW.rent_id IS NULL)
                    ) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'billing_type must match its required FK';
                    END IF;
                END
            ");

            DB::unprepared("
                CREATE TRIGGER trg_billings_bu BEFORE UPDATE ON billings FOR EACH ROW
                BEGIN
                    IF NOT (
                        (NEW.billing_type = 'RENTA' AND NEW.rent_id IS NOT NULL AND NEW.sale_id IS NULL) OR
                        (NEW.billing_type = 'VENTA' AND NEW.sale_id IS NOT NULL AND NEW.rent_id IS NULL)
                    ) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'billing_type must match its required FK';
                    END IF;
                END
            ");
        }
    }
};
