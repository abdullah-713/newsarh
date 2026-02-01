<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkShift extends Model
{
    protected $table = 'work_shifts';
    protected $guarded = [];
    
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i:s',
            'end_time' => 'datetime:H:i:s',
            'is_overnight' => 'boolean',
            'grace_period_minutes' => 'integer',
            'min_working_hours' => 'decimal:2',
            'max_working_hours' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get user shift assignments for this shift
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(UserShiftAssignment::class, 'shift_id');
    }
}
