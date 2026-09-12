<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolClass extends Model
{
    use SoftDeletes;
    
    protected $table = 'classes';

    protected $fillable = ['name', 'level', 'next_class_id'];
}
