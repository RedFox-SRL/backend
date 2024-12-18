<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'management_id',
        'creator_id',
        'code',
        'short_name',
        'long_name',
        'contact_email',
        'contact_phone',
        'logo',
        'cross_evaluation_score',
    ];

    protected $casts = [
        'cross_evaluation_score' => 'decimal:2',
    ];

    public static function generateUniqueCode()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $length = 7;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        while (self::where('code', $code)->exists()) {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        }

        return $code;
    }

    public function management()
    {
        return $this->belongsTo(Management::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'group_student');
    }

    public function creator()
    {
        return $this->belongsTo(Student::class, 'creator_id');
    }

    public function sprints()
    {
        return $this->hasMany(Sprint::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function projectLinks()
    {
        return $this->hasMany(ProjectLink::class);
    }

    public function crossEvaluationsAsEvaluator()
    {
        return $this->hasMany(CrossEvaluation::class, 'evaluator_group_id');
    }

    public function crossEvaluationsAsEvaluated()
    {
        return $this->hasMany(CrossEvaluation::class, 'evaluated_group_id');
    }

    public function proposalSubmission()
    {
        return $this->hasOne(ProposalSubmission::class);
    }
}
