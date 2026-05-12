<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 200)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['client_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
