<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class ItemRequest
 *
 * @package App\Http\Requests\Tenant
 * @mixin FormRequest
 */
class UnitMeasureRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [
            'code' => 'required',
             'description' => 'required'
          ];
    }

    public function messages()
    {
        return [
            'description.required' => 'El campo nombre es obligatorio.',
            'code.required' => 'El codigo es un campo obligatorio.',
         ];
    }
}
