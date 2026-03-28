<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rents', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number', 50)->unique()->nullable()->index();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->decimal('rent', 10, 2);
            $table->enum('contract_status', ['PENDIENTE', 'SIN_FIRMAR', 'VIGENTE', 'FINALIZADO', 'CANCELADO'])->default('PENDIENTE')->index();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_foreign')->default(false);
            $table->boolean('has_print_service')->default(false)->index();
            $table->integer('bn_included')->default(0)->nullable();
            $table->decimal('bn_cost_per_excess', 10, 4)->default(0)->nullable();
            $table->integer('color_included')->default(0)->nullable();
            $table->decimal('color_cost_per_excess', 10, 4)->default(0)->nullable();
            $table->text('print_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rents');
    }
};
