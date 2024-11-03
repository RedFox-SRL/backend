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
            'student_grades.*.grade' => 'required|numeric|min:0|max:100',
            'student_grades.*.comments' => 'nullable|string|max:500',
            'strengths' => 'required|array|min:1',
            'strengths.*' => 'required|string|max:255',
            'weaknesses' => 'required|array|min:1',
            'weaknesses.*' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'strengths.required' => 'At least one strength is required.',
            'weaknesses.required' => 'At least one weakness is required.',
        ];
    }
}
