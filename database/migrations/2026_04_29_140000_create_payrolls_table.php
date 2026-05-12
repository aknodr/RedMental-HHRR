<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('frequency', 20); // bi_weekly | monthly
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('hours_worked', 8, 2)->default(0);
            $table->decimal('hourly_rate',  10, 2)->default(0);
            $table->decimal('per_patient_bonus', 10, 2)->default(0);
            $table->unsignedInteger('patients_seen')->default(0);
            $table->decimal('gross', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('net', 12, 2)->default(0);
            $table->string('status', 20)->default('draft'); // draft | approved | paid
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'period_start', 'period_end']);
            $table->index(['client_id', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
