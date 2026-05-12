<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'name',
        'legal_name',
        'tax_id',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'zip',
        'logo_path',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function admin(): ?User
    {
        return $this->users()
            ->whereHas('roles', fn ($q) => $q->where('name', 'Client Admin'))
            ->first();
    }
}
