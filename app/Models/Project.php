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
        'created_by',
    ];

    /**
     * Get the user who created the project.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
