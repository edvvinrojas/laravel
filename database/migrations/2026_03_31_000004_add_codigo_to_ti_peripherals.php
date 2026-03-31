<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ti_peripherals', function (Blueprint $table) {
            $table->string('codigo', 20)->nullable()->unique()->after('ti_equipment_id');
        });

        // Expand tipo enum to include ELIMINADOR
        $driver = DB::getDriverName();
        $types  = ['MONITOR','TECLADO','MOUSE','CARGADOR','DOCKING','HEADSET','CAMARA','ELIMINADOR','OTRO'];

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE ti_peripherals DROP CONSTRAINT IF EXISTS ti_peripherals_tipo_check');
            $list = implode("','", $types);
            DB::statement("ALTER TABLE ti_peripherals ADD CONSTRAINT ti_peripherals_tipo_check CHECK (tipo IN ('{$list}'))");
        } else {
            $list = "'" . implode("','", $types) . "'";
            DB::statement("ALTER TABLE ti_peripherals MODIFY COLUMN tipo ENUM({$list}) NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::table('ti_peripherals', function (Blueprint $table) {
            $table->dropColumn('codigo');
        });

        $driver  = DB::getDriverName();
        $originals = ['MONITOR','TECLADO','MOUSE','CARGADOR','DOCKING','HEADSET','CAMARA','OTRO'];

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE ti_peripherals DROP CONSTRAINT IF EXISTS ti_peripherals_tipo_check');
            $list = implode("','", $originals);
            DB::statement("ALTER TABLE ti_peripherals ADD CONSTRAINT ti_peripherals_tipo_check CHECK (tipo IN ('{$list}'))");
        } else {
            $list = "'" . implode("','", $originals) . "'";
            DB::statement("ALTER TABLE ti_peripherals MODIFY COLUMN tipo ENUM({$list}) NOT NULL");
        }
    }
};
