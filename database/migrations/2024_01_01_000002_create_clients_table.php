<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('comercial_name')->nullable();
            $table->string('rfc', 50)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('colonia', 100)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('city', 100)->nullable();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_main')->default(false);
            $table->string('name');
            $table->string('address', 500)->nullable();
            $table->string('colonia', 100)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('city', 100)->nullable();
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->timestamps();
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('clients');
    }
};
