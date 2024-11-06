<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateCriterion extends Model
{
    use HasFactory;

    protected $fillable = ['template_section_id', 'name', 'description', 'order'];

    public function section()
    {
        return $this->belongsTo(TemplateSection::class, 'template_section_id');
    }
}
