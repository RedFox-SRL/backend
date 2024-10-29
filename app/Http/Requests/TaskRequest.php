<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'sprint_id' => 'required|exists:sprints,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:todo,in_progress,done',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:students,id',
            'links' => 'nullable|array',
            'links.*.url' => 'required|string',
            'links.*.description' => 'nullable|string',
        ];
    }
}
