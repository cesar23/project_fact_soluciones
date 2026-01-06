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
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');

    $left =  ($document->series) ? $document->series : $document->prefix;
    $tittle = $left.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
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
    }    $accounts = \App\Models\Tenant\BankAccount::all();
    $configuration = \App\Models\Tenant\Configuration::first();
    $configuration = \App\Models\Tenant\Configuration::first();
    $configurations = \App\Models\Tenant\Configuration::first();
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

    $marca_agua = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'custom_gasolution'.DIRECTORY_SEPARATOR.'marca_agua.png');
    $footer_image = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'custom_gasolution'.DIRECTORY_SEPARATOR.'pie.png');

@endphp
<html>
<head>
    {{--<title>{{ $tittle }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
<div class="item_watermark" style="position: absolute; text-align: center; top:35%;">
    <img style="width: 100%" height="180px" src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="marca_agua" class="" style="opacity: 0.2;width: 95%">
</div>
<table class="full-width">
    <tr>
        @if($company->logo)
            <td width="20%">
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 150px;">
                </div>
            </td>
        @else
            <td width="20%">
            </td>
        @endif
        <td width="50%" class="pl-3">
            <div class="text-left">
                <h4 class="">{{ $company_name }}</h4>
                <h5>{{ 'RUC '.$company->number }}</h5>
                @if ($company_owner)
                    <h6 class="">De: {{ $company_owner }}</h6>
                @endif
                <h6 style="text-transform: uppercase;">
                    {{ ($establishment->address !== '-')? $establishment->address : '' }}
                    {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </h6>
                <h6>{{ ($establishment->email !== '-')? $establishment->email : '' }}</h6>
                <h6>{{ ($establishment->telephone !== '-')? $establishment->telephone : '' }}</h6>
            </div>
        </td>
        <td width="30%" class="border-box py-4 px-2 text-center">
            <h5 class="text-center">NOTA DE VENTA</h5>
            <h3 class="text-center">{{ $tittle }}</h3>
        </td>
    </tr>
</table>
<table class="full-width mt-5">
    <tr>
        <td width="15%">Cliente:</td>
        <td width="45%">{{ $customer->name }}</td>
        <td width="25%">Fecha de emisión:</td>
        <td width="15%">{{ $document->date_of_issue->format('Y-m-d') }}</td>
    </tr>
    <tr>
        <td>{{ $customer->identity_document_type->description }}:</td>
        <td>{{ $customer->number }}</td>
    </tr>
    @if ($customer->address !== '')
    <tr>
        <td class="align-top">Dirección:</td>
        <td colspan="3">
            {{ strtoupper($customer->address) }}
            {{ ($customer->district_id !== '-')? ', '.strtoupper($customer->district->description) : '' }}
            {{ ($customer->province_id !== '-')? ', '.strtoupper($customer->province->description) : '' }}
            {{ ($customer->department_id !== '-')? '- '.strtoupper($customer->department->description) : '' }}
        </td>
    </tr>
    @endif
    <tr>
        <td>Teléfono:</td>
        <td>{{ $customer->telephone }}</td>
        <td>Vendedor:</td>
        <td> {{ $document->user->name }}</td>
    </tr>
    @if ($document->plate_number !== null)
    <tr>
        <td width="15%">N° Placa:</td>
        <td width="85%">{{ $document->plate_number }}</td>
    </tr>
    @endif
    @if ($document->total_canceled)
    <tr>
        <td class="align-top">Estado:</td>
        <td colspan="3">CANCELADO</td>
    </tr>
    @else
    <tr>
        <td class="align-top">Estado:</td>
        <td colspan="3">PENDIENTE DE PAGO</td>
    </tr>
    @endif
    @if ($document->observation)
    <tr>
        <td class="align-top">Observación:</td>
        <td colspan="3">{{ $document->observation }}</td>
    </tr>
    @endif
    @if ($document->reference_data)
        <tr>
            <td class="align-top">D. Referencia:</td>
            <td colspan="3">{{ $document->reference_data }}</td>
        </tr>
    @endif
    @if ($document->purchase_order)
        <tr>
            <td class="align-top">Orden de compra:</td>
            <td colspan="3">{{ $document->purchase_order }}</td>
        </tr>
    @endif
</table>

@if ($document->guides)
<br/>
{{--<strong>Guías:</strong>--}}
<table>
    @foreach($document->guides as $guide)
        <tr>
            @if(isset($guide->document_type_description))
            <td>{{ $guide->document_type_description }}</td>
            @else
            <td>{{ $guide->document_type_id }}</td>
            @endif
            <td>:</td>
            <td>{{ $guide->number }}</td>
        </tr>
    @endforeach
</table>
@endif

<table class="full-width mt-10 mb-10">
    <thead class="">
    <tr class="">
        <th class="border-top-bottom text-center py-1 desc" class="cell-solid" width="12%">CÓDIGO</th>
        <th class="border-top-bottom text-center py-1 desc" class="cell-solid" width="8%">CANT.</th>
        {{-- <th class="border-top-bottom text-center py-1 desc" class="cell-solid" width="8%">U.M.</th> --}}
        <th class="border-top-bottom text-center py-1 desc" class="cell-solid" width="40%">DESCRIPCIÓN</th>
        <th class="border-top-bottom text-center py-1 desc" class="cell-solid" width="12%">LOTE</th>
        <th class="border-top-bottom text-center py-1 desc" class="cell-solid" width="12%">F. VCTO.</th>
        <th class="border-top-bottom text-right py-1 desc" class="cell-solid" width="12%">P.UNIT</th>
        <th class="border-top-bottom text-center py-1 desc" class="cell-solid" width="8%">DCTO.</th>
        <th class="border-top-bottom text-center py-1 desc" class="cell-solid" width="12%">TOTAL</th>
    </tr>
    </thead>
    @foreach($document->items as $row)
    @php
        // dd($row->item->lot_code);
    @endphp
        <tr>
            <td class="p-1 text-center align-top desc cell-solid-rl">{{ $row->item->internal_id }}</td>
            <td class="p-1 text-center align-top desc cell-solid-rl">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            {{-- <td class="p-1 text-center align-top desc cell-solid-rl">{{ symbol_or_code($row->item->unit_type_id) }}</td> --}}
            <td class="p-1 text-left align-top desc text-upp cell-solid-rl">
                @if($row->name_product_pdf)
                    {!!$row->name_product_pdf!!}
                @else
                    {!!$row->item->description!!}
                @endif

                  

                @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        @if($attr->attribute_type_id === '5032')
                            @php
                                $total_weight += $attr->value * $row->quantity;
                            @endphp
                        @endif
                        <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                    @endforeach
                @endif
                {{-- @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                    @endforeach
                @endif --}}

                @if($row->item->is_set == 1)
                    <br>
                    @inject('itemSet', 'App\Services\ItemSetService')
                    {{join( "-", $itemSet->getItemsSet($row->item_id) )}}
                @endif
            </td>                    
            <td class="p-1 text-center align-top desc cell-solid-rl">
                    @php
                        foreach ($row->item->lots_group as $flote) {
                            if ($flote->compromise_quantity!=0) {
                               echo $flote->code;
                            }

                        }
                    @endphp

            </td>
            <td class="p-1 text-center align-top desc cell-solid-rl">
                    @php
                        foreach ($row->item->lots_group as $flote) {
                            if ($flote->compromise_quantity!=0) {
                               echo $flote->date_of_due;
                            }

                        }
                    @endphp

            </td>
            <td class="p-1 text-right align-top desc cell-solid-rl">{{ number_format($row->unit_price, 2) }}</td>
            <td class="p-1 text-right align-top desc cell-solid-rl">
                @if($row->discounts)
                    @php
                        $total_discount_line = 0;
                        foreach ($row->discounts as $disto) {
                            $total_discount_line = $total_discount_line + $disto->amount;
                        }
                    @endphp
                    {{ number_format($total_discount_line, 2) }}
                @endif
            </td>
            <td class="p-1 text-right align-top desc cell-solid-rl">{{ number_format($row->total, 2) }}</td>
        </tr>
    @endforeach
        @if($document->total_exportation > 0)
            <tr>
                <td colspan="7" class="text-right font-bold">OP. EXPORTACIÓN: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
            </tr>
        @endif
        @if($document->total_free > 0)
            <tr>
                <td colspan="7" class="text-right font-bold">OP. GRATUITAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
            </tr>
        @endif
        @if($document->total_unaffected > 0)
            <tr>
                <td colspan="7" class="text-right font-bold">OP. INAFECTAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
            </tr>
        @endif
        @if($document->total_exonerated > 0)
            <tr>
                <td colspan="7" class="text-right font-bold">OP. EXONERADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
            </tr>
        @endif
        {{-- @if($document->total_taxed > 0)
             <tr>
                <td colspan="7" class="text-right font-bold">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif --}}
        @if($document->total_discount > 0)
            <tr>
                <td colspan="7" class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'ANTICIPO':'DESCUENTO TOTAL')}}: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        @endif
        {{--<tr>
            <td colspan="7" class="text-right font-bold">IGV: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
        </tr>--}}
        <tr>
            <td colspan="7" class="text-right font-bold">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
        </tr>
    </tbody>
</table>

<table class="full-width">
    <tr>
        <td width="65%" style="text-align: top; vertical-align: top;">
            <br>
            @foreach($accounts as $account)
                <p>
                    <span class="font-bold">{{$account->bank->description}}</span>
                    {{$account->currency_type->description}}
                    <br> <span class="font-bold">NUMERO DE CUENTA:</span> {{$account->number}}
                    @if($account->cci)
                        <br><span class="font-bold">CCI:</span> {{$account->cci}}
                    @endif
                </p>
                <br>
            @endforeach
        </td>
    </tr>
</table>
<br>
@if($document->payment_method_type_id && $payments->count() == 0)
    <table class="full-width">
        <tr>
            <td>
                <strong>PAGO: </strong>{{ $document->payment_method_type->description }}
            </td>
        </tr>
    </table>
@endif

@if($payments->count())

<table class="full-width">
<tr>
    <td>
    <strong>PAGOS:</strong> </td></tr>
        @php
            $payment = 0;
        @endphp
        @foreach($payments as $row)
            <tr><td>- {{ $row->date_of_payment->format('d/m/Y') }} - {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment }}</td></tr>
            @php
                $payment += (float) $row->payment;
            @endphp
        @endforeach
        <tr><td><strong>SALDO:</strong> {{ $document->currency_type->symbol }} {{ number_format($document->total - $payment, 2) }}</td>
    </tr>

</table>
@endif
<div class="text-center">
    @if($configuration->header_image)
    <img style="width: 45%" height="80px" src="data:{{mime_content_type(public_path("storage/uploads/header_images/{$configuration->header_image}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/header_images/{$configuration->header_image}")))}}" alt="image" class="">
    @endif
</div>
</body>
</html>
