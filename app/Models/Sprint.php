<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'title', 'features', 'start_date', 'end_date'];

    protected $casts = [
        'features' => 'array',
    ];

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
}
