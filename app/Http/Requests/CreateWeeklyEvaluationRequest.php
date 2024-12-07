<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Sprint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CreateWeeklyEvaluationRequest extends FormRequest
{
    public function authorize()
    {
        $sprint = Sprint::findOrFail($this->route('id'));
        $user = Auth::user();
        $result = $user->role === 'teacher' && $user->teacher && $sprint->group->management->teacher_id === $user->teacher->id;
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
                        $fail('La tarea no pertenece a este sprint.');
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
                $validator->errors()->add('general', 'No se pueden crear más evaluaciones de las permitidas para este sprint.');
            }

            if (Carbon::now()->gt($sprint->end_date)) {
                $validator->errors()->add('general', 'No se pueden crear evaluaciones después de la fecha de finalización del sprint.');
            }

            $existingEvaluation = $sprint->weeklyEvaluations()->where('week_number', $currentWeekNumber)->first();
            if ($existingEvaluation) {
                $validator->errors()->add('general', 'Ya existe una evaluación para esta semana.');
            }
        });
    }

    public function messages()
    {
        return [
            'tasks.required' => 'Se debe evaluar al menos una tarea.',
            'tasks.*.id.required' => 'Se requiere el ID de la tarea.',
            'tasks.*.id.exists' => 'ID de tarea no válido.',
            'tasks.*.comments.required' => 'Se requieren comentarios para cada tarea.',
            'tasks.*.satisfaction_level.required' => 'Se requiere el nivel de satisfacción para cada tarea.',
            'tasks.*.satisfaction_level.integer' => 'El nivel de satisfacción debe ser un número entero.',
            'tasks.*.satisfaction_level.min' => 'El nivel de satisfacción debe ser al menos 1.',
            'tasks.*.satisfaction_level.max' => 'El nivel de satisfacción no debe ser mayor a 5.',
        ];
    }
}
