<?php

namespace App\Models\Hhrr;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use BelongsToClient;

    protected $fillable = ['client_id', 'name', 'code', 'description', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
