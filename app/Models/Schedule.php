<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Routing\Loader\Configurator\Traits\AddTrait;

class Schedule extends Model
{

    use SoftDeletes;
    protected $fillable = [
            'cycle_type',
            'period_number',
            'duration_periods',
            'teacher_id', 
            'class_id',
            'room_id',
            'subject_id',
            'day_of_week',
            'source'
    ];
}
