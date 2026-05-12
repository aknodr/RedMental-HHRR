<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['employment', 'service_agreement', 'vendor', 'other'])->default('employment');
            $table->string('title');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->enum('status', ['draft', 'active', 'expired', 'terminated'])->default('draft');
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['client_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
