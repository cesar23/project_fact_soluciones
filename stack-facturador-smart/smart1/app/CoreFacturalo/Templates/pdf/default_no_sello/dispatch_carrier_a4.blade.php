@php
    $establishment = $document->establishment;
    $configuration = \App\Models\Tenant\Configuration::first();
    $configurations = \App\Models\Tenant\Configuration::first();
    $company_name = $company->name;
    $company_owner = null;

    if ($configurations && $configurations->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }

    $establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $logo = $establishment__->logo ?? $company->logo;

    if ($logo === null || !file_exists(public_path($logo))) {
        $logo = $company->logo;
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

    $customer = $document->customer;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');

    $document_number = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
    // $document_type_driver = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->driver->identity_document_type_id);
    // dd($document->items);
@endphp
<html>

<head>
    {{-- <title>{{ $document_number }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>
    <table class="full-width">
        <tr>
            @if ($company->logo)
                <td width="10%">
                    <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                        alt="{{ $company->name }}" class="company_logo"
                        style="max-width: 300px">
                </td>
            @else
                <td width="10%">
                    {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px"> --}}
                </td>
            @endif
            <td width="50%" class="pl-3">
                <div class="text-left">
                    <h4>{{ $company_name }}</h4>
                    @if ($company_owner)
                        De: {{ $company_owner }}
                    @endif
                    <h5 style="text-transform: uppercase;">
                        {{ $establishment->address !== '-' ? $establishment->address : '' }}
                        {{ $establishment->district_id !== '-' && isset($establishment->district) ? ', ' . $establishment->district->description : '' }}
                        {{ $establishment->province_id !== '-' && isset($establishment->province) ? ', ' . $establishment->province->description : '' }}
                        {{ $establishment->department_id !== '-' && isset($establishment->department) ? '- ' . $establishment->department->description : '' }}
                    </h5>
                    <h5>{{ $establishment->email !== '-' ? $establishment->email : '' }}</h5>
                    <h5>{{ $establishment->telephone !== '-' ? $establishment->telephone : '' }}</h5>
                </div>
            </td>
            <td width="40%" class="border-box bg-blue-light" style="padding: 0px;">
                <table width="100%">
                    <tr>
                        <td class="text-center h4">
                            R.U.C. N° {{ $company->number }}
                        </td>
                    </tr>
                    <tr>
                        <td class=" h4 text-center">
                            GUÍA DE REMISIÓN TRANSPORTISTA
                        </td>
                    </tr>
                    <tr>
                        <td class="h3 text-center">
                            {{ $document_number }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="full-width mt-10">
        <thead>
            <tr>
                <th colspan="2" class="bg-blue-light text-left">
                    DATOS GENERALES
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%" class="text-left desc">
                    <strong>Punto de partida:</strong> <br>
                    {{ optional($document->sender_address_data)['address'] }}

                    @if (isset($document->sender_address_data['location_id']))
                        <br> {{ func_get_location($document->sender_address_data['location_id']) }}
                    @endif
                </td>
                <td width="50%" class="text-left desc">
                    <strong>
                        Punto de llegada:
                    </strong> <br>
                    {{ optional($document->receiver_address_data)['address'] }}

                    @if (isset($document->receiver_address_data['location_id']))
                        <br> {{ func_get_location($document->receiver_address_data['location_id']) }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="text-left desc">
                    <strong>Fecha de emisión:</strong>
                    {{ $document->date_of_issue->format('d/m/Y') }}
                </td>
                <td class="text-left desc">
                    <strong>Fecha de traslado:</strong>
                    {{ $document->date_of_shipping->format('d/m/Y') }}
                </td>
            </tr>
            @if ($document->flete_payer)
                <tr>
                    <td class="text-left desc">
                        <strong>Pagador del flete:</strong>
                        {{ $document->flete_payer->name ?? '' }}
                    </td>
                    <td class="text-left desc">
                        <strong>RUC/Doc. del pagador:</strong>
                        {{ $document->flete_payer->number ?? '' }}
                    </td>
                </tr>
            @endif
            {{--@if($document->subcontract_company)
                <tr>
                    <td class="text-left desc">
                        <strong>Subcontratista:</strong>
                        {{ $document->subcontract_company->name ?? '' }}
                    </td>
                    <td class="text-left desc">
                        <strong>RUC/Doc. del subcontratista:</strong>
                        {{ $document->subcontract_company->number ?? '' }}
                    </td>
                </tr>
            @endif--}}
            <tr>
                <td colspan="2" class="text-left desc">
                    <strong>Documentos relacionados:</strong><br>
                    @foreach ($document->dispatches_related as $related)
                        Guía remitente {{ $related->serie_number }} RUC: {{ $related->company_number }}<br>
                    @endforeach
                </td>
            </tr>
        </tbody>
    </table>
    <table class="full-width mt-10">
        <thead>
            <tr>
                <th colspan="2" class="bg-blue-light text-left">
                    DATOS DEL REMITENTE
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%" class="text-left desc">
                    <strong>Nombre o razón social:</strong> <br>
                    {{ $document->sender_data ? $document->sender_data['name'] : '' }}
                </td>
                <td width="50%" class="text-left desc">
                    <strong>
                        Tipo y número de identificación:
                    </strong> <br>
                    {{ $document->sender_data ? $document->sender_data['identity_document_type_description'] : '' }}:
                    {{ $document->sender_data ? $document->sender_data['number'] : '' }}
                </td>
            </tr>
        </tbody>
    </table>
    <table class="full-width mt-10">
        <thead>
            <tr>
                <th colspan="2" class="bg-blue-light text-left">
                    DATOS DEL DESTINATARIO
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%" class="text-left desc">
                    <strong>Nombre o razón social:</strong> <br>
                    {{ $document->receiver_data ? $document->receiver_data['name'] : '' }}
                </td>
                <td width="50%" class="text-left desc">
                    <strong>
                        Tipo y número de identificación:
                    </strong> <br>
                    {{ $document->receiver_data ? $document->receiver_data['identity_document_type_description'] : '' }}:
                    {{ $document->receiver_data ? $document->receiver_data['number'] : '' }}
                </td>
            </tr>
        </tbody>
    </table>
    <table class="full-width mt-10">
        <thead>
            <tr>
                <th colspan="2" class="bg-blue-light text-left">
                    DATOS DEL TRANSPORTE Y TRASLADO
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%" class="text-left desc">
                    <strong>Número de bultos:</strong> {{ $document->packages_number }} bultos
                </td>
                <td width="50%" class="text-left desc">
                    <strong>Peso bruto total:</strong> {{ $document->total_weight }} {{ $document->unit_type_id }}
                </td>
            </tr>

            @if ($company->mtc_auth)
                <tr>
                    <td width="50%" colspan="2" class="text-left desc"><strong>Número de autorización
                            MTC:</strong> {{ $company->mtc_auth }}</td>
                </tr>
            @endif
            @if ($document->transport_data)
                <tr>
                    <td width="50%" class="text-left desc"><strong>Número de placa del vehículo:</strong>
                        {{ $document->transport_data['plate_number'] }}</td>
                    @if (isset($document->transport_data['auth_plate_primary']))
                        <td width="50%" class="text-left desc"><strong>Autorización de placa principal:</strong>
                            {{ $document->transport_data['auth_plate_primary'] }}</td>
                    @endif
                </tr>
                @if (isset($document->transport_data['tuc']))
                    <tr>
                        <td width="50%" class="text-left desc"><strong>Tarjeta Única de Circulación Principal:</strong>
                            {{ $document->transport_data['tuc'] ?? '' }}
                        </td>
                    </tr>
                @endif
                <tr>
                    @if (isset($document->secondary_transport_data['secondary_plate_number']))
                        <td width="50%" class="text-left desc"><strong>Número de placa secundaria del
                                vehículo:</strong>
                            {{ $document->secondary_transport_data['secondary_plate_number'] }}</td>
                    @elseif (isset($document->transport_data['secondary_plate_number']))
                        <td width="50%" class="text-left desc"><strong>Número de placa secundaria del
                                vehículo:</strong>
                            {{ $document->transport_data['secondary_plate_number'] }}</td>
                    @endif

                    @if (isset($document->secondary_transport_data['auth_plate_secondary']))
                        <td width="50%" class="text-left desc"><strong>Autorización de placa secundaria:</strong>
                            {{ $document->secondary_transport_data['auth_plate_secondary'] }}</td>
                    @elseif (isset($document->transport_data['auth_plate_secondary']))
                        <td width="50%" class="text-left desc"><strong>Autorización de placa
                                secundaria:</strong>
                            {{ $document->transport_data['auth_plate_secondary'] }}
                        </td>
                    @endif
                </tr>
                @if (isset($document->transport_data['tuc_secondary']))
                    <tr>
                        <td width="50%" class="text-left desc"><strong>Tarjeta Única de Circulación secundaria:</strong>
                            {{ $document->transport_data['tuc_secondary'] }}
                        </td>
                    </tr>
                @endif
                @if (isset($document->driver) && $document->driver->name)
                    <tr>
                        <td width="50%" class="text-left desc"><strong>Nombre Conductor:</strong>
                            {{ $document->driver->name }}</td>
                        <td width="50%" class="text-left desc"><strong>Documento Conductor:</strong>
                            {{ $document->driver->number }}</td>
                    </tr>
                @endif
                @if (isset($document->driver) && $document->driver->license)
                    <tr>
                        <td width="50%" colspan="2" class="text-left desc"><strong>Licencia del conductor:</strong>
                            {{ $document->driver->license }}</td>
                    </tr>
                @endif
                @if (isset($document->secondary_driver) && $document->secondary_driver->name)
                    <tr>
                        <td width="50%" class="text-left desc"><strong>Nombre Conductor Secundario:</strong>
                            {{ $document->secondary_driver->name }}</td>
                        <td width="50%" class="text-left desc"><strong>Documento Conductor Secundario:</strong>
                            {{ $document->secondary_driver->number }}</td>
                    </tr>
                @endif
                @if (isset($document->secondary_driver) && $document->secondary_driver->license)
                    <tr>
                        <td width="50%" colspan="2" class="text-left desc"><strong>Licencia del conductor Secundario:</strong>
                            {{ $document->secondary_driver->license }}</td>
                    </tr>
                @endif
                <tr>
                    <td width="50%" class="text-left desc"><strong>Modelo del vehículo:</strong>
                        {{ $document->transport_data['model'] }}
                    </td>
                    @if (isset($document->transport_data['configuration']))
                        <td width="50%" class="text-left desc"><strong>Configuración vehícular:</strong>
                            {{ $document->transport_data['configuration'] }}
                        </td>
                </tr>
                    @endif
            @endif
            @if ($document->tracto_carreta)
                <tr>
                    <td width="50%" colspan="2" class="text-left desc"><strong>Marca de tracto carreta:</strong>
                        {{ $document->tracto_carreta }}</td>
                </tr>
            @endif
            @if ($document->observations)
                <tr>
                    <td width="50%" colspan="2" class="text-left desc"><strong>Observaciones:</strong>
                        {{ $document->observations }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="full-width mt-10">
        <thead>
            <tr>
                <th class="border-box text-center bg-grey desc">N°</th>
                <th class="border-box text-center bg-grey desc">Código</th>
                <th class="border-box text-center bg-grey desc">
                    Descripción
                </th>
                <th class="border-box text-center bg-grey desc">Unidad</th>
                <th class="border-box text-center bg-grey desc">Cantidad</th>
                <th class="border-box text-center bg-grey desc">Peso</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $idx => $row)
                <tr>
                    <td class="border-box bg-grey-light text-center desc">{{ $idx + 1 }}</td>
                    <td class="border-box bg-grey-light text-center desc">{{ $row->item->internal_id }}</td>
                    <td class="border-box bg-grey-light text-left desc">{{ $row->item->description }}</td>
                    <td class="border-box bg-grey-light text-center desc">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="border-box bg-grey-light text-right desc">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 2) }}
                        @endif
                    </td>
                    <td class="border-box bg-grey-light text-right desc">{{ number_format($row->item->weight, 2) }} {{ $document->unit_type_id }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if (isset($document['qr']) && $document['qr'])
        <table class="full-width">
            <tr>
                <td class="text-left">
                    <img src="data:image/png;base64, {{ $document['qr'] }}" style="margin-right: -10px;" />
                </td>
            </tr>
        </table>
    @endif

    @if ($configurations->terms_condition_dispatches)
        <br>
        <table class="full-width">
            <tr>
                <td>
                    <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                    {!! $configurations->terms_condition_dispatches !!}
                </td>
            </tr>
        </table>
    @endif
</body>

</html>
