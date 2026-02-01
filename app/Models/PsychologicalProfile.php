<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsychologicalProfile extends Model
{
    protected $table = 'psychological_profiles';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'trust_score' => 'integer',
            'curiosity_score' => 'integer',
            'integrity_score' => 'integer',
            'total_traps_seen' => 'integer',
            'total_violations' => 'integer',
            'last_trap_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
