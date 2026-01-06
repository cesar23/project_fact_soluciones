<?php

namespace Modules\Restaurant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Client\Request;

class FoodRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $request = $this->instance()->all();
        return [
            'file' => 'nullable|max:2000',
            'category_food_id' => [
                'required',
            ],
            'description' => [
                'required',
            ],
            'price' => [
                'required',
            ],
            'code' => [
                'max:5'
            ]
        ];
    }
}
