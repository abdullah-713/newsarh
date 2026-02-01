<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficialHoliday extends Model
{
    protected $table = 'official_holidays';
    protected $guarded = [];
    
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
            'is_paid' => 'boolean',
        ];
    }

    /**
     * Get the branch this holiday belongs to (null = all branches)
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
