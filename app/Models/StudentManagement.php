<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentManagement extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'management_id'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function management()
    {
        return $this->belongsTo(Management::class);
    }
}
