<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrapConfiguration extends Model
{
    protected $table = 'trap_configurations';
    protected $guarded = [];
    
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'trigger_chance' => 'decimal:2',
            'cooldown_minutes' => 'integer',
            'min_role_level' => 'integer',
            'max_role_level' => 'integer',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TrapLog::class, 'trap_config_id');
    }
}
