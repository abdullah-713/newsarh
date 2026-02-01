<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrityReport extends Model
{
    protected $table = 'integrity_reports';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'evidence_files' => 'array',
            'is_anonymous_claim' => 'boolean',
            'resolved_at' => 'datetime',
            'sender_revealed_to' => 'array',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
