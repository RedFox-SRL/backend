<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'last_name',
        'email',
        'role',
        'verification_code',
        'verification_code_expires_at',
    ];

    protected $hidden = [
        'verification_code',
        'verification_code_expires_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function evaluations()
    {
        return $this->hasMany(StudentEvaluation::class, 'evaluator_id');
    }

    public function generateVerificationCode()
    {
        $this->verification_code = sprintf("%06d", mt_rand(1, 999999));
        $this->verification_code_expires_at = now()->addMinutes(15);
        $this->save();
    }
}

