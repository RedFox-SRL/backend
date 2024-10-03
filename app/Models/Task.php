<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Add this line

class Task extends Model
{
    use HasFactory, SoftDeletes; // Add SoftDeletes here

    protected $fillable = ['sprint_id', 'assigned_to', 'title', 'description', 'status'];

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(Student::class, 'assigned_to');
    }

    public function evaluation()
    {
        return $this->hasOne(TaskEvaluation::class)->latest();
    }

    public function evaluations()
    {
        return $this->hasMany(TaskEvaluation::class);
    }
}
