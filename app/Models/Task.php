<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['sprint_id', 'assigned_to', 'title', 'description', 'status'];

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(Student::class, 'assigned_to');
    }
}
