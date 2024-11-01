<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyEvaluation extends Model
{
    use HasFactory;

    protected $fillable = ['sprint_id', 'evaluator_id', 'week_number', 'evaluation_date'];

    protected $casts = [
        'evaluation_date' => 'date',
    ];

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class)->withPivot('comments', 'satisfaction_level');
    }
}
