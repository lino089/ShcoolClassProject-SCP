<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = [
        'schedule_id',
        'date',
        'status'
    ];

    public function schedule() {
        return $this->belongsTo(Schedule::class);
    }
}
