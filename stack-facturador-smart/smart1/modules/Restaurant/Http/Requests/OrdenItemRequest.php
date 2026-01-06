<?php

namespace Modules\Restaurant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class OrdenItemRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [
            'date' => [
                'required',
            ],
            'observations' => [
                'required',
            ],
            'orden_id' => [
                'required',
            ],
            'item_id' => [
                'required',
            ],
            'status_orden_id'
            => [
                'required',
            ]
        ];
    }
}
