<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getStartsAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function getEndsAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

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
