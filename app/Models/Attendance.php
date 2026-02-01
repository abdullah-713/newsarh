<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendance';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in_time' => 'datetime:H:i:s',
            'check_out_time' => 'datetime:H:i:s',
            'check_in_lat' => 'decimal:7',
            'check_in_lng' => 'decimal:7',
            'check_out_lat' => 'decimal:7',
            'check_out_lng' => 'decimal:7',
            'check_in_distance' => 'decimal:2',
            'check_out_distance' => 'decimal:2',
            'work_minutes' => 'integer',
            'late_minutes' => 'integer',
            'early_leave_minutes' => 'integer',
            'overtime_minutes' => 'integer',
            'penalty_points' => 'decimal:2',
            'bonus_points' => 'decimal:2',
            'is_locked' => 'boolean',
            'mood_score' => 'integer',
            'fraud_flags' => 'array',
        ];
    }

    /**
     * Get the user this attendance belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch this attendance belongs to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the recorded branch (where attendance was recorded)
     */
    public function recordedBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'recorded_branch_id');
    }
}
