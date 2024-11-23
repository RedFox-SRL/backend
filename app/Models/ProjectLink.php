<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectLink extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'url', 'category', 'description'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
