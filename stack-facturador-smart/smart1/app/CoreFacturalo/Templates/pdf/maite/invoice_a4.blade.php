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
    $customer = $document->customer;
    $invoice = $document->invoice;
    $document_base = ($document->note) ? $document->note : null;

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();

    if($document_base) {

        $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    } else {

        $affected_document_number = null;
    }

    $configuration = \App\Models\Tenant\Configuration::first();
    $configurations = \App\Models\Tenant\Configuration::first();
    $company_name = $company->name;
    $company_owner = null;
    if ($configurations->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }

    $configuration = \App\Models\Tenant\Configuration::first();
    $configurations = \App\Models\Tenant\Configuration::first();
    $company_name = $company->name;
    $company_owner = null;
    if ($configurations->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }

    $configuration = \App\Models\Tenant\Configuration::first();
    $configurations = \App\Models\Tenant\Configuration::first();
    $company_name = $company->name;
    $company_owner = null;
    if ($configurations->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }

$payments = $document->payments;

$company_name = $company->name;
$company_owner = null;
if ($configurations->trade_name_pdf) {
    $company_name = $company->trade_name;
    $company_owner = $company->name;
}

    $company_name = $company->name;
    $company_owner = null;
    if ($configurations->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }
    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');
    $count_=0;
    // dd($document->items);
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
    <div class="header">
        <div class="text-center float-left header-logo">
            @if($company->logo)
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("storage".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."logos".DIRECTORY_SEPARATOR."{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."logos".DIRECTORY_SEPARATOR."{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 150px;">
                </div>
            @endif
        </div>
        <div class="text-center float-left header-company">
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

    <div class="information mt-3">
        <div class="div-table">
             <div class="div-table-row">
                <div class="div-table-col font-xs" style="width:100%">
                    <div class="div-table-col w-100 font-bold" style="background-color: #c0c0c0; padding: 5px">
                        CLIENTE {{ $document->customer_id }}
                    </div>
                    <div class="div-table-col w-100 font-bold" style="background-color: #fff">
                        {{ $customer->name }}
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        DIRECCIÓN
                    </div>
                    <div class="div-table-col w-85" style="background-color: #fff">
                        : {{ $customer->address }}
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        DISTRITO
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        : {{ ($customer->district_id !== '-')? ''.$customer->district->description : '' }}
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        PROVINCIA
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        :  {{ ($customer->province_id !== '-')? ''.$customer->province->description : '' }}
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        DEPARTAMENTO
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        : {{ ($customer->department_id !== '-')? ''.$customer->department->description : '' }}
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        TELÉFONO
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        : {{ $customer->telephone }}
                    </div>
                    {{-- <div class="div-table-col w-15" style="background-color: #fff">
                        E-MAIL
                    </div> --}}
                    <div class="div-table-col w-30" style="background-color: #fff">
                        {{-- :  --}}{{$customer->email}}
                    </div>
                    <div class="div-table-col w-70" style="background-color: #fff">
                        
                    </div>
                    <div class="div-table-col w-15" style="background-color: #fff">
                        {{ $customer->identity_document_type->description }}
                    </div>
                    <div class="div-table-col w-85" style="background-color: #fff">
                        :  {{$customer->number}}
                    </div>
                </div>
                <div style="width: 10px; float: left">&nbsp;</div>
                <div class="div-table-col font-xs" style="width:328px">

@if ($document->transport)

@php
    $transport = $document->transport;
    $origin_district_id = (array)$transport->origin_district_id;
    $destinatation_district_id = (array)$transport->destinatation_district_id;
    $origin_district = Modules\Order\Services\AddressFullService::getDescription($origin_district_id[2]);
    $destinatation_district = Modules\Order\Services\AddressFullService::getDescription($destinatation_district_id[2]);
$ubigeo_ = explode("-", $origin_district);
// dd(trim($pieces[1]));
@endphp

                    <div class="div-table-col w-100 font-bold" style="background-color: #c0c0c0; padding: 5px">
                        DESTINATARIO
                    </div>
                    <div class="div-table-col w-100 font-bold" style="background-color: #fff">
                        {{ $company->name }}
                    </div>
                    <div class="div-table-col w-25" style="background-color: #fff">
                        DIRECCIÓN
                    </div>
                    <div class="div-table-col w-75" style="background-color: #fff">
                        : {{ $transport->origin_address }}<
                    </div>
                    <div class="div-table-col w-25" style="background-color: #fff">
                        DISTRITO
                    </div>
                    <div class="div-table-col w-75" style="background-color: #fff">
                        : {{ trim($ubigeo_[2]) }}
                    </div>
                    <div class="div-table-col w-25" style="background-color: #fff">
                        PROVINCIA
                    </div>
                    <div class="div-table-col w-75" style="background-color: #fff">
                        :  {{ trim($ubigeo_[1]) }}
                    </div>
                    <div class="div-table-col w-25" style="background-color: #fff">
                        DEPARTAMENTO
                    </div>
                    <div class="div-table-col w-75" style="background-color: #fff">
                        : {{ trim($ubigeo_[0]) }}
                    </div>
                    <div class="div-table-col w-25" style="background-color: #fff">
                        TELÉFONO
                    </div>
                    <div class="div-table-col w-75" style="background-color: #fff">
                        : {{ $transport->seat_number }}
                    </div>

@endif

                </div>

             </div>

        </div>
    </div>



<table class="full-width mt-10 mb-10">
    <thead class="">
        <tr class="table_xs" style="background-color: #c0c0c0; ">
            <th class="text-center py-2" width="90px">ORDEN DE COMPRA</th>
            <th class="text-center py-2" width="">PEDIDO</th>
            <th class="text-center py-2">ZONA</th>
            <th class="text-center py-2">RUTA</th>
            <th class="text-center py-2" width="">GUÍA</th>
            <th class="text-center py-2" width="120px">FECHA Y HORA DE EMISIÓN</th>
            <th class="text-center py-2" width="">CONDICIÓN DE PAGO</th>
            <th class="text-center py-2" width="">VENCIMIENTO</th>
            <th class="text-center py-2" width="">Moneda</th>
        </tr>
    </thead>
    <tbody>
        <tr class="table_xs2">
            <td class="text-center align-top">@if ($document->purchase_order) {{ $document->purchase_order }} @endif</td>
            <td class="text-center align-top">@if($document->additional_information)
                @foreach($document->additional_information as $information)
                    @if ($information)
                        {{ $information }}
                    @endif
                @endforeach
            @endif
            </td>
            <td class="text-center align-top">@if($document->person->zone_id != null) {{\App\CoreFacturalo\Helpers\Template\TemplateHelper::getZoneById($document->person->zone_id) }} @endif</td>
            <td class="text-center align-top">{{$document->plate_number}}</td>
            <td class="text-center align-top">                
@if ($document->guides)
<table>
    @foreach($document->guides as $guide)
        <tr>
            {{-- @if(isset($guide->document_type_description))
            <td>{{ $guide->document_type_description }}</td>
            @else --}}
            {{-- <td>{{ $guide->document_type_id }}</td> --}}
            {{-- @endif --}}
            {{-- <td>:</td> --}}
            <td>{{ $guide->number }}</td>
        </tr>
    @endforeach
</table>
@endif


@if ($document->reference_guides)
    @if (count($document->reference_guides) > 0)
    <table>
        @foreach($document->reference_guides as $guide)
            <tr>
                <td>{{ $guide->series }} - {{ $guide->number }}</td>
                {{-- <td>-</td>
                <td>{{ $guide->number }}</td> --}}
            </tr>
        @endforeach
    </table>
    @endif
@endif</td>
            <td class="text-center align-top">{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} </td>
            <td class="text-center align-top">
@php
    $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
@endphp
            {{ $paymentCondition }}</td>
            <td class="text-center align-top">@if($invoice) {{$invoice->date_of_due->format('Y-m-d')}} @endif</td>
            <td class="text-center align-top">{{ $document->currency_type->description }}</td>
        </tr>
    </tbody>
</table>

<table class="full-width mt-10 mb-10 table_strip" style="margin-top: 20px;">
    <thead class="">
    <tr class="table_xs" style="background-color: #c0c0c0; ">
        <th class="text-center py-2">Item</th>
        <th class="text-center py-2">DESCRIPCIÓN DEL PRODUCTO</th>
        <th class="text-center py-2" width="8%">CÓDIGO DEL PRODUCTO</th>
        <th class="text-center py-2" width="8%">CAN- <br>TIDAD</th>
        <th class="text-center py-2">UM</th>
        <th class="text-center py-2" width="12%">VALOR UNITARIO</th>
        <th class="text-center py-2" width="8%">DESCUENTOS</th>
        <th class="text-center py-2" width="12%">V.UNIT</th>
        <th class="text-center py-2" width="12%">I.G.V.</th>
        <th class="text-center py-2" width="12%">IMPORTE TOTAL</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
    @php
     $count_++;
    @endphp
        <tr class="table_xs2">
            <td class="text-center align-top strip_table">{{$count_}}</td>
            <td class="text-left align-top strip_table">
                @if($row->name_product_pdf)
                    {!!$row->name_product_pdf!!}
                @else
                    {!!$row->item->description!!}
                @endif

                @if($row->total_isc > 0)
                    <br/><span style="font-size: 9px">ISC : {{ $row->total_isc }} ({{ $row->percentage_isc }}%)</span>
                @endif

                  

                {{-- @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                    @endforeach
                @endif --}}
                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                    @endforeach
                @endif

                @if($row->item->is_set == 1)
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
            <td class="text-center align-top strip_table">{{ $row->item->internal_id }}</td>
            <td class="text-center align-top strip_table">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center align-top strip_table">{{ symbol_or_code($row->item->unit_type_id) }}</td>
            <td class="text-right align-top strip_table">{{ number_format($row->unit_price, 2) }}</td>
            <td class="text-right align-top strip_table">
                @if($row->discounts)
                    @php
                        $total_discount_line = 0;
                        foreach ($row->discounts as $disto) {
                            $total_discount_line = $total_discount_line + $disto->amount;
                        }
                    @endphp
                    {{ number_format($total_discount_line, 2) }}
                @else
                0
                @endif
            </td>
            <td class="text-right align-top strip_table">{{ number_format($row->unit_price, 2) }}</td>
            <td class="text-right align-top strip_table">{{ number_format($row->total_taxes, 2) }}</td>
            <td class="text-right align-top strip_table">{{ number_format($row->total, 2) }}</td>
        </tr>
    @endforeach

    </tbody>
</table>
<div style="height:50px">&nbsp;</div>
<table class="full-width mt-10 mb-10 table_strip">
    <tr>
        <th width="120px"><img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -40px;margin-left: -40px; width: 130px;" /></th>
        <th width="300px">
            @if(in_array($document->document_type->id,['01','03']))
<table class="full-width" >
    <thead class="">
    <tr class="" style="background-color: #c0c0c0; color: #000!important; width: 100px;">
        <th class=" text-center py-2" width="8%" style="color: #000!important; font-size: 10px;">Banco</th>
        <th class=" text-center py-2" width="50px" style="color: #000!important; font-size: 10px;">Moneda</th>
        <th class=" text-center py-2" width="8%" style="color: #000!important; font-size: 10px;">Cta. Cte.</th>
        <th class=" text-center py-2" width="8%" style="color: #000!important; font-size: 10px;">Cuenta Interbancaria (CCI)</th>
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
 
            @foreach(array_reverse( (array) $document->legends) as $row)
                @if ($row->code == "1000")
                <br><br><br>
                    <p style="text-transform: uppercase;font-size: 15px; margin-top: 15px;">Son: <span class="">{{ $row->value }} {{ $document->currency_type->description }}</span></p>
                    @if (count((array) $document->legends)>1)
                        <p><span class="">Leyendas</span></p>
                    @endif
                @else
                    <p> {{$row->code}}: {{ $row->value }} </p>
                @endif

            @endforeach
                   </th>
        <th width="50px"></th>
        <th width="300px">

<table class="full-width mt-10 mb-10 ">
    <tbody>

        {{-- @if($document->total_discount > 0 && $document->subtotal > 0) --}}
        <tr>
            <td  class="text-right font-bold strip_table">SUBTOTAL: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold strip_table">{{ number_format($document->subtotal-$document->total_discount, 2) }}</td>
        </tr>
        {{-- @endif --}}

        {{-- @if($document->total_discount > 0) --}}
            <tr>
                <td  class="text-right font-bold strip_table">{{(($document->total_prepayment > 0) ? 'ANTICIPO':'TOTAL DESC. FINANC.')}}: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        {{-- @endif --}}


        {{-- @if($document->total_discount > 0 && $document->subtotal > 0) --}}
        <tr>
            <td  class="text-right font-bold strip_table">SUBTOTAL: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold strip_table">{{ number_format($document->subtotal, 2) }}</td>
        </tr>
        {{-- @endif --}}

        {{-- @if ($document->document_type_id === '07') --}}
           {{--  @if($document->total_taxed >= 0)
            <tr>
                <td  class="text-right font-bold strip_table">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
            @endif
        @elseif($document->total_taxed > 0) --}}
            <tr>
                <td  class="text-right font-bold strip_table">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        {{-- @endif --}}

            <tr>
                <td  class="text-right font-bold strip_table">OP. INAFECTAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table">{{ number_format($document->total_unaffected, 2) }}</td>
            </tr>

            <tr>
                <td  class="text-right font-bold strip_table">OP. EXONERADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table">{{ number_format($document->total_exonerated, 2) }}</td>
            </tr>

            <tr>
                <td  class="text-right font-bold strip_table">OP. GRATUITAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table">{{ number_format($document->total_free, 2) }}</td>
            </tr>

        @if($document->total_exportation > 0)
            <tr>
                <td  class="text-right font-bold strip_table" width="60%">OP. EXPORTACIÓN: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table" width="40%">{{ number_format($document->total_exportation, 2) }}</td>
            </tr>
        @endif

        

        @if($document->total_plastic_bag_taxes > 0)
            <tr>
                <td  class="text-right font-bold strip_table">ICBPER: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
            </tr>
        @endif
        
        @if($document->total_isc > 0)
        <tr>
            <td  class="text-right font-bold strip_table">ISC: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold strip_table">{{ number_format($document->total_isc, 2) }}</td>
        </tr>
        @endif
        
        
        @if($document->total_charge > 0)
            @php
                $total_factor = 0;
                foreach($document->charges as $charge) {
                    $total_factor = ($total_factor + $charge->factor) * 100;
                }
            @endphp
            <tr>
                <td  class="text-right font-bold strip_table">CARGOS ({{$total_factor}}%): {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table">{{ number_format($document->total_charge, 2) }}</td>
            </tr>
        @endif

        <tr>
            <td  class="text-right font-bold strip_table">IGV: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold strip_table">{{ number_format($document->total_igv, 2) }}</td>
        </tr>



        @if($document->perception)
            <tr>
                <td  class="text-right font-bold strip_table"> IMPORTE TOTAL: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table">{{ number_format($document->total, 2) }}</td>
            </tr>
            <tr>
                <td  class="text-right font-bold strip_table">PERCEPCIÓN: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table">{{ number_format($document->perception->amount, 2) }}</td>
            </tr>
            <tr>
                <td  class="text-right font-bold strip_table">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table">{{ number_format(($document->total + $document->perception->amount), 2) }}</td>
            </tr>
        @else
            <tr>
                <td  class="text-right font-bold strip_table">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table">{{ number_format($document->total, 2) }}</td>
            </tr>
        @endif
  
        {{-- @if(($document->retention || $document->detraction) && $document->total_pending_payment > 0) --}}
            {{-- <tr>
                <td  class="text-right font-bold strip_table">M. PENDIENTE: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table">{{ number_format($document->total_pending_payment, 2) }}</td>
            </tr> --}}
        {{-- @endif --}}

        @if($balance < 0)

            <tr>
                <td  class="text-right font-bold strip_table">VUELTO: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold strip_table">{{ number_format(abs($balance),2, ".", "") }}</td>
            </tr>

        @endif



    </tbody>
</table>
        </th>
    </tr>
</table>

<div style="background-color: #c0c0c0; width: 100%; height: 25px; margin-bottom: 15px">&nbsp;</div>

<table class="full-width" style="border: double black">
    <tr class=" table_xs2">
        <th width="25%" class="font-bold text-left strip_table padding_left">Información del crédito</th>
        <th width="1%"></th>
        <th></th>
    </tr>
    <tr class="strip_table table_xs2">
        <th width="25%" class="text-left strip_table padding_left">Monto neto pendiente de pago</th>
        <th width="1%">:</th>
        <th class="text-left padding_left">
            @php
            $total_pago_cuotas = 0;
            @endphp
            @foreach($document->fee as $key => $quote)
                @php
                $total_pago_cuotas += $quote->amount;
                @endphp

                {{-- {{ array_sum(array)}} --}}
                {{-- <tr>
                    <td>&#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }} / Fecha: {{ $quote->date->format('d-m-Y') }} / Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
                </tr> --}}
            @endforeach
            {{$total_pago_cuotas}}
        </th>
    </tr>
    <tr class="strip_table table_xs2">
        <th width="25%" class="text-left strip_table padding_left">Total de cuotas</th>
        <th width="1%">:</th>
        <th class="text-left padding_left">{{count($document->fee)}}</th>
    </tr>

</table>


<table class="full-width" style="border: double black; margin-top: 2px;">
    <tr class=" table_xs2">
        <th class="font-bold text-center strip_table padding_left">Nº Cuota</th>
        <th class="font-bold text-center strip_table padding_left">Fec. Venc.</th>
        <th class="font-bold text-center strip_table padding_left">Monto</th>
        <th class="font-bold text-center strip_table padding_left">Nº Cuota</th>
        <th class="font-bold text-center strip_table padding_left">Fec. Venc.</th>
        <th class="font-bold text-center strip_table padding_left">Monto</th>
        <th class="font-bold text-center strip_table padding_left">Nº Cuota</th>
        <th class="font-bold text-center strip_table padding_left">Fec. Venc.</th>
        <th class="font-bold text-center strip_table padding_left">Monto</th>
    </tr>
    <tr class=" table_xs2">
        @foreach($document->fee as $key => $quote)
        <th class="font-bold text-center strip_table padding_left">{{$key + 1}}</th>
        <th class="font-bold text-center strip_table padding_left">{{$quote->date->format('d-m-Y')}}</th>
        <th class="font-bold text-center strip_table padding_left">{{$quote->amount}}</th>
        @endforeach
    </tr>

</table>


@if ($document->retention)
<table class="full-width" style="border: double black; margin-top: 5px">
    <tr class=" table_xs2">
        <th  colspan="3" class="font-bold text-left strip_table padding_left">Información de la retención</th>
    </tr>
    <tr class="strip_table table_xs2">
        <th width="120px" class="text-left strip_table padding_left">Base imponible de la Retención</th>
        <th class="text-left padding_left">: {{ $document->currency_type->symbol}} {{ $document->retention->base }}</th>
        <th class="text-left padding_left">Porcentaje de la retención</th>
        <th class="text-left padding_left">: {{ $document->retention->percentage * 100 }}%</th>
        <th class="text-left padding_left">monto de la retención</th>
        <th class="text-left padding_left">: {{ $document->currency_type->symbol}} {{ $document->retention->amount }}</th>
    </tr>
</table>
@endif


    {{-- <table class="full-width mt-3">
        <tr>
            <td colspan="3">
                <strong>Información de la retención</strong>
            </td>
        </tr>
        <tr>
            <td width="120px">Base imponible</td>
            <td width="8px">:</td>
            <td>{{ $document->currency_type->symbol}} {{ $document->retention->base }}</td>

            <td width="80px">Porcentaje</td>
            <td width="8px">:</td>
            <td>{{ $document->retention->percentage * 100 }}%</td>
        </tr>
        <tr>
            <td width="120px">Monto</td>
            <td width="8px">:</td>
            <td>{{ $document->currency_type->symbol}} {{ $document->retention->amount }}</td>
        </tr>
    </table> --}}
</body>
</html>
