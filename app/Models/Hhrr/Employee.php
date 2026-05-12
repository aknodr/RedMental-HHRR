<?php

namespace App\Models\Hhrr;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use BelongsToClient;

    protected $fillable = [
        'client_id', 'department_id', 'employee_number', 'npi',
        'first_name', 'last_name', 'email', 'phone',
        'date_of_birth', 'gender', 'position', 'is_provider',
        'hourly_rate', 'salary', 'hire_date', 'termination_date',
        'address', 'city', 'state', 'zip',
        'emergency_contact_name', 'emergency_contact_phone',
        'notes', 'active',
    ];

    protected $casts = [
        'date_of_birth'    => 'date',
        'hire_date'        => 'date',
        'termination_date' => 'date',
        'hourly_rate'      => 'decimal:2',
        'salary'           => 'decimal:2',
        'is_provider'      => 'boolean',
        'active'           => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function assignedPatients(): HasMany
    {
        return $this->hasMany(Patient::class, 'assigned_provider_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
