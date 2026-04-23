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
            $table->foreignId('branch_id')->nullable()->after('item_id')->constrained('branches')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->after('branch_id')->constrained('areas')->nullOnDelete();
            $table->index(['branch_id', 'area_id']);
        });

        Schema::table('sale_item', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('item_id')->constrained('branches')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->after('branch_id')->constrained('areas')->nullOnDelete();
            $table->index(['branch_id', 'area_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('UPDATE rent_item ri SET branch_id = r.branch_id, area_id = r.area_id FROM rents r WHERE r.id = ri.rent_id AND ri.branch_id IS NULL AND ri.area_id IS NULL');
            DB::statement('UPDATE sale_item si SET branch_id = s.branch_id, area_id = s.area_id FROM sales s WHERE s.id = si.sale_id AND si.branch_id IS NULL AND si.area_id IS NULL');
        } else {
            DB::statement('UPDATE rent_item ri INNER JOIN rents r ON r.id = ri.rent_id SET ri.branch_id = r.branch_id, ri.area_id = r.area_id WHERE ri.branch_id IS NULL AND ri.area_id IS NULL');
            DB::statement('UPDATE sale_item si INNER JOIN sales s ON s.id = si.sale_id SET si.branch_id = s.branch_id, si.area_id = s.area_id WHERE si.branch_id IS NULL AND si.area_id IS NULL');
        }
    }

    public function down(): void
    {
        Schema::table('sale_item', function (Blueprint $table) {
            $table->dropIndex('sale_item_branch_id_area_id_index');
            $table->dropConstrainedForeignId('area_id');
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('rent_item', function (Blueprint $table) {
            $table->dropIndex('rent_item_branch_id_area_id_index');
            $table->dropConstrainedForeignId('area_id');
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
