<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_clinic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->date('enrollment_date')->nullable();
            $table->string('status', 20)->default('active'); // active | discharged | transferred
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['patient_id', 'clinic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_clinic');
    }
};
