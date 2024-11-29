<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrossEvaluationResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'cross_evaluation_id',
        'template_criterion_id',
        'score',
    ];

    public function crossEvaluation()
    {
        return $this->belongsTo(CrossEvaluation::class);
    }

    public function templateCriterion()
    {
        return $this->belongsTo(TemplateCriterion::class);
    }
}
