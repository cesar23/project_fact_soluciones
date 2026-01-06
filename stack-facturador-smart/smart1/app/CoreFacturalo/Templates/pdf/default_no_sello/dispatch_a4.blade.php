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
                    {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px"> --}}
                </td>
            @endif
            <td width="50%" class="pl-3">
                <div class="text-left">
                    <h4 class="">{{ $company_name }}</h4>
                    @if ($company_owner)
                        De: {{ $company_owner }}
                    @endif
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
                    @if(is_integrate_system())
                    <td  class="text-left desc ">
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
                    <td  class="text-left desc ">
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
                @if(is_integrate_system() && $document->dispatch_orden)
                <tr>
                    <td  class="text-left desc">
                        <strong>Nota de venta:</strong><br>
                        {{$document->dispatch_orden->sale_note->identifier}}
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
    </table>

    <table class="full-width mt-5">
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
                            @if(count($row->item->lots) > 0)
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

                        @if($row->item->IdLoteSelected)
                            @inject('itemLotGroup', 'App\Services\ItemLotsGroupService')
                            @php
                                $lote = $itemLotGroup->getLote($row->item->IdLoteSelected);
                                $loteParts = explode('/', $lote);
                            @endphp
                            @foreach ($loteParts as $part)
                                <div style="font-size: 9px">Lote: {{ $part }}
                            @endforeach
                        @endif

                        @if($row->item->IdLoteSelected)
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
