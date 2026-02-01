<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrapLog extends Model
{
    protected $table = 'trap_logs';
    protected $guarded = [];
    
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'score_change' => 'integer',
            'trust_delta' => 'integer',
            'curiosity_delta' => 'integer',
            'integrity_delta' => 'integer',
            'response_time_ms' => 'integer',
            'context_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(TrapConfiguration::class, 'trap_config_id');
    }
}
