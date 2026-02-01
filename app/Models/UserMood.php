<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMood extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get mood emoji
     */
    public function getMoodEmojiAttribute(): string
    {
        return match($this->mood) {
            'very_happy' => '😄',
            'happy' => '😊',
            'neutral' => '😐',
            'sad' => '😟',
            'very_sad' => '😢',
            default => '😐',
        };
    }
}
