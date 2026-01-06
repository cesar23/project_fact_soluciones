<?php

namespace Modules\Dispatch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        $id = $this->input('id');

        return [
            'plate_number' => [
                Rule::unique('tenant.transports')->ignore($id)->whereNotNull('plate_number'),
            ],
            'brand' => [
            ],
            'model' => [
            ],
            'secondary_plate_number' => [
                Rule::unique('tenant.transports')->ignore($id)->whereNotNull('secondary_plate_number'),
            ],

        ];
    }

}
