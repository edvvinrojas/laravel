<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rent_item', function (Blueprint $table) {
            $table->unsignedInteger('contador_inicial_bn')->default(0)->after('area_id');
            $table->unsignedInteger('contador_inicial_color')->default(0)->after('contador_inicial_bn');
        });

        DB::statement('UPDATE rent_item ri INNER JOIN rents r ON r.id = ri.rent_id SET ri.contador_inicial_bn = COALESCE(r.contador_inicial_bn, 0), ri.contador_inicial_color = COALESCE(r.contador_inicial_color, 0)');
    }

    public function down(): void
    {
        Schema::table('rent_item', function (Blueprint $table) {
            $table->dropColumn(['contador_inicial_bn', 'contador_inicial_color']);
        });
    }
};
