<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCalendar extends Model
{
    protected $fillable = [
        'working_days',
        'business_hours_start',
        'business_hours_end',
        'cutoff_time',
    ];

    protected $casts = [
        'working_days' => 'array',
    ];

    /**
     * The platform-wide calendar is a single row, created with sane defaults
     * the first time it's needed rather than requiring manual setup.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'working_days' => [1, 2, 3, 4, 5],
            'business_hours_start' => '08:00:00',
            'business_hours_end' => '17:00:00',
            'cutoff_time' => '17:00:00',
        ]);
    }
}
