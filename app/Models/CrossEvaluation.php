<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrossEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluator_group_id',
        'evaluated_group_id',
        'management_id',
        'evaluation_template_id',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function evaluatorGroup()
    {
        return $this->belongsTo(Group::class, 'evaluator_group_id');
    }

    public function evaluatedGroup()
    {
        return $this->belongsTo(Group::class, 'evaluated_group_id');
    }

    public function management()
    {
        return $this->belongsTo(Management::class);
    }

    public function evaluationTemplate()
    {
        return $this->belongsTo(EvaluationTemplate::class);
    }

    public function responses()
    {
        return $this->hasMany(CrossEvaluationResponse::class);
    }
}
