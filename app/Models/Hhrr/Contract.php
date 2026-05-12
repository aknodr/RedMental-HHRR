<?php

namespace App\Models\Hhrr;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use BelongsToClient;

    protected $fillable = [
        'client_id', 'employee_id', 'patient_id',
        'type', 'title', 'start_date', 'end_date',
        'amount', 'status', 'terms', 'notes', 'file_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'amount'     => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public const TYPES = [
        'employment'        => 'Employment contract',
        'service_agreement' => 'Service agreement',
        'vendor'            => 'Vendor agreement',
        'other'             => 'Other',
    ];

    public const STATUSES = [
        'draft'      => 'Draft',
        'active'     => 'Active',
        'expired'    => 'Expired',
        'terminated' => 'Terminated',
    ];

    public const EXPIRING_SOON_DAYS = 30;

    /** Days until end_date — null if no end date. Negative = already past. */
    public function getDaysToExpiryAttribute(): ?int
    {
        return $this->end_date ? (int) now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false) : null;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        $d = $this->days_to_expiry;
        return $d !== null && $d >= 0 && $d <= self::EXPIRING_SOON_DAYS;
    }

    /** "Effective" status considering dates — overrides stored status when end_date is past. */
    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === 'terminated' || $this->status === 'draft') return $this->status;
        if ($this->is_expired) return 'expired';
        if ($this->is_expiring_soon) return 'expiring';
        return $this->status;
    }

    public function scopeExpired($q)        { return $q->whereNotNull('end_date')->whereDate('end_date', '<', now()); }
    public function scopeExpiringSoon($q)   { return $q->whereNotNull('end_date')->whereDate('end_date', '>=', now())->whereDate('end_date', '<=', now()->addDays(self::EXPIRING_SOON_DAYS)); }
    public function scopeActive($q)         { return $q->where('status', 'active')->where(fn ($w) => $w->whereNull('end_date')->orWhereDate('end_date', '>', now())); }
}
