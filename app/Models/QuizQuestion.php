<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'options' => 'array',
    ];
}
