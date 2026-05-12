<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('clinic_id')->nullable()->constrained('clinics')->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(45);
            $table->string('status', 20)->default('scheduled'); // scheduled | completed | cancelled | no_show
            $table->string('reason', 200)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'scheduled_at']);
            $table->index(['provider_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
