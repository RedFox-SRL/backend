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
        'sprint_teacher_percentage',
        'sprint_self_percentage',
        'sprint_peer_percentage',
        'proposal_part_a_percentage',
        'proposal_part_b_percentage',
    ];

    protected $casts = [
        'sprint_points' => 'integer',
        'cross_evaluation_points' => 'integer',
        'proposal_points' => 'integer',
        'sprint_teacher_percentage' => 'float',
        'sprint_self_percentage' => 'float',
        'sprint_peer_percentage' => 'float',
        'proposal_part_a_percentage' => 'float',
        'proposal_part_b_percentage' => 'float',
    ];

    public function management()
    {
        return $this->belongsTo(Management::class);
    }
}
