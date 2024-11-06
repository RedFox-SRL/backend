<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateSection extends Model
{
    use HasFactory;

    protected $fillable = ['evaluation_template_id', 'title', 'order'];

    public function evaluationTemplate()
    {
        return $this->belongsTo(EvaluationTemplate::class);
    }

    public function criteria()
    {
        return $this->hasMany(TemplateCriterion::class)->orderBy('order');
    }
}
