<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSchedule extends Model
{
    protected $table = 'employee_schedules';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'work_start_time' => 'datetime:H:i:s',
            'work_end_time' => 'datetime:H:i:s',
            'grace_period_minutes' => 'integer',
            'working_days' => 'array',
            'allowed_branches' => 'array',
            'geofence_radius' => 'integer',
            'is_flexible_hours' => 'boolean',
            'min_working_hours' => 'decimal:2',
            'max_working_hours' => 'decimal:2',
            'early_checkin_minutes' => 'integer',
            'late_checkout_allowed' => 'boolean',
            'overtime_allowed' => 'boolean',
            'remote_checkin_allowed' => 'boolean',
            'late_penalty_per_minute' => 'decimal:2',
            'early_bonus_points' => 'decimal:2',
            'overtime_bonus_per_hour' => 'decimal:2',
            'is_active' => 'boolean',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    /**
     * Get the user this schedule belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the creator of this schedule
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
