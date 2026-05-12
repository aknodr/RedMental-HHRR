<?php

namespace App\Models\Hhrr;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use BelongsToClient;

    /** Florida thesis-rule: a provider may not see more than this in a single day. */
    public const MAX_PER_PROVIDER_PER_DAY = 20;

    public const STATUSES = [
        'scheduled' => 'Scheduled',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show'   => 'No-show',
    ];

    protected $fillable = [
        'client_id', 'patient_id', 'provider_id', 'clinic_id',
        'scheduled_at', 'duration_minutes', 'status', 'reason', 'notes',
    ];

    protected $casts = [
        'scheduled_at'     => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function patient(): BelongsTo  { return $this->belongsTo(Patient::class); }
    public function provider(): BelongsTo { return $this->belongsTo(Employee::class, 'provider_id'); }
    public function clinic(): BelongsTo   { return $this->belongsTo(Clinic::class); }

    /** How many appointments a provider already has on a given date (excluding cancelled). */
    public static function countForProviderOnDate(int $providerId, \DateTimeInterface $date, ?int $excludeId = null): int
    {
        return static::query()
            ->where('provider_id', $providerId)
            ->whereDate('scheduled_at', $date)
            ->whereNotIn('status', ['cancelled'])
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->count();
    }
}
