<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_student');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_student');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'invited_student_id');
    }
}
