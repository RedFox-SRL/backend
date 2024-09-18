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

    protected $table = 'gestiones';

    // Relación con el modelo Teacher
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    // Relationship with groups
    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    // Genera un código único
    public static function generateUniqueCode()
    {
        do {
            $code = strtoupper(bin2hex(random_bytes(3))); // Genera un código aleatorio de 6 caracteres
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
