<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalSubmission extends Model
{
    protected $fillable = ['group_id', 'part_a_file', 'part_b_file', 'part_a_submitted_at', 'part_b_submitted_at'];

    protected $casts = [
        'part_a_submitted_at' => 'datetime',
        'part_b_submitted_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
