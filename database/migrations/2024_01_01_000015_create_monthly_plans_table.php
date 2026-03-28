<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('monthly_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('service_type_id')->constrained('service_types');
            $table->enum('attendance_status', ['VISITADO', 'NO_QUEDO', 'PENDIENTE'])->default('PENDIENTE');
            $table->text('description')->nullable();
            $table->dateTime('visit_date');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('monthly_plan_users', function (Blueprint $table) {
            $table->foreignId('monthly_plan_id')->constrained('monthly_plans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['monthly_plan_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_plan_users');
        Schema::dropIfExists('monthly_plans');
        Schema::dropIfExists('service_types');
    }
};
