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
    $logo = null;
    if (!$document->alter_company) {
        $establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
        $logo = $establishment__->logo ?? $company->logo;
    }

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
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
    @if (
        $document->state_type->id == '11' ||
            $document->state_type->id == '09' ||
            $document->state_type->id == '55' ||
            $document->state_type->id == '01' ||
            $document->state_type->id == '03')
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
    <table class="full-width">
        <tr>
            @if ($company->logo)
                <td width="10%">
                    <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                        alt="{{ $company->name }}" alt="{{ $company->name }}" class="company_logo"
                        style="max-width: 300px">
                </td>
            @else
                <td width="10%">
                    {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 600px"> --}}
                </td>
            @endif
            <td width="40%" class="border-box bg-blue-light" style="padding: 0px;">
                <table width="100%">
                    <tr>
                        <td class="text-center h4">
                            R.U.C. N° {{ $company->number }}
                        </td>
                    </tr>
                    <tr>
                        <td class=" h4 text-center">
                            GUÍA DE REMISIÓN REMITENTE
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
                <th colspan="2" class="bg-blue-light text-left ">
                    DATOS GENERALES
                </th>
            </tr>
        </thead>
        <tbody>
            @if ($document->is_transport_category_m1l)
                <tr>
                    <td colspan="2" class="text-left desc">
                        <strong>Indicador traslado en vehículos de categoría M1 o L: Si</strong>
                    </td>
                </tr>
            @endif
            <tr>
                <td width="50%" class="text-left desc">
                    <strong>Punto de partida:</strong> <br>
                    {{ $document->origin ? $document->origin->address : null }}
                    <br> {{ $document->origin ? func_get_location($document->origin->location_id) : null }}
                </td>
                <td width="50%" class="text-left desc">
                    <strong>
                        Punto de llegada:
                    </strong> <br>
                    {{ $document->delivery ? $document->delivery->address : null }}
                    <br> {{ $document->delivery ? func_get_location($document->delivery->location_id) : null }}
                </td>
            </tr>
            <tr>
                <td class="text-left desc">
                    <strong>Motivo Traslado:</strong>
                    <br> {{ $document->transfer_reason_type->description }}
                </td>
                <td class="text-left desc">
                    <strong>Modalidad de Transporte:</strong>
                    <br> {{ $document->transport_mode_type->description }}
                </td>
            </tr>

            <tr>
                <td class="text-left desc">
                    <strong>Fecha de emisión:</strong>
                    <br> {{ $document->date_of_issue->format('d/m/Y') }}
                </td>
                <td class="text-left desc">
                    <strong>Fecha de traslado:</strong>
                    <br> {{ $document->date_of_shipping->format('d/m/Y') }}
                </td>
            </tr>
            @if($document->related_document)
            <tr>
                <td class="text-left desc">
                    <strong>Documento relacionado:</strong>
                    <br> {{ $document->related_document}}
                </td>
                <td></td>
            </tr>
            @endif
            <tr>
                @if ($document->transfer_reason_description)
                    <td class="text-left desc">
                        <strong>Descripción de motivo de traslado:</strong>
                        <br>{{ $document->transfer_reason_description }}
                    </td>
                @endif
                @if ($document->order_form_external)
                    <td class="text-left desc">
                        <strong>Orden de pedido:</strong>
                        <br>{{ $document->order_form_external }}
                    </td>
                @endif
            </tr>

            <tr>
                @if ($document->observations)
                    <td class="text-left desc">
                        <strong>Observaciones:</strong>
                        <br>{{ $document->observations }}
                    </td>
                @endif
                @if ($document->reference_document)
                    <td class="text-left desc">
                        <strong>{{ $document->reference_document->document_type->description }}</strong>
                        <br>{{ $document->reference_document ? $document->reference_document->number_full : '' }}
                    </td>
                @endif
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
                <td width="50%" class="text-left desc ">
                    <strong>Nombre o razón social:</strong> <br>
                    {{ $customer->name }}
                </td>
                <td width="50%" class="text-left desc">
                    <strong>
                        Tipo y número de identificación:
                    </strong> <br>
                    {{ $customer->identity_document_type->description }}: {{ $customer->number }}
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-left desc ">
                    <strong>Dirección:</strong> <br>

                    @if ($document->transfer_reason_type_id === '09')
                        {{ $customer->address }} - {{ $customer->country->description }}
                    @else
                        {{ $customer->address }}
                        {{ $customer->district_id !== '-' ? ', ' . $customer->district->description : '' }}
                        {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                        {{ $customer->department_id !== '-' ? '- ' . $customer->department->description : '' }}
                    @endif
                </td>
            </tr>

            <tr>
                <td class="text-left desc ">
                    <strong>Número de bultos:</strong> <br> {{ $document->packages_number }} bultos
                </td>
                <td class="text-left desc">
                    <strong>Peso bruto total:</strong> <br> {{ $document->total_weight }}
                    {{ $document->unit_type_id }}
                </td>
            </tr>

        </tbody>
    </table>
    @if ($document->supplier_id)
        <table class="full-width mt-10 ">
            <thead>
                <tr>
                    <th colspan="2" class="bg-blue-light text-left ">
                        DATOS DEL PROVEEDOR
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left desc">
                        <strong>Razón social:</strong> <br> {{ $document->supplier->name }}

                    </td>
                    <td width="50%" class="text-left desc">
                        <strong>
                            Tipo y número de identificación:
                        </strong> <br>
                        {{ $document->supplier->identity_document_type->description }}:
                        {{ $document->supplier->number }}
                    </td>
                </tr>
                <tr>
                    <td class="text-left desc">
                        <strong>Dirección:</strong><br>
                        {{ $document->supplier->address }}
                        {{ $document->supplier->district_id !== '-' ? ', ' . $document->supplier->district->description : '' }}
                        {{ $document->supplier->province_id !== '-' ? ', ' . $document->supplier->province->description : '' }}
                        {{ $document->supplier->department_id !== '-' ? '- ' . $document->supplier->department->description : '' }}
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @endif
    <table class="full-width mt-10 ">
        <thead>
            <tr>
                <th colspan="2" class="bg-blue-light text-left ">
                    DATOS DEL TRANSPORTE Y TRASLADO
                </th>
            </tr>
        </thead>
        <tbody>
            @if ($document->is_transport_category_m1l)
                @if ($document->plate_number)
                    <tr>
                        <td colspan="2">Placa de vehículo: {{ $document->plate_number }}</td>
                    </tr>
                @endif
            @else
                @if ($document->transport_mode_type_id === '01')
                    @php
                        $document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail(
                            $document->dispatcher->identity_document_type_id,
                        );
                    @endphp
                    <tr>
                        <td width="50%" class="text-left desc ">
                            <strong>
                                Nombre y/o razón social:
                            </strong>
                            {{ $document->dispatcher->name }}
                        </td>
                        <td width="50%" class="text-left desc ">
                            <strong>
                                {{ $document_type_dispatcher->description }}:
                            </strong>
                            {{ $document->dispatcher->number }}
                        </td>
                    </tr>
                @endif


                @if ($document->transport_data)
                    <tr>
                        <td width="50%" class="text-left desc "><strong>Número de placa del
                                vehículo:</strong> {{ $document->transport_data['plate_number'] }}</td>
                        @if (isset($document->transport_data['auth_plate_primary']))
                            <td width="50%" class="text-left desc "><strong>Autorización de placa
                                    principal:</strong> {{ $document->transport_data['auth_plate_primary'] }}</td>
                        @endif
                    </tr>
                    <tr>
                        @if (isset($document->secondary_transport_data['secondary_plate_number']))
                            <td width="50%" class="text-left desc "><strong>Número de placa secundaria del
                                    vehículo:</strong>
                                {{ $document->secondary_transport_data['secondary_plate_number'] }}</td>
                        @else
                            @if (isset($document->transport_data['secondary_plate_number']))
                                <td width="50%" class="text-left desc "><strong>Número de placa secundaria
                                        del vehículo:</strong>
                                    {{ $document->transport_data['secondary_plate_number'] }}</td>
                            @endif
                        @endif
                        @if (isset($document->secondary_transport_data['auth_plate_secondary']))
                            <td width="50%" class="text-left desc "><strong>Autorización de placa
                                    secundaria:</strong>
                                {{ $document->secondary_transport_data['auth_plate_secondary'] }}</td>
                        @else
                            @if (isset($document->transport_data['auth_plate_secondary']))
                                <td width="50%" class="text-left desc "><strong>Autorización de placa
                                        secundaria:</strong>
                                    {{ $document->transport_data['auth_plate_secondary'] }}
                                </td>
                            @endif
                        @endif
                    </tr>
                    @isset($document->driver->name)
                        <tr>
                            <td width="50%" class="text-left desc "><strong>Nombre Conductor:</strong>
                                {{ $document->driver->name }}</td>
                            <td width="50%" class="text-left desc "><strong>Documento Conductor:</strong>
                                {{ $document->driver->number }}</td>
                        </tr>
                    @endisset
                    @isset($document->secondary_driver->name)
                        <tr>
                            <td width="50%" class="text-left desc "><strong>Nombre Conductor
                                    Secundario:</strong> {{ $document->secondary_driver->name }}</td>
                            <td width="50%" class="text-left desc "><strong>Documento Conductor
                                    Secundario:</strong> {{ $document->secondary_driver->number }}</td>
                        </tr>
                    @endisset
                    <tr>
                        <td width="50%" colspan="2" class="text-left desc "><strong>Modelo del vehículo:</strong>
                            {{ $document->transport_data['model'] }}</td>
                    </tr>
                @endif
                @if ($document->tracto_carreta)
                    <tr>
                        <td width="50%" colspan="2" class="text-left desc "><strong>Marca de tracto
                                carreta:</strong>
                            {{ $document->tracto_carreta }}</td>
                    </tr>
                @endif


                @isset($document->driver->license)
                    <tr>
                        <td width="50%" colspan="2" class="text-left desc "><strong>Licencia del
                                conductor:</strong>
                            {{ $document->driver->license }}</td>
                    </tr>
                @endisset

            @endif
        </tbody>
    </table>

    <table class="full-width mt-5">
        <thead>
            <tr>
                <th class="border-box text-center bg-grey desc"width="4%">#</th>
                <th class="border-box text-center bg-grey desc" width="10%">Código</th>
                <th class="border-box text-center bg-grey desc">Descripción</th>
                <th class="border-box text-center bg-grey desc" width="22%">DUA</th>
                <th class="border-box text-center bg-grey desc" width="10%">Cantidad</th>
                <th class="border-box text-center bg-grey desc" width="10%">U.M.</th>

            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $idx => $row)
                <tr>
                    <td class="border-box bg-grey-light text-center desc">{{ $idx + 1 }}</td>
                    <td class="border-box bg-grey-light text-center desc">{{ $row->item->internal_id }}</td>
                    <td class="border-box bg-grey-light text-left desc">{{ $row->item->description }}</td>
                    <td class="border-box bg-grey-light text-right desc" valign="top">
                        @if (isset($row->item->IdLoteSelected))
                            @foreach ($row->item->IdLoteSelected as $lote)
                                <div>{{ $lote->code }} : {{ $lote->date_of_due }}</div>
                            @endforeach
                        @endif


                    </td>
                    <td class="border-box bg-grey-light text-right desc">{{ $row->quantity }}</td>
                    <td class="border-box bg-grey-light text-center desc">
                        {{ symbol_or_code($row->item->unit_type_id) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if ($document['qr'])
        <table class="full-width">
            <tr>
                <td class="text-left">
                    <img src="data:image/png;base64, {{ $document['qr'] }}" style="margin-right: -10px;" />
                </td>
            </tr>
        </table>
    @endif



</body>

</html>
