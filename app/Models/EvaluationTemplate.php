<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['management_id', 'type'];

    public function management()
    {
        return $this->belongsTo(Management::class);
    }

    public function sections()
    {
        return $this->hasMany(TemplateSection::class)->orderBy('order');
    }
}
