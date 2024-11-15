<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeerEvaluationAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_period_id',
        'evaluator_id',
        'evaluated_id',
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
}
