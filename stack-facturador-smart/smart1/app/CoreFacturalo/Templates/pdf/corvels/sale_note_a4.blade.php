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
    $template_data = \App\Models\Tenant\Establishment::where('code', '=', $establishment->code)->first();

@endphp
<html>
<head>
    {{--<title>{{ $tittle }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
<table class="full-width">
    <tr>
        <td width="70%" class="pl-3">
        @if ($establishment->logo ?? false)
            <div class="company_logo_box">
                <img src="{{ public_path($establishment->logo) }}" alt="{{$company->name}}" class="company_logo" style="max-width: 150px;">
            </div>
        @else
            @if($company->logo)
                    <div class="company_logo_box">
                        <img src="{{ public_path("storage/uploads/logos/{$company->logo}") }}" alt="{{$company->name}}" class="company_logo" style="max-width: 150px;">
                    </div>
            @else
                    <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px">
            @endif
        @endif
            <div class="text-left">
                <h4 class="text-center font-xlg font-bold">{{ $company->name }}</h4>
                
                <h6 style="text-transform: uppercase;">
                    {{ ($establishment->address !== '-')? $establishment->address : '' }}
                    {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </h6>

                @isset($establishment->trade_address)
                    <h6>{{ ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : '' }}</h6>
                @endisset

                <h6>{{ ($establishment->telephone !== '-')? ' '.$establishment->telephone : '' }}  -  {{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}
                @isset($establishment->web_address)
                    {{ ($establishment->web_address !== '-')? ' -  Web: '.$establishment->web_address : '' }}
                @endisset
            </h6>

                

                @isset($establishment->aditional_information)
                    <h6>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</h6>
                @endisset
            </div>
        </td>

        <td width="30%" class="border-box py-4 px-2 text-center" style="background: url('{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'bgbox.png') }}'); background-repeat: no-repeat;">
            <h5 class="text-center font-xxlg font-bold">{{ 'RUC '.$company->number }}</h5>
            <h5 class="text-center font-xlg font-bold">NOTA DE VENTA</h5>
            <h3 class="text-center font-xxlg font-bold">{{ $tittle }}</h3>
        </td>
    </tr>
</table>

<table class="full-widths mt-3">
    <tr>
        <td width="70%" class="pl-2 pr-4 border-box2" style="background: url('{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'bgbox2.png') }}'); background-repeat: no-repeat;">
<div class="border-box font-bold" style="background: url('{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'bgbox2.png') }}'); background-repeat: no-repeat;">
    Datos del cliente
</div>

<table class="full-width py-4s px-s2 ext-center">
{{--     <tr>
        <td colspan="3" width="80px" class=" font-bold" style="vertical-align: top;">Datos del cliente</td>
    </tr> --}}
    <tr>
        <td style="vertical-align: top;" class="font-bold font-xs">Cliente</td>
        <td style="vertical-align: top;" class="font-bold font-xs">:</td>
        <td style="vertical-align: top;" class=" font-xs">{{ $customer->name }}</td>
{{--         <td width="15%">Cliente:</td>
        <td width="45%">{{ $customer->name }}</td> --}}
    </tr>
    <tr>
        <td class="font-bold  font-xs">{{ $customer->identity_document_type->description }}</td>
        <td class=" font-xs">:</td>
        <td class=" font-xs">{{ $customer->number }}</td>
    </tr>
    @if ($customer->address !== '')
    <tr>
        <td class="font-bold align-top font-xs">Dirección</td>
        <td class="align-top font-xs">:</td>
        <td colspan="" class=" font-xs">
            {{ strtoupper($customer->address) }}
            {{ ($customer->district_id !== '-')? ', '.strtoupper($customer->district->description) : '' }}
            {{ ($customer->province_id !== '-')? ', '.strtoupper($customer->province->description) : '' }}
            {{ ($customer->department_id !== '-')? '- '.strtoupper($customer->department->description) : '' }}
        </td>
    </tr>
    @endif

</table>

        </td>
        <td width="30%" class="border-box1 py-4 px-2 ext-center" style="background: url('{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'bgbox1.png') }}'); background-repeat: no-repeat;">



<table class="full-width ">

    <tr>
        <td width="" class=" font-xs">Fecha de emisión:</td>
        <td width="" class=" font-xs">{{ $document->date_of_issue->format('Y-m-d') }}</td>
    </tr>
    <tr>
        <td class=" font-xs">Teléfono:</td>
        <td class=" font-xs">{{ $customer->telephone }}</td>
    </tr>
    <tr>
        <td class=" font-xs">Vendedor:</td>
        <td class=" font-xs"> {{ $document->user->name }}</td>
    </tr>
    @if ($document->plate_number !== null)
    <tr>
        <td width="15%" class=" font-xs">N° Placa:</td>
        <td width="85%" class=" font-xs">{{ $document->plate_number }}</td>
    </tr>
    @endif
    @if ($document->total_canceled)
    <tr>
        <td class="align-top font-xs">Estado:</td>
        <td colspan="3" class=" font-xs">CANCELADO</td>
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

        </td>
    </tr>
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
    <thead class="border-tables">
    
    <tr class="bg-grey " >
        <th class="border-top-bottom2 text-center py-2" width="8%">Cant.</th>
        <th class="border-top-bottom2 text-center py-2" width="8%">Unidad</th>
        <th class="border-top-bottom2 text-left py-2">Descripción</th>
        <th class="border-top-bottom2 text-center py-2" width="8%">Lote</th>
        <th class="border-top-bottom2 text-center py-2" width="8%">Serie</th>
        <th class="border-top-bottom2 text-right py-2" width="12%">P.Unit</th>
        <th class="border-top-bottom2 text-right py-2" width="8%">Dto.</th>
        <th class="border-top-bottom2 text-right py-2" width="12%">Total</th>
    </tr>
    </thead>
    <tbody class="colores">
    @foreach($document->items as $row)
        <tr class="color-border">
            <td class="text-center align-top color-border">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center align-top color-border">{{ symbol_or_code($row->item->unit_type_id) }}</td>
            <td class="text-left color-border">
                {!!$row->item->description!!}   

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

                @if($row->item->is_set == 1)

                 <br>
                 @inject('itemSet', 'App\Services\ItemSetService')
                 @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                     {{$item}}<br>
                 @endforeach
                @endif

            </td>
            <td class="text-center align-top color-border">
                @inject('itemLotGroup', 'App\Services\ItemLotsGroupService')
                @php
                    $lot_code = isset($row->item->lots_group) ? collect($row->item->lots_group)->first(function($row){ return $row->checked == true;}):null;
                @endphp
                {{
                    $itemLotGroup->getLote($lot_code ? $lot_code->id : null)
                }}

            </td>
            <td class="text-center align-top color-border">

                @isset($row->item->lots)
                    @foreach($row->item->lots as $lot)
                        @if( isset($lot->has_sale) && $lot->has_sale)
                            <span style="font-size: 9px">{{ $lot->series }}</span><br>
                        @endif
                    @endforeach
                @endisset
            </td>
            <td class="text-right align-top color-border">{{ number_format($row->unit_price, 2) }}</td>
            <td class="text-right align-top color-border">
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
            <td class="text-right align-top color-border">{{ number_format($row->total, 2) }}</td>
        </tr>
        
    @endforeach
 
    </tbody>
    <tbody class="">

        @if($document->total_discount > 0)
            <tr>
                <td colspan="7" class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'Anticipo':'Descuento TOTAL')}}: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="7" class="text-right font-bold">Total a pagar: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
        </tr>
    </tbody>
</table>

@if($accounts->count())
<div class="border-box-number">
            @foreach($accounts as $account)
                <p>
                <span class="font-bold">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                <span class="font-bold">N°:</span> {{$account->number}}
                @if($account->cci)
                - <span class="font-bold">CCI:</span> {{$account->cci}}
                @endif
                </p>
            @endforeach
</div>
@endif

@if($document->payment_method_type_id && $payments->count() == 0)
<br>
    <div class="border-box-number">
               <strong>Pago: </strong>{{ $document->payment_method_type->description }}
    </div>
@endif

@if($payments->count())
<br>
    <div class="border-box-number">
        <strong>Pagos:</strong><br>
        @php
            $payment = 0;
        @endphp
        @foreach($payments as $row)
            <span>- {{ $row->date_of_payment->format('d/m/Y') }} - {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment }}</span>
            @php
                $payment += (float) $row->payment;
            @endphp
        @endforeach
<br>
        <strong>Saldo:</strong> {{ $document->currency_type->symbol }} {{ number_format($document->total - $payment, 2) }}
    </div>

@endif

</body>
</html>
