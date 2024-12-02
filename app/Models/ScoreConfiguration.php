<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoreConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'management_id',
        'sprint_points',
        'cross_evaluation_points',
        'proposal_points',
    ];

    public function management()
    {
        return $this->belongsTo(Management::class);
    }
}
