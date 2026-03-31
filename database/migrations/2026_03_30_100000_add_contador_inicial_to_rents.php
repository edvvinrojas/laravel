<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rents', function (Blueprint $table) {
            $table->integer('contador_inicial_bn')->default(0)->after('print_notes');
            $table->integer('contador_inicial_color')->default(0)->after('contador_inicial_bn');
        });
    }

    public function down(): void
    {
        Schema::table('rents', function (Blueprint $table) {
            $table->dropColumn(['contador_inicial_bn', 'contador_inicial_color']);
        });
    }
};
