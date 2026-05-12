<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * HHRR invoices.
 *
 *   invoices       — top-level billing record per patient/payer
 *   invoice_lines  — line items (service, units, rate)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->constrained()->cascadeOnDelete();
            $t->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $t->foreignId('payer_id')->nullable()->constrained('payers')->nullOnDelete();

            $t->string('invoice_number', 30)->unique();
            $t->date('issue_date');
            $t->date('due_date');
            $t->date('paid_date')->nullable();

            $t->string('status', 20)->default('draft'); // draft, sent, paid, overdue, void
            $t->decimal('subtotal', 10, 2)->default(0);
            $t->decimal('tax', 10, 2)->default(0);
            $t->decimal('total', 10, 2)->default(0);
            $t->decimal('amount_paid', 10, 2)->default(0);

            $t->text('notes')->nullable();
            $t->text('terms')->nullable();

            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['client_id', 'status', 'due_date']);
            $t->index(['patient_id', 'issue_date']);
        });

        Schema::create('invoice_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $t->string('description');
            $t->string('cpt_code', 20)->nullable();
            $t->date('service_date')->nullable();
            $t->decimal('quantity', 8, 2)->default(1);
            $t->decimal('unit_price', 10, 2);
            $t->decimal('line_total', 10, 2);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
    }
};
