<?php

namespace Modules\Restaurant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class CategoryFoodRequest extends FormRequest
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
