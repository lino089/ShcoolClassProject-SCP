<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleImport extends Model
{
    protected $fillable = [
            'waka_id',
            'cycle_type',
            'file_path_kelas',
            'file_path_ruangan',
            'status',
    ];
}
