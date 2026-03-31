<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('services_included')->default(false)->after('is_foreign');
            $table->unsignedSmallInteger('services_quantity')->nullable()->after('services_included');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['services_included', 'services_quantity']);
        });
    }
};
