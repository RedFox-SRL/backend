<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SprintEvaluation extends Model
{
    use HasFactory;

    protected $fillable = ['sprint_id', 'summary'];

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function studentGrades()
    {
        return $this->hasMany(StudentSprintGrade::class);
    }

    public function points()
    {
        return $this->hasMany(SprintEvaluationPoint::class);
    }

    public function strengths()
    {
        return $this->points()->where('type', 'strength');
    }

    public function weaknesses()
    {
        return $this->points()->where('type', 'weakness');
    }
}
