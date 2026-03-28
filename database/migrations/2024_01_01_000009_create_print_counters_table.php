<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rent_id')->constrained('rents');
            $table->foreignId('billing_id')->nullable()->constrained('billings')->nullOnDelete();
            $table->integer('period_month')->index();
            $table->integer('period_year')->index();
            $table->integer('bn_previous')->default(0);
            $table->integer('bn_current')->default(0);
            $table->integer('bn_printed')->default(0);
            $table->integer('bn_included')->default(0);
            $table->integer('bn_excess')->default(0);
            $table->decimal('bn_cost_per_page', 10, 4)->default(0);
            $table->decimal('bn_excess_amount', 10, 2)->default(0);
            $table->integer('color_previous')->default(0);
            $table->integer('color_current')->default(0);
            $table->integer('color_printed')->default(0);
            $table->integer('color_included')->default(0);
            $table->integer('color_excess')->default(0);
            $table->decimal('color_cost_per_page', 10, 4)->default(0);
            $table->decimal('color_excess_amount', 10, 2)->default(0);
            $table->decimal('total_excess_amount', 10, 2)->default(0);
            $table->string('counter_photo_url', 500)->nullable();
            $table->string('notes', 500)->nullable();
            $table->date('reading_date');
            $table->boolean('is_billed')->default(false)->index();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_counters');
    }
};
