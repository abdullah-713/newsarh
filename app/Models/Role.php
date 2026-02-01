<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'roles';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'role_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get users with this role
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
