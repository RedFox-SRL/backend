<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'title', 'description', 'start_date', 'end_date'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
