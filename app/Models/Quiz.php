<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quiz extends Model
{
    protected $guarded = ['id'];

    public function journal(){
        return $this->BelongsTo(Journal::class);
    }

    public function questions(){
        return $this->hasMany(QuizQuestion::class);
    }
}
