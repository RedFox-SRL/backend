<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateManagementRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'semester' => 'required|in:first,second',
            'year' => 'required|integer|min:2000|max:2100',
        ];
    }
}
