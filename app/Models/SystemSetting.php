<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'setting_value' => 'array',
            'is_public' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
