<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rent_item', function (Blueprint $table) {
            $table->decimal('rent', 10, 2)->default(0)->after('area_id');
        });

        // Distribuir la renta global entre los equipos de cada renta existente
        \DB::statement("
            UPDATE rent_item ri
            INNER JOIN rents r ON r.id = ri.rent_id
            INNER JOIN (SELECT rent_id, COUNT(*) AS cnt FROM rent_item GROUP BY rent_id) counts ON counts.rent_id = ri.rent_id
            SET ri.rent = r.rent / counts.cnt
            WHERE r.rent > 0
        ");
    }

    public function down(): void
    {
        Schema::table('rent_item', function (Blueprint $table) {
            $table->dropColumn('rent');
        });
    }
};
