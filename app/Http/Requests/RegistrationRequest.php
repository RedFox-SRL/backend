<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\ApiCode;

class RegistrationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users',
                function ($attribute, $value, $fail) {
                    if (preg_match('/@est\.umss\.edu$/', $value)) {
                        if (!preg_match('/^20\d{7}@est\.umss\.edu$/', $value)) {
                            $fail(__('api.invalid_student_email'));
                        }
                    } elseif (!preg_match('/@fcyt\.umss\.edu\.bo$/', $value)) {
                        $fail(__('api.invalid_email_domain'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'El correo electrónico ya está en uso.',
        ];
    }
}
