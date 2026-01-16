<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Specify that IDs are not auto-incrementing
    public $incrementing = false;
    protected $fillable = ['id', 'name'];
}
