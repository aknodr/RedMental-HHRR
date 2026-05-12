<?php

namespace App\Models\Hhrr;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use BelongsToClient;

    public const FREQUENCIES = [
        'bi_weekly' => 'Bi-weekly',
        'monthly'   => 'Monthly',
    ];

    public const STATUSES = [
        'draft'    => 'Draft',
        'approved' => 'Approved',
        'paid'     => 'Paid',
    ];

    protected $fillable = [
        'client_id', 'employee_id', 'frequency',
        'period_start', 'period_end',
        'hours_worked', 'hourly_rate', 'per_patient_bonus', 'patients_seen',
        'gross', 'deductions', 'net', 'status', 'notes',
    ];

    protected $casts = [
        'period_start'      => 'date',
        'period_end'        => 'date',
        'hours_worked'      => 'decimal:2',
        'hourly_rate'       => 'decimal:2',
        'per_patient_bonus' => 'decimal:2',
        'gross'             => 'decimal:2',
        'deductions'        => 'decimal:2',
        'net'               => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function recalculate(): void
    {
        $gross = round((float) $this->hours_worked * (float) $this->hourly_rate
               + (int) $this->patients_seen * (float) $this->per_patient_bonus, 2);
        $this->gross = $gross;
        $this->net   = round($gross - (float) $this->deductions, 2);
    }
}
