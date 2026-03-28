<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('nombre')->nullable();
            $table->string('nss', 11)->unique()->index();
            $table->string('rfc', 13)->unique()->index();
            $table->string('curp', 18)->unique()->index();
            $table->date('birthday');
            $table->date('hire_date');
            $table->string('phone_emergency', 15);
            $table->string('contact_emergency');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('salary', 10, 2);
            $table->date('pay_day');
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('commission', 10, 2)->default(0);
            $table->decimal('total_pay', 10, 2);
            $table->enum('status', ['PENDIENTE', 'APROBADO', 'RECHAZADO', 'ACTIVO', 'PAGADO'])->default('PENDIENTE');
            $table->timestamps();
        });

        Schema::create('vacations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->integer('vacation_days');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('requested_by')->constrained('users');
            $table->enum('status', ['PENDIENTE', 'APROBADO', 'RECHAZADO', 'ACTIVO', 'PAGADO'])->default('PENDIENTE');
            $table->integer('remaining_days');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('administrative_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('type_administrative', [
                'RETROALIMENTACION_ESCRITA', 'AMONESTACION',
                'ACTA_ADMINISTRATIVA', 'ENTREVISTA_AUSENTISMO'
            ]);
            $table->integer('suspended_days')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->text('description');
            $table->foreignId('issued_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('absence_type', ['ENFERMEDAD', 'AUSENTISMO', 'PERMISO_PERSONAL', 'OTRO']);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_justified')->default(false);
            $table->text('justification')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->enum('status', ['PENDIENTE', 'APROBADO', 'RECHAZADO', 'ACTIVO', 'PAGADO'])->default('PENDIENTE');
            $table->text('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
        Schema::dropIfExists('administrative_records');
        Schema::dropIfExists('vacations');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('employees');
    }
};
