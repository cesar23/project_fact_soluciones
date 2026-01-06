@php
    $establishment = $document->establishment;
$configuration = \App\Models\Tenant\Configuration::first();
$configurations = \App\Models\Tenant\Configuration::first();
$company_name = $company->name;
$company_owner = null;
if ($configurations->trade_name_pdf) {
    $company_name = $company->trade_name;
    $company_owner = $company->name;
}
    $establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $logo = $establishment__->logo ?? $company->logo;

    if ($logo === null || !file_exists(public_path("$logo"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

    $document_number = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
@endphp
<!DOCTYPE html>
<html>

<head>
</head>

<body style="font-family: Arial, sans-serif; font-size: 10px;">
    @if ($document->state_type->id == '11' || $document->state_type->id == '09' || $document->state_type->id == '55' || $document->state_type->id == '01' || $document->state_type->id == '03')
        <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
            <img src="data:{{ mime_content_type(public_path('status_images' . DIRECTORY_SEPARATOR . 'anulado.png')) }};base64, {{ base64_encode(file_get_contents(public_path('status_images' . DIRECTORY_SEPARATOR . 'anulado.png'))) }}"
                alt="anulado" class="" style="opacity: 0.6;">
        </div>
    @endif
    @if ($document->soap_type_id == '01')
        <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
            <img src="data:{{ mime_content_type(public_path('status_images' . DIRECTORY_SEPARATOR . 'demo.png')) }};base64, {{ base64_encode(file_get_contents(public_path('status_images' . DIRECTORY_SEPARATOR . 'demo.png'))) }}"
                alt="anulado" class="" style="opacity: 0.6;">
        </div>
    @endif
    <table style="width: 100%;">
        <tr>
            @if ($company->logo)
                <td style="width: 20%;">
                    <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                        alt="{{ $company->name }}" style="max-width: 150px;">
                </td>
            @else
                <td style="width: 10%;">
                </td>
            @endif
            <td style="width: 50%; padding-left: 3px;     text-align: center;">
                <div style="text-align: left;">
                    <h3 style="margin: 2px 0;">{{ $company->name }}</h3>
                    <h4 style="margin: 2px 0;">{{ 'RUC ' . $company->number }}</h4>
                    <h5 style="margin: 2px 0; text-transform: uppercase;">
                        {{ $establishment->address !== '-' ? $establishment->address : '' }}
                        {{ $establishment->district_id !== '-' ? ', ' . ($establishment->district->description ?? '') : '' }}
                        {{ $establishment->province_id !== '-' ? ', ' . ($establishment->province->description ?? '') : '' }}
                        {{ $establishment->department_id !== '-' ? '- ' . ($establishment->department->description ?? '') : '' }}
                    </h5>
                    <h5 style="margin: 2px 0;">{{ $establishment->email !== '-' ? $establishment->email : '' }}</h5>
                    <h5 style="margin: 2px 0;">{{ $establishment->telephone !== '-' ? $establishment->telephone : '' }}
                    </h5>
                </div>
            </td>
            <td style="width: 40%; border: 1px solid #000; padding: 5px; text-align: center;">
                <h4 style="margin: 2px 0;">{{ $document->document_type->description }}</h4>
                <h3 style="margin: 2px 0;">{{ $document_number }}</h3>
            </td>
        </tr>
    </table>
    <table style="width: 100%; border: 1px solid #000; padding: 5px; margin-top: 10px; margin-bottom: 10px;">
        <thead>
            <tr>
                <th style="border-bottom: 1px solid #000; text-align: center;" colspan="3">Datos del Traslado</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span style="font-weight: bold;">Fecha Emisión:</span>
                    {{ $document->date_of_issue->format('Y-m-d') }}</td>
                <td><span style="font-weight: bold;">Fecha Inicio de Traslado:</span>
                    {{ $document->date_of_shipping->format('Y-m-d') }}</td>
                <td><span style="font-weight: bold;">Peso Bruto Total ({{ $document->unit_type_id }}):</span>
                    {{ $document->total_weight }}</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; border: 1px solid #000; padding: 5px; margin-top: 10px; margin-bottom: 10px;">
        <thead>
            <tr>
                <th style="border-bottom: 1px solid #000; text-align: center;" colspan="3">Datos del Remitente</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span style="font-weight: bold;">Datos del Remitente:</span>
                    {{ $document->sender_data['name'] ?? '' }}</td>
                <!-- <td><span style="font-weight: bold;">Tipo de Documento:</span> {{ $document->sender_data['identity_document_type_description'] ?? '' }}</td> -->
                <td><span style="font-weight: bold;">Ruc/Doc. Identidad:</span>
                    {{ $document->sender_data['number'] ?? '' }}</td>
            </tr>
        </tbody>
    </table>


    <table style="width: 100%; border: 1px solid #000; padding: 5px; margin-top: 10px; margin-bottom: 10px;">
        <thead>
            <tr>
                <th style="border-bottom: 1px solid #000; text-align: center;" colspan="3">Datos del Destinatario
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span style="font-weight: bold;">Datos del Destinatario:</span>
                    {{ $document->receiver_data['name'] ?? '' }}</td>
                <!-- <td><span style="font-weight: bold;">Tipo de Documento:</span> {{ $document->receiver_data['identity_document_type_description'] ?? '' }}</td> -->
                <td><span style="font-weight: bold;">Ruc/Doc. Identidad:</span>
                    {{ $document->receiver_data['number'] ?? '' }}</td>
            </tr>
        </tbody>
    </table>



    <table style="width: 100%; border: 1px solid #000; padding: 5px; margin-top: 10px; margin-bottom: 10px;">
        <thead>
            <tr>
                <th style="border-bottom: 1px solid #000; text-align: center;" colspan="2">DATOS DEL PUNTO DE PARTIDA
                    Y PUNTO DE LLEGADA
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span style="font-weight: bold;">Punto de Partida:</span>
                    {{ $document->sender_address_data['location_id'] ?? '' }}
                    - {{ $document->sender_address_data['address'] ?? '' }}</td>
            </tr>
            <tr>
                <td><span style="font-weight: bold;">Punto de Llegada:</span>
                    {{ $document->receiver_address_data['location_id'] ?? '' }}
                    - {{ $document->receiver_address_data['address'] ?? '' }}</td>
            </tr>


            @foreach ($document->dispatches_related as $related)
                <tr>
                    <td><span style="font-weight: bold;">Guias de remisión: </span>{{ $related->serie_number }} RUC:
                        {{ $related->company_number }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <table style="width: 100%; border: 1px solid #000; padding: 5px; margin-top: 10px; margin-bottom: 10px;">
        <thead>
            <tr>
                <th style="border-bottom: 1px solid #000; text-align: center;" colspan="2">DATOS DE IDENTIFICACIÓN DE
                    LA UNIDAD DE TRANSPORTE Y DEL CONDUCTOR
                </th>
            </tr>
        </thead>
        <tbody>
            @if ($company->mtc_auth)
                <tr>
                    <td colspan="2"><span style="font-weight: bold;">Número de autorización MTC:</span>
                        {{ $company->mtc_auth }}</td>
                </tr>
            @endif
            @if ($document->transport_data)
                <tr>
                    <td><span style="font-weight: bold;">Número de placa del vehículo:
                        </span>{{ $document->transport_data['plate_number'] ?? '' }}</td>
                    @if (isset($document->transport_data['auth_plate_primary']))
                        <td><span style="font-weight: bold;">Autorización de placa principal:
                            </span>{{ $document->transport_data['auth_plate_primary'] ?? '' }}</td>
                    @endif
                </tr>
                @if (isset($document->transport_data['tuc']))
                    <tr>
                        <td><span style="font-weight: bold;">T.U.C:</span>
                            {{ $document->transport_data['tuc'] ?? '' }}</td>
                    </tr>
                @endif
                <tr>
                    @if (isset($document->secondary_transport_data['secondary_plate_number']))
                        <td><span style="font-weight: bold;">Número de placa semiremolque:</span>
                            {{ $document->secondary_transport_data['secondary_plate_number'] ?? '' }}</td>
                    @endif
                    @if (isset($document->secondary_transport_data['auth_plate_secondary']))
                        <td><span style="font-weight: bold;">Autorización de placa
                                semiremolque:</span>{{ $document->secondary_transport_data['auth_plate_secondary'] ?? '' }}
                        </td>
                    @endif
                </tr>
            @if (isset($document->secondary_transport_data['tuc_secondary']))
                <tr>
                    <td><span style="font-weight: bold;">T.U.C semiremolque:</span>
                        {{ $document->secondary_transport_data['tuc_secondary'] ?? '' }}</td>
                </tr>
            @endif

                <tr>
                    <td><span style="font-weight: bold;">Modelo del vehículo:</span>
                        {{ $document->transport_data['model'] ?? '' }}</td>



                </tr>
            @endif
            @if ($document->tracto_carreta)
                <tr>
                    <td><span style="font-weight: bold;">Marca de tracto carreta:
                        </span>{{ $document->tracto_carreta }}</td>
                </tr>
            @endif
            @if ($document->driver->name)
                <tr>
                    <td><span style="font-weight: bold;">Nombre Conductor: </span>{{ $document->driver->name ?? '' }}
                    </td>
                    @isset($document->secondary_driver->name)
                        <td><span style="font-weight: bold;">Nombre Conductor Secundario:
                            </span>{{ $document->secondary_driver->name ?? '' }}</td>
                    @endisset
                </tr>
            @endif
            @if ($document->driver->number)
                <tr>
                    <td><span style="font-weight: bold;">Documento Conductor:</span>
                        {{ $document->driver->number ?? '' }}</td>
                    @isset($document->secondary_driver->number)
                        <td><span style="font-weight: bold;">Documento Conductor Secundario:</span>
                            {{ $document->secondary_driver->number ?? '' }}</td>
                    @endisset
                </tr>
            @endif
            @if ($document->driver->license)
                <tr>
                    <td><span style="font-weight: bold;">Licencia del conductor:
                        </span>{{ $document->driver->license ?? '' }}</td>
                    @isset($document->secondary_driver->license)
                        <td><span style="font-weight: bold;">Licencia del conductor secundario:
                            </span>{{ $document->secondary_driver->license ?? '' }}</td>
                    @endisset
                </tr>
            @endif
        </tbody>
    </table>
    @if ($document->flete_payer_id)
        <table style="width: 100%; border: 1px solid #000; padding: 5px; margin-top: 10px; margin-bottom: 10px;">
            <tr>
                <td colspan="2" style="font-weight: bold; border-bottom: 1px solid #000; text-align: center;">DATOS
                    DEL PAGADOR DEL
                    FLETE
                </td>
            </tr>
            <tr>
                <td><span style="font-weight: bold;">Razón Social/ Nombres:
                    </span>{{ $document->flete_payer->name ?? '' }}
                </td>
                <td><span style="font-weight: bold;">RUC/Doc. Identidad:
                    </span>{{ $document->flete_payer->number ?? '' }}
                </td>

            </tr>
        </table>
    @endif
    @if ($document->observations)
        <table style="width: 100%; border: 1px solid #000; padding: 5px; margin-top: 10px; margin-bottom: 10px;">
            <tr>
                <td style="font-weight: bold; border-bottom: 1px solid #000; text-align: center;">OBSERVACIONES
                </td>
            </tr>
            <tr>
                <td>{{ $document->observations ?? '' }}
                </td>
            </tr>


        </table>
    @endif
    <table style="width: 100%; border: 1px solid #000; padding: 5px; margin-top: 10px; margin-bottom: 10px;">
        <thead>
            <tr>
                <th style="border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: center;">Item</th>
                <th style="border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: center;">Código</th>
                <th style="border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: left;">Descripción
                </th>
                <th style="border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: left;">Modelo</th>
                <th style="border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: center;">Unidad</th>
                <th style="border-top: 1px solid #000; border-bottom: 1px solid #000; text-align: right;">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="text-align: center;">{{ $row->item->internal_id ?? '' }}</td>
                    <td style="text-align: left;">
                        @if ($row->name_product_pdf)
                            {!! $row->name_product_pdf !!}
                        @else
                            {!! $row->item->description !!}
                        @endif
                        @if ($row->attributes)
                            @foreach ($row->attributes as $attr)
                                <br /><span style="font-size: 9px">{{ $attr->description ?? '' }} :
                                    {{ $attr->value ?? '' }}</span>
                            @endforeach
                        @endif
                        @if ($row->discounts)
                            @foreach ($row->discounts as $dtos)
                                <br /><span style="font-size: 9px">{{ $dtos->factor * 100 }}%
                                    {{ $dtos->description ?? '' }}</span>
                            @endforeach
                        @endif
                        @if ($row->relation_item->is_set == 1)
                            <br>
                            @inject('itemSet', 'App\Services\ItemSetService')
                            @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                                {{ $item }}<br>
                            @endforeach
                        @endif
                        @if ($document->has_prepayment)
                            <br>
                            *** Pago Anticipado ***
                        @endif
                    </td>
                    <td style="text-align: left;">{{ $row->item->model ?? '' }}</td>
                    <td style="text-align: center;">{{ symbol_or_code($row->item->unit_type_id ?? '') }}</td>
                    <td style="text-align: right;">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>




    <br>
    <br>
    <br>
    <br>
    <br>
    <br>

    <table style="width: 100%; text-align: center;">
        <tr>
            <td style="width: 50%;">
                <hr
                    style="border: 1px solid #000; margin-bottom: 5px; width: 80%; margin-left: auto; margin-right: auto;">
                <h4 style="margin: 2px 0;">{{ $company->name }}</h4>
            </td>
            <td style="width: 50%;">
                <hr
                    style="border: 1px solid #000; margin-bottom: 5px; width: 80%; margin-left: auto; margin-right: auto;">

                <h5 style="margin: 2px 0;">CONFORMIDAD DEL CLIENTE</h5>
                <p>Sr(a): ___________________________________________</p>
            </td>
        </tr>
    </table>

    @if ($document->qr)
        <table class="full-width">
            <tr>
                <td class="text-left">
                    <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px;" />
                </td>
            </tr>
        </table>
    @endif
</body>

</html>
