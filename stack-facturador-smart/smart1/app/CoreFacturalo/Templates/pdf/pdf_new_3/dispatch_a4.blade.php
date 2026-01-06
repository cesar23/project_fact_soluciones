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

    $customer = $document->customer;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');

    $document_number = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
    // $document_type_driver = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->driver->identity_document_type_id);
    // $document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->dispatcher->identity_document_type_id);

    $allowed_items = 90;
    $quantity_items = $document->items()->count();
    $cycle_items = $allowed_items - $quantity_items * 5;
    $total_weight = 0;

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
        @if ($company->logo)
            <div style="width: 20%; float: left;">
                <div class="company_logo_box">
                    <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                        alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                </div>
            </div>
        @else
            <div style="width: 20%; float: left;">
            </div>
        @endif
        <div style="width: 45%; float: left; text-align: center;">
            <div style="text-align: center;">
                <h4>{{ $company_name }}</h4>
                @if ($company_owner)
                    De: {{ $company_owner }}
                @endif
                <h5>{{ 'RUC ' . $company->number }}</h5>
                <h6 style="text-transform: uppercase;">
                    {{ $establishment->address !== '-' ? $establishment->address : '' }}
                    {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                    {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                    {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                </h6>

                @isset($establishment->trade_address)
                    <h6>{{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                    </h6>
                @endisset

                <h6>{{ $establishment->telephone !== '-' ? 'Central telefónica: ' . $establishment->telephone : '' }}
                </h6>

                <h6>{{ $establishment->email !== '-' ? 'Email: ' . $establishment->email : '' }}</h6>

                @isset($establishment->web_address)
                    <h6>{{ $establishment->web_address !== '-' ? 'Web: ' . $establishment->web_address : '' }}</h6>
                @endisset

                @isset($establishment->aditional_information)
                    <h6>{{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
                    </h6>
                @endisset
            </div>
        </div>
        <div style="width: 30%; float: left; padding-left: 20px;">
            <div class="border-box rounded-box py-10">
                <div style="text-align: center; font-weight: bold;">{{ 'R.U.C. ' . $company->number }}</div>
                <div style="text-align: center; font-weight: bold; padding: 10px;" class="primary-bg">
                    {{ $document->document_type->description }} ELECTRÓNICA</div>
                <div style="text-align: center; font-weight: bold;">Nro. {{ $document_number }}</div>
            </div>
        </div>
    </div>

    <div class="full-width rounded-box relative mt-2">
        <div class="border-box rounded-box full-width primary-bg p-2 text-center" style="z-index: 100;">
            <strong>DATOS DEL TRASLADO</strong>
        </div>
        <div class="border-box rounded-box  rounded-box-wt-top full-width p-2 left"
            style="margin-top: -15px;padding-top: 20px;border-top: none;">
            <table class="full-width">
                <tr>
                    <td>
                        <strong>
                            Fecha de emisión:
                        </strong>
                    </td>
                    <td>
                        {{ $document->date_of_issue->format('d/m/Y') }}
                    </td>
                    <td>
                        <strong>
                            Fecha de inicio de traslado:
                        </strong>
                    </td>
                    <td>
                        {{ $document->date_of_shipping->format('d/m/Y') }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>
                            Motivo de traslado:
                        </strong>
                    </td>
                    <td>
                        {{ $document->transfer_reason_type->description }}
                    </td>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td>
                        <strong>
                            Modalidad de transporte:
                        </strong>
                    </td>
                    <td>
                        {{ $document->transport_mode_type->description }}
                    </td>
                    <td>
                        <strong>
                            Total bultos:
                        </strong>
                    </td>
                    <td>
                        {{ $document->packages_number }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="full-width rounded-box relative mt-2">
        <div class="border-box rounded-box full-width primary-bg p-2 text-center" style="z-index: 100;">
            <strong>DATOS DEL DESTINATARIO</strong>
        </div>
        <div class="border-box rounded-box  rounded-box-wt-top full-width p-2 left"
            style="margin-top: -15px;padding-top: 20px;border-top: none;">
            <table class="full-width">
                <tr>
                    <td>
                        <strong>
                            Ruc:
                        </strong>
                    </td>
                    <td>
                        {{ $customer->number }}
                    </td>
                    <td colspan="2"></td>

                </tr>
                <tr>
                    <td>
                        <strong>
                            Razón social:
                        </strong>
                    </td>
                    <td>
                        {{ $customer->name }}
                    </td>
                    <td colspan="2"></td>
                </tr>

            </table>
        </div>
    </div>
    <div class="full-width rounded-box relative mt-2">
        <div class="border-box rounded-box full-width primary-bg p-2 text-center" style="z-index: 100;">
            <strong>DATOS DEL PUNTO DE PARTIDA Y PUNTO DE LLEGADA</strong>
        </div>
        <div class="border-box rounded-box  rounded-box-wt-top full-width p-2 left"
            style="margin-top: -15px;padding-top: 20px;border-top: none;">
            <table class="full-width">
                <tr>
                    <td>
                        <strong>
                            Dirección del punto de partida:
                        </strong>
                    </td>
                    <td colspan="3">
                         {{ $document->origin->address }} {{ func_get_location($document->origin->location_id) }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>
                            Dirección del punto de llegada:
                        </strong>
                    </td>
                    <td colspan="3">
                        {{ $document->delivery->address }} {{ func_get_location($document->delivery->location_id) }}
                    </td>
                </tr>

            </table>
        </div>
    </div>
    <div class="full-width rounded-box relative mt-2">
        <div class="border-box rounded-box full-width primary-bg p-2 text-center" style="z-index: 100;">
            <strong>DATOS DEL TRANSPORTISTA</strong>
        </div>
        <div class="border-box rounded-box  rounded-box-wt-top full-width p-2 left"
            style="margin-top: -15px;padding-top: 20px;border-top: none;">
            <table class="full-width">
                <tr>
                    <td>
                        <strong>
                            Ruc:
                        </strong>
                    </td>
                    <td>
                        @if ($document->dispatcher)
                            {{ $document->dispatcher->number }}
                        @endif

                    </td>
                    <td>
                        <strong>
                            Empresa:
                        </strong>
                    </td>
                    <td>
                        @if ($document->dispatcher)
                            {{ $document->dispatcher->name }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>
                            Conductor:
                        </strong>
                    </td>
                    <td>
                        @if ($document->driver)
                            {{ $document->driver->name }}
                        @endif
                    </td>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td>
                        <strong>
                            Dni del conductor:
                        </strong>
                    </td>
                    <td>
                        @if ($document->driver)
                            {{ $document->driver->number }}
                        @endif
                    </td>
                    <td>
                        <strong>
                            N° de licencia:
                        </strong>
                    </td>
                    <td>
                        @if ($document->driver)
                            {{ $document->driver->license }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="full-width rounded-box relative mt-2">
        <div class="border-box rounded-box full-width primary-bg p-2 text-center" style="z-index: 100;">
            <strong>DATOS DE LA UNIDAD DE TRANSPORTE</strong>
        </div>
        <div class="border-box rounded-box  rounded-box-wt-top full-width p-2 left"
            style="margin-top: -15px;padding-top: 20px;border-top: none;">
            <table class="full-width">
                <tr>
                    <td>
                        <strong>
                            Marca del vehículo:
                        </strong>
                    </td>
                    <td>
                        {{ $document->transport_data['brand'] }}
                    </td>
                    <td>
                        <strong>
                            Número de placa:
                        </strong>
                    </td>
                    <td>
                        {{ $document->transport_data['plate_number'] }}
                    </td>
                </tr>

            </table>
        </div>
    </div>
    <div class="full-width rounded-box border-box relative mt-2" style="border-top: none;">
        <div class="border-box rounded-box full-width primary-bg p-2 text-center" style="
    height: 20px;
    ">

        </div>
        <table class="full-width" style="float: left;margin-top: -24px;border-top: none;z-index: 100;">
            <thead>
                <tr>
                    <th class="text-white pb-2">NRO</th>
                    <th class="text-white pb-2">CÓDIGO</th>
                    <th class="text-white pb-2">DESCRIPCIÓN</th>
                    <th class="text-white pb-2">UND</th>
                    <th class="text-white pb-2">CANT</th>
                    <th class="text-white pb-2">PESO</th>
                </tr>
                <tr>
                    <th colspan="6">
                        <div style="height: 8px;"></div>
                    </th>
                </tr>
            </thead>
            <tbody class="">
                <tr>
                    <td class="p-1 text-center align-top desc ">
                    </td>
                    <td class="p-1 text-center align-top desc cell-solid-rl">
                    </td>
                    <td class="p-1 text-center align-top desc cell-solid-rl">
                    </td>
                    <td class="p-1 text-center align-top desc cell-solid-rl">
                    </td>
                    <td class="p-1 text-center align-top desc cell-solid-rl">
                    </td>
                    <td class="p-1 text-center align-top desc ">
                    </td>

                </tr>
                @foreach ($document->items as $row)
                    @php
                        $total_weight_line = 0;
                    @endphp
                    <tr>
                        <td class="p-1 text-center align-top desc ">{{ $loop->iteration }}</td>
                        <td class="p-1 text-center align-top desc cell-solid-rl">{{ $row->item->internal_id }}</td>
                        <td class="p-1 text-left align-top desc text-upp cell-solid-rl">
                            {!! $row->item->description !!}
                            @if ($row->relation_item->attributes)
                                @foreach ($row->relation_item->attributes as $attr)
                                    @if ($attr->attribute_type_id === '5032')
                                        @php
                                            $total_weight += $attr->value * $row->quantity;
                                            $total_weight_line += $attr->value * $row->quantity;

                                        @endphp
                                    @endif
                                    <br /><span style="font-size: 9px">{!! $attr->description !!} :
                                        {{ $attr->value }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td class="p-1 text-center align-top desc cell-solid-rl">
                            {{ symbol_or_code($row->item->unit_type_id) }}</td>

                        <td class="p-1 text-center align-top desc cell-solid-rl">
                            @if ((int) $row->quantity != $row->quantity)
                                {{ $row->quantity }}
                            @else
                                {{ number_format($row->quantity, 0) }}
                            @endif

                        </td>

                        <td class="p-1 text-center align-top desc ">
                            {{ $row->item->weight }}
                        </td>
                    </tr>
                @endforeach



            </tbody>
        </table>
    </div>
    {{-- <table class="full-width border-box mt-10 mb-10"> 
    <tbody >
        <tr >
            <td style="text-decoration: underline;" colspan="2" class="pl-3">DESTINATARIO</td>
        </tr>
        <tr>
            <td class="pl-3"><strong>Razón Social:</strong> {{ $customer->name }}</td>

            @if ($document->reference_document)
            <td class="pl-3"><strong>COMPROBANTE:</strong> {{$document->reference_document->document_type->description}} {{$document->reference_document->number_full}}</td>
            @else
            <td class="pl-3"></td>
            @endif
        </tr>

        <tr>
            <td class="pl-3"><strong>RUC:</strong> {{ $customer->number }}</td>
            @if ($document->reference_document)
                @if ($document->reference_document->purchase_order)
                <td class="pl-3"><strong>O. COMPRA:</strong> {{$document->reference_document->purchase_order}}</td>
                @else
                <td class="pl-3"></td>
                @endif
            @else
            <td class="pl-3"></td>
            @endif
        </tr>
        <tr>
            <td colspan="2" class="pl-3"><strong>Dirección:</strong> {{ $customer->address }}
                {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
            </td>
        </tr>
    </tbody>
</table>
<table class="full-width border-box mt-10 mb-10"> 
    <tbody>
        <tr>
            <td style="text-decoration: underline;" colspan="2" class="pl-3">ENVIO</td>
        </tr>
        <tr>
            <td class="pl-3"><strong>Fecha Emisión:</strong> {{ $document->date_of_issue->format('d/m/Y') }}</td>
            <td rowspan="2">
                <p style="text-decoration: underline;"><strong>PUNTO DE PARTIDA</strong></p>
                <label>
                    <strong>Dirección:</strong> {{ $document->origin->address }} - {{ $document->origin->location_id }}
                </label>
            </td>
        </tr>
        <tr>
            <td class="pl-3"><strong>Fecha de Traslado:</strong> {{ $document->date_of_shipping->format('d/m/Y') }}</td>
        </tr>

        <tr>
            <td class="pl-3"><strong>Motivo Traslado:</strong> {{ $document->transfer_reason_type->description }}</td>
            <td rowspan="2">
                <p style="text-decoration: underline;"><strong>PUNTO DE LLEGADA</strong></p>
                <label>
                    <strong>Dirección:</strong> {{ $document->delivery->address }} - {{ $document->delivery->location_id }}
                </label>
            </td>
        </tr>

        <tr>
            <td class="pl-3"><strong>Modalidad de Transporte:</strong> {{ $document->transport_mode_type->description }}</td>
        </tr>



    </tbody>
</table>
<table class="full-width border-box mt-10 mb-10"> 
    <tr>
        <td width="45%" class="border-box pl-3">
            <table class="full-width">
                <tr>
                    <td style="text-decoration: underline;" colspan="2"><strong>Unidad DE TRANSPORTE - CONDUCTOR</td>
                </tr>

    @if ($document->transport_mode_type_id === '02')
                <tr>
                    <td><strong>Conductor:</strong> 
                        @if ($document->driver->name)
                            {{ $document->driver->name }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>N° Doc:</strong> 
                        @if ($document->driver->number)
                            {{ $document->driver->number }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>Placa N°:</strong> @if ($document->transport_data) {{ $document->transport_data['plate_number'] }} @endif</td>
                </tr>
                <tr>
                    <td><strong>N° Licencia:</strong> @if ($document->driver->license) {{ $document->driver->license }} @endif</td>
                </tr>

        </tr>
    @else
                <tr>
                    <td><strong>N° Doc:</strong> {{ $document->driver->identity_document_type_id }}: {{ $document->driver->number }}</td>
                </tr>
                <tr>
                    <td><strong>Placa N°:</strong> {{ $document->license_plate }}</td>
                </tr>
                <tr>
                    <td><strong>N° Licencia:</strong> {{ $document->driver->license }}</td>
                </tr>    
    @endif


            </table>
        </td>
        <td width="3%"></td>

        <td width="50%" class="border-box pl-3">
            <table class="full-width">
                <tr>
                    <td style="text-decoration: underline;" colspan="2"><strong>EMPRESA DE TRANSPORTE</strong></td>
                </tr>

    @if ($document->transport_mode_type_id === '01')
        @php
            $document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->dispatcher->identity_document_type_id);
        @endphp
                <tr>
                    <td><strong>Nombre y/o razón social:</strong> {{ $document->dispatcher->name }}</td>
                </tr>
                <tr>
                    <td>{{ $document_type_dispatcher->description }}: {{ $document->dispatcher->number }}</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
    @else
                <tr>
                    <td><strong>Transportista:</strong> &nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
    @endif
            </table>
        </td>

    </tr>
</table>

 
<table class="full-width mt-0 mb-0" >
    <thead >
        <tr class="">
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  width="8%">Item</th>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  width="12%">Código</th>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  width="8%">Cantidad</th>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  width="8%">U.M.</th>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  width="40%">Descripción</th>
            <th class="border-top-bottom text-right py-1 desc" class="cell-solid"  width="12%">PESO</th>
        </tr>
    </thead>
    <tbody class=""> 
        @foreach ($document->items as $row)
            @php
                $total_weight_line = 0;
            @endphp
            <tr>
                <td class="p-1 text-center align-top desc cell-solid-rl">{{ $loop->iteration }}</td>
                <td class="p-1 text-center align-top desc cell-solid-rl">{{ $row->item->internal_id }}</td>
                <td class="p-1 text-center align-top desc cell-solid-rl">
                    @if ((int) $row->quantity != $row->quantity)
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                    
                </td>
                <td class="p-1 text-center align-top desc cell-solid-rl">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                <td class="p-1 text-left align-top desc text-upp cell-solid-rl">
                    {!!$row->item->description!!}
                    @if ($row->relation_item->attributes)
                        @foreach ($row->relation_item->attributes as $attr)
                            @if ($attr->attribute_type_id === '5032')
                            @php
                                $total_weight += $attr->value * $row->quantity;  
                                $total_weight_line += $attr->value * $row->quantity;  
                                  
                            @endphp
                            @endif
                            <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                        @endforeach
                    @endif
                </td> 
                <td class="p-1 text-center align-top desc cell-solid-rl">
                    {{ $row->item->weight }}
                </td>
            </tr>

        @endforeach

        @for ($i = 0; $i < $cycle_items; $i++)
        <tr>
            <td class="p-1 text-center align-top desc cell-solid-rl"></td>
            <td class="p-1 text-center align-top desc cell-solid-rl"></td>
            <td class="p-1 text-right align-top desc cell-solid-rl"></td>
            <td class="p-1 text-right align-top desc cell-solid-rl"></td>
            <td class="p-1 text-right align-top desc cell-solid-rl"></td>
            <td class="p-1 text-right align-top desc cell-solid-rl"></td>
        </tr>
        @endfor
        <tr>
            <td class="cell-solid-offtop"></td>
            <td class="cell-solid-offtop"></td>
            <td class="cell-solid-offtop"></td>
            <td class="cell-solid-offtop"></td>
            <td class="cell-solid-offtop"></td>
            <td class="cell-solid-offtop"></td>
        </tr>
    </tbody>
</table> --}}


    <table class="full-widthmt-10 mb-10 mt-2">
        <tr>
            {{-- <td width="75%">
                <table class="full-width">
                    <tr>
                        @php
                            $total_packages = $document->items()->sum('quantity');
                        @endphp
                        <td><strong>TOTAL NÚMERO DE BULTOS:</strong>
                            @if ((int) $total_packages != $total_packages)
                                {{ $total_packages }}
                            @else
                                {{ number_format($total_packages, 0) }}
                            @endif
                        </td>
                    </tr>
                </table>
            </td> --}}

            <td width="25%" class="pl-3">
                <table class="full-width">
                    <tr>
                        <td><strong>PESO TOTAL:</strong> {{ $document->unit_type_id }}: {{ $document->total_weight }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>


    <table class="full-width">
        <tr>
            <td width="50%" class="" valign="top">
                <div>
                    <strong>Observaciones:</strong>
                </div>


                <div>{{ $document->observations }}</div>

            </td>
            <td width="3%"></td>

            <td width="47%" class="">
                @if ($document['qr'])
                    <table class="full-width">
                        <tr>
                            <td class="text-center">
                                <img src="data:image/png;base64, {{ $document['qr'] }}"
                                    style="margin-right: -10px;" />
                                    
                                    <div style="text-align: center; font-size: 10px; font-weight: bold;">
                                        <strong>REPRESENTACIÓN IMPRESA DE LA GUÍA DE REMISIÓN</strong>
                                    </div>
                            </td>
                        </tr>
                    </table>
                @endif
            </td>

        </tr>
    </table>
    @if ($document->terms_condition)
        <br>
        <table class="full-width">
            <tr>
                <td class="text-right">
                    <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                    {!! $document->terms_condition !!}
                </td>
            </tr>
        </table>
    @endif
</body>

</html>
