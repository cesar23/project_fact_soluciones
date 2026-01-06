<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TutorialRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [
             
            'title' => 'required',
            'type' => 'required',
            'link'        => 'required',
            'location'    => 'required',
        ];
    }
}