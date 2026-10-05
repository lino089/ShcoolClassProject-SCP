<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPermision extends Model
{
    protected $guarded = ['id'];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
