<?php

namespace App\Models\Hhrr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id', 'description', 'cpt_code', 'service_date',
        'quantity', 'unit_price', 'line_total',
    ];

    protected $casts = [
        'service_date' => 'date',
        'quantity'     => 'decimal:2',
        'unit_price'   => 'decimal:2',
        'line_total'   => 'decimal:2',
    ];

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }

    protected static function booted(): void
    {
        // Keep line_total = quantity * unit_price.
        static::saving(function (self $line) {
            $line->line_total = round((float) $line->quantity * (float) $line->unit_price, 2);
        });
    }
}
