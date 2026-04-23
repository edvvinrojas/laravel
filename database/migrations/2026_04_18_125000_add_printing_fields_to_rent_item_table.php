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
            $table->boolean('has_print_service')->default(false)->after('contador_inicial_color');
            $table->unsignedInteger('bn_included')->default(0)->after('has_print_service');
            $table->decimal('bn_cost_per_excess', 10, 4)->default(0)->after('bn_included');
            $table->unsignedInteger('color_included')->default(0)->after('bn_cost_per_excess');
            $table->decimal('color_cost_per_excess', 10, 4)->default(0)->after('color_included');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('UPDATE rent_item ri SET has_print_service = COALESCE(r.has_print_service, false), bn_included = COALESCE(r.bn_included, 0), bn_cost_per_excess = COALESCE(r.bn_cost_per_excess, 0), color_included = COALESCE(r.color_included, 0), color_cost_per_excess = COALESCE(r.color_cost_per_excess, 0) FROM rents r WHERE r.id = ri.rent_id');
        } else {
            DB::statement("UPDATE rent_item ri INNER JOIN rents r ON r.id = ri.rent_id SET ri.has_print_service = COALESCE(r.has_print_service, 0), ri.bn_included = COALESCE(r.bn_included, 0), ri.bn_cost_per_excess = COALESCE(r.bn_cost_per_excess, 0), ri.color_included = COALESCE(r.color_included, 0), ri.color_cost_per_excess = COALESCE(r.color_cost_per_excess, 0)");
        }
    }

    public function down(): void
    {
        Schema::table('rent_item', function (Blueprint $table) {
            $table->dropColumn([
                'has_print_service',
                'bn_included',
                'bn_cost_per_excess',
                'color_included',
                'color_cost_per_excess',
            ]);
        });
    }
};
