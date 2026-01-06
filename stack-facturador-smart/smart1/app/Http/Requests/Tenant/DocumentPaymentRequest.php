<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentPaymentRequest extends FormRequest
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
                function ($attribute, $value, $fail) {
                    $value = str_replace(',', '', $value);
                    if (!is_numeric($value) || $value <= 0) {
                        $fail('El pago debe ser un número mayor que 0.');
                    }
                },
            ],
        ];
    }
}