<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'ticket_code')) {
                $table->string('ticket_code', 20)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('tickets', 'priority')) {
                $table->enum('priority', ['URGENTE', 'NORMAL', 'BAJA'])->default('NORMAL')->after('report_status');
            }
            if (!Schema::hasColumn('tickets', 'item_id')) {
                $table->foreignId('item_id')->nullable()->after('area_id')->constrained('items')->nullOnDelete();
            }
        });

        // Extender el enum report_type para incluir MANCHAS y ESCANEO
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tickets MODIFY COLUMN report_type ENUM(
                'CONECTIVIDAD','ATASCO','TONER','QUEJAS','COPIA','RUIDOS',
                'IMPRESION','MANCHAS','ESCANEO','OTROS'
            ) NOT NULL");
        }

        // Generar códigos para tickets existentes que no tengan ticket_code
        $year = date('Y');
        $existing = DB::table('tickets')->whereNull('ticket_code')->orderBy('id')->get(['id']);
        $counter = 1;
        foreach ($existing as $row) {
            DB::table('tickets')
                ->where('id', $row->id)
                ->update(['ticket_code' => sprintf('TKT-%s-%04d', $year, $counter++)]);
        }
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'item_id')) {
                $table->dropConstrainedForeignId('item_id');
            }
            if (Schema::hasColumn('tickets', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::hasColumn('tickets', 'ticket_code')) {
                $table->dropColumn('ticket_code');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tickets MODIFY COLUMN report_type ENUM(
                'CONECTIVIDAD','ATASCO','TONER','QUEJAS','COPIA','RUIDOS',
                'IMPRESION','OTROS'
            ) NOT NULL");
        }
    }
};
