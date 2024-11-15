<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_evaluation_id',
        'template_criterion_id',
        'score',
    ];

    public function studentEvaluation()
    {
        return $this->belongsTo(StudentEvaluation::class);
    }

    public function templateCriterion()
    {
        return $this->belongsTo(TemplateCriterion::class);
    }
}
