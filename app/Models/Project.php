<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'department',
        'location',
        'project_custom_id',
        'project_code',
        'name',
        'start_date',
        'end_date',
        'description',
        'status',
        'is_parked',
        'created_by',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::addGlobalScope('exclude_parked', function ($builder) {
            $builder->where('is_parked', false);
        });
    }

    /**
     * Get the user who created the project.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
