<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'sprint_id',
        'evaluation_template_id',
        'type',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function evaluationTemplate()
    {
        return $this->belongsTo(EvaluationTemplate::class);
    }

    public function studentEvaluations()
    {
        return $this->hasMany(StudentEvaluation::class);
    }

    public function peerEvaluationAssignments()
    {
        return $this->hasMany(PeerEvaluationAssignment::class);
    }
}
