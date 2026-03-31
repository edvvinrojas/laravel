<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE repairs MODIFY procedencia VARCHAR(255) NOT NULL DEFAULT 'BODEGA'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE repairs MODIFY procedencia ENUM('BODEGA','ASIGNADO','VENDIDO','DESCONOCIDO') NOT NULL");
    }
};
