<?php

namespace App\Models\Hhrr;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PatientClinic extends Pivot
{
    protected $table = 'patient_clinic';

    public $incrementing = true;

    protected $casts = [
        'enrollment_date' => 'date',
    ];
}
