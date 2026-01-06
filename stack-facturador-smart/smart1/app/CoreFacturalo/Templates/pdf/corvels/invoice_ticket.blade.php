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
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::all();
    $document_base = ($document->note) ? $document->note : null;
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
    if($document_base) {
        $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    } else {
        $affected_document_number = null;
    }
    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');
//dd(url());
@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
&nbsp;
@if($company->logo)
    <div class="text-center company_logo_box">
        <img src="{{public_path("storage/uploads/logos/{$company->logo}")}}" alt="{{$company->name}}" class="company_logo_ticket contain">
    </div>
{{--@else--}}
    {{--<div class="text-center company_logo_box">--}}
        {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo_ticket contain">--}}
    {{--</div>--}}
@endif

@if($document->state_type->id == '11'  || $document->state_type->id == '09')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:500px">
        <img src="data:{{public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
@endif
<table class="full-width">
    <tr>.
        <td class="text-center"><h4 style="font-size: 15px">{{ $company->name }}</h4></td>
    </tr>
    {{-- <tr>
        <td class="text-center"><h5 style="font-size: 20px">{{ $company->trade_name }}</h5></td>
    </tr> --}}
    <tr>
        <td class="text-center"><h5>{{ 'RUC '.$company->number }}</h5></td>
    </tr>
    <tr>
        <td class="text-center">
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
        <td class="text-center ">{{ ($establishment->telephone !== '-')? ''.$establishment->telephone : '' }}  |  {{ ($establishment->email !== '-')? ' '.$establishment->email : '' }}</td>
    </tr>
    <!--tr>
        <td class="text-center"></td>
    </tr-->
    @isset($establishment->web_address)
        <tr>
            <td class="text-center">{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</td>
        </tr>
    @endisset

    

    <tr>
        <td class="text-center pt-1 border-top"><h4>{{ $document->document_type->description }}</h4></td>
    </tr>
    <tr>
        <td class="text-center pb-1 border-bottom"><h3>{{ $document_number }}</h3></td>
    </tr>
</table>
<table class="full-width">
    <tr >
        <td width="" class="pt-1"><p class="desc">F. Emisión:</p></td>
        <td width="" class="pt-1"><p class="desc">{{ $document->date_of_issue->format('Y-m-d') }}</p></td>
    </tr>
    <tr>
        <td width="" ><p class="desc">H. Emisión:</p></td>
        <td width="" ><p class="desc">{{ $document->time_of_issue }}</p></td>
    </tr>
    @isset($invoice->date_of_due)
    <tr>
        <td><p class="desc">F. Vencimiento:</p></td>
        <td><p class="desc">{{ $invoice->date_of_due->format('Y-m-d') }}</p></td>
    </tr>
    @endisset

    <tr>
        <td class="align-top"><p class="desc">Cliente:</p></td>
        <td><p class="desc">{{ $customer->name }}</p></td>
    </tr>
    <tr>
        <td><p class="desc">{{ $customer->identity_document_type->description }}:</p></td>
        <td><p class="desc">{{ $customer->number }}</p></td>
    </tr>
    @if ($customer->address !== '')
        <tr>
            <td class="align-top"><p class="desc">Dirección:</p></td>
            <td>
                <p class="desc">
                    {{ $customer->address }}
                    {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                    {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                    {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
                </p>
            </td>
        </tr>
    @endif

    @if ($document->detraction)
    {{--<strong>Operación sujeta a detracción</strong>--}}
        <tr>
            <td  class="align-top"><p class="desc">N. Cta detracciones:</p></td>
            <td><p class="desc">{{ $document->detraction->bank_account}}</p></td>
        </tr>
        <tr>
            <td  class="align-top"><p class="desc">B/S Sujeto a detracción:</p></td>
            @inject('detractionType', 'GV9Jm2u7rmsCe65wKzPTw5jtS38n2tVEGi')
            <td><p class="desc">{{$document->detraction->detraction_type_id}} - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id ) }}</p></td>
        </tr>
        <tr>
            <td  class="align-top"><p class="desc">Método de Pago:</p></td>
            <td><p class="desc">{{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id ) }}</p></td>
        </tr>
        <tr>
            <td  class="align-top"><p class="desc">Porcentaje detracción:</p></td>
            <td><p class="desc">{{ $document->detraction->percentage}}%</p></td>
        </tr>
        <tr>
            <td  class="align-top"><p class="desc">Monto detracción:</p></td>
            <td><p class="desc">{{ $document->currency_type->symbol }} {{ $document->detraction->amount}}</p></td>
        </tr>
        @if($document->detraction->pay_constancy)
        <tr>
            <td  class="align-top"><p class="desc">Constancia de Pago:</p></td>
            <td><p class="desc">{{ $document->detraction->pay_constancy}}</p></td>
        </tr>
        @endif
    @endif

    @if ($document->prepayments)
        @foreach($document->prepayments as $p)
        <tr>
            <td><p class="desc">Anticipo :</p></td>
            <td><p class="desc">{{$p->number}}</p></td>
        </tr>
        @endforeach
    @endif
    @if ($document->purchase_order)
        <tr>
            <td><p class="desc">Orden de compra:</p></td>
            <td><p class="desc">{{ $document->purchase_order }}</p></td>
        </tr>
    @endif
    @if ($document->quotation_id)
        <tr>
            <td><p class="desc">Cotización:</p></td>
            <td><p class="desc">{{ $document->quotation->identifier }}</p></td>
        </tr>
    @endif
    @isset($document->quotation->delivery_date)
        <tr>
            <td><p class="desc">F. Entrega</p></td>
            <td><p class="desc">{{ $document->quotation->delivery_date->format('Y-m-d')}}</p></td>
        </tr>
    @endisset
    @isset($document->quotation->sale_opportunity)
        <tr>
            <td><p class="desc">O. Venta</p></td>
            <td><p class="desc">{{ $document->quotation->sale_opportunity->number_full}}</p></td>
        </tr>
    @endisset
</table>

@if ($document->guides)
{{--<strong>Guías:</strong>--}}
<table>
    @foreach($document->guides as $guide)
        <tr>
            @if(isset($guide->document_type_description))
                <td class="desc">{{ $guide->document_type_description }}</td>
            @else
                <td class="desc">{{ $guide->document_type_id }}</td>
            @endif
            <td class="desc">:</td>
            <td class="desc">{{ $guide->number }}</td>
        </tr>
    @endforeach
</table>
@endif

@if (count($document->reference_guides_valid) > 0)
<br/>
<strong>Guias de remisión</strong>
<table>
    @foreach($document->reference_guides_valid as $guide)
        <tr>
            <td>{{ $guide->series }}</td>
            <td>-</td>
            <td>{{ $guide->number }}</td>
        </tr>
    @endforeach
</table>
@endif

@if(!is_null($document_base))
<table>
    <tr>
        <td class="desc">Documento Afectado:</td>
        <td class="desc">{{ $affected_document_number }}</td>
    </tr>
    <tr>
        <td class="desc">Tipo de nota:</td>
        <td class="desc">{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>
    </tr>
    <tr>
        <td class="align-top desc">Descripción:</td>
        <td class="text-left desc">{{ $document_base->note_description }}</td>
    </tr>
</table>
@endif

<table class="full-width mt-10 mb-10">
    <thead class="">
    <tr>
        <th class="border-top-bottom desc-9 text-left">Cant.</th>
        <th class="border-top-bottom desc-9 text-left">Unidad</th>
        <th class="border-top-bottom desc-9 text-left">Descripción</th>
        <th class="border-top-bottom desc-9 text-left">P.Unit</th>
        <th class="border-top-bottom desc-9 text-left">Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        <tr>
            <td class="text-center desc-9 align-top">
                @if(((int)$row->quantity != $row->quantity))
                    <b>{{ $row->quantity }}</b>
                @else
                   <b>{{ number_format($row->quantity, 0) }}</b>
                @endif
            </td>
            <td class="text-center desc-9 align-top"><b>{{ symbol_or_code($row->item->unit_type_id) }}</b></td>
            <td class="text-left desc-9 align-top">
                @if($row->name_product_pdf)
                    <b>{!!$row->name_product_pdf!!}</b>
                @else
                    <b>{!!$row->item->description!!}</b>
                @endif

                @if (!empty($row->item->presentation)) <b>{!!$row->item->presentation->description!!}</b> @endif

                @foreach($row->additional_information as $information)
                    @if ($information)
                        <br/><b>{{ $information }}</b>
                    @endif
                @endforeach

                @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/><b>{!! $attr->description !!} : {{ $attr->value }}</b>
                    @endforeach
                @endif
                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><small><b>{{ $dtos->factor * 100 }}% {{$dtos->description }}</b></small>
                    @endforeach
                @endif

                @if($row->item->is_set == 1)

                 <br>
                 @inject('itemSet', 'App\Services\ItemSetService')

                    <b>{{join( "-", $itemSet->getItemsSet($row->item_id) )}}</b>

                @endif

            </td>
            <td class="text-right desc-9 align-top"><b>{{ number_format($row->unit_price, 2) }}</b></td>
            <td class="text-right desc-9 align-top"><b>{{ number_format($row->total, 2) }}</b></td>
        </tr>
        <tr>
            <td colspan="5" class="border-bottom"></td>
        </tr>
    @endforeach

    @if ($document->prepayments)
        @foreach($document->prepayments as $p)
        <tr>
            <td class="text-center desc-9 align-top">
                1
            </td>
            <td class="text-center desc-9 align-top">NIU</td>
            <td class="text-left desc-9 align-top">
                Anticipo: {{($p->document_type_id == '02')? 'Factura':'Boleta'}} Nro. {{$p->number}}
            </td>
            <td class="text-right  desc-9 align-top">-{{ number_format($p->total, 2) }}</td>
            <td class="text-right  desc-9 align-top">-{{ number_format($p->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="5" class="border-bottom"></td>
        </tr>
        @endforeach
    @endif

        @if($document->total_exportation > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">Op. Exportación: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_exportation, 2) }}</td>
            </tr>
        @endif
        @if($document->total_free > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">Op. Gratuitas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_free, 2) }}</td>
            </tr>
        @endif
        @if($document->total_unaffected > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">Op. Inafectas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_unaffected, 2) }}</td>
            </tr>
        @endif
        @if($document->total_exonerated > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">Op. Exoneradas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_exonerated, 2) }}</td>
            </tr>
        @endif
        @if($document->total_taxed > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">Op. Gravadas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif
        @if($document->total_discount > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">Descuento TOTAL: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        @endif
        @if($document->total_plastic_bag_taxes > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">Icbper: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="4" class="text-right font-bold desc">IGV: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc">{{ number_format($document->total_igv, 2) }}</td>
        </tr>
        <tr>
            <td colspan="4" class="text-right font-bold desc">Total a pagar: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc">{{ number_format($document->total, 2) }}</td>
        </tr>
        @if($balance < 0)
           <tr>
               <td colspan="4" class="text-right font-bold desc">Vuelto: {{ $document->currency_type->symbol }}</td>
               <td class="text-right font-bold desc">{{ number_format(abs($balance),2, ".", "") }}</td>
           </tr>
        @endif
    </tbody>
</table>
<table class="full-width">
    <tr>
        @foreach(array_reverse((array) $document->legends) as $row)
            <tr>
                @if ($row->code == "1000")
                    <td class="desc pt-1" colspan="2">Son: <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></td>
                    @if (count((array) $document->legends)>1)
                    {{-- <tr><td class="desc pt-1" colspan="2"><span class="font-bold">Leyendas</span></td></tr> --}}
                    @endif
                @else
                    {{-- <td class="desc pt-1" colspan="2">{{$row->code}}: {{ $row->value }}</td> --}}
                @endif
            </tr>
        @endforeach
    </tr>


    @if ($document->detraction)
        <tr>
            <td class="desc pt-1 font-bold">
                Operación sujeta al Sistema de Pago de Obligaciones Tributarias
            </td>
        </tr>
    @endif

    <tr>
        <td class="desc  text-center" colspan="2">
            @foreach($document->additional_information as $information)
                @if ($information)
                    @if ($loop->first)
                        <strong>Información adicional</strong>
                    @endif
                    <p>{{ $information }}</p>
                @endif
            @endforeach
            <br>
            @if(in_array($document->document_type->id,['01','03']))
                @foreach($accounts as $account)
                    <p>
                    <span class="font-bold">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                    <span class="font-bold">N°:</span> {{$account->number}}
                    @if($account->cci)
                    <span class="font-bold">CCI:</span> {{$account->cci}}
                    @endif
                    </p>
                @endforeach
            @endif

        </td>
    </tr>
    <tr>
        <td class="text-center pt-1"><img class="qr_code" src="data:image/png;base64, {{ $document->qr }}" /></td>
        <td class="text-center pt-1">Código Hash: {{ $document->hash }}
            @if ($customer->department_id == 16)

                <br/>Representación impresa del Comprobante de Pago Electrónico.
                <br/>Esta puede ser consultada en:
                <br/> <a href="{!! route('search.index', ['external_id' => $document->external_id]) !!}" style="text-decoration: none; font-weight: bold;color:black;">{!! searchUrl() !!}</a>
                <br/> "Bienes transferidos en la Amazonía
                <br/>para ser consumidos en la misma
<br>
            @foreach($document->additional_information as $information)
                @if ($information)
			<span>{{ $information }}</span>
                @endif
            @endforeach

    @endif

        </td>
    </tr>
    <!-- <tr>
        <td class="text-center desc">Código Hash: {{ $document->hash }}</td>
    </tr> -->

    <!-- @if ($customer->department_id == 16)
        <tr>
            <td class="text-center desc pt-1">
                Representación impresa del Comprobante de Pago Electrónico.
                <br/>Esta puede ser consultada en:
                <br/> <a href="{!! route('search.index', ['external_id' => $document->external_id]) !!}" style="text-decoration: none; font-weight: bold;color:black;">{!! searchUrl() !!}</a>
                <br/> "Bienes transferidos en la Amazonía
                <br/>para ser consumidos en la misma
            </td>
        </tr>
    @endif -->

    @if($payments->count())
        <tr>
            <td class="desc pt-1 text-center">
                <strong>Pagos:</strong><br>
                @foreach($payments as $row)
            &#8226; {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}
            <br>
        @endforeach
            </td>
        <td class="desc pt-1 text-center">
        <strong>Vendedor:</strong><br>
        @if ($document->seller)
            {{ $document->seller->name }}
        @else
            {{ $document->user->name }}
        @endif
</td>

        </tr>
        <!-- @foreach($payments as $row)
            <tr>
                <td class="desc">&#8226; {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
            </tr>
        @endforeach -->
    @endif

  <!--   <tr>
        <td class="desc pt-1">
        <strong>Vendedor:</strong> </td>
    </tr>
                <tr>
                    <td class="desc">{{ $document->user->name }}</td>
                </tr> -->

        </tr>

    <tr>
        <td class="text-center desc pt-1" colspan="2">Representación impresa del Comprobante de Pago Electrónico. <br/>Esta puede ser consultada en <a href="{!! route('search.index', ['external_id' => $document->external_id]) !!}" style="text-decoration: none; font-weight: bold;color:black;">{!! searchUrl() !!}</a></td>
    </tr>
</table>

</body>
</html>
