<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:todo,in_progress,done',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:students,id',
            'links' => 'nullable|array',
            'links.*.id' => 'nullable|exists:task_links,id',
            'links.*.url' => 'required|string',
            'links.*.description' => 'nullable|string',
        ];
    }
}
