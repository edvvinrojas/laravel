<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('color', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('equipment')->nullable();
            $table->string('code', 100)->nullable()->unique()->index();
            $table->string('supplier', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spareparts');
    }
};
