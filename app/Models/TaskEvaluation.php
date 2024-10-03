<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskEvaluation extends Model
{
    use HasFactory;

    protected $fillable = ['task_id', 'grade', 'comment', 'evaluated_by'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function evaluatedBy()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
