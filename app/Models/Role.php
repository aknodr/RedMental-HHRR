<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = ['name', 'guard_name', 'client_id'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeForClient(Builder $query, ?int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('client_id');
    }

    /**
     * Roles visible to a given user: their client's scoped roles (never the
     * globally-reserved "Super Admin" / "Client Admin" ones).
     */
    public function scopeAssignableBy(Builder $query, User $user): Builder
    {
        return $query->where('client_id', $user->client_id);
    }
}
