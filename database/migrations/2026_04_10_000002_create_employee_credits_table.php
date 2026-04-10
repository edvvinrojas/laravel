<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('credit_amount', 10, 2);
            $table->text('credit_reason');
            $table->decimal('biweekly_discount', 10, 2);
            $table->decimal('pending_amount', 10, 2);
            $table->unsignedInteger('pending_biweeks');
            $table->date('approval_date')->nullable();
            $table->date('payment_end_date')->nullable();
            $table->enum('status', ['SOLICITADO', 'AUTORIZADO', 'LIQUIDADO', 'CANCELADO'])->default('SOLICITADO');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_credits');
    }
};
