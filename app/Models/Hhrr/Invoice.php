<?php

namespace App\Models\Hhrr;

use App\Models\Concerns\BelongsToClient;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToClient;

    public const STATUSES = [
        'draft'   => 'Draft',
        'sent'    => 'Sent',
        'paid'    => 'Paid',
        'overdue' => 'Overdue',
        'void'    => 'Void',
    ];

    protected $fillable = [
        'client_id', 'patient_id', 'payer_id',
        'invoice_number', 'issue_date', 'due_date', 'paid_date',
        'status', 'subtotal', 'tax', 'total', 'amount_paid',
        'notes', 'terms', 'created_by',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'due_date'    => 'date',
        'paid_date'   => 'date',
        'subtotal'    => 'decimal:2',
        'tax'         => 'decimal:2',
        'total'       => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function payer(): BelongsTo   { return $this->belongsTo(Payer::class); }
    public function lines(): HasMany     { return $this->hasMany(InvoiceLine::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    /** Mark `sent` invoices past due_date as `overdue`. */
    public function scopeOverdueNow(Builder $q): Builder
    {
        return $q->where('status', 'sent')->whereDate('due_date', '<', now()->toDateString());
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->amount_paid);
    }

    /** Recompute subtotal / tax / total from the loaded line collection. */
    public function recalculate(float $taxRate = 0): self
    {
        $subtotal = (float) $this->lines->sum('line_total');
        $this->subtotal = $subtotal;
        $this->tax      = round($subtotal * $taxRate, 2);
        $this->total    = round($subtotal + $this->tax, 2);
        return $this;
    }
}
