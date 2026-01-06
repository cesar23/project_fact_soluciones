<?php

namespace Modules\Restaurant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class WorkerRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {

        return [


            'name' => [
                'required',
            ],
            'worker_type_id' => [
                'required',
            ],
            'area_id' => ['required']
        ];
    }
}
