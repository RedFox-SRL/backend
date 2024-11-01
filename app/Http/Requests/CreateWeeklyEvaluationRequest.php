<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Sprint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreateWeeklyEvaluationRequest extends FormRequest
{
    public function authorize()
    {
        Log::info('Authorize method called');
        $sprint = Sprint::findOrFail($this->route('id'));
        $user = Auth::user();
        $result = $user->role === 'teacher' && $user->teacher && $sprint->group->management->teacher_id === $user->teacher->id;
        Log::info('Authorization result: ' . ($result ? 'true' : 'false'));
        return $result;
    }

    public function rules()
    {
        return [
            'tasks' => 'required|array',
            'tasks.*.id' => [
                'required',
                'exists:tasks,id',
                function ($attribute, $value, $fail) {
                    $sprint = Sprint::findOrFail($this->route('id'));
                    $task = $sprint->tasks()->find($value);
                    if (!$task) {
                        $fail('The task does not belong to this sprint.');
                    }
                    if ($task->weeklyEvaluations()->exists()) {
                        $fail('The task has already been evaluated in a previous week.');
                    }
                },
            ],
            'tasks.*.comments' => 'required|string',
            'tasks.*.satisfaction_level' => 'required|integer|min:1|max:5',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $sprint = Sprint::findOrFail($this->route('id'));
            $currentWeekNumber = $sprint->getCurrentWeekNumber();

            if ($currentWeekNumber > $sprint->max_evaluations) {
                $validator->errors()->add('general', 'Cannot create more evaluations than allowed for this sprint.');
            }

            if (Carbon::now()->gt($sprint->end_date)) {
                $validator->errors()->add('general', 'Cannot create evaluations after the sprint end date.');
            }

            $existingEvaluation = $sprint->weeklyEvaluations()->where('week_number', $currentWeekNumber)->first();
            if ($existingEvaluation) {
                $validator->errors()->add('general', 'An evaluation for this week already exists.');
            }
        });
    }

    public function messages()
    {
        return [
            'tasks.required' => 'At least one task must be evaluated.',
            'tasks.*.id.required' => 'Task ID is required.',
            'tasks.*.id.exists' => 'Invalid task ID.',
            'tasks.*.comments.required' => 'Comments are required for each task.',
            'tasks.*.satisfaction_level.required' => 'Satisfaction level is required for each task.',
            'tasks.*.satisfaction_level.integer' => 'Satisfaction level must be an integer.',
            'tasks.*.satisfaction_level.min' => 'Satisfaction level must be at least 1.',
            'tasks.*.satisfaction_level.max' => 'Satisfaction level must not be greater than 5.',
        ];
    }
}
