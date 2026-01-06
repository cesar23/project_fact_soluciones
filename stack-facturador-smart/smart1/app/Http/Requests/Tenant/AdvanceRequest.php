<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class AdvanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [ 
            'beginning_balance' => [
                'required',
                'numeric',
                'min:0',
            ], 
            'reference_number' => 'required',
            'person_id' => 'required',

        ];
        
    }
    public function messages()
	{
		return [
            'beginning_balance.required' => 'El campo Saldo inicial es obligatorio.',
            'reference_number.required' => 'El campo Refencia es obligatorio.',

		];
	}
}