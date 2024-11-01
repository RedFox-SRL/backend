<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSprintGrade extends Model
{
    protected $fillable = ['sprint_evaluation_id', 'student_id', 'grade', 'comments'];

    public function sprintEvaluation()
    {
        return $this->belongsTo(SprintEvaluation::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
