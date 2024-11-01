<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyEvaluation extends Model
{
    protected $fillable = ['sprint_id', 'week_number', 'evaluation_date'];

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class)->withPivot('comments', 'satisfaction_level');
    }
}
