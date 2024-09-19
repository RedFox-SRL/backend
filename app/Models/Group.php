<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'management_id',
        'creator_id', // ID of the student who created the group
        'code',
        'short_name',
        'long_name',
        'contact_email',
        'contact_phone',
        'logo',
        'max_members',
    ];

    public static function generateUniqueCode()
    {
        do {
            $code = strtoupper(bin2hex(random_bytes(3)));
        } while (self::where('codigo', $code)->exists());

        return $code;
    }

    public function management()
    {
        return $this->belongsTo(Management::class);
    }

    public function student()
    {
        return $this->belongsToMany(Student::class, 'group_student')
                    ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(Student::class, 'creator_id');
    }
}
