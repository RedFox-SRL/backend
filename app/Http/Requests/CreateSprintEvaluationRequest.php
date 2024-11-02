<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Sprint;

class CreateSprintEvaluationRequest extends FormRequest
{
    public function authorize()
    {
        $sprint = Sprint::findOrFail($this->route('id'));
        return $this->user()->teacher && $sprint->group->management->teacher_id === $this->user()->teacher->id;
    }

    public function rules()
    {
        return [
            'summary' => 'required|string|max:1000',
            'student_grades' => 'required|array|min:1',
            'student_grades.*.student_id' => 'required|exists:students,id',
            'student_grades.*.grade' => 'required|numeric|min:0',
            'student_grades.*.comments' => 'nullable|string|max:500',
        ];
    }
}
