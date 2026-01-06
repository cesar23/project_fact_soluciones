@php
$establishment = $document->establishment;
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

    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    // $document_type_driver = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->driver->identity_document_type_id);
    $document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->dispatcher->identity_document_type_id);

    //calculate items
    $allowed_items = 20;
    $quantity_items = $document->items()->count();
    $cycle_items = $allowed_items - ($quantity_items * 3);
    $total_weight = 0;

$item_=0;
    $template_data = \App\Models\Tenant\Establishment::where('code', '=', $establishment->code)->first();

@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
@if($document->state_type->id == '11'  || $document->state_type->id == '09')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
@endif
    <div class="company_logo_box" style="position: absolute; text-align: center; top:94%; right: 5%;">
        <img src="{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'logo.png') }}" alt="{{$company->name}}" style="width: 50px;">
    </div>

    <div class="header">
        <div class="text-center float-left header-logo">
            @if($company->logo)
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("storage".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."logos".DIRECTORY_SEPARATOR."{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."logos".DIRECTORY_SEPARATOR."{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="width: 80%;">
                </div>
            @endif
        {{-- <div class="text-center float-left headTer-company"> --}}
            <div class="text-left font-xs">
                
                <span class="font-xlg text-uppercase font-bold font-xs">{{ $company->name }}</span><br>
                <span class="font-bold"></span>{{ ($establishment->address !== '-')? $establishment->address.'' : '' }}
                {{ ($establishment->district_id !== '-')? '- '.$establishment->district->description : '' }}
                {{ ($establishment->province_id !== '-')? '- '.$establishment->province->description : '' }}
                {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }} <br>
                <span class="font-bold">Teléf: </span>{{ ($establishment->telephone !== '-')? $establishment->telephone : '' }} 
                @isset($establishment->web_address)
                    Web: {{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}
                @endisset
                E-mail: {{ ($establishment->email !== '-')? $establishment->email : '' }}
                @isset($establishment->aditional_information)
                <br>
                    <span>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</span>
                @endisset

                
            </div>
        {{-- </div> --}}
        </div>
        <div  class="text-center float-left header-number py-3 font-bold borde_celeste1 font-xlg">
            R.U.C. {{$company->number }}
            {{-- <br> --}}
            <div class="fondo_gris alto_">{{ $document->document_type->description }}</div>
            {{-- <br> --}}
             {{ $document_number }}
        </div>
    </div>



    <div class="information mt-3 borde_celeste1">
        <div class="div-table">
            <div class="div-table-row">
                <div class="div-table-col w-100 font-xs font-bold"><strong>INFORMACIÓN DEL DESTINATARIO:</div>
            </div>
            <div class="div-table-row">
                <div class="div-table-col w-10 font-xs"><strong>Cliente:</div>
                <div class="div-table-col w-90 font-xs">: {{ $customer->name }}</div>
            </div>
            <div class="div-table-row">
                <div class="div-table-col w-10 font-xs"><strong>Dirección</div>
                <div class="div-table-col w-90 font-xs">: 
                        {{ $customer->address }}
                        {{ ($customer->district_id !== '-')? '- '.strtoupper($customer->district->description) : '' }}
                        {{ ($customer->province_id !== '-')? '- '.strtoupper($customer->province->description) : '' }}
                        {{ ($customer->department_id !== '-')? '- '.strtoupper($customer->department->description) : '' }}
                </div>
            </div>
            <div class="div-table-row">
                <div class="div-table-col w-10 font-xs"><strong>R.U.C.</div>
                <div class="div-table-col w-90 font-xs">: {{ $customer->number }}</div>
            </div>
        </div>
    </div>

    <div class="information mt-3 borde_celeste1">
        <div class="div-table">
            <div class="div-table-row">
                <div class="div-table-col w-100 font-xs font-bold"><strong>DATOS DEL TRASLADO:</div>
            </div>
            <div class="div-table-row">
                <div class="div-table-col w-15 font-xs"><strong>Fecha de emisión</div>
                <div class="div-table-col w-30 font-xs">: {{ $document->date_of_issue->format('Y-m-d') }}</div>
                <div class="div-table-col w-20 font-xs"><strong>FECHA INICIO TRASLADO:</div>
                <div class="div-table-col w-30 font-xs">: {{ $document->date_of_shipping->format('Y-m-d') }}</div>
                <div class="div-table-col w-15 font-xs"><strong>MOT. TRASLADO:</div>
                <div class="div-table-col w-30 font-xs">: {{ $document->transfer_reason_type->description }}</div>
                <div class="div-table-col w-20 font-xs"><strong>MOD. DE TRANSPORTE:</div>
                <div class="div-table-col w-30 font-xs">: {{ $document->transport_mode_type->description }}</div>
                <div class="div-table-col w-15 font-xs"><strong>PESO BRUTO TOTAL:({{ $document->unit_type_id }})</div>
                <div class="div-table-col w-30 font-xs">: {{ $document->total_weight }}</div>
                <div class="div-table-col w-20 font-xs"><strong># DE BULTOS</div>
                <div class="div-table-col w-30 font-xs">: @if($document->packages_number) {{ $document->packages_number }} @endif</div>
            @php
                $district = \App\Models\Tenant\Catalogs\District::find($document->origin->location_id);
                $district_llegada = \App\Models\Tenant\Catalogs\District::find($document->delivery->location_id);
            @endphp
                <div class="div-table-col w-15 font-xs"><strong>PUNTO DE PARTIDA</div>
                <div class="div-table-col w-30 font-xs">: {{ $document->origin->location_id }} - {{ $document->origin->address }} - {{ strtoupper($district->description) }} - {{ strtoupper($district->province->description) }} - {{ strtoupper($district->province->department->description) }}</div>
                <div class="div-table-col w-20 font-xs"><strong>PUNTO DE LLEGADA</div>
                <div class="div-table-col w-30 font-xs">: {{ $document->delivery->location_id }} - {{ $document->delivery->address }} - {{ strtoupper($district_llegada->description) }} - {{ strtoupper($district_llegada->province->description) }} - {{ strtoupper($district_llegada->province->department->description) }}</div>
            </div>
        </div>
    </div>
    <div class="information mt-3 borde_celeste1">
        <div class="div-table">
            <div class="div-table-row">
                <div class="div-table-col w-100 font-xs font-bold"><strong>DATOS DEL TRANSPORTISTA:</div>
            </div>
            <div class="div-table-row">
                <div class="div-table-col w-19 font-xs"><strong>RAZÓN SOCIAL:</div>
                <div class="div-table-col w-30 font-xs">: {{ $document->dispatcher->name }}</div>
                <div class="div-table-col w-19 font-xs"><strong>{{ $document_type_dispatcher->description }}</div>
                <div class="div-table-col w-30 font-xs">: {{ $document->dispatcher->number }}</div>
                <div class="div-table-col w-19 font-xs"><strong>Nro. PLACA VEHICULO</div>
                <div class="div-table-col w-30 font-xs">: @if($document->license_plate) {{ $document->license_plate }} @endif</div>
                <div class="div-table-col w-19 font-xs"><strong>CONDUCTOR</div>
                <div class="div-table-col w-30 font-xs">: @if($document->driver->number) {{ $document->driver->number }} @endif</div>
            </div>
        </div>
    </div>


<table class="full-width mt-0 mb-0 borde_celeste1">
        <thead class="">
        <tr class=""
            style="background-color: rgb(51,153,255);">
            <th class="border-c_todo_dashed text-center py-1 desc" width="5%" style="color: white;">I T E M</th>
            <th class="border-c_todo_dashed text-center py-1 desc" width="10%" style="color: white;">C Ó D I G O</th>
            <th class="border-c_todo_dashed text-center py-1 desc" width="60%" style="color: white;">D  E  S  C  R  I  P  C  I  Ó  N</th>
            <th class="border-c_todo_dashed text-center py-1 desc" width="8%" style="color: white;">U.  M.</th>
            <th class="border-c_todo_dashed text-center py-1 desc" width="8%" style="color: white;">C A N T.</th>
        </tr>
    </thead>
    <tbody class="">
        @foreach($document->items as $row)
        @php $item_++; @endphp
            <tr>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales">{{ $item_ }}</td>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales">{{ $row->item->internal_id }}</td>
                <td class="p-1 border-c_todo_dashed text-left align-top desc text-upp border_c_laterales">
                    @if($row->name_product_pdf)
                        {!!$row->name_product_pdf!!}
                    @else
                        {!!$row->item->description!!}
                    @endif

                      

                    @if($row->attributes)
                        @foreach($row->attributes as $attr)
                            <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                        @endforeach
                    @endif
                    {{-- @if($row->discounts)
                        @foreach($row->discounts as $dtos)
                            <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                        @endforeach
                    @endif --}}

                 
                </td>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales">{{ $row->item->unit_type_id }}</td>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales">
                    @if(((int)$row->quantity != $row->quantity))
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
            </tr>

        @endforeach
            @for($i = 0; $i < $cycle_items; $i++)
            <tr>
                <td class="p-1 text-center align-top desc border_c_laterales">&nbsp;</td>
                <td class="p-1 text-center align-top desc border_c_laterales"></td>
                <td class="p-1 text-center align-top desc border_c_laterales"></td>
                <td class="p-1 text-center align-top desc border_c_laterales"></td>
                <td class="p-1 text-center align-top desc border_c_laterales"></td>
            </tr>
            @endfor
    </tbody>
</table>


    <div class="div-table">
        <div class="div-table-row">
            <div class="div-table-col w-74">&nbsp;</div>
            <div class="div-table-col w-25 font-xxs prueba">

<table class="full-width mt-0 mb-10 font-xxs">

        <tr>
            <td colspan="8" class="text-center font-bold font-xxs border-c_todo_solid"> TOTAL  </td>
            <td class="text-right font-bold font-xxs border-c_todo_solid padding_right">{{ number_format($document->total, 2) }}</td>
        </tr>


</table>

            </div>
        </div>
    </div>


@if($document->observations)
<table class="full-width border-box mt-10 mb-10 border-c_todo_solid">
    <tr>
        <td class="text-bold border-bottom font-bold border-c_todo_solid  left_right">OBSERVACIONES</td>
    </tr>
    <tr>
        <td class="left_right">{{ $document->observations }}</td>
    </tr>
</table>
@endif

@if ($document->reference_document)
<table class="full-width border-box border-c_todo_solid">
    @if($document->reference_document)
    <tr>
        <td class="text-bold border-bottom font-bold border-c_todo_solid left_right">COMPROBANTE DE PAGO RELACIONADO A LA GUIA DE REMISIÓN REMITEMTE:</td>
    </tr>
    <tr>
        <td class="left_right">{{ ($document->reference_document) ? $document->reference_document->number_full : "" }}</td>
    </tr>
    @endif
</table>
@endif
@if ($document->data_affected_document)
    @php
        $document_data_affected_document = $document->data_affected_document;

    $number = (property_exists($document_data_affected_document,'number'))?$document_data_affected_document->number:null;
    $Series = (property_exists($document_data_affected_document,'Series'))?$document_data_affected_document->series:null;
    $document_type_id = (property_exists($document_data_affected_document,'document_type_id'))?$document_data_affected_document->document_type_id:null;

    @endphp
    @if($number !== null && $Series !== null && $document_type_id !== null)

        @php
            $documentType  = App\Models\Tenant\Catalogs\DocumentType::find($document_type_id);
            $textDocumentType = $documentType->getDescription();
        @endphp
        <table class="full-width border-box">
            <tr>
                <td class="text-bold border-bottom font-bold">{{$textDocumentType}}</td>
            </tr>
            <tr>
                <td>{{$Series }}-{{$number}}</td>
            </tr>
        </table>
    @endif
@endif
@if ($document->reference_order_form_id)
<table class="full-width border-box">
    @if($document->order_form)
    <tr>
        <td class="text-bold border-bottom font-bold">ORDEN DE PEDIDO</td>
    </tr>
    <tr>
        <td>{{ ($document->order_form) ? $document->order_form->number_full : "" }}</td>
    </tr>
    @endif
</table>
@endif

@if ($document->reference_sale_note_id)
<table class="full-width border-box">
    @if($document->sale_note)
    <tr>
        <td class="text-bold border-bottom font-bold">NOTA DE VENTA</td>
    </tr>
    <tr>
        <td>{{ ($document->sale_note) ? $document->sale_note->number_full : "" }}</td>
    </tr>
    @endif
</table>
@endif
<br><br>
Representación Impresa del <strong>COMPROBANTE ELECTRONICO</strong>, generado desde el Sistema del Contribuyente, comprobante emitido desde <strong>www.econtfact.com. </strong></td>
</body>
</html>
