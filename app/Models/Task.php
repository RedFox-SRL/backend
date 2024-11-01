<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['sprint_id', 'title', 'description', 'status'];

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function assignedTo()
    {
        return $this->belongsToMany(Student::class, 'task_student')->withTimestamps();
    }

    public function weeklyEvaluations()
    {
        return $this->belongsToMany(WeeklyEvaluation::class, 'task_weekly_evaluation')
            ->withPivot('comments', 'satisfaction_level')
            ->withTimestamps();
    }

    public function links()
    {
        return $this->hasMany(TaskLink::class);
    }
}
