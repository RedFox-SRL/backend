<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Sprint extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'title', 'features', 'start_date', 'end_date', 'percentage', 'reviewed'];

    protected $casts = [
        'features' => 'array',
        'start_date' => 'datetime:Y-m-d',
        'end_date' => 'datetime:Y-m-d',
    ];

    public function sprintEvaluation()
    {
        return $this->hasOne(SprintEvaluation::class);
    }

    public function weeklyEvaluations()
    {
        return $this->hasMany(WeeklyEvaluation::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class)->withTrashed();
    }

    public function setFeaturesAttribute($value)
    {
        $this->attributes['features'] = json_encode(array_values(array_filter(explode("\n", $value))));
    }

    public function getFeaturesAttribute($value)
    {
        return implode("\n", json_decode($value, true) ?? []);
    }

    public function getMaxEvaluationsAttribute()
    {
        $startDate = $this->start_date;
        $endDate = $this->end_date;

        $firstMonday = $startDate->copy()->next(Carbon::MONDAY);
        $lastMonday = $endDate->copy()->previous(Carbon::MONDAY);

        if ($lastMonday->lte($firstMonday)) {
            return 1;
        }

        return $firstMonday->diffInWeeks($lastMonday) + 2;
    }

    public function getCurrentWeekNumber()
    {
        $startDate = $this->start_date;
        $currentDate = Carbon::now();
        $nextMonday = $startDate->copy()->next(Carbon::MONDAY);

        if ($currentDate->lt($nextMonday)) {
            return 1;
        }

        $weeksPassed = $nextMonday->diffInWeeks($currentDate);
        return min($weeksPassed + 2, $this->max_evaluations);
    }

    public function evaluationPeriods()
    {
        return $this->hasMany(EvaluationPeriod::class);
    }
}
