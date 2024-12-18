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
        if ($this->isTestEmail()) {
            $this->verification_code = '123456';
        } else {
            $this->verification_code = sprintf("%06d", mt_rand(1, 999999));
        }
        $this->verification_code_expires_at = now()->addMinutes(15);
        $this->save();
    }

    public function isTestEmail()
    {
        $testEmails = [
            'redfox.es1@est.umss.edu',
            'redfox.es2@est.umss.edu',
            'redfox.es3@est.umss.edu',
            'redfox.es4@est.umss.edu',
            'redfox.es5@est.umss.edu',
            'redfox.es6@est.umss.edu',
            'redfox.es7@est.umss.edu',
            'redfox.es8@est.umss.edu',
            'redfox.es9@est.umss.edu',
            'redfox.es10@est.umss.edu',
            'redfox.es11@est.umss.edu',
            'redfox.es12@est.umss.edu',
            'redfox.es13@est.umss.edu',
            'redfox.es14@est.umss.edu',
            'redfox.es15@est.umss.edu',
            'redfox.es16@est.umss.edu',
            'redfox.es17@est.umss.edu',
            'redfox.es18@est.umss.edu',
            'redfox.do1@fcyt.umss.edu.bo',
            'redfox.do2@fcyt.umss.edu.bo',
            'redfox.do3@fcyt.umss.edu.bo',
            'redfox.do4@fcyt.umss.edu.bo',
        ];

        return in_array($this->email, $testEmails);
    }
}

