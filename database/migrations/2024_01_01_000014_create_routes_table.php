<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_code', 50)->unique()->index();
            $table->string('driver_name', 200);
            $table->string('vehicle', 100)->nullable();
            $table->enum('status', ['PROGRAMADA', 'EN_RUTA', 'PAUSADA', 'COMPLETADA', 'CANCELADA'])->default('PROGRAMADA');
            $table->date('scheduled_date');
            $table->integer('total_stops')->default(0);
            $table->integer('completed_stops')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->integer('stop_order')->default(0);
            $table->string('address', 500)->nullable();
            $table->string('city', 100)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->string('visit_status', 20)->default('pendiente');
            $table->text('no_visit_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_stops');
        Schema::dropIfExists('routes');
    }
};
