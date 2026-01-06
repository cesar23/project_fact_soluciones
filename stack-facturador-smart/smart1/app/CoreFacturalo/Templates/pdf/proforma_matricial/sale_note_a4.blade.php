@php
    $establishment = \App\Models\Tenant\Establishment::where('id', $document->establishment_id )->first();
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

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

    //calculate items
    $allowed_items = 20;
    $quantity_items = $document->items()->count();
    $cycle_items = $allowed_items - ($quantity_items * 3);
    $total_weight = 0;
    $count_=0;
@endphp
<html>
<head>
    {{--<title>{{ $tittle }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
<table class="full-width">
   
    <tr>
        <td width="270"></td>
        <td width="" class="text-center">
            {{-- <h5 class="text-center">NOTA DE VENTA</h5> --}}
            <h5 class="text-center">&nbsp;</h5>
            <h5 class="text-center">&nbsp;</h5>
            <h4 class="text-center font-md">{{ $tittle }}</h4>
        </td>
    </tr>
</table>
<table class="full-width mt-5">
    <tr>
        <td width="15%">&nbsp;</td>
        <td width="55%" class="font-xs">{{ $customer->name }}</td>
        <td width="15%">&nbsp;</td>
        <td width="15%" class="font-xs">{{ $document->date_of_issue->format('Y-m-d') }}</td>
    </tr>
   {{--  <tr>
        <td>{{ $customer->identity_document_type->description }}:</td>
        <td>{{ $customer->number }}</td>

        @if ($document->due_date)
            <td class="align-top">Fecha Vencimiento:</td>
            <td>{{ $document->getFormatDueDate() }}</td>
        @endif

    </tr> --}}
    @if ($customer->address !== '')
    <tr>
        <td class="align-top">&nbsp;</td>
        <td class="font-xs">
            {{ strtoupper($customer->address) }}
            {{ ($customer->district_id !== '-')? ', '.strtoupper($customer->district->description) : '' }}
            {{ ($customer->province_id !== '-')? ', '.strtoupper($customer->province->description) : '' }}
            {{ ($customer->department_id !== '-')? '- '.strtoupper($customer->department->description) : '' }}
        </td>
        <td class="align-top">&nbsp;</td>
        <td class="font-xs">
            @if($document->payment_method_type_id && $payments->count() == 0)
                {{ $document->payment_method_type->description }}
            @endif
        </td>
    </tr>
    @endif
{{--    <tr>
        <td>Teléfono:</td>
        <td>{{ $customer->telephone }}</td>
        <td>Vendedor:</td>
        <td> @if($document->seller_id != 0){{$document->seller->name }} @else {{ $document->user->name }} @endif</td>
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
    @if ($document->hotelRent)
    <tr>
        <td class="align-top">Destino:</td>
        <td colspan="3">{{$document->hotelRent->destiny}}</td>
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
    @endif --}}
</table>
<br>
<table class="full-width mt-10 mb-10">
    <tbody>
    @foreach($document->items as $row)
        <tr>
            <td class="text-center align-top">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-left">
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

                @if($row->item->is_set == 1)

                 <br>
                 @inject('itemSet', 'App\Services\ItemSetService')
                 @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                     {{$item}}<br>
                 @endforeach
                @endif

                @if($row->item->used_points_for_exchange ?? false)
                    <br>
                    <span style="font-size: 9px">*** Canjeado por {{$row->item->used_points_for_exchange}}  puntos ***</span>
                @endif

            </td>
            <td class="text-right align-top">{{ number_format($row->unit_price, 2) }}</td>
            <td class="text-right align-top">{{ number_format($row->total, 2) }}</td>
        </tr>
    @endforeach

        @for($i = 0; $i < $cycle_items; $i++)
            <tr>
                <td colspan="4" class="">&nbsp;</td>
            </tr>
        @endfor
        <tr>
            <td colspan="3" class="text-right font-bold"></td>
            <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
        </tr>


    </tbody>
</table>

{{-- <table class="full-width">
    <tr>
        <td width="65%" style="text-align: top; vertical-align: top;">
            <br>
            @foreach($accounts as $account)
                <p>
                <span class="font-bold">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                <span class="font-bold">N°:</span> {{$account->number}}
                @if($account->cci)
                - <span class="font-bold">CCI:</span> {{$account->cci}}
                @endif
                </p>
            @endforeach
        </td>
    </tr>
</table>
<br>

@if($document->payment_method_type_id && $payments->count() == 0)
    <table class="full-width">
        <tr>
            <td>
                <strong>Pago: </strong>{{ $document->payment_method_type->description }}
            </td>
        </tr>
    </table>
@endif --}}

{{-- @if($payments->count())

<table class="full-width">
<tr>
    <td>
    <strong>Pagos:</strong> </td></tr>
        @php
            $payment = 0;
        @endphp
        @foreach($payments as $row)
            <tr><td>- {{ $row->date_of_payment->format('d/m/Y') }} - {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td></tr>
            @php
                $payment += (float) $row->payment;
            @endphp
        @endforeach
        <tr><td><strong>Saldo:</strong> {{ $document->currency_type->symbol }} {{ number_format($document->total - $payment, 2) }}</td>
    </tr>

</table>
@endif --}}
{{-- @if ($document->terms_condition)
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
