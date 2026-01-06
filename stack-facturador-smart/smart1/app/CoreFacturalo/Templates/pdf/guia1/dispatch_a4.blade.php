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
    $logo = str_replace("storage/uploads/logos/storage/uploads/logos/", "storage/uploads/logos/", $logo);
}


    $customer = $document->customer;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    
    $document_number = $document->series. '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
    // $document_type_driver = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->driver->identity_document_type_id);
    // dd($document->items); 
@endphp
<html>

<head>
    {{-- <title>{{ $document_number }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>

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

<div class="header">
<div class="text-left float-left header-logo">
        @if ($establishment->logo ?? false)
            <div class=" text-left">
                <img src="{{ public_path($establishment->logo) }}" alt="{{$company->name}}" class="company_logo_rec" style="width:100%; ">
            </div>
        @else
            @if($company->logo)
                <div class=" text-left">
                    <img src="{{ public_path("storage/uploads/logos/{$company->logo}") }}" alt="{{$company->name}}" class="company_logo_rec" style="width:100%; ">
                </div>
            @else
                <img src="{{ asset('logo/logo.jpg') }}" class="company_logo_rec" style="width: 100%; max-height: 70px">
            @endif
        @endif
    </div>
    <div class="text-left float-left header-company">
        @php
        $header_img = $configurations->header_image; 
        @endphp
        @if($header_img)
        {{-- <img src="{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$establishment__->template_pdf.''.DIRECTORY_SEPARATOR.'datos.png') }}" style="max-height: 180px" /> --}}
        
        <img src="{{ public_path("storage/uploads/header_images/{$header_img}") }}" alt="{{$company->name}}" class="company_logo_rec" style="max-height: 180px" />
        @else
        <div class="text-uppercase font-bold" style="font-size: 17px; color: #fff width:250px;">.</div>
        @endif
        {{-- <div class="text-uppercase font-bold" style="font-size: 17px; color: #3a628c">{{ $company->name }}</div>
        <div class="text-uppercase mayus">
            Oficina Principal: {{ ($establishment->address !== '-')? $establishment->address.'' : '' }}
        </div>
        <div class="text-uppercase mayus">
            {{ ($establishment->district_id !== '-')? ' '.$establishment->district->description : '' }}
            {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
            {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
        </div>
        @isset($establishment->trade_address)
        <div class="text-uppercase mayus">
        {{ $establishment->trade_address !== '-' ? 'Sucursal: ' . $establishment->trade_address : '' }}
        </div>
        @endisset
        <div>
            {{ ($establishment->telephone !== '-')? ''.$establishment->telephone : '' }}
            {{ $establishment->email !== '-' ? ' ' . $establishment->email : '' }}
        </div>
        <div class="text-left">
            {{ ($establishment->web_address !== '-')? ''.$establishment->web_address : '' }}
        </div>
        @isset($establishment->aditional_information)
            <div>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</div>
        @endisset --}}
    </div>
    <div  class="text-center float-left header-number py-3 font-bold ">
        <div style="margin-top: 5px" class="font-lg">RUC {{$company->number }}</div>
        <div style="margin-top: 2px" class="font-lg">{{ $document->document_type->description }}</div>

        <div style="margin-top: 5px">Nro {{ $document_number }}</div>
    </div>
</div>
<table class="full-width border-box">
    <tr>
        <td class="font-xs font-bold pt-1 pb-1 pl-2" width="30%"> Fecha de Emision : {{ $document->date_of_issue->format('Y-m-d') }}</td>
        <td class="font-xs font-bold pt-1 pb-1" width="30%">Fecha de Traslado : {{ $document->date_of_shipping->format('Y-m-d') }}</td>
        <td class="font-xs font-bold pt-1 pb-1" width="20%">Factura Ref.: 
            @if ($document->reference_document)
                @if ($document->reference_document)
                    {{ $document->reference_document->document_type->description }} {{ $document->reference_document ? $document->reference_document->number_full : '' }}
                @endif
            @endif
        </td>
        <td class="font-xs font-bold pt-1 pb-1" width="20%">O/C. : 
{{$document->purchase_order}}

        </td>
    </tr>
</table>
<table class="full-width border-box mt-2">
    <tr>
        <td class="font-xs font-bold pt-1 pb-1 pl-2" width="100%"> Direc. partida : {{ optional($document->origin)->location_id }} - {{ optional($document->origin)->address }}</td>
    </tr>
</table>
<table class="full-width border-box mt-2">
    <tr>
        <td class="font-xs font-bold pt-1 pb-1 pl-2" width="100%"> Direc. llegada : {{ optional($document->delivery)->location_id }} - {{ optional($document->delivery)->address }}</td>
    </tr>
</table>
<table class="full-width  mt-2">
    <tr>
        <td width="50%">
            <table class="full-width border-box text-center font-bold">
                <tr>
                    <td>REMITENTE</td>
                </tr>
            </table>
        </td>
        <td width="50%">
            <table class="full-width border-box text-center font-bold">
                <tr>
                    <td>DESTINATARIO</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td width="50%">
            <table class="full-width font-bold align-top">
                <tr>
                    <td class="pl-2 align-top">{{ $company->name }}</td>
                </tr>
                <tr>
                    <td class="pl-2 align-top">{{ $company->identity_document_type->description }}: {{ $company->number }}</td>
                </tr>
            </table>
        </td>
        <td width="50%">
            <table class="full-width font-bold align-top">
                <tr>
                    <td class="pl-2 align-top">{{ $customer->name }}</td>
                </tr>
                <tr>
                    <td class="pl-2 align-top">{{ $customer->identity_document_type->description }}: {{ $customer->number }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
{{-- 
    <table class="full-width border-box mt-10 mb-10">
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
    <table class="full-width  mt-10 mb-10">
        <thead>
            <tr class="border-box">
                <th class="pt-2 pb-2 text-center" width="12%">CÓDIGO</th>
                <th class="pt-2 pb-2 text-center" width="10%">CANT.</th>
                <th class="pt-2 pb-2 text-left" style="padding-left: 30px">DESCRIPCIÓN</th>
                <th class="pt-2 pb-2 text-center" width="15%">UNIDADES</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
            @php
                $data_item = \App\Models\Tenant\Item::find($row->item_id);
                $digemid_codes = \App\Models\System\Digemid::select('cod_prod', 'num_regsan', 'nom_prod', 'nom_form_farm_simplif', 'concent', 'nom_titular', 'fracciones')->where('cod_prod',$data_item->cod_digemid)->first();
                // dd($data_item->cod_digemid);
            @endphp
                <tr>
                    <td class="text-center" style="vertical-align: top">{{ $row->item->internal_id }}</td>
                    <td class="text-center" style="vertical-align: top">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                    <td class="text-left" style="vertical-align: top">
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
                        <div>
                            @isset($digemid_codes)
                                LAB: {{$digemid_codes->nom_titular}} 
                            @endisset
                            @if ($row->item->IdLoteSelected ?? false)
                                @foreach ($row->item->IdLoteSelected as $lot)
                                    LOTE: {{ $lot->code }} - FEC.VCTO.: {{ $lot->date_of_due }}
                                @endforeach
                            @endif
                            @isset($data_item->sanitary)
                                RS: {{$data_item->sanitary}} 
                            @endisset
                        </div>
                    </td>
                    <td class="text-center" style="vertical-align: top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

<table class="full-width  mt-2">
    <tr>
        <td width="50%">
            <table class="full-width border-box text-center font-bold">
                <tr class="bg_celeste">
                    <td class="text-white">
                        @if ($document->transport_mode_type_id === '01')
                            TRANSPORTISTA
                        @else
                            CONDUCTOR
                        @endif
                    </td>
                </tr>
            </table>
        </td>
        <td width="50%">
            <table class="full-width border-box text-center font-bold">
                <tr class="bg_celeste">
                    <td class="text-white">DATOS DE LA UNIDAD DE TRANSPORTE</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td width="50%" class="align-top">
            <table class="full-width font-bold align-top">
                @if ($document->transport_mode_type_id === '01')
                    @php
                        $document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->dispatcher->identity_document_type_id);
                    @endphp
                    <tr>
                        <td class="pl-2 align-top" width="50px">Empresa</td>
                        <td class="pl-2 align-top" width="2px">:</td>
                        <td class="pl-2 align-top" width="">{{ $document->dispatcher->name }}</td>
                    </tr>
                    <tr>
                        <td class="pl-2 align-top">{{ $document_type_dispatcher->description }}</td>
                        <td class="pl-2 align-top">:</td>
                        <td class="pl-2 align-top">{{ $document->dispatcher->number }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="pl-2 align-top" width="50px">Nombres</td>
                        <td class="pl-2 align-top" width="2px">:</td>
                        <td class="pl-2 align-top" width="">{{ $document->driver->name }}</td>
                    </tr>
                    <tr>
                        <td class="pl-2 align-top">Licencia </td>
                        <td class="pl-2 align-top">:</td>
                        <td class="pl-2 align-top">{{ $document->driver->number }}</td>
                    </tr>
                @endif
            </table>
        </td>
        <td width="50%">
            <table class="full-width font-bold align-top">
                @if ($document->transport_mode_type_id === '01')
                <tr>
                    <td class="pl-2 align-top" width="130px">Marca Vehiculo</td>
                    <td class="pl-2 align-top" width="2px">:</td>
                    <td class="pl-2 align-top" width="">@isset ( $document->transport_data['brand']) {{ $document->transport_data['brand'] }} @endif </td>
                </tr>
                <tr>
                    <td class="pl-2 align-top">Modelo</td>
                    <td class="pl-2 align-top">:</td>
                    <td class="pl-2 align-top">@isset ($document->transport_data['model'] ) {{ $document->transport_data['model'] }} @endif</td>
                </tr>
                <tr>
                    <td class="pl-2 align-top">No.de Placa</td>
                    <td class="pl-2 align-top">:</td>
                    <td class="pl-2 align-top">@isset ($document->transport_data['plate_number'] ) {{ $document->transport_data['plate_number'] }} @endif</td>
                </tr>

                @else
                    <tr>
                        <td class="pl-2 align-top" width="130px">Marca Vehiculo</td>
                        <td class="pl-2 align-top" width="2px">:</td>
                        <td class="pl-2 align-top" width="">@if ($document->transport_data) {{ $document->transport_data['brand'] }} @endif </td>
                    </tr>
                    <tr>
                        <td class="pl-2 align-top">No.de Placa</td>
                        <td class="pl-2 align-top">:</td>
                        <td class="pl-2 align-top">@if ($document->transport_data) {{ $document->transport_data['plate_number'] }} @endif</td>
                    </tr>
                    @if ($document->secondary_license_plates)
                        @if ($document->secondary_license_plates->semitrailer)
                            <tr>
                                <td class="pl-2 align-top">No.de Placa semirremolque</td>
                                <td class="pl-2 align-top">:</td>
                                <td class="pl-2 align-top">{{ $document->secondary_license_plates->semitrailer }}</td>
                            </tr>
                        @endif
                    @endif
                @endif
            </table>
        </td>
    </tr>
</table>

<table class="full-width  mt-2">
    <tr class="bg_celeste border-box">
        <td class="text-white text-center font-bold" width="50%">MOTIVO DE TRANSPORTE</td>
        <td class="text-white text-center font-bold" width="50%">DATOS DE ENVIO</td>
    </tr>
    <tr class="">
        <td class="" width="50%">
            <div>1. Venta @if($document->transfer_reason_type_id == '01') <span class="font-bold font-xlg">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;X</span> @endif</div>
            <div>2. Venta Sujeta a confirmacion @if($document->transfer_reason_type_id == '14') <span class="font-bold font-xlg">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;X</span> @endif</div>
            <div>3. Consignacion @if($document->transfer_reason_type_id == '05') <span class="font-bold font-xlg">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;X</span> @endif</div>
            <div>4. Devolucion @if($document->transfer_reason_type_id == '06') <span class="font-bold font-xlg">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;X</span> @endif</div>
            <div>5. Traslado entre establecimientos de la misma empresa @if($document->transfer_reason_type_id == '04') <span class="font-bold font-xlg">&nbsp;&nbsp;X</span> @endif</div>
        </td>
        <td class="" width="50%">
            <table class="full-width">
                <tr>
                    <td class="font-bold pl-3" width="130px">Peso Bruto ({{ $document->unit_type_id }})</td>
                    <td class="font-bold pl-3" width="2px">:</td>
                    <td class="font-bold pl-3" width=""> {{ $document->total_weight }}</td>
                </tr>
                <tr>
                    <td class="font-bold pl-3" width="110px">Numero de Bultos</td>
                    <td class="font-bold pl-3" width="2px">:</td>
                    <td class="font-bold pl-3" width=""> {{ $document->packages_number }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="full-width">
    <tr>
        <td width="80%"><br><br><br><br>
            Representacion impresa de la {{ $document->document_type->description }}, esta puede ser consultada en <br>
            <span class="text-celeste">{!! searchUrl() !!}</span>
        </td>
        <td width="20%">
            @if ($document->qr)
                <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px;" width="150px" />
            @endif
        </td>
    </tr>
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
    @endif 

</body>

</html>
