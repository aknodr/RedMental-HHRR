<?php

namespace App\Models\Hhrr;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Clinic extends Model
{
    use BelongsToClient;

    protected $fillable = [
        'client_id', 'name', 'code',
        'address', 'city', 'state', 'zip',
        'latitude', 'longitude',
        'phone', 'email', 'active',
    ];

    protected $casts = [
        'active'    => 'boolean',
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'patient_clinic')
            ->withPivot(['enrollment_date', 'status', 'notes'])
            ->using(PatientClinic::class)
            ->withTimestamps();
    }
}
