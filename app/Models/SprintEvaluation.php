<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SprintEvaluation extends Model
{
    protected $fillable = ['sprint_id', 'summary'];

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function studentGrades()
    {
        return $this->hasMany(StudentSprintGrade::class);
    }
}
