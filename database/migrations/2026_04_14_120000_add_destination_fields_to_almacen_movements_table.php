<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('almacen_movements', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('inventory_id')->constrained('clients')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->after('client_id')->constrained('branches')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->after('branch_id')->constrained('areas')->nullOnDelete();

            $table->index('client_id');
            $table->index('branch_id');
            $table->index('area_id');
        });
    }

    public function down(): void
    {
        Schema::table('almacen_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('area_id');
            $table->dropConstrainedForeignId('branch_id');
            $table->dropConstrainedForeignId('client_id');
        });
    }
};