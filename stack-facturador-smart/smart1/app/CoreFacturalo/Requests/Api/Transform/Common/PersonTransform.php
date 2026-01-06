<?php

namespace App\CoreFacturalo\Requests\Api\Transform\Common;

use App\CoreFacturalo\Requests\Api\Transform\Functions;
 
class PersonTransform
{
    public static function transform($inputs, $key)
    { 
        if (!key_exists($key, $inputs)) {
            return null;
        }
        $data = $inputs[$key];

        return [
            'identity_document_type_id' => $data['codigo_tipo_documento_identidad'],
            'number' => $data['numero_documento'],
            'name' => $data['apellidos_y_nombres_o_razon_social'],
            'trade_name' => Functions::valueKeyInArray($data, 'nombre_comercial'),
            'country_id' => Functions::valueKeyInArray($data, 'codigo_pais'),
            'district_id' => Functions::valueKeyInArray($data, 'ubigeo'),
            'address' => Functions::valueKeyInArray($data, 'direccion'),
            'email' => Functions::valueKeyInArray($data, 'correo_electronico'),
            'telephone' => Functions::valueKeyInArray($data, 'telefono'),
            'address_type_id' => Functions::valueKeyInArray($data, 'codigo_tipo_direccion'),
        ];
    }
    public static function transform_customer($customer)
    {
        return [
            "identity_document_type_id" =>  $customer->identity_document_type_id,
            "identity_document_type" => [
                "id" => $customer->identity_document_type->id,
                "description" => $customer->identity_document_type->description
            ],
              "number" => $customer->number,
              "name"  => $customer->name,
              "trade_name" => $customer->name,
              "country_id" => $customer->country_id,
              "country"=> [
                "id" => $customer->id,
                "description" => $customer->description
            ],
              "department_id" => $customer->department_id ==null ? null : $customer->department_id,
              "department" => [
                "id" => $customer->department_id ==null ?  null : $customer->department->id,
                "description" => $customer->department_id ==null ?  null : $customer->department->description,
            ],
              "province_id" => $customer->province_id == null ? null : $customer->province_id,
              "province" => [
                "id" => $customer->province_id == null ? null : $customer->province->id,
                "description" => $customer->province_id == null ? null : $customer->province->iddescription
              ],
              "district_id" => $customer->district_id== null ? null : $customer->district_id,
              "district" => [
                "id" =>  $customer->district_id== null ? null : $customer->district->id,
                "description"=>  $customer->district_id== null ? null : $customer->description
              ],
              "address" => $customer->address,
              "email" => $customer->email,
              "telephone" => $customer->telephone,
              "address_type_id" => $customer->address_type_id == null ? null : $customer->address_type_id,
              "address_type"=> [
                "id" => $customer->address_type_id ==null ? null : $customer->address_type->id,
                "description" => $customer->address_type_id ==null ? null : $customer->address_type->description
            ]
            // 'identity_document_type_id' => $inputs['codigo_tipo_documento_identidad'],
            // 'number' => $inputs['numero_documento'],
            // 'name' => $inputs['apellidos_y_nombres_o_razon_social'],
            // 'trade_name' => Functions::valueKeyInArray($inputs, 'nombre_comercial'),
            // 'country_id' => Functions::valueKeyInArray($inputs, 'codigo_pais'),
            // 'district_id' => Functions::valueKeyInArray($inputs, 'ubigeo'),
            // 'address' => Functions::valueKeyInArray($inputs, 'direccion'),
            // 'email' => Functions::valueKeyInArray($inputs, 'correo_electronico'),
            // 'telephone' => Functions::valueKeyInArray($inputs, 'telefono'),
            // 'address_type_id' => Functions::valueKeyInArray($inputs, 'codigo_tipo_direccion'),
        ];
    }
}