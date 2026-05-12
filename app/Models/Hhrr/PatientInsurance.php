<?php

namespace App\Models\Hhrr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientInsurance extends Model
{
    protected $fillable = [
        'patient_id', 'payer_id', 'priority',
        'policy_number', 'group_number',
        'subscriber_name', 'subscriber_relationship',
        'effective_date', 'termination_date', 'active',
    ];

    protected $casts = [
        'effective_date'   => 'date',
        'termination_date' => 'date',
        'active'           => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(Payer::class);
    }
}
