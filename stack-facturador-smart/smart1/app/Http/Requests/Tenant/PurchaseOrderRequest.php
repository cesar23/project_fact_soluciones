<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Tenant\Series;

class PurchaseOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');

        return [
            'supplier_id' => [
                'required'
            ],
            'series' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $type = $this->input('type', 'goods');
                    $establishment_id = $this->input('establishment_id', auth()->user()->establishment_id);
                    $document_type_id = $type === 'goods' ? 'OCB' : 'OCS';
                    
                    $series = Series::where('number', $value)
                        ->where('establishment_id', $establishment_id)
                        ->where('document_type_id', $document_type_id)
                        ->first();
                        
                    if (!$series) {
                        $fail('La serie seleccionada no existe o no corresponde al tipo y establecimiento especificados.');
                    }
                }
            ],
            'type' => [
                'required',
                'in:goods,services'
            ],
        ];
    }

    public function messages()
    {
        return [
            'supplier_id.required' => 'El campo Proveedor es obligatorio.',
            'series.required' => 'El campo Serie es obligatorio.',
            'type.required' => 'El campo Tipo es obligatorio.',
            'type.in' => 'El tipo debe ser bienes o servicios.',
        ];
    }
}
