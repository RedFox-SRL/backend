<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Management extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'code',
        'semester',
        'start_date',
        'end_date',
        'group_limit',
        'is_code_active',
    ];

    protected $table = 'management';

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public static function generateUniqueCode()
    {
        do {
            $code = strtoupper(bin2hex(random_bytes(3))); // Genera un código aleatorio de 6 caracteres
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
