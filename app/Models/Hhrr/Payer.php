<?php

namespace App\Models\Hhrr;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payer extends Model
{
    use BelongsToClient;

    protected $fillable = [
        'client_id', 'name', 'edi_payer_id', 'type',
        'phone', 'email', 'address', 'city', 'state', 'zip',
        'notes', 'active',
    ];

    protected $casts = ['active' => 'boolean'];

    public function patientInsurances(): HasMany
    {
        return $this->hasMany(PatientInsurance::class);
    }

    public const TYPES = [
        'Medicaid'     => 'Medicaid',
        'Medicare'     => 'Medicare',
        'Commercial'   => 'Commercial',
        'MA'           => 'Medicare Advantage',
        'Marketplace'  => 'Marketplace',
        'Behavioral'   => 'Behavioral Health',
        'Military'     => 'Military',
        'VA'           => 'VA',
        'Self-Pay'     => 'Self-Pay',
        'Other'        => 'Other',
    ];
}
