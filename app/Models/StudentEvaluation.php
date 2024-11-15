<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_period_id',
        'evaluator_id',
        'evaluated_id',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function evaluationPeriod()
    {
        return $this->belongsTo(EvaluationPeriod::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(Student::class, 'evaluator_id');
    }

    public function evaluated()
    {
        return $this->belongsTo(Student::class, 'evaluated_id');
    }

    public function responses()
    {
        return $this->hasMany(EvaluationResponse::class);
    }
}
