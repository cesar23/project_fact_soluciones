@php
    $establishment = $document->establishment;
    $customer = $document->customer;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');

    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    // $document_type_driver = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->driver->identity_document_type_id);
    $document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->dispatcher->identity_document_type_id);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
// dd($document->reference_document->total);
// dd($document->total);
@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
    <div class="header">
        <div class="text-center float-left header-logo">
            @if($company->logo)
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("storage".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."logos".DIRECTORY_SEPARATOR."{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."logos".DIRECTORY_SEPARATOR."{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 200px;">
                </div>
            @endif
        </div>
        <div class="text-left float-left header-company">
                <span class="font-xlg text-uppercase font-bold">{{ $company->name }}</span>
                <br>
                {{ ($establishment->address !== '-')? $establishment->address.',' : '' }}
                {{ ($establishment->district_id !== '-')? ' '.$establishment->district->description : '' }}
                {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                <br>
                {{ ($establishment->email !== '-')? $establishment->email : '' }}
                <br>
                {{ ($establishment->telephone !== '-')? $establishment->telephone : '' }}
                <br>
        </div>
        <div  class="text-center float-left header-number py-3 font-bold">
           <div style="height:25px; background-color: #c0c0c0; padding: 10px 0 0 0; font-size:13px"> {{ $document->document_type->description }}</div>
           <div style="margin:10px 0 0 0"> RUC: {{$company->number }}</div>
            Nº {{ $document_number }}
        </div>
    </div>
<span class="mt-5 font-lg font-bold"> REFERENCIA DE FACTURA: @if ($document->reference_document)  @if($document->reference_document) {{$document->reference_document->document_type->description}} {{ ($document->reference_document) ? $document->reference_document->number_full : "" }} @endif @endif </span>



    <div class="information mt-3">
        <div class="div-table">
             <div class="div-table-row">
                <div class="div-table-col font-xs" >
                    <div class="div-table-col w-100 font-bold" style="background-color: #c0c0c0; padding: 5px">
                        DESTINATARIO
                    </div>
                    {{-- linea1 --}}
                    <div class="div-table-col w-20 font-bold" style="background-color: #fff">
                        Razon Social
                    </div>
                    <div class="div-table-col w-75" style="background-color: #fff">
                        : {{ $customer->name }}
                    </div>
                    {{-- linea2 --}}
                    <div class="div-table-col w-20 font-bold" style="background-color: #fff">
                        {{ $customer->identity_document_type->description }}
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        : {{ $customer->number }}
                    </div>
                    <div class="div-table-col w-16 font-bold" style="background-color: #fff">
                        Teléfono
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        : {{ $customer->number }}
                    </div>
                    <div class="div-table-col w-15 font-bold" style="background-color: #fff">
                        E-mail registrado
                    </div>
                    <div class="div-table-col w-19" style="background-color: #fff">
                        : {{ $customer->number }}
                    </div>
                    {{-- linea3 --}}
                    <div class="div-table-col w-20 font-bold" style="background-color: #fff">
                        Nº de factura
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        :  @if ($document->reference_document)  @if($document->reference_document)  {{ ($document->reference_document) ? $document->reference_document->number_full : "" }} @endif @endif
                    </div>
                    <div class="div-table-col w-16 font-bold" style="background-color: #fff">
                        Nº Orden de compra
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        :  OC-434
                    </div>
                    <div class="div-table-col w-15 font-bold" style="background-color: #fff">
                        Fecha de emisión
                    </div>
                    <div class="div-table-col w-19" style="background-color: #fff">
                        :  2021-12-12
                    </div>
                    {{-- linea4 --}}
                    <div class="div-table-col w-20 font-bold" style="background-color: #fff">
                        Motivo de Traslado
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        :  {{ $document->transfer_reason_type->description }}
                    </div>
                    <div class="div-table-col w-16 font-bold" style="background-color: #fff">
                        Modalidad traslado
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        :  {{ $document->transport_mode_type->description }}
                    </div>
                    <div class="div-table-col w-15 font-bold" style="background-color: #fff">
                        Fecha traslado
                    </div>
                    <div class="div-table-col w-19" style="background-color: #fff">
                        :  {{ $document->date_of_issue->format('Y-m-d') }}
                    </div>
                    <div class="div-table-col w-20 font-bold" style="background-color: #fff">
                        Dirección de partida
                    </div>
                    <div class="div-table-col w-75" style="background-color: #fff">
                        : {{ $document->origin->location_id }} - {{ $document->origin->address }}
                    </div>
                    <div class="div-table-col w-20 font-bold" style="background-color: #fff">
                        Dirección de llegada
                    </div>
                    <div class="div-table-col w-75" style="background-color: #fff">
                        :  {{ $document->delivery->location_id }} - {{ $document->delivery->address }}
                    </div>
                    <div class="div-table-col w-20 font-bold" style="background-color: #fff">
                        Transportista
                    </div>
                    <div class="div-table-col w-30" style="background-color: #fff">
                        : {{ $document->dispatcher->name }}
                    </div>
                    <div class="div-table-col w-20 font-bold" style="background-color: #fff">
                        {{ $document_type_dispatcher->description }} transportista
                    </div>
                    <div class="div-table-col w-20" style="background-color: #fff">
                        : {{ $document->dispatcher->number }}
                    </div>
                    <div class="div-table-col w-20 font-bold" style="background-color: #fff">
                        Domicilio
                    </div>
                    <div class="div-table-col w-75" style="background-color: #fff">
                        : 
                    </div>
                    <div class="div-table-col w-20 font-bold" style="background-color: #fff">
                        Conductor
                    </div>
                    <div class="div-table-col w-75" style="background-color: #fff">
                        : @if($document->driver->number) {{ $document->driver->number }} @endif
                    </div>
                    <div class="div-table-col w-20 font-bold" style="background-color: #fff">
                        Placa Vehiculo
                    </div>
                    <div class="div-table-col w-10" style="background-color: #fff">
                        : @if($document->license_plate) {{ $document->license_plate }} @endif
                    </div>
                    <div class="div-table-col w-6 font-bold" style="background-color: #fff">
                        Marca
                    </div>
                    <div class="div-table-col w-10" style="background-color: #fff">
                        : @if($document->secondary_license_plates) @if($document->secondary_license_plates->semitrailer) {{ $document->secondary_license_plates->semitrailer }} @endif @endif
                    </div>
                    <div class="div-table-col w-20 font-bold" style="background-color: #fff">
                        Total Cantidad de bultos
                    </div>
                    <div class="div-table-col w-5" style="background-color: #fff">
                        : @if($document->packages_number) {{ $document->packages_number }} @endif
                    </div>
                    <div class="div-table-col w-19 font-bold" style="background-color: #fff">
                        Total peso bruto ({{ $document->unit_type_id }})
                    </div>
                    <div class="div-table-col w-5" style="background-color: #fff">
                        : {{ $document->total_weight }}
                    </div>
                    <div class="div-table-col w-100 mt-2 mb-2" style="height:3px; background-color:#c0c0c0"></div>
                    <div class="div-table-col w-100 font-bold font-sm" style="background-color: #fff">OBSERVACIONES</div>
                    <div class="div-table-col w-100 font-bold font-sm" style="background-color: #fff">
                    @if($document->observations) {{ $document->observations }} @endif
                </div>
                </div>
                <div style="width: 10px; float: left">&nbsp;</div>
             </div>

        </div>
    </div>

<table class="full-width mb-10 table_strip">
    <thead class="">
    <tr class="" style="background-color: #c0c0c0">
        <th class="text-center" style="height: 40px">Item</th>
        <th class="text-center" style="height: 40px">Código</th>
        <th class="text-left" style="height: 40px">Descripción</th>
        <th class="text-left" style="height: 40px">Modelo</th>
        <th class="text-center" style="height: 40px">Unidad</th>
        <th class="text-center" style="height: 40px">Cantidad</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        <tr class="table_xs2" >
            <td class="text-center strip_table">{{ $loop->iteration }}</td>
            <td class="text-center strip_table">{{ $row->item->internal_id }}</td>
            <td class="text-left strip_table">
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
                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                    @endforeach
                @endif
                @if($row->relation_item->is_set == 1)
                    <br>
                    @inject('itemSet', 'App\Services\ItemSetService')
                    @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                        {{$item}}<br>
                    @endforeach
                @endif

                @if($document->has_prepayment)
                    <br>
                    *** Pago Anticipado ***
                @endif
            </td>
            <td class="text-left strip_table">{{ $row->item->model ?? '' }}</td>
            <td class="text-center strip_table">{{ $row->item->unit_type_id }}</td>
            <td class="text-center strip_table">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>


<div style="height: 70px; width: 100%; border: 2px solid #c0c0c0; margin-bottom: 8px;">
    <span class="font-bold">COMENTARIOS</span><br>
</div>

<table class="full-width">
    <tr style="background-color: #c0c0c0">
        <th colspan="3">DETALLES DEL PEDIDO CON I.G.V</th>
    </tr>
    <tr style="background-color: #c0c0c0">
        <th>VALOR DE PEDIDO</th>
        <th>VALOR DE LA FACTURA</th>
        <th>NIVEL DE SERVICIO</th>
    </tr>
    <tr>
        <th>{{$document->reference_document->total}}</th>
        <th>{{$document->reference_document->total}}</th>
        <th>100%</th>
    </tr>
</table>
<div class="div-table-col w-100 mt-2 mb-2" style="height:3px; background-color:#c0c0c0"></div>

<table class="full-width mt-10 mb-10 table_strip">
    <tr>
        {{-- <th width="120px"><img src="data:image/png;base64, {{ $document->reference_document->qr }}" style="margin-right: -40px;margin-left: -40px; width: 130px;" /></th> --}}
        <th width="300px" style="text-align: left">
            Para transacciones
            @if(in_array($document->reference_document->document_type->id,['01','03']))
<table class="full-width" >
    <thead class="">
    <tr class="" style="background-color: #c0c0c0; color: #000!important; width: 100px;">
        <th class=" text-center py-2" width="8%" style="color: #000!important; font-size: 10px;">BANCO</th>
        <th class=" text-center py-2" width="50px" style="color: #000!important; font-size: 10px;">MONEDA</th>
        <th class=" text-center py-2" width="8%" style="color: #000!important; font-size: 10px;">CTA. CTE.</th>
        <th class=" text-center py-2" width="8%" style="color: #000!important; font-size: 10px;">CUENTA INTERBANCARIA (CCI)</th>
    </tr>
    </thead>.
    <tbody>

        @foreach($accounts as $account)
    <tr>
        <td width="80%" class="text-center">{{$account->description}}</td>
        <td width="80%" class="text-center">{{$account->currency_type->description}}</td>
        <td width="80%" class="text-center">{{$account->number}}</td>
        <td width="80%" class="text-center">{{$account->cci}}</td>
    </tr>
        @endforeach
    </tbody>
</table>
            @endif
 
            @foreach(array_reverse( (array) $document->reference_document->legends) as $row)
                @if ($row->code == "1000")
                <br><br><br>
                    <p style="text-transform: uppercase;font-size: 12px; margin-top: 15px;">Son: <span class="">{{ $row->value }} {{ $document->reference_document->currency_type->description }}</span></p>
                    @if (count((array) $document->reference_document->legends)>1)
                        <p><span class="">Leyendas</span></p>
                    @endif
                @else
                    <p> {{$row->code}}: {{ $row->value }} </p>
                @endif

            @endforeach
                   </th>
        <th width="50px"></th>
        <th width="300px">

<table class="full-width  ">
    <tbody>
        <tr>
            <td  class="text-right font-bold strip_table">SUBTOTAL: {{ $document->reference_document->currency_type->symbol }}</td>
            <td class="text-right font-bold strip_table">{{ number_format($document->reference_document->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td  class="text-right font-bold strip_table">IGV: {{ $document->reference_document->currency_type->symbol }}</td>
            <td class="text-right font-bold strip_table">{{ number_format($document->reference_document->total_igv, 2) }}</td>
        </tr>
        <tr>
            <td  class="text-right font-bold strip_table">TOTAL A PAGAR: {{ $document->reference_document->currency_type->symbol }}</td>
            <td class="text-right font-bold strip_table">{{ number_format($document->reference_document->total, 2) }}</td>
        </tr>
    </tbody>
</table>
        </th>
    </tr>
</table>

<div style="height: 20px; width: 100%; border: 2px solid #c0c0c0; margin-bottom: 40px; background-color: #c0c0c0;"></div>

<table class="full-width" style="border: 2px solid #c0c0c0">
    <tr>
        <td width="50%" class="font-bold" style="border: 2px solid #c0c0c0; padding: 15px;">Los tiempos de entrega se pueden retrasar ocasionalmente por diferente imprevistos</td>
        <td rowspan="2" width="50%" style="border: 2px solid #c0c0c0; padding: 10px" class="text-center">A la firma /sello del presente documentos se tiene por recibida la conformidad de la mercaderia. <br><br>   <br><br><center><strong> ............................... <br>FIRMA/SELLO</strong></center></td>
    </tr>
    <tr>
        <td width="50%" class="font-bold" style="border: 2px solid #c0c0c0; padding: 15px">No se aceptan reclamaciones posteriores a la firma de este documento, favor de verificar que su mercaderia le recibió completa y en buen estado.</td>
    </tr>
</table>

{{-- 

@if ($document->data_affected_document)
    @php
        $document_data_affected_document = $document->data_affected_document;

    $number = (property_exists($document_data_affected_document,'number'))?$document_data_affected_document->number:null;
    $series = (property_exists($document_data_affected_document,'series'))?$document_data_affected_document->series:null;
    $document_type_id = (property_exists($document_data_affected_document,'document_type_id'))?$document_data_affected_document->document_type_id:null;

    @endphp
    @if($number !== null && $series !== null && $document_type_id !== null)

        @php
            $documentType  = App\Models\Tenant\Catalogs\DocumentType::find($document_type_id);
            $textDocumentType = $documentType->getDescription();
        @endphp
        <table class="full-width border-box">
            <tr>
                <td class="text-bold border-bottom font-bold">{{$textDocumentType}}</td>
            </tr>
            <tr>
                <td>{{$series }}-{{$number}}</td>
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
@endif --}}
</body>
</html>
