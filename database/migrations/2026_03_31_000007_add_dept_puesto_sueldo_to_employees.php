<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('departamento', 100)->nullable()->after('nombre');
            $table->string('puesto', 150)->nullable()->after('departamento');
            $table->decimal('sueldo', 10, 2)->nullable()->after('puesto');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['departamento', 'puesto', 'sueldo']);
        });
    }
};
