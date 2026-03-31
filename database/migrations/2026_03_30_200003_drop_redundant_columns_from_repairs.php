<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            // model, serie y model_toner son dependencias transitivas:
            // repair → item_id → items.model / items.serie / items.model_toner
            $table->dropColumn(['model', 'serie', 'model_toner']);
        });
    }

    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->string('model')->after('item_id')->default('');
            $table->string('serie')->after('model')->default('');
            $table->string('model_toner')->after('serie')->default('');
        });
    }
};
