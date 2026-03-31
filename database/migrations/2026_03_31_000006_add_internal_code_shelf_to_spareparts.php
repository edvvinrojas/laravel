<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spareparts', function (Blueprint $table) {
            $table->string('internal_code', 120)->nullable()->unique()->after('code');
            $table->foreignId('shelf_id')->nullable()->constrained('shelves')->nullOnDelete()->after('internal_code');
        });
    }

    public function down(): void
    {
        Schema::table('spareparts', function (Blueprint $table) {
            $table->dropForeign(['shelf_id']);
            $table->dropColumn(['internal_code', 'shelf_id']);
        });
    }
};
