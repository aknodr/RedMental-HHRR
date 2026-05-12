<?php

namespace App\Models\Concerns;

use App\Models\Client;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Attach to any domain model that belongs to a Client. Adds:
 *   - the belongsTo relationship
 *   - a global scope that filters queries by the authenticated user's client
 *     (bypassed for super admins so they can inspect any client's data)
 *   - auto-population of client_id on create
 */
trait BelongsToClient
{
    public static function bootBelongsToClient(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->client_id) && auth()->check() && auth()->user()->client_id) {
                $model->client_id = auth()->user()->client_id;
            }
        });

        static::addGlobalScope('client', new class implements Scope {
            public function apply(Builder $builder, Model $model): void
            {
                $user = auth()->user();
                if (! $user) return;
                if ($user->isSuperAdmin()) return; // super admin can see everything
                if (! $user->client_id) return;

                $builder->where($model->getTable() . '.client_id', $user->client_id);
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
