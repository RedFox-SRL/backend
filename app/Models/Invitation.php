<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'invited_by',
        'invited_student_id',
        'token',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function invitedBy()
    {
        return $this->belongsTo(Student::class, 'invited_by');
    }

    public function invitedStudent()
    {
        return $this->belongsTo(Student::class, 'invited_student_id');
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }
}
