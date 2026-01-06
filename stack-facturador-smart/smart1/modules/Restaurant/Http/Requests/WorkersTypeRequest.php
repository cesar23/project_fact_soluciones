<?php

namespace Modules\Restaurant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class WorkersTypeRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [
            'description' => [
                'required',
            ],
            'active' => [
                'required',
            ]
        ];
    }
}
