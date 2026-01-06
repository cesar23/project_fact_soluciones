<?php

namespace Modules\Restaurant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class OrdenRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [
            'table_id' => [
                'required',
            ],
            'status_orden_id' => [
                'required',
            ],

        ];
    }
}
