<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('edi_payer_id', 20)->nullable();
            $table->enum('type', ['Medicaid', 'Medicare', 'Commercial', 'MA', 'Marketplace', 'Behavioral', 'Military', 'VA', 'Self-Pay', 'Other'])
                ->default('Commercial');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 10)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['client_id', 'active']);
            $table->index(['client_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payers');
    }
};
