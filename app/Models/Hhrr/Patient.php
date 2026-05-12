<?php

namespace App\Models\Hhrr;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use BelongsToClient;

    protected $fillable = [
        'client_id', 'assigned_provider_id', 'mrn',
        'first_name', 'last_name', 'middle_name',
        'date_of_birth', 'gender', 'ssn',
        'phone', 'email',
        'address', 'city', 'state', 'zip',
        'latitude', 'longitude',
        'emergency_contact_name', 'emergency_contact_phone',
        'preferred_language', 'intake_date',
        'notes', 'active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'intake_date'   => 'date',
        'active'        => 'boolean',
        'latitude'      => 'decimal:7',
        'longitude'     => 'decimal:7',
    ];

    public function insurances(): HasMany
    {
        return $this->hasMany(PatientInsurance::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function assignedProvider(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_provider_id');
    }

    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class, 'patient_clinic')
            ->withPivot(['enrollment_date', 'status', 'notes'])
            ->using(PatientClinic::class)
            ->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        $mi = $this->middle_name ? ' ' . mb_substr($this->middle_name, 0, 1) . '.' : '';
        return trim("{$this->first_name}{$mi} {$this->last_name}");
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }
}
