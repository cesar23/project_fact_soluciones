@php
use App\CoreFacturalo\Helpers\Template\TemplateHelper;
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
$accounts = \App\Models\Tenant\BankAccount::all();

if($document_base) {

$affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

} else {

$affected_document_number = null;
}

$configurations = \App\Models\Tenant\Configuration::first();

$payments = $document->payments;

$company_name = $company->name;
$company_owner = null;
if ($configurations->trade_name_pdf) {
    $company_name = $company->trade_name;
    $company_owner = $company->name;
}

$document->load('reference_guides');

$total_payment = $document->payments->sum('payment');
$balance = ($document->total - $total_payment) - $document->payments->sum('change');

// Condicion de pago
$condition = TemplateHelper::getDocumentPaymentCondition($document);
// Pago/Coutas detalladas
$paymentDetailed = TemplateHelper::getDetailedPayment($document)

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
    @if($document->soap_type_id == '01')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."demo.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."demo.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
    @endif
    <table class="full-width">
        <tr>
            <td width="60%" class="text-center pr-2">
                @if($company->logo)
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 100px;">
                </div>
                @endif
                <br>
                <h5 class="font-bold text-upp">{{ $company->name }}</h5>
                <hr>
                <h6 style="text-transform: uppercase;">
                    {{ ($establishment->address !== '-')? $establishment->address.',' : '' }}
                    {{ ($establishment->district_id !== '-')? $establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </h6>

                @isset($establishment->trade_address)
                <h6>{{ ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : '' }}</h6>
                @endisset

                <h6>{{ ($establishment->telephone !== '-')? 'Telf. '.$establishment->telephone : '' }} {{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</h6>


                @isset($establishment->web_address)
                <h6>{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</h6>
                @endisset

                @isset($establishment->aditional_information)
                <h6>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</h6>
                @endisset
            </td>
            <td width="40%" class="border-box py-2 px-2 text-center">
                <h3 class="font-bold">{{ 'RUC '.$company->number }}</h3>
                <h3 class="text-center font-bold">{{ $document->document_type->description }}</h3>
                <br>
                <h3 class="text-center">{{ $document_number }}</h3>
            </td>
        </tr>
    </table>
    <table class="full-width mt-2">
        <tr>
            <td width="95%" class="border-box pl-3">
                <table class="full-width">
                    <tr>
                        <td colspan="2" class="font-xlg">
                            <strong>Señor(es):</strong>
                            {{ $customer->name }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="font-xlg">
                            <strong>Dirección: </strong>
                            @if ($customer->address !== '')
                            <span style="text-transform: uppercase;">
                                {{ $customer->address }}
                                {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                                {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                                {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
                            </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="font-xlg">
                            <strong>RUC: </strong>
                            {{$customer->number}}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="" class="font-xlg">
                            <strong>Moneda:</strong>
                            <span class="text-upp">{{ $document->currency_type->description }}</span>
                        </td>
                        <td colspan="" class="font-xlg">
                            <strong>Condición de Pago: </strong>
                            <span class="text-upp">{{ $condition }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-xlg">
                            <strong>Fecha:</strong>
                            {{$document->date_of_issue->format('Y-m-d')}}
                        </td>
                        <td class="font-xlg">
                            @if($invoice)
                            <strong>Fecha venc.:</strong>
                            {{$invoice->date_of_due->format('Y-m-d')}}
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
            <td width="5%" class="p-0 m-0">
                <img src="data:image/png;base64, {{ $document->qr }}" class="p-0 m-0" style="width: 120px;" />
            </td>
        </tr>
    </table>
    <table class="full-width my-4 text-center" border="1">
        <tr>
            <td width="16.6%" class="desc">Ubigeo</td>
            <td width="16.6%" class="desc">O/C</td>
            <td width="16.6%" class="desc">Condiciones de pago</td>
            <td width="16.6%" class="desc">Vendedor</td>
            <td width="16.6%" class="desc">Guia de remisión</td>
            <td width="16.6%" class="desc">Agencia de transporte</td>
        </tr>
        <tr>
            <td class="desc"></td>
            <td class="desc">{{$document->purchase_order}}</td>
            <td class="desc">
                @php
                $payment = 0;
                @endphp
                @foreach($payments as $row)
                {{ $row->payment_method_type->description }}
                @endforeach
            </td>
            <td class="desc">{{ $document->user->name }}</td>
            <td class="desc">
                @if ($document->guides)
                @foreach($document->guides as $guide)
                {{ $guide->number }}
                @endforeach
                @endif

                @if ($document->reference_guides)
                @foreach($document->reference_guides as $guide)
                {{ $guide->number }}
                @endforeach
                @endif
            </td>
            <td class="desc"></td>
        </tr>
    </table>
    <div style="border: 1px solid #000;height: 48%;padding-left: -1px;width:95.1%;position: absolute;display: table;">
    </div>
    <div style="border-right: 1px solid #000;height: 48.1%;padding-left: -1px;width:11.4%;position: absolute;display: table;">
    </div>
    <div style="border-right: 1px solid #000;height: 48.1%;padding-left: -1px;width:49.5%;position: absolute;display: table;">
    </div>
    <div style="border-right: 1px solid #000;height: 48.1%;padding-left: -1px;width:57.2%;position: absolute;display: table;">
    </div>
    <div style="border-right: 1px solid #000;height: 48.1%;padding-left: -1px;width:64.8%;position: absolute;display: table;">
    </div>
    <div style="border-right: 1px solid #000;height: 48.1%;padding-left: -1px;width:76.3%;position: absolute;display: table;">
    </div>
    <div style="border-right: 1px solid #000;height: 48.1%;padding-left: -1px;width:83.7%;position: absolute;display: table;">
    </div>


    <table class="full-width mt-0 mb-0">
        <thead>
            <tr class="">
                <th class="border-top-bottom text-center py-1 desc" width="12%">Código</th>
                <th class="border-top-bottom text-center py-1 desc" width="40%">Descripción</th>
                <th class="border-top-bottom text-center py-1 desc" width="8%">Cant.</th>
                <th class="border-top-bottom text-center py-1 desc" width="8%">U.M.</th>
                <th class="border-top-bottom text-right py-1 desc" width="12%">P.U</th>
                <th class="border-top-bottom text-center py-1 desc" width="8%">Desc</th>
                <th class="border-top-bottom text-center py-1 desc" width="12%">Importe</th>
            </tr>
        </thead>
        <tbody class="">
            @foreach($document->items as $row)
            <tr>
                <td class="p-1 text-center align-top desc">{{ $row->item->internal_id }}</div>
                </td>
                <td class="p-1 text-left align-top desc text-upp">
                    @if($row->name_product_pdf)
                    {!!$row->name_product_pdf!!}
                    @else
                    {!!$row->item->description!!}
                    @endif

                      

                    @if($row->attributes)
                    @foreach($row->attributes as $attr)
                    <br /><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
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
                <td class="p-1 text-center align-top desc">
                    @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                    @else
                    {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                <td class="p-1 text-center align-top desc">{{ symbol_or_code($row->item->unit_type_id )}}</td>
                <td class="p-1 text-right align-top desc">{{ number_format($row->unit_price, 2) }}</td>
                <td class="p-1 text-right align-top desc">
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
                <td class="p-1 text-right align-top desc">{{ number_format($row->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>


</body>

</html>
{{-- <table class="full-width">
    <tr>
        <td width="65%" style="text-align: top; vertical-align: top;">

            <br/>
            @if ($document->detraction)
            <p>
                <span class="font-bold">
                Operación sujeta al Sistema de Pago de Obligaciones Tributarias
                </span>
            </p>
            <br/>
            @endif
            @if ($customer->department_id == 16)
                <br/><br/><br/>
                <div>
                    <center>
                        Representación impresa del Comprobante de Pago Electrónico.
                        <br/>Esta puede ser consultada en:
                        <br/><a href="{!! route('search.index', ['external_id' => $document->external_id]) !!}" style="text-decoration: none; font-weight: bold;color:black;">{!! searchUrl() !!}</a>
                        <br/> "Bienes transferidos en la Amazonía
                        <br/>para ser consumidos en la misma".
                    </center>
                </div>
                <br/>
            @endif

            <br>

        </td>
        <td width="35%" class="text-right">

            <p style="font-size: 9px">Código Hash: {{ $document->hash }}</p>
</td>
</tr>
</table> --}}

{{-- @if($payments->count())


    <table class="full-width">
        <tr>
            <td>
                <strong>Pagos:</strong>
            </td>
        </tr>
            @php
                $payment = 0;
            @endphp
            @foreach($payments as $row)
                <tr>
                    <td>&#8226; {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
</tr>
@endforeach
</tr>

</table>
@endif --}}
{{-- <table>
    @if ($document->prepayments)
        @foreach($document->prepayments as $p)
        <tr>
            <td class="text-center align-top">
                1
            </td>
            <td class="text-center align-top">NIU</td>
            <td class="text-left align-top">
                Anticipo: {{($p->document_type_id == '02')? 'Factura':'Boleta'}} Nro. {{$p->number}}
</td>
<td class="text-center align-top"></td>
<td class="text-center align-top"></td>
<td class="text-right align-top">-{{ number_format($p->total, 2) }}</td>
<td class="text-right align-top">
    0
</td>
<td class="text-right align-top">-{{ number_format($p->total, 2) }}</td>
</tr>
<tr>
    <td colspan="8" class="border-bottom"></td>
</tr>
@endforeach
@endif

@if($document->total_exportation > 0)
<tr>
    <td colspan="7" class="text-right font-bold">Op. Exportación: {{ $document->currency_type->symbol }}</td>
    <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
</tr>
@endif
@if($document->total_free > 0)
<tr>
    <td colspan="7" class="text-right font-bold">Op. Gratuitas: {{ $document->currency_type->symbol }}</td>
    <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
</tr>
@endif
@if($document->total_unaffected > 0)
<tr>
    <td colspan="7" class="text-right font-bold">Op. Inafectas: {{ $document->currency_type->symbol }}</td>
    <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
</tr>
@endif
@if($document->total_exonerated > 0)
<tr>
    <td colspan="7" class="text-right font-bold">Op. Exoneradas: {{ $document->currency_type->symbol }}</td>
    <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
</tr>
@endif
@if($document->total_taxed > 0)
<tr>
    <td colspan="7" class="text-right font-bold">Op. Gravadas: {{ $document->currency_type->symbol }}</td>
    <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
</tr>
@endif
@if($document->total_discount > 0)
<tr>
    <td colspan="7" class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'Anticipo':'Descuento TOTAL')}}: {{ $document->currency_type->symbol }}</td>
    <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
</tr>
@endif
@if($document->total_plastic_bag_taxes > 0)
<tr>
    <td colspan="7" class="text-right font-bold">Icbper: {{ $document->currency_type->symbol }}</td>
    <td class="text-right font-bold">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
</tr>
@endif
<tr>
    <td colspan="7" class="text-right font-bold">IGV: {{ $document->currency_type->symbol }}</td>
    <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
</tr>

@if($document->perception)
<tr>
    <td colspan="7" class="text-right font-bold"> Importe total: {{ $document->currency_type->symbol }}</td>
    <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
</tr>
<tr>
    <td colspan="7" class="text-right font-bold">Percepción: {{ $document->currency_type->symbol }}</td>
    <td class="text-right font-bold">{{ number_format($document->perception->amount, 2) }}</td>
</tr>
<tr>
    <td colspan="7" class="text-right font-bold">Total a pagar: {{ $document->currency_type->symbol }}</td>
    <td class="text-right font-bold">{{ number_format(($document->total + $document->perception->amount), 2) }}</td>
</tr>
@else
<tr>
    <td colspan="7" class="text-right font-bold">Total a pagar: {{ $document->currency_type->symbol }}</td>
    <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
</tr>
@endif

@if($balance < 0) <tr>
    <td colspan="7" class="text-right font-bold">Vuelto: {{ $document->currency_type->symbol }}</td>
    <td class="text-right font-bold">{{ number_format(abs($balance),2, ".", "") }}</td>
    </tr>

    @endif
    </table> --}}
    {{-- <table class="full-width mt-3">
    @if ($document->prepayments)
        @foreach($document->prepayments as $p)
        <tr>
            <td width="120px">Anticipo</td>
            <td width="8px">:</td>
            <td>{{$p->number}}</td>
    </tr>
    @endforeach
    @endif
    @if ($document->purchase_order)
    <tr>
        <td width="120px">Orden de compra</td>
        <td width="8px">:</td>
        <td>{{ $document->purchase_order }}</td>
    </tr>
    @endif
    @if ($document->quotation_id)
    <tr>
        <td width="120px">Cotización</td>
        <td width="8px">:</td>
        <td>{{ $document->quotation->identifier }}</td>

        @isset($document->quotation->delivery_date)
        <td width="120px">T. Entrega</td>
        <td width="8px">:</td>
        <td>{{ $document->quotation->delivery_date}}</td>
        @endisset
    </tr>

    @endif
    @isset($document->quotation->sale_opportunity)
    <tr>
        <td width="120px">O. Venta</td>
        <td width="8px">:</td>
        <td>{{ $document->quotation->sale_opportunity->number_full}}</td>
    </tr>
    @endisset
    @if(!is_null($document_base))
    <tr>
        <td width="120px">Doc. Afectado</td>
        <td width="8px">:</td>
        <td>{{ $affected_document_number }}</td>
    </tr>
    <tr>
        <td>Tipo de nota</td>
        <td>:</td>
        <td>{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>
    </tr>
    <tr>
        <td>Descripción</td>
        <td>:</td>
        <td>{{ $document_base->note_description }}</td>
    </tr>
    @endif
    </table> --}}
    {{--<table class="full-width mt-3">--}}
    {{--<tr>--}}
    {{--<td width="25%">Documento Afectado:</td>--}}
    {{--<td width="20%">{{ $document_base->affected_document->series }}-{{ $document_base->affected_document->number }}</td>--}}
    {{--<td width="15%">Tipo de nota:</td>--}}
    {{--<td width="40%">{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>--}}
    {{--</tr>--}}
    {{--<tr>--}}
    {{--<td class="align-top">Descripción:</td>--}}
    {{--<td class="text-left" colspan="3">{{ $document_base->note_description }}</td>--}}
    {{--</tr>--}}
    {{--</table>--}}

    {{-- <table class="full-width mt-5">
    <tr>
        <td width="120px">Fecha de emisión</td>
        <td width="8px">:</td>
        <td></td>

        @if ($document->detraction)

            <td width="120px">N. Cta detracciones</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->bank_account}}</td>
    @endif
    </tr>
    @if($invoice)
    <tr>
        <td>F. de vencimiento</td>
        <td width="8px">:</td>
        <td></td>
    </tr>
    @endif

    @if ($document->detraction)
    <td width="140px">B/S Sujeto a detracción</td>
    <td width="8px">:</td>
    @inject('detractionType', 'App\Services\DetractionTypeService')
    <td width="220px">{{$document->detraction->detraction_type_id}} - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id ) }}</td>

    @endif
    <tr>
        <td>Cliente:</td>
        <td>:</td>
        <td></td>

        @if ($document->detraction)
        <td width="120px">Método de pago</td>
        <td width="8px">:</td>
        <td width="220px">{{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id ) }}</td>
        @endif

    </tr>
    <tr>
        <td>{{ $customer->identity_document_type->description }}</td>
        <td>:</td>
        <td></td>

        @if ($document->detraction)

        <td width="120px">P. Detracción</td>
        <td width="8px">:</td>
        <td>{{ $document->detraction->percentage}}%</td>
        @endif
    </tr>

    @if ($document->detraction)
    @if($document->detraction->pay_constancy)
    <tr>
        <td colspan="3">
        </td>
        <td width="120px">Constancia de pago</td>
        <td width="8px">:</td>
        <td>{{ $document->detraction->pay_constancy}}</td>
    </tr>
    @endif
    @endif
    </table> --}}

    {{--<table class="full-width mt-3">--}}
    {{--@if ($document->purchase_order)--}}
    {{--<tr>--}}
    {{--<td width="25%">Orden de compra: </td>--}}
    {{--<td>:</td>--}}
    {{--<td class="text-left">{{ $document->purchase_order }}</td>--}}
    {{--</tr>--}}
    {{--@endif--}}
    {{--@if ($document->quotation_id)--}}
    {{--<tr>--}}
    {{--<td width="15%">Cotización:</td>--}}
    {{--<td class="text-left" width="85%">{{ $document->quotation->identifier }}</td>--}}
    {{--</tr>--}}
    {{--@endif--}}
    {{--</table>--}}