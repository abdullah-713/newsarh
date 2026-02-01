<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $table = 'branches';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'geofence_radius' => 'integer',
            'is_active' => 'boolean',
            'is_ghost_branch' => 'boolean',
            'ghost_visible_to' => 'array',
            'settings' => 'array',
        ];
    }

    /**
     * Get users in this branch
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get departments in this branch
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}
