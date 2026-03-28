<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->enum('billing_type', ['RENTA', 'VENTA'])->index();
            $table->foreignId('rent_id')->nullable()->constrained('rents')->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->string('invoice_number', 50)->unique()->nullable()->index();
            $table->decimal('amount', 10, 2);
            $table->date('target_date')->index();
            $table->date('due_date')->index();
            $table->date('payment_date')->nullable();
            $table->enum('status', ['PENDIENTE', 'PAGADO', 'VENCIDO'])->default('PENDIENTE')->index();
            $table->boolean('follow_up')->default(false)->index();
            $table->integer('payment_term')->nullable();
            $table->integer('payment_day')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
