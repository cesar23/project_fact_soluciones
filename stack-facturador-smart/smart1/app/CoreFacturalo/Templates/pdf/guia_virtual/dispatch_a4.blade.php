@php
    $establishment = $document->establishment;
    $configuration = \App\Models\Tenant\Configuration::first();
    $configurations = \App\Models\Tenant\Configuration::first();
    $establishments = \App\Models\Tenant\Establishment::all();
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
    $is_itinerant = $document->transfer_reason_type_id === '18' ? true : false;
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
    <div style="width: 100%;">
        <div style="float: left; width: 70%;">
            <div style="width: 100%;">
                <div style="float: left; width: 50%;">
                    @if ($company->logo)
                        <div style="width: 350px;">
                            <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                                alt="{{ $company->name }}" alt="{{ $company->name }}" class="company_logo"
                                style="max-width: 300px">
                        </div>
                    @else
                        <div style="width: 350px;">
                            {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px"> --}}
                        </div>
                    @endif
                </div>
                <div style="float: left; width: 50%;text-align: center;">
                    <div>
                        <h4 style="padding:0;margin:0;">{{ $company_name }}</h4>
                    </div>
                    @foreach ($establishments as $establishment)
                        <div style="font-size: 9px;">
                            {{ $establishment->description }}: {{ $establishment->address }}
                        </div>
                        <div style="font-size: 9px;">
                            Cel: {{ $establishment->telephone }}
                        </div>
                        <div style="font-size: 9px;">
                            {{ $establishment->email }}
                        </div>
                    @endforeach
                </div>
            </div>
            <div style="border: 1px solid black; text-align: left; padding: 5px;">
                <div>
                    <strong>
                        Razón Social: {{ $customer->name }}
                    </strong>
                </div>
                <div>
                    <strong>
                        Dirección: {{ $customer->address }}
                    </strong>
                </div>

                <div>
                    <strong>
                        Ruc: {{ $customer->number }}
                    </strong>
                </div>
            </div>
        </div>
        <div style="float: right; width: 25%;">
            <div style="border: 1px solid black; text-align: center;">
                <h5 style="padding:2;margin:2;"><strong>
                        RUC: {{ $company->number }}
                    </strong></h5>
                <h5 style="padding:2;margin:2;">
                    <strong>
                        GUIA DE REMISIÓN REMITENTE
                    </strong>
                </h5>
                <h5 style="padding:2;margin:2;">
                    <strong>
                        {{ $document_number }}
                    </strong>
                </h5>
            </div>
        </div>
    </div>
    <div style="width: 100%;margin-top: 10px; padding: 5px; border: 1px solid black;">
        <div>
            <strong>
                Punto de partida:
            </strong>
            {{ $document->origin ? $document->origin->address : null }}
        </div>
        <div>
            <strong>
                Punto de llegada:
            </strong>
            {{ $document->delivery ? $document->delivery->address : null }}
        </div>
        <div style="width: 100%;">
            <div style="float: left; width: 50%;">
                <strong>
                    Fecha de emisión:
                </strong>
                {{ $document->date_of_issue->format('d/m/Y') }}
            </div>
            <div style="float: left; width: 50%;">
                <strong>
                    Fecha de traslado:
                </strong>
                {{ $document->date_of_shipping->format('d/m/Y') }}
            </div>
        </div>

    </div>

    <table class="full-width mt-2 border-box">
        {{-- N° DE ORDEN DE COMPRA			VENDEDOR						CONDICIÓN DE PAGO		 --}}
        <thead>
            <tr>
                <th class="border-box" style="background: #8ea9db">N° DE ORDEN DE COMPRA</th>
                <th class="border-box" style="background: #8ea9db">VENDEDOR</th>
                <th class="border-box" style="background: #8ea9db">CONDICIÓN DE PAGO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border-box" style="text-align: center;">
                    {{ $document->purchase_order }}
                </td>
                <td class="border-box" style="text-align: center;">
                    {{ $document->user->name }}
                </td>
                <td class="border-box">
                </td>
            </tr>
        </tbody>

    </table>
    <table class="full-width mt-2 border-box">
        {{-- N° DE ORDEN DE COMPRA			VENDEDOR						CONDICIÓN DE PAGO		 --}}
        <thead>
            <tr>
                <th colspan="7" class="border-box" style="background: #8ea9db">
                    DATOS DEL TRANSPORTE
                </th>
            </tr>
            <tr>
                {{-- EMPRESA		RUC	TRANSPORTISTA		DOC. IDENTIDAD		LIC. DE CONDUCIR	MARCA/MODELO		PLACA	 --}}
                <th class="border-box" style="background: #8ea9db" width="28%">EMPRESA</th>
                <th class="border-box" style="background: #8ea9db" width="13%">RUC</th>
                <th class="border-box" style="background: #8ea9db">TRANSPORTISTA</th>
                <th class="border-box" style="background: #8ea9db">DOC. IDENTIDAD</th>
                <th class="border-box" style="background: #8ea9db">LIC. DE CONDUCIR</th>
                <th class="border-box" style="background: #8ea9db">MARCA/MODELO</th>
                <th class="border-box" style="background: #8ea9db">PLACA</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border-box" style="text-align: center;">
                    @isset($document->dispatcher)
                        {{ $document->dispatcher->name }}
                    @endisset
                </td>
                <td class="border-box" style="text-align: center;">
                    @isset($document->dispatcher)
                        {{ $document->dispatcher->number }}
                    @endisset
                </td>
                <td class="border-box" style="text-align: center;">
                    @isset($document->driver)
                        {{ $document->driver->name }}
                    @endisset
                </td>
                <td class="border-box" style="text-align: center;">
                    @isset($document->driver)
                        {{ $document->driver->number }}
                    @endisset
                </td>
                <td class="border-box" style="text-align: center;">
                    @isset($document->driver)
                        {{ $document->driver->license }}
                    @endisset
                </td>
                <td class="border-box" style="text-align: center;">
                    @isset($document->transport_data)
                        {{ $document->transport_data['brand'] }}/{{ $document->transport_data['model'] }}
                    @endisset
                </td>
                <td class="border-box" style="text-align: center;">
                    @isset($document->transport_data)
                        {{ $document->transport_data['plate_number'] }}
                    @endisset
                </td>

            </tr>
        </tbody>

    </table>
    {{-- <table class="full-width mt-10">
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
                    @if (is_integrate_system())
                        {{ $document->origin ? func_get_location($document->origin->location_id) : null }}
                        <br>{{ $document->origin ? $document->origin->address : null }}
                    @else
                        {{ $document->origin ? $document->origin->address : null }}
                        <br> {{ $document->origin ? func_get_location($document->origin->location_id) : null }}
                    @endif
                </td>
                <td width="50%" class="text-left desc">
                    @if (!$is_itinerant)
                        <strong>
                            Punto de llegada:
                        </strong> <br>
                        @if (is_integrate_system())
                            {{ $document->delivery ? func_get_location($document->delivery->location_id) : null }} <br>
                            {{ $document->delivery ? $document->delivery->address : null }}
                        @else
                            {{ $document->delivery ? $document->delivery->address : null }}
                            <br> {{ $document->delivery ? func_get_location($document->delivery->location_id) : null }}
                        @endif
                    @endif
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
            <tr>
                @if ($document->related_document)
                    <td class="text-left desc">
                        <strong>Documento relacionado:</strong>
                        <br> {{ $document->related_document }}
                    </td>
                @endif
                @if ($document->purchase_order)
                    <td class="text-left desc">
                        <strong>Orden de compra:</strong>
                        <br>{{ $document->purchase_order }}
                    </td>
                @endif
            </tr>
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
            @if ($is_itinerant)
                <tr>
                    <td class="text-left desc ">
                        <strong>Número de bultos:</strong> <br> {{ $document->packages_number }} bultos
                    </td>
                    <td class="text-left desc">
                        <strong>Peso bruto total:</strong> <br> {{ $document->total_weight }}
                        {{ $document->unit_type_id }}
                    </td>
                </tr>
            @endif

        </tbody>
    </table>
    @if (!$is_itinerant)
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
                    @if (is_integrate_system())
                        <td class="text-left desc ">
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
                        <td class="text-left desc ">
                            <strong>Teléfono:</strong> <br>
                            {{ $customer->telephone }}
                        </td>
                    @else
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
                    @endif
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
                @if (is_integrate_system() && $document->dispatch_orden)
                    <tr>
                        <td class="text-left desc">
                            <strong>Nota de venta:</strong><br>
                            {{ $document->dispatch_orden->sale_note->identifier }}
                        </td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif
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
                @if ($document->transport_mode_type_id === '01')
                    <tr>
                        @isset($document->dispatcher->number_mtc)
                            <td width="50%" class="text-left desc ">
                                <strong>
                                    Autorización MTC:
                                </strong>
                                {{ $document->dispatcher->number_mtc }}
                            </td>
                        @endisset
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
                    <tr>
                        <td width="50%" colspan="2" class="text-left desc "><strong>Modelo del
                                vehículo:</strong>
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



                @isset($document->driver->license)
                    <tr>
                        <td width="50%" colspan="2" class="text-left desc "><strong>Licencia del
                                conductor:</strong>
                            {{ $document->driver->license }}</td>
                    </tr>
                @endisset

            @endif
        </tbody>
    </table> --}}
    {{-- CÓDIGO	DESCRIPCIÓN DEL PRODUCTO				CANTIDAD	U/M	LOTE		VCTO	PESO (KG)	 --}}
    <table class="full-width mt-2 border-box">
        <thead>
            <tr>
                <th class="border-box text-center" style="background: #8ea9db" width="10%">CÓDIGO</th>
                <th class="border-box text-center" style="background: #8ea9db">DESCRIPCIÓN DEL PRODUCTO</th>
                <th class="border-box text-center" style="background: #8ea9db" width="10%">CANTIDAD</th>
                <th class="border-box text-center" style="background: #8ea9db" width="5%">U/M</th>
                <th class="border-box text-center" style="background: #8ea9db" width="10%">LOTE</th>
                <th class="border-box text-center" style="background: #8ea9db" width="10%">VCTO</th>
                <th class="border-box text-center" style="background: #8ea9db" width="10%">PESO (KG)</th>
            </tr>
        </thead>
        @php
            $cycle = 24;
            $count_items = count($document->items);
            if ($count_items > 7) {
                $cycle = 0;
            } else {
                $cycle = 24 - $count_items;
            }

        @endphp
        <tbody>
            @foreach ($document->items as $idx => $row)
                <tr>
                    <td class="border-left-right  text-center">{{ $row->item->internal_id }}</td>
                    <td class="border-left-right  text-center">{{ $row->item->description }}</td>
                    <td class="border-left-right  text-center">{{ number_format($row->quantity, 2) }}</td>
                    <td class="border-left-right  text-center">{{ $row->item->unit_type_id }}</td>
                    <td class="border-left-right  text-center">
                        @if ($row->item->IdLoteSelected)
                            @inject('itemLotGroup', 'App\Services\ItemLotsGroupService')
                            @php
                                $lote = $itemLotGroup->getLote($row->item->IdLoteSelected);
                                $loteParts = explode('/', $lote);
                            @endphp
                            @foreach ($loteParts as $part)
                                <div style="">{{ $part }}
                            @endforeach
                        @endif


                    </td>
                    <td class="border-left-right  text-center">
                        @if ($row->item->IdLoteSelected)
                            @inject('itemLotGroup', 'App\Services\ItemLotsGroupService')
                            @php
                                $fechaVencimiento = $itemLotGroup->getLotDateOfDue($row->item->IdLoteSelected);
                                $fechaParts = explode('/', $fechaVencimiento);
                            @endphp
                            @foreach ($fechaParts as $part)
                                {{ $part }}</div>
                            @endforeach
                        @endif
                    </td>
                    <td class="border-left-right  text-center">{{ $row->item->weight }}</td>
                </tr>
            @endforeach
            @for ($i = 0; $i < $cycle; $i++)
                <tr>
                    <td class="border-left-right  text-center">
                        <br>
                    </td>
                    <td class="border-left-right  text-center">
                        <br>
                    </td>
                    <td class="border-left-right  text-center">
                        <br>
                    </td>
                    <td class="border-left-right  text-center">
                        <br>
                    </td>
                    <td class="border-left-right  text-center">
                        <br>
                    </td>
                    <td class="border-left-right  text-center">
                        <br>
                    </td>
                    <td class="border-left-right  text-center">
                        <br>
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>


    {{-- <table class="full-width mt-5">
        <thead>
            <tr>
                <th class="border-box text-center bg-grey desc">N°</th>
                <th class="border-box text-center bg-grey desc">Código</th>
                <th class="border-box text-center bg-grey desc">Descripción</th>
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
                    <td class="border-box bg-grey-light text-left desc">
                        @if ($row->name_product_pdf)
                            {!! $row->name_product_pdf !!}
                        @else
                            {!! $row->item->description !!}
                        @endif



                        @if ($row->attributes)
                            @foreach ($row->attributes as $attr)
                                <br /><span style="font-size: 9px">{!! $attr->description !!} :
                                    {{ $attr->value }}</span>
                            @endforeach
                        @endif
                        @isset($row->item->attributes)
                            @foreach ($row->item->attributes as $attr)
                                <br /><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                            @endforeach
                        @endisset

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
                            * Pago Anticipado *
                        @endif
                        @isset($row->item->lots)
                            @if (count($row->item->lots) > 0)
                                <br>Serie:
                                @foreach ($row->item->lots as $key => $lot)
                                    @if (isset($lot->has_sale) && $lot->has_sale)
                                        <span style="font-size: 9px">{{ $lot->series }}
                                            @if ($key != count($row->item->lots) - 1)
                                                /
                                            @endif

                                        </span>
                                    @endif
                                @endforeach
                            @endif
                        @endisset

                        @if ($row->item->IdLoteSelected)
                            @inject('itemLotGroup', 'App\Services\ItemLotsGroupService')
                            @php
                                $lote = $itemLotGroup->getLote($row->item->IdLoteSelected);
                                $loteParts = explode('/', $lote);
                            @endphp
                            @foreach ($loteParts as $part)
                                <div style="font-size: 9px">Lote: {{ $part }}
                            @endforeach
                        @endif

                        @if ($row->item->IdLoteSelected)
                            @inject('itemLotGroup', 'App\Services\ItemLotsGroupService')
                            @php
                                $fechaVencimiento = $itemLotGroup->getLotDateOfDue($row->item->IdLoteSelected);
                                $fechaParts = explode('/', $fechaVencimiento);
                            @endphp
                            @foreach ($fechaParts as $part)
                                Fecha: {{ $part }}</div>
                            @endforeach
                        @endif

                    </td>
                    <td class="border-box bg-grey-light text-center desc">
                        {{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="border-box bg-grey-light text-right desc">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 2) }}
                        @endif
                    </td>
                    <td class="border-box bg-grey-light text-right desc">{{ number_format($row->item->weight, 2) }}
                        {{ $document->unit_type_id }}</td>
                </tr>
            @endforeach
        </tbody>
    </table> --}}
    {{-- @if ($document['qr'])
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
    @endif --}}
    <div class="border-box w-100 p-2 mt-2">
        <strong>Observaciones:</strong> {{ $document->observations }}

    </div>



    <div style="width: 100%; margin-top: 10px;">
        <div style="width: 50%; float: left;">
            <div style="border: 1px solid black; padding: 10px;">
                <div style="margin-bottom: 10px;">
                    <strong>MOTIVO DE TRASLADO:</strong>
                </div>
                <table>
                    <tr>
                        <td>Venta:</td>
                        <td>
                            {!! $document->transfer_reason_type_id == '01' ? '☒' : '☐' !!}
                        </td>
                        <td>
                            Consignación:    
                        </td>
                        <td>
                            {!! $document->transfer_reason_type_id == '05' ? '☒' : '☐' !!}
                        </td>
                        <td>
                            Compra:
                        </td>
                        <td>
                            {!! $document->transfer_reason_type_id == '02' ? '☒' : '☐' !!}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Devolución:
                        </td>
                        <td>
                            {!! $document->transfer_reason_type_id == '06' ? '☒' : '☐' !!}
                        </td>
                        <td>
                            Muestras:
                        </td>
                        <td>
                            {!! $document->transfer_reason_type_id == '07' ? '☒' : '☐' !!}
                        </td>
                        <td>
                            Transformación:
                        </td>
                        <td>
                            {!! $document->transfer_reason_type_id == '17' ? '☒' : '☐' !!}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Traslados Est.:
                        </td>
                        <td>
                            {!! $document->transfer_reason_type_id == '04' ? '☒' : '☐' !!}
                        </td>
                        <td>
                            Otros:
                        </td>
                        @php
                            $transfer_reason_type_id = $document->transfer_reason_type_id;
                            $ids_before = ['01', '02', '05', '06', '07', '17', '04'];
                            $is_before = in_array($transfer_reason_type_id, $ids_before);
                        @endphp
                        <td>
                            {!! !$is_before ? '☒' : '☐' !!}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </table>
            </div>
        </div>

        <div style="width: 50%; float: left;">

            <div style="border: 1px solid black; padding: 10px; height: 78px;">
                <br>
                <br>
                <br>
                <table>
                    <tr>
                        <td class="text-center">
                            <div>
                                ________________________
                            </div>
                            <div>
                                GRUPO KMEDIC E.I.R.L
                            </div>
                        </td>
                        <td class="text-center">
                            <div>
                                ________________________
                            </div>
                            <div>
                                RECIBÍ CONFORME 
                            </div>
                        </td>
                        <td class="text-center">
                        
                            <div>
                                FECHA
                            </div>
                            <div>
                                ___/___/___
                            </div>
                        </td>
                    
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
