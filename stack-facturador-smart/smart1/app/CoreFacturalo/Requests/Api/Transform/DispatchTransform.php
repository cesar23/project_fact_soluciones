<?php

namespace App\CoreFacturalo\Requests\Api\Transform;

use App\CoreFacturalo\Requests\Api\Transform\Common\EstablishmentTransform;
use App\CoreFacturalo\Requests\Api\Transform\Common\PersonTransform;
use App\CoreFacturalo\Requests\Api\Transform\Common\LegendTransform;
use App\CoreFacturalo\Requests\Api\Transform\Common\ActionTransform;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use Illuminate\Support\Facades\Log;
use Modules\Dispatch\Models\Transport;

class DispatchTransform
{
    public static function transform($inputs)
    {
        $data = [
            'id' => null,
            'series' => Functions::valueKeyInArray($inputs, 'serie_documento'),
            'number' => Functions::valueKeyInArray($inputs, 'numero_documento'),
            'date_of_issue' => Functions::valueKeyInArray($inputs, 'fecha_de_emision'),
            'time_of_issue' => Functions::valueKeyInArray($inputs, 'hora_de_emision'),
            'document_type_id' => Functions::valueKeyInArray($inputs, 'codigo_tipo_documento'),
            'establishment' => EstablishmentTransform::transform($inputs['datos_del_emisor']),
            'customer' => PersonTransform::transform($inputs, 'datos_del_cliente_o_receptor'),
            'observations' => Functions::valueKeyInArray($inputs, 'observaciones'),
            'transport_mode_type_id' => Functions::valueKeyInArray($inputs, 'codigo_modo_transporte'),
            'transfer_reason_type_id' => Functions::valueKeyInArray($inputs, 'codigo_motivo_traslado'),
            'transfer_reason_description' => Functions::valueKeyInArray($inputs, 'descripcion_motivo_traslado'),
            'date_of_shipping' => Functions::valueKeyInArray($inputs, 'fecha_de_traslado'),
            'transshipment_indicator' => Functions::valueKeyInArray($inputs, 'indicador_de_transbordo'),
            'port_code' => Functions::valueKeyInArray($inputs, 'codigo_de_puerto'),
            'unit_type_id' => Functions::valueKeyInArray($inputs, 'unidad_peso_total'),
            'total_weight' => Functions::valueKeyInArray($inputs, 'peso_total'),
            'packages_number' => Functions::valueKeyInArray($inputs, 'numero_de_bultos'),
            'container_number' => Functions::valueKeyInArray($inputs, 'numero_de_contenedor'),
            'is_transport_category_m1l' => Functions::valueKeyInArray($inputs, 'traslado_categoria_m1l', false),
            'plate_number' => Functions::valueKeyInArray($inputs, 'numero_de_placa'),
//            'license_plate' => Functions::valueKeyInArray($inputs, 'numero_de_placa'),
            'origin' => self::origin($inputs),
            'delivery' => self::delivery($inputs),
            'dispatcher' => self::dispatcher($inputs),
            'dispatcher_data' => self::dispatcher($inputs),
            'dispatcher_id' => Functions::valueKeyInArray($inputs, 'dispatcher_id'),
            'driver' => self::driver($inputs),
            'driver_data' => self::driver($inputs),
            'driver_id' => Functions::valueKeyInArray($inputs, 'driver_id'),
            'transport' => self::transport($inputs),
            'transport_data' => self::transport($inputs),
            'transport_id' => Functions::valueKeyInArray($inputs, 'transport_id'),
            'items' => self::items($inputs),
            'sender_data' => self::senderData($inputs),
            'receiver_data' => self::receiverData($inputs),
            'sender_address_data' => self::senderAddressData($inputs),
            'sender_address' => self::senderAddressData($inputs),
            'receiver_address_data' => self::receiverAddressData($inputs),
            'legends' => LegendTransform::transform($inputs),
            'actions' => ActionTransform::transform($inputs),
            'additional_data' => Functions::valueKeyInArray($inputs, 'dato_adicional'),
            'transports' => self::transports($inputs),
        ];
        self::AffectedDocument($data, $inputs);
        return $data;
    }

    private static function AffectedDocument(&$data, $inputs)
    {
        if (isset($inputs['documento_afectado']) && isset($inputs['documento_afectado']['external_id'])) {
            $data['affected_document_external_id'] = Functions::valueKeyInArray($inputs['documento_afectado'],
                'external_id');
        } elseif (isset($inputs['documento_afectado'])) {
            $data['data_affected_document']['number'] = Functions::valueKeyInArray($inputs['documento_afectado'],
                'numero_documento');
            $data['data_affected_document']['series'] = Functions::valueKeyInArray($inputs['documento_afectado'],
                'serie_documento');
            $data['data_affected_document']['document_type_id'] = Functions::valueKeyInArray($inputs['documento_afectado'],
                'codigo_tipo_documento');
        }
    }

    private static function origin($inputs)
    {
        if (key_exists('direccion_partida', $inputs)) {
            $origin = $inputs['direccion_partida'];

            return [
                'location_id' => $origin['ubigeo'],
                'address' => $origin['direccion'],
                'code' => '0000',
            ];
        }
        return null;
    }

    private static function delivery($inputs)
    {
        if (key_exists('direccion_llegada', $inputs)) {
            $delivery = $inputs['direccion_llegada'];

            return [
                'location_id' => $delivery['ubigeo'],
                'address' => $delivery['direccion'],
                'code' => '0000',
            ];
        }
        return null;
    }

    private static function dispatcher($inputs)
    {
        if (key_exists('transportista', $inputs)) {
            $dispatcher = $inputs['transportista'];

            return [
                'identity_document_type_id' => $dispatcher['codigo_tipo_documento_identidad'],
                'number' => $dispatcher['numero_documento'],
                'name' => $dispatcher['apellidos_y_nombres_o_razon_social'],
                'number_mtc' => $dispatcher['numero_mtc'],
            ];
        }
        return null;
    }

    private static function driver($inputs)
    {
        if (key_exists('chofer', $inputs)) {
            $driver = $inputs['chofer'];
            return [
                'identity_document_type_id' => $driver['codigo_tipo_documento_identidad'],
                'number' => $driver['numero_documento'],
                'name' => Functions::valueKeyInArray($driver, 'nombres'),
                'license' => Functions::valueKeyInArray($driver, 'numero_licencia'),
                'telephone' => Functions::valueKeyInArray($driver, 'telefono')
            ];
        }
        return null;
    }

    private static function transport($inputs)
    {
        if (key_exists('vehiculo', $inputs)) {
            $transport = $inputs['vehiculo'];
            return [
                'plate_number'           => $transport['numero_de_placa'],
                'model'                  => Functions::valueKeyInArray($transport, 'modelo'),
                'brand'                  => Functions::valueKeyInArray($transport, 'marca'),
                'tuc'                    => Functions::valueKeyInArray($transport, 'tuc_principal'),
                'auth_plate_primary'     => Functions::valueKeyInArray($transport, 'autorizacion_placa_principal'),
                'secondary_plate_number' => Functions::valueKeyInArray($transport, 'placa_secundaria'),
                'auth_plate_secondary'   => Functions::valueKeyInArray($transport, 'autorizacion_placa_secundaria'),
                'tuc_secondary'          => Functions::valueKeyInArray($transport, 'tuc_secundaria'),
            ];
        }
        return null;
    }

    private static function transports($inputs)
    {
        if (key_exists('vehiculo', $inputs)) {
            $transport = $inputs['vehiculo'];

            $tr = Transport::query()->firstOrCreate([
                'plate_number' => $transport['numero_de_placa'],
            ], [
                'model' => Functions::valueKeyInArray($transport, 'modelo'),
                'brand' => Functions::valueKeyInArray($transport, 'marca'),
            ]);

//            $tr = Transport::query()
//                ->select('id', 'plate_number', 'model', 'brand', 'tuce_number', 'authorization_entity_id', 'authorization_entity_number')
//                ->first($record->id);

            return [
                [
                    'transport_id' => $tr->id,
                    'transport_data' => [
                        'id' => $tr->id,
                        'plate_number' => $tr->plate_number,
                        'model' => $tr->model,
                        'brand' => $tr->brand,
                        'tuce_number' => $tr->tuce_number,
                        'authorization_entity_id' => $tr->authorization_entity_id,
                        'authorization_entity_number' => $tr->authorization_entity_number
                    ]// $tr->toArray()
                ]
            ];
        }
        return [];
    }

    private static function senderData($inputs)
    {
        if (key_exists('remitente', $inputs)) {
            $data = $inputs['remitente'];
            $identity_document_type_id = $data['codigo_tipo_documento_identidad'];
            $idt = IdentityDocumentType::query()->find($identity_document_type_id);

            return [
                'identity_document_type_id' => $data['codigo_tipo_documento_identidad'],
                'identity_document_type_description' => $idt->description,
                'number' => $data['numero_documento'],
                'name' => $data['apellidos_y_nombres_o_razon_social'],
            ];
        }

        return null;
    }

    private static function receiverData($inputs)
    {
        if (key_exists('destinatario', $inputs)) {
            $data = $inputs['destinatario'];
            $identity_document_type_id = $data['codigo_tipo_documento_identidad'];
            $idt = IdentityDocumentType::query()->find($identity_document_type_id);

            return [
                'identity_document_type_id' => $identity_document_type_id,
                'identity_document_type_description' => $idt->description,
                'number' => $data['numero_documento'],
                'name' => $data['apellidos_y_nombres_o_razon_social'],
            ];
        }

        return null;
    }

    private static function senderAddressData($inputs)
    {
        // dd($inputs["direccion_partida_remitente"]);
        if (key_exists('direccion_partida_remitente', $inputs)) {
            $data = $inputs['direccion_partida_remitente'];
            $location_id = $data['ubigeo'];

            return [
                'location_id' => [
                    substr($location_id, 0, 2),
                    substr($location_id, 0, 4),
                    substr($location_id, 0, 6)
                ],
                'address' => $data['direccion'],
            ];
        }
        return null;
    }

    private static function receiverAddressData($inputs)
    {
        if (key_exists('direccion_llegada_destinatario', $inputs)) {
            $data = $inputs['direccion_llegada_destinatario'];
            $location_id = $data['ubigeo'];

            return [
                'location_id' => [
                    substr($location_id, 0, 2),
                    substr($location_id, 0, 4),
                    substr($location_id, 0, 6)
                ],
                'address' => $data['direccion'],
            ];
        }
        return null;
    }

    private static function items($inputs)
    {
        if (key_exists('items', $inputs)) {
            $items = [];
            foreach ($inputs['items'] as $row) {
                Log::info(json_encode($row));
                $items[] = [
                    'internal_id' => isset($row['codigo_interno']) ? $row['codigo_interno'] : '',
                    'description' => Functions::valueKeyInArray($row, 'descripcion'),
                    'name' => Functions::valueKeyInArray($row, 'nombre'),
                    'second_name' => Functions::valueKeyInArray($row, 'nombre_secundario'),
                    'item_type_id' => Functions::valueKeyInArray($row, 'codigo_tipo_item', '01'),
                    'item_code' => Functions::valueKeyInArray($row, 'codigo_producto_sunat'),
                    'item_code_gs1' => Functions::valueKeyInArray($row, 'codigo_producto_gsl'),
                    'unit_type_id' => Functions::valueKeyInArray($row, 'unidad_de_medida','NIU'),
                    'currency_type_id' => 'PEN',

                    'quantity' => Functions::valueKeyInArray($row, 'cantidad'),
                    'unit_value' => Functions::valueKeyInArray($row, 'valor_unitario'),
                    'price_type_id' => Functions::valueKeyInArray($row, 'codigo_tipo_precio'),
                    'unit_price' => Functions::valueKeyInArray($row, 'precio_unitario'),

                    'affectation_igv_type_id' => Functions::valueKeyInArray($row, 'codigo_tipo_afectacion_igv'),
                    'total_base_igv' => Functions::valueKeyInArray($row, 'total_base_igv'),
                    'percentage_igv' => Functions::valueKeyInArray($row, 'porcentaje_igv'),
                    'total_igv' => Functions::valueKeyInArray($row, 'total_igv'),

                    'system_isc_type_id' => Functions::valueKeyInArray($row, 'codigo_tipo_sistema_isc'),
                    'total_base_isc' => Functions::valueKeyInArray($row, 'total_base_isc'),
                    'percentage_isc' => Functions::valueKeyInArray($row, 'porcentaje_isc'),
                    'total_isc' => Functions::valueKeyInArray($row, 'total_isc'),

                    'total_base_other_taxes' => Functions::valueKeyInArray($row, 'total_base_otros_impuestos'),
                    'percentage_other_taxes' => Functions::valueKeyInArray($row, 'porcentaje_otros_impuestos'),
                    'total_other_taxes' => Functions::valueKeyInArray($row, 'total_otros_impuestos'),
                    'total_plastic_bag_taxes' => Functions::valueKeyInArray($row, 'total_impuestos_bolsa_plastica'),

                    'total_taxes' => Functions::valueKeyInArray($row, 'total_impuestos'),
                    'total_value' => Functions::valueKeyInArray($row, 'total_valor_item'),
                    'total_charge' => Functions::valueKeyInArray($row, 'total_cargos'),
                    'total_discount' => Functions::valueKeyInArray($row, 'total_descuentos'),
                    'total' => Functions::valueKeyInArray($row, 'total_item'),

                    'additional_information' => Functions::valueKeyInArray($row, 'informacion_adicional'),
                    'additional_data' => Functions::valueKeyInArray($row, 'dato_adicional'),
                ];
            }

            return $items;
        }
        return null;
    }
}
