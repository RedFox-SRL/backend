<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'project_delivery_date',
        'proposal_part_a_deadline',
        'proposal_part_b_deadline'
    ];

    protected $table = 'management';

    protected $dates = ['start_date', 'end_date', 'project_delivery_date'];

    protected $casts = [
        'proposal_part_a_deadline' => 'datetime',
        'proposal_part_b_deadline' => 'datetime',
    ];


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
            $code = strtoupper(bin2hex(random_bytes(6)));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function evaluationTemplates()
    {
        return $this->hasMany(EvaluationTemplate::class);
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public static function calculateDates($semester, $year)
    {
        if ($semester === 'first') {
            $startDate = Carbon::create($year, 1, 1);
            $endDate = Carbon::create($year, 6, 30);
        } else {
            $startDate = Carbon::create($year, 7, 1);
            $endDate = Carbon::create($year, 12, 31);
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    public function crossEvaluations()
    {
        return $this->hasMany(CrossEvaluation::class);
    }

    public function scoreConfiguration()
    {
        return $this->hasOne(ScoreConfiguration::class);
    }
}
