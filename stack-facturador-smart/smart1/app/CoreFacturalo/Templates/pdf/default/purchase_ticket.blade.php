@php
    $establishment = $document->establishment;
    $supplier = $document->supplier;
    $payments = $document->payments;
    $tittle = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
    
    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
    $items = $document->items;
    $same_warehouse_id = $items->pluck('warehouse_id')->unique()->count() == 1;
    $warehouse_id_destination = null;
    $warehouse_destination = null;
    if($same_warehouse_id){
        $warehouse_id_destination = $items->first()->warehouse_id;
        $warehouse_destination = \App\Models\Tenant\Warehouse::where('id', $warehouse_id_destination)->first();
    }
@endphp
<html>
<head>
    {{--<title>{{ $tittle }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body class="ticket">

@if($company->logo)
    <div class="text-center company_logo_box pt-5">
        <img src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" class="company_logo_ticket contain">
    </div>
@endif

@if($document->state_type->id == '11')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:500px">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
@endif

<table class="full-width">
    <tr>
        <td class="text-center"><h4>{{ $company->name }}</h4></td>
    </tr>
    <tr>
        <td class="text-center"><h5>{{ 'RUC '.$company->number }}</h5></td>
    </tr>
    <tr>
        <td class="text-center" style="text-transform: uppercase;">
            {{ ($establishment->address !== '-')? $establishment->address : '' }}
            {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
            {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
            {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
        </td>
    </tr>

    @isset($establishment->trade_address)
    <tr>
        <td class="text-center ">{{  ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : ''  }}</td>
    </tr>
    @endisset
    <tr>
        <td class="text-center ">{{ ($establishment->telephone !== '-')? 'Central telefónica: '.$establishment->telephone : '' }}</td>
    </tr>
    <tr>
        <td class="text-center">{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</td>
    </tr>
    @isset($establishment->web_address)
        <tr>
            <td class="text-center">{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</td>
        </tr>
    @endisset

    @isset($establishment->aditional_information)
        <tr>
            <td class="text-center pb-3">{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</td>
        </tr>
    @endisset

    <tr>
        <td class="text-center pt-3 border-top"><h4>{{ $document->document_type->description }}</h4></td>
    </tr>
    <tr>
        <td class="text-center pb-3 border-bottom"><h3>{{ $tittle }}</h3></td>
    </tr>
</table>

<table class="full-width">
    <tr>
        <td width="" class="pt-3"><p class="desc">F. Emisión:</p></td>
        <td width="" class="pt-3"><p class="desc">{{ $document->date_of_issue->format('Y-m-d') }}</p></td>
    </tr>
    @if($document->date_of_due)
    <tr>
        <td><p class="desc">F. Vencimiento:</p></td>
        <td><p class="desc">{{ $document->date_of_due->format('Y-m-d') }}</p></td>
    </tr>
    @endif

    <tr>
        <td class="align-top"><p class="desc">Proveedor:</p></td>
        <td><p class="desc">{{ $supplier->name }}</p></td>
    </tr>
    <tr>
        <td><p class="desc">{{ $supplier->identity_document_type->description }}:</p></td>
        <td><p class="desc">{{ $supplier->number }}</p></td>
    </tr>
    @if ($supplier->address !== '')
        <tr>
            <td class="align-top"><p class="desc">Dirección:</p></td>
            <td>
                <p class="desc">
                    {{ $supplier->address }}
                    {{ ($supplier->district_id !== '-')? ', '.$supplier->district->description : '' }}
                    {{ ($supplier->province_id !== '-')? ', '.$supplier->province->description : '' }}
                    {{ ($supplier->department_id !== '-')? '- '.$supplier->department->description : '' }}
                </p>
            </td>
        </tr>
    @endif
    @if ($supplier->telephone)
        <tr>
            <td class="align-top"><p class="desc">Teléfono:</p></td>
            <td><p class="desc">{{ $supplier->telephone }}</p></td>
        </tr>
    @endif
    <tr>
        <td class="align-top"><p class="desc">Usuario:</p></td>
        <td><p class="desc">{{ $document->user->name }}</p></td>
    </tr>
    @if($warehouse_destination)
    <tr>
        <td class="align-top"><p class="desc">Almacén:</p></td>
        <td><p class="desc">{{ $warehouse_destination->description }}</p></td>
    </tr>
    @endif
    @if($document->purchase_order)
    <tr>
        <td class="align-top"><p class="desc">O. Compra:</p></td>
        <td><p class="desc">{{ $document->purchase_order->number_full }}</p></td>
    </tr>
    @endif
    @if ($document->observation)
    <tr>
        <td class="align-top"><p class="desc">Observación:</p></td>
        <td><p class="desc">{{ $document->observation }}</p></td>
    </tr>
    @endif
</table>

<table class="full-width mt-10 mb-10">
    <thead class="">
    <tr>
        <th class="border-top-bottom desc-9 text-left">CANT.</th>
        <th class="border-top-bottom desc-9 text-left">UNIDAD</th>
        <th class="border-top-bottom desc-9 text-left">COD</th>
        <th class="border-top-bottom desc-9 text-left">DESCP.</th>
        <th class="border-top-bottom desc-9 text-left">P.UNIT</th>
        <th class="border-top-bottom desc-9 text-left">TOTAL</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        <tr>
            <td class="text-center desc-9 align-top font-bold">
                @if(isset($row->item->quantity_neta))
                    @if(!is_null($row->item->quantity_neta) && $row->item->quantity_neta > 0)
                        {{ $row->item->quantity_neta }}
                    @else
                        {{ $row->quantity }}
                    @endif
                @else
                    @if(((int)$row->quantity != $row->quantity))
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                @endif
            </td>
            <td class="text-center desc-9 align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
            <td class="text-center desc-9 align-top">{{ isset($row->item->internal_id) ? $row->item->internal_id : '' }}</td>
            <td class="text-left desc-9 align-top font-bold">
                {!!$row->item->description!!} 
                @if (!empty($row->item->presentation) && !str_contains($row->item->description, $row->item->presentation->description))
                    {!! $row->item->presentation->description !!}
                @endif

                @if($row->total_isc > 0)
                    <br/>ISC : {{ $row->total_isc }} ({{ $row->percentage_isc }}%)
                @endif
                
            
        

                @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/>{!! $attr->description !!} : {{ $attr->value }}
                    @endforeach
                @endif
                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><small>{{ $dtos->factor * 100 }}% {{$dtos->description }}</small>
                    @endforeach
                @endif
            </td>
            <td class="text-right desc-9 align-top">{{ number_format($row->unit_price, 2) }}</td>
        
            <td class="text-right desc-9 align-top font-bold">{{ number_format($row->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="6" class="border-bottom"></td>
        </tr>
    @endforeach

        @if($document->total_exportation > 0)
            <tr>
                <td colspan="5" class="text-right font-bold desc">OP. EXPORTACIÓN: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_exportation, 2) }}</td>
            </tr>
        @endif
        @if($document->total_free > 0)
            <tr>
                <td colspan="5" class="text-right font-bold desc">OP. GRATUITAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_free, 2) }}</td>
            </tr>
        @endif
        @if($document->total_unaffected > 0)
            <tr>
                <td colspan="5" class="text-right font-bold desc">OP. INAFECTAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_unaffected, 2) }}</td>
            </tr>
        @endif
        @if($document->total_exonerated > 0)
            <tr>
                <td colspan="5" class="text-right font-bold desc">OP. EXONERADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_exonerated, 2) }}</td>
            </tr>
        @endif
        @if($document->total_taxed > 0)
            <tr>
                <td colspan="5" class="text-right font-bold desc">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif
        @if($document->total_discount > 0)
            <tr>
                <td colspan="5" class="text-right font-bold desc">{{(($document->total_prepayment > 0) ? 'ANTICIPO':'DESCUENTO TOTAL')}}: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="5" class="text-right font-bold desc">IGV: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc">{{ number_format($document->total_igv, 2) }}</td>
        </tr>

        @if($document->total_isc > 0)
        <tr>
            <td colspan="5" class="text-right font-bold desc">ISC: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc">{{ number_format($document->total_isc, 2) }}</td>
        </tr>
        @endif

        <tr>
            <td colspan="5" class="text-right font-bold desc">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc">{{ number_format($document->total, 2) }}</td>
        </tr>
        @if($document->total_sacos > 0)
            <tr>
                <td colspan="5" class="text-right font-bold desc">TOTAL SACOS : </td>
                <td class="text-right font-bold desc">{{ number_format($document->total_sacos, 2) }}</td>
            </tr>
        @endif
    </tbody>
</table>

@if($document->payment_condition_id && ($payments->count() || $document->fee->count()))
<table class="full-width">
    <tr>
        <td>
            <strong>CONDICIÓN DE PAGO: {{ $document->payment_condition->name }} </strong>
        </td>
    </tr>
</table>
@endif

@if($payments->count())
    <table class="full-width">
        <tr>
            <td>
                <strong>PAGOS:</strong>
            </td>
        </tr>
            @php
                $payment = 0;
            @endphp
            @foreach($payments as $row)
                <tr>
                    <td>&#8226; {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
                </tr>
                @php
                    $payment += (float) $row->payment;
                @endphp
            @endforeach
            <tr><td><strong>SALDO:</strong> {{ $document->currency_type->symbol }} {{ number_format($document->total - $payment, 2) }}</td>
        </tr>

    </table>
@endif

@if($document->total_sale_note > 0)
    <strong>SALDO TOTAL VENTAS:</strong> {{ $document->total_sale_note }}
@endif

@if($document->fee->count())
<table class="full-width">
        @foreach($document->fee as $key => $quote)
            <tr>
                <td>&#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }} / Fecha: {{ $quote->date->format('d-m-Y') }} / Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
            </tr>
        @endforeach
    </tr>
</table>
@endif



</body>
</html> 