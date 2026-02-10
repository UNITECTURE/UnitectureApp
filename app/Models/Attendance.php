<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'duration',
        'type',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'date' => 'date',
    ];

    public function setDurationAttribute($value)
    {
        if ($value === null) {
            $this->attributes['duration'] = null;
            return;
        }

        if (!is_string($value)) {
            $this->attributes['duration'] = $value;
            return;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            $this->attributes['duration'] = $value;
            return;
        }

        // Check for negative signs
        if (str_contains($trimmed, '-')) {
            \Illuminate\Support\Facades\Log::warning("Negative duration attempted for Attendance ID {$this->id}: '$trimmed'. Stack trace: " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)));

            // Remove all negative signs
            $trimmed = str_replace('-', '', $trimmed);
        }

        if (preg_match('/(\d+)\s*hrs?\s*(\d+)\s*mins?/i', $trimmed, $matches)) {
            $h = abs((int) $matches[1]);
            $m = abs((int) $matches[2]);
            $this->attributes['duration'] = "{$h} Hrs {$m} Mins";
            return;
        }

        $hMatch = [];
        $mMatch = [];
        preg_match('/(\d+)\s*h/i', $trimmed, $hMatch);
        preg_match('/(\d+)\s*m/i', $trimmed, $mMatch);

        if (!empty($hMatch) || !empty($mMatch)) {
            $h = isset($hMatch[1]) ? abs((int) $hMatch[1]) : 0;
            $m = isset($mMatch[1]) ? abs((int) $mMatch[1]) : 0;
            $this->attributes['duration'] = "{$h} Hrs {$m} Mins";
            return;
        }

        $this->attributes['duration'] = $trimmed;
    }

    // Accessor to ensure duration is always positive when retrieved
    public function getDurationAttribute($value)
    {
        if (!$value)
            return $value;

        // If it contains a negative sign, remove it for display
        if (str_contains($value, '-')) {
            return str_replace('-', '', $value);
        }

        return $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
