<?php

namespace Modules\Restaurant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class ExpensesRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' =>  [
                'required',
                'numeric',
                'gt:0'
            ],
            'date' => [
                'required',
            ],
            'description' => [
                'required',
            ],
            'method' => [
                'required',
            ]
        ];
    }
    public function messages()
    {
        return [
            'amount.gt'              => 'El efectivo debe ser mayor que 0.',
            'date.required'          => 'El fecha es obligatorio.',
            'description.required'   => 'El descripción es obligatorio.',
            'method.required'        => 'El metodo de pago es obligatorio.',
        ];
    }
}
