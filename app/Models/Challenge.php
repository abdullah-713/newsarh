<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Challenge extends Model
{
    protected $table = 'challenges';
    protected $guarded = [];
    
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'target_value' => 'integer',
            'reward_points' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'max_participants' => 'integer',
        ];
    }

    public function rewardBadge(): BelongsTo
    {
        return $this->belongsTo(Badge::class, 'reward_badge_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
