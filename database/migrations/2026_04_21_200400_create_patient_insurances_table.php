<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payer_id')->constrained()->cascadeOnDelete();
            $table->enum('priority', ['primary', 'secondary', 'tertiary'])->default('primary');
            $table->string('policy_number', 50)->nullable();
            $table->string('group_number', 50)->nullable();
            $table->string('subscriber_name')->nullable();
            $table->enum('subscriber_relationship', ['self', 'spouse', 'child', 'other'])->default('self');
            $table->date('effective_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['patient_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_insurances');
    }
};
