<?php

namespace Modules\Optometry\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OptometryServicePaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        
        return [
            'date_of_payment' => [
                'date',
                'required',
            ],
            'payment_method_type_id' => [
                'required',
            ],
            'payment_destination_id' => [
                'required',
            ],
            'payment' => [
                'required',
            ],
        ];
    }
}