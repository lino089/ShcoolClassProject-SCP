<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleImportRow extends Model
{
    protected $fillable = [
        'import_id',
        'raw_data',
        'matched_class_id',
        'matched_teacher_id',
        'matched_room_id',
        'matched_subject_id',
        'day_of_week',
        'period_number',
        'duration_periods',
        'match_status',
        'room_source_conflict',
        'resolved_by_waka',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];
}
