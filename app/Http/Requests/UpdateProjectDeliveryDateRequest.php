<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectDeliveryDateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'project_delivery_date' => 'required|date_format:Y-m-d H:i:s',
        ];
    }
}
