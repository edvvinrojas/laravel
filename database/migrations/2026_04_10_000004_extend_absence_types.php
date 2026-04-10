<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE absences MODIFY COLUMN absence_type ENUM('ENFERMEDAD','AUSENTISMO','PERMISO_PERSONAL','SALIDA_TEMPRANA','LLEGADA_TARDE','OTRO') NOT NULL");
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE absences DROP CONSTRAINT IF EXISTS absences_absence_type_check");
            DB::statement("ALTER TABLE absences ADD CONSTRAINT absences_absence_type_check CHECK (absence_type IN ('ENFERMEDAD','AUSENTISMO','PERMISO_PERSONAL','SALIDA_TEMPRANA','LLEGADA_TARDE','OTRO'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE absences MODIFY COLUMN absence_type ENUM('ENFERMEDAD','AUSENTISMO','PERMISO_PERSONAL','OTRO') NOT NULL");
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE absences DROP CONSTRAINT IF EXISTS absences_absence_type_check");
            DB::statement("ALTER TABLE absences ADD CONSTRAINT absences_absence_type_check CHECK (absence_type IN ('ENFERMEDAD','AUSENTISMO','PERMISO_PERSONAL','OTRO'))");
        }
    }
};
