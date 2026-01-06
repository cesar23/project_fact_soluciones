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

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

    $document_number = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
@endphp
<html>

<head>
</head>

<body>
    <table class="full-width">
        <tr>
            @if ($company->logo)
                <td width="10%">
                    <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                        alt="{{ $company->name }}" class="company_logo" style="max-width: 300px">
                </td>
            @else
                <td width="10%">
                    {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px"> --}}
                </td>
            @endif
            <td width="50%" class="pl-3">
                <div class="text-left">
                    <h3 class="">{{ $company->name }}</h3>
                    <h4>{{ 'RUC ' . $company->number }}</h4>
                    <h5 style="text-transform: uppercase;">
                        {{ $establishment->address !== '-' ? $establishment->address : '' }}
                        {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                        {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                        {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                    </h5>
                    <h5>{{ $establishment->email !== '-' ? $establishment->email : '' }}</h5>
                    <h5>{{ $establishment->telephone !== '-' ? $establishment->telephone : '' }}</h5>
                </div>
            </td>
            <td width="40%" class="border-box p-4 text-center">
                <h4 class="text-center">{{ $document->document_type->description }}</h4>
                <h3 class="text-center">{{ $document_number }}</h3>
            </td>
        </tr>
    </table>
    <table class="full-width border-box mt-10 mb-10">
        <tbody>
            <tr>
                <td>Fecha Emisión: {{ $document->date_of_issue->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td>Fecha Inicio de Traslado: {{ $document->date_of_shipping->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td>Peso Bruto Total({{ $document->unit_type_id }}): {{ $document->total_weight }}</td>
            </tr>
            <tr>
                <td>Punto de Partida: {{ $document->sender_address_data['location_id'] }}
                    - {{ $document->sender_address_data['address'] }}</td>
            </tr>
            <tr>
                <td>Punto de Llegada: {{ $document->receiver_address_data['location_id'] }}
                    - {{ $document->receiver_address_data['address'] }}</td>
            </tr>
            <tr>
                <td>Datos del Remitente: {{ $document->sender_data['name'] }}
                    - {{ $document->sender_data['identity_document_type_description'] }}
                    {{ $document->sender_data['number'] }}</td>
            </tr>
            <tr>
                <td>Datos del Destinatario: {{ $document->receiver_data['name'] }}
                    - {{ $document->receiver_data['identity_document_type_description'] }}
                    {{ $document->receiver_data['number'] }}</td>
            </tr>
            @foreach ($document->dispatches_related as $related)
                <tr>
                    <td>Guias de remisión: {{ $related->serie_number }} RUC: {{ $related->company_number }}</td>

                </tr>
            @endforeach
        </tbody>
    </table>
    <table class="full-width border-box mt-10 mb-10">
        <thead>
            <tr>
                <th class="border-bottom text-left" colspan="2">TRANSPORTE</th>
            </tr>
        </thead>
        <tbody>

            @if ($company->mtc_auth)
                <tr>
                    <td colspan="2">Número de autorización MTC: {{ $company->mtc_auth }}</td>
                </tr>
            @endif
            @if ($document->transport_data)
                <tr>
                    <td>Número de placa del vehículo: {{ $document->transport_data['plate_number'] }}</td>
                    @if (isset($document->transport_data['auth_plate_primary']))
                        <td>Autorización de placa principal: {{ $document->transport_data['auth_plate_primary'] }}</td>
                    @endif
                </tr>
                <tr>
                    @if (isset($document->secondary_transport_data['secondary_plate_number']))
                        <td>Número de placa secundaria del vehículo:
                            {{ $document->secondary_transport_data['secondary_plate_number'] }}</td>
                    @else
                        @if (isset($document->transport_data['secondary_plate_number']))
                            <td>Número de placa secundaria del vehículo:
                                {{ $document->transport_data['secondary_plate_number'] }}</td>
                        @endif
                    @endif
                    @if (isset($document->secondary_transport_data['auth_plate_secondary']))
                        <td>Autorización de placa secundaria:
                            {{ $document->secondary_transport_data['auth_plate_secondary'] }}</td>
                    @else
                        @if (isset($document->transport_data['auth_plate_secondary']))
                            <td>Autorización de placa secundaria:
                                {{ $document->transport_data['auth_plate_secondary'] }}
                            </td>
                        @endif
                    @endif
                </tr>
                @if ($document->driver->name)
                    <tr>
                        <td>Nombre Conductor: {{ $document->driver->name }}</td>
                        <td>Documento Conductor: {{ $document->driver->number }}</td>
                    </tr>
                @endif
                @isset ($document->secondary_driver->name)
                    <tr>
                        <td>Nombre Conductor Secundario: {{ $document->secondary_driver->name }}</td>
                        <td>Documento Conductor Secundario: {{ $document->secondary_driver->number }}</td>
                    </tr>
                @endisset
                <tr>
                    <td>Modelo del vehículo: {{ $document->transport_data['model'] }}</td>
                </tr>
            @endif
            @if ($document->tracto_carreta)
                <tr>
                    <td>Marca de tracto carreta: {{ $document->tracto_carreta }}</td>
                </tr>
            @endif


            @if ($document->driver->license)
                <tr>
                    <td>Licencia del conductor: {{ $document->driver->license }}</td>
                </tr>
            @endif
        </tbody>
    </table>
    @if ($document->flete_payment_name && $document->flete_payment_number)

            <tr>
                <td colspan="2" style="font-weight: bold; border-bottom: 1px solid #000; text-align: center;">DATOS
                    DEL PAGADOR DEL
                    FLETE
                </td>
            </tr>
            <tr>
                <td><span style="font-weight: bold;">Razón Social/ Nombres
                    </span>{{ $document->flete_payment_name ?? '' }}
                </td>
                <td><span style="font-weight: bold;">RUC/Doc. Identidad:
                    </span>{{ $document->flete_payment_number ?? '' }}
                </td>

            </tr>

    @endif
    <table class="full-width border-box mt-10 mb-10">
        <thead class="">
            <tr>
                <th class="border-top-bottom text-center">Item</th>
                <th class="border-top-bottom text-center">Código</th>
                <th class="border-top-bottom text-left">Descripción</th>
                <th class="border-top-bottom text-left">Modelo</th>
                <th class="border-top-bottom text-center">Unidad</th>
                <th class="border-top-bottom text-right">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $row->item->internal_id }}</td>
                    <td class="text-left">
                        @if ($row->name_product_pdf)
                            {!! $row->name_product_pdf !!}
                        @else
                            {!! $row->item->description !!}
                        @endif



                        @if ($row->attributes)
                            @foreach ($row->attributes as $attr)
                                <br /><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                            @endforeach
                        @endif
                        @if ($row->discounts)
                            @foreach ($row->discounts as $dtos)
                                <br /><span style="font-size: 9px">{{ $dtos->factor * 100 }}%
                                    {{ $dtos->description }}</span>
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
                    <td class="text-left">{{ $row->item->model ?? '' }}</td>
                    <td class="text-center">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="text-right">
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
    @if ($document->observations)
        <table class="full-width border-box mt-10 mb-10">
            <tr>
                <td class="text-bold border-bottom font-bold">OBSERVACIONES</td>
            </tr>
            <tr>
                <td>{{ $document->observations }}</td>
            </tr>
        </table>
    @endif
    
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
