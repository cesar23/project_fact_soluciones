@php
    $establishment = $document->establishment;
    $configuration = \App\Models\Tenant\Configuration::first();
    $stock_decimal = $configuration->stock_decimal;
    $company_name = $company->name;
    $company_owner = null;
    if ($configuration->trade_name_pdf) {
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
            <td width="40%" class="border-blue" style="padding: 0px;">
                <table width="100%">
                    <tr>
                        <td class="text-center h4">
                            R.U.C. N° {{ $company->number }}
                        </td>
                    </tr>
                    <tr>
                        <td class="bg-blue p-10  h4 text-center">
                            {{ $document->document_type->description }} ELECTRÓNICA
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
        <tr>
            <td valign="top" width="20%">PUNTO PARTIDA</td>
            <td valign="top" width="40%">:{{ $document->origin->address }}
                @isset($document->origin->location_id)
                    - {{ func_get_location($document->origin->location_id) }}
                @endisset

            </td>
            <td valign="top" width="20%">FECHA DE EMISIÓN</td>
            <td valign="top" width="20%">:{{ $document->date_of_issue->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td valign="top" width="20%">PUNTO LLEGADA</td>
            <td valign="top" width="40%">:{{ $document->delivery->address }}
                @isset($document->delivery->location_id)
                    - {{ func_get_location($document->delivery->location_id) }}
                @endisset
            </td>
            <td valign="top" width="20%">INICIO DE TRASLADO</td>
            <td valign="top" width="20%">:{{ $document->date_of_shipping->format('d/m/Y') }}</td>
        </tr>
    </table>
    {{-- <table class="full-width mt-10 border-blue">
        <tr>
            <td class="bg-blue text-center" colspan="2"> INFORMACION PROVEEDOR
            </td>
        </tr>
        <tr>
            <td>RUC:</td>
            <td>SEÑOR(ES):</td>
        </tr>
    </table> --}}
    <table class="full-width  mt-10 desc-9">
        <tr>
            <td width="49%">
                <table class="full-width border-blue">
                    <thead>
                        <tr>
                            <th colspan="2" class="bg-blue text-center desc">
                                DATOS DEL DESTINATARIO
                            </th>
                        </tr>
                    </thead>
                    <tr>
                        <td class="desc" width="30%">SEÑOR(ES)</td>
                        <td class="desc">:{{ $customer->name }}</td>
                    </tr>
                    <tr>
                        <td class="desc" width="30%">RUC</td>
                        <td class="desc">:{{ $customer->number }}</td>
                    </tr>
                    <tr>
                        <td class="desc" width="30%">O/C</td>
                        <td class="desc">:{{ $document->order_form ? $document->order_form->number_full : '' }}</td>
                    </tr>
                    {{-- <tr>
                        <td class="desc" width="30%">OBSERVACION</td>
                        <td class="desc">:{{ $document->observations }}</td>
                    </tr> --}}
                </table>
            </td>
            <td width="2%"></td>
            <td widt="49%" valign="top" style="height: 100%">
                <table class="full-width border-blue">
                    <thead>
                        <tr>
                            <th colspan="2" class="bg-blue text-center desc">
                                INFORMACION DEL TRANSPORTISTA
                            </th>
                        </tr>
                    </thead>
                    <tr>
                        <td class="desc" width="30%">SEÑOR(ES)</td>
                        <td class="desc">:
                            {{ optional($document->dispatcher)->name }} </td>
                    </tr>
                    <tr>
                        <td class="desc" width="30%">RUC</td>
                        <td class="desc">:
                            {{ optional($document->dispatcher)->number }} </td>

            </td>
        </tr>

        <tr>
            <td class="desc" width="60%">NRO. DE REGISTRO DEL MTC</td>
            <td class="desc">:{{ optional($document->dispatcher)->number_mtc }}</td>
        </tr>
    </table>
    </td>
    </tr>
    </table>
    <table class="full-width  mt-10 desc-9">
        <tr>
            <td width="49%">
                <table class="full-width border-blue">
                    <thead>
                        <tr>
                            <th colspan="3" class="bg-blue text-center desc">
                                DATOS DEL VEHICULO
                            </th>
                        </tr>
                        <tr>
                            <th class="bg-blue desc text-center">MARCA</th>
                            <th width="50%" class="bg-blue desc text-center">PLACA PRINCIPAL/SECUNDARIA</th>
                            <th class="bg-blue desc text-center">CONSTANCIA</th>
                        </tr>
                    </thead>
                    <tr>
                        <td class="desc text-center">
                            {{ $document->transport_data ? $document->transport_data['brand'] : '' }}
                        </td>
                        <td class="desc text-center" width="50%">
                            {{ $document->transport_data ? $document->transport_data['plate_number'] : '' }}
                        </td>
                        <td class="desc text-center"></td>
                    </tr>

                </table>
            </td>
            <td width="2%"></td>
            <td widt="49%" valign="top" style="height: 100%">
                <table class="full-width border-blue">
                    <thead>
                        <tr>
                            <th colspan="2" class="bg-blue text-center desc">
                                DATOS DEL CONDUCTOR REGISTRO MTC
                            </th>
                        </tr>
                        <tr>
                            <th class="bg-blue desc text-center">N° DE LICENCIA</th>
                            <th class="bg-blue desc text-center">CONDUCTOR</th>
                        </tr>
                    </thead>
                    <tr>
                        <td class="desc text-center">
                            {{ optional($document->driver)->license }}
                        </td>
                        <td class="desc text-center">{{ optional($document->driver)->name }}</td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
    <table class="full-width  mt-10 desc-9 border-blue">
        <thead>
            <tr>
                <th class="bg-blue text-center desc">Item</th>
                <th class="bg-blue text-center desc">CANT.</th>
                <th class="bg-blue text-center desc">COD. BIEN</th>
                <th class="bg-blue text-center desc">DESCRIPCIÓN</th>
                <th class="bg-blue text-center desc">UND MEDIDA</th>
                <th class="bg-blue text-center desc">PESO</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $idx => $row)
                <tr>
                    <td class="desc text-center border-blue">{{ $idx + 1 }}</td>
                    <td class="desc text-center border-blue">{{ number_format($row->quantity, $stock_decimal) }}</td>
                    <td class="desc text-center border-blue">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="desc text-center border-blue">{{ $row->item->description }}</td>
                    <td class="desc text-center border-blue">{{ $document->unit_type_id }}</td>
                    <td class="desc text-center border-blue">{{ isset($row->item->weight) ? $row->item->weight : 0 }}
                    </td>
                </tr>
            @endforeach

        </tbody>
        <tfoot>
            <tr>
                <td class="border-blue desc" colspan="4">USUARIO REGISTRO: {{ $document->user->name }}
                    &nbsp;&nbsp;&nbsp;&nbsp; N° DE PAQUETES: {{ $document->packages_number }}

                </td>
                <td class="border-blue desc" colspan="2">
                
                </td>
            </tr>
            <tr>
                <td class="border-blue desc" colspan="4">NOTA:</td>
                <td class="border-blue desc">PESO TOTAL</td>
                <td class="border-blue desc">{{ number_format($document->total_weight, $stock_decimal) }}
                    {{ $document->unit_type_id }}</td>
            </tr>
        </tfoot>
    </table>
    <table class="full-width  mt-10 desc-9 border-blue">
        <thead>
            <tr>
                <th colspan="3" class="bg-blue desc text-center">
                    DATOS DE TRASLADO
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="desc">MOTIVO DE TRASLADO: {{ optional($document->transfer_reason_type)->description }}
                </td>
                <td class="desc">DOC. RELACIONADO: {{ $document->reference_check() }} </td>
                <td class="desc">MOD. TRASLADO: {{ optional($document->transport_mode_type)->description }}</td>
            </tr>
        </tbody>
    </table>
    <div>
        <span>Observaciones: {{ $document->observations }}</span>
    </div>
    <table class="full-width  mt-10 desc-9 border-blue">
        <tr>
            <td class="desc text-center" width="6%"></td>
            <td class="desc text-center" width="41%">
                <br>
                <br>
                <br>
                <br>
                _______________________________
                <br>
                Transporte
            </td>
            <td class="desc text-center" width="6%"></td>
            <td class="desc text-center" width="41%">
                <br>
                <br>
                <br>
                <br>
                _______________________________
                <br>
                Recibí conforme
            </td>
            <td class="desc text-center" width="6%"></td>
        </tr>
    </table>


    {{-- <div>
        <span>
            Observaciones sunat: 
        </span>
    </div> --}}
    {{-- <table class="full-width border-box mt-10 mb-10">
        <thead>
            <tr>
                <th class="border-bottom text-left">DESTINATARIO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Razón Social: {{ $customer->name }}</td>
            </tr>
            <tr>
                <td>{{ $customer->identity_document_type->description }}: {{ $customer->number }}
                </td>
            </tr>
            <tr>
                @if ($document->transfer_reason_type_id === '09')
                    <td>Dirección: {{ $customer->address }} - {{ $customer->country->description }}
                    </td>
                @else
                    <td>Dirección: {{ $customer->address }}
                        {{ $customer->district_id !== '-' ? ', ' . $customer->district->description : '' }}
                        {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                        {{ $customer->department_id !== '-' ? '- ' . $customer->department->description : '' }}
                    </td>
                @endif
            </tr>
            @if ($customer->telephone)
                <tr>
                    <td>Teléfono:{{ $customer->telephone }}</td>
                </tr>
            @endif
            <tr>
                <td>Vendedor: {{ $document->user->name }}</td>
            </tr>
        </tbody>
    </table>
    <table class="full-width border-box mt-10 mb-10">
        <thead>
            <tr>
                <th class="border-bottom text-left" colspan="2">ENVIO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Fecha Emisión: {{ $document->date_of_issue->format('Y-m-d') }}</td>
                <td>Fecha Inicio de Traslado: {{ $document->date_of_shipping->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td>Motivo Traslado: {{ $document->transfer_reason_type->description }}</td>
                <td>Modalidad de Transporte: {{ $document->transport_mode_type->description }}</td>
            </tr>

            @if ($document->transfer_reason_description)
                <tr>
                    <td colspan="2">Descripción de motivo de traslado: {{ $document->transfer_reason_description }}
                    </td>
                </tr>
            @endif

            @if ($document->related)
                <tr>
                    <td>Número de documento (DAM): {{ $document->related->number }}</td>
                    <td>Tipo documento relacionado: {{ $document->getRelatedDocumentTypeDescription() }}</td>
                </tr>
            @endif

            <tr>
                <td>Peso Bruto Total({{ $document->unit_type_id }}): {{ $document->total_weight }}</td>
                @if ($document->packages_number)
                    <td>Número de Bultos: {{ $document->packages_number }}</td>
                @endif
            </tr>
            <tr>
                <td colspan="2">P.Partida: {{ $document->origin->location_id }} - {{ $document->origin->address }}
                </td>
            </tr>
            <tr>
                <td colspan="2">P.Llegada: {{ $document->delivery->location_id }} -
                    {{ $document->delivery->address }}</td>
            </tr>
            @if ($document->order_form_external)
                <tr>
                    <td>Orden de pedido: {{ $document->order_form_external }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>
    <table class="full-width border-box mt-10 mb-10">
        <thead>
            <tr>
                <th class="border-bottom text-left" colspan="2">TRANSPORTE</th>
            </tr>
        </thead>
        <tbody>
            @if ($document->transport_mode_type_id === '01')
                @php
                    $document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->dispatcher->identity_document_type_id);
                @endphp
                <tr>
                    <td>Nombre y/o razón social: {{ $document->dispatcher->name }}</td>
                    <td>{{ $document_type_dispatcher->description }}: {{ $document->dispatcher->number }}</td>
                </tr>
            @else
                <tr>
                    @if ($document->transport_data)
                        <td>Número de placa del vehículo: {{ $document->transport_data['plate_number'] }}</td>
                    @endif
                    @if ($document->driver->number)
                        <td>Conductor: {{ $document->driver->number }}</td>
                    @endif
                </tr>
                <tr>
                    @if ($document->secondary_license_plates)
                        @if ($document->secondary_license_plates->semitrailer)
                            <td>Número de placa semirremolque: {{ $document->secondary_license_plates->semitrailer }}
                            </td>
                        @endif
                    @endif
                    @if ($document->driver->license)
                        <td>Licencia del conductor: {{ $document->driver->license }}</td>
                    @endif
                </tr>
            @endif
        </tbody>
    </table> --}}
    {{-- <table class="full-width border-box mt-10 mb-10">
        <thead class="">
            <tr>
                <th class="border-top-bottom text-center">Item</th>
                <th class="border-top-bottom text-center">Código</th>
                <th class="border-top-bottom text-left">Descripción</th>
                <th class="border-top-bottom text-left">Modelo</th>
                <th class="border-top-bottom text-center">Unidad</th>
                <th class="border-top-bottom text-center">Peso</th>
                <th class="border-top-bottom text-right">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
                <tr>
                    <td class="text-center" style="vertical-align: top">{{ $loop->iteration }}</td>
                    <td class="text-center" style="vertical-align: top">{{ $row->item->internal_id }}</td>
                    <td class="text-left" style="vertical-align: top">
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
                            *** Pago Anticipado ***
                        @endif
                    </td>
                    <td class="text-left" style="vertical-align: top">{{ $row->item->model ?? '' }}</td>
                    <td class="text-center" style="vertical-align: top">
                        {{ symbol_or_code($row->item->unit_type_id) }}
                    </td>
                    <td class="text-center" style="vertical-align: top">{{ $row->item->weight }}</td>
                    <td class="text-right" style="vertical-align: top">

                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table> --}}

    {{-- @if ($document->observations)
        <table class="full-width border-box mt-10 mb-10">
            <tr>
                <td class="text-bold border-bottom font-bold">OBSERVACIONES</td>
            </tr>
            <tr>
                <td>{{ $document->observations }}</td>
            </tr>
        </table>
    @endif

    @if ($document->reference_document)
        <table class="full-width border-box">
            @if ($document->reference_document)
                <tr>
                    <td class="text-bold border-bottom font-bold">
                        {{ $document->reference_document->document_type->description }}</td>
                </tr>
                <tr>
                    <td>{{ $document->reference_document ? $document->reference_document->number_full : '' }}</td>
                </tr>
            @endif
        </table>
    @endif
    @if ($document->data_affected_document)
        @php
            $document_data_affected_document = $document->data_affected_document;
            
            $number = property_exists($document_data_affected_document, 'number') ? $document_data_affected_document->number : null;
            $Series = property_exists($document_data_affected_document, 'Series') ? $document_data_affected_document->series : null;
            $document_type_id = property_exists($document_data_affected_document, 'document_type_id') ? $document_data_affected_document->document_type_id : null;
            
        @endphp
        @if ($number !== null && $Series !== null && $document_type_id !== null)
            @php
                $documentType = App\Models\Tenant\Catalogs\DocumentType::find($document_type_id);
                $textDocumentType = $documentType->getDescription();
            @endphp
            <table class="full-width border-box">
                <tr>
                    <td class="text-bold border-bottom font-bold">{{ $textDocumentType }}</td>
                </tr>
                <tr>
                    <td>{{ $Series }}-{{ $number }}</td>
                </tr>
            </table>
        @endif
    @endif
    @if ($document->reference_order_form_id)
        <table class="full-width border-box">
            @if ($document->order_form)
                <tr>
                    <td class="text-bold border-bottom font-bold">ORDEN DE PEDIDO</td>
                </tr>
                <tr>
                    <td>{{ $document->order_form ? $document->order_form->number_full : '' }}</td>
                </tr>
            @endif
        </table>
    @elseif ($document->order_form_external)
        <table class="full-width border-box">
            <tr>
                <td class="text-bold border-bottom font-bold">ORDEN DE PEDIDO</td>
            </tr>
            <tr>
                <td>{{ $document->order_form_external }}</td>
            </tr>
        </table>

    @endif


    @if ($document->reference_sale_note_id)
        <table class="full-width border-box">
            @if ($document->sale_note)
                <tr>
                    <td class="text-bold border-bottom font-bold">NOTA DE VENTA</td>
                </tr>
                <tr>
                    <td>{{ $document->sale_note ? $document->sale_note->number_full : '' }}</td>
                </tr>
            @endif
        </table>
    @endif

    @if ($document->terms_condition)
        <br>
        <table class="full-width">
            <tr>
                <td>
                    <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                    {!! $document->terms_condition !!}
                </td>
            </tr>
        </table>
    @endif --}}

</body>

</html>
