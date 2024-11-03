<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SprintEvaluationPoint extends Model
{
    use HasFactory;

    protected $fillable = ['sprint_evaluation_id', 'type', 'description'];

    public function sprintEvaluation()
    {
        return $this->belongsTo(SprintEvaluation::class);
    }
}
