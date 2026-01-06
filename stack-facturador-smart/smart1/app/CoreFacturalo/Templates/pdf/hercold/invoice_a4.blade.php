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

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();

    $template_data = \App\Models\Tenant\Establishment::where('code', '=', $establishment->code)->first();

@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body >

   

@if($document->state_type->id == '11'  || $document->state_type->id == '09')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
@endif
<table class="full-width">
    <tr>
        @if($company->logo)
            <td width="60%">
                <div class="">
                    <img src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" class="" style="width: 300px;">
                </div>
            </td>
        @else
            <td width="60%">
            </td>
        @endif
        <td width="40%" class="pl-3">
            <table class="full-width">
                <tr><td colspan="3" class="text-right font-xxxlg">{{ $company->name }}</td></tr>
                <tr><td colspan="3" class="text-right font-lg">{{ 'RUC '.$company->number }}</td></tr>
                <tr><td colspan="3" class="text-right font-lg">{{ ($establishment->address !== '-')? $establishment->address : '' }}
                {{--{{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}--}}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}</td></tr>
                <tr>


                    <td class="text-right font-lg" colspan="2">
                        <img src="{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'') }}wp.jpg" alt="Footer" class="" style="width: 20px;">
                    
                    <td class="text-right font-lg" style="background-color: #000; color: #fff">{{ ($establishment->telephone !== '-')? ' '.$establishment->telephone : '' }}</td>
                </tr>
                <tr>
                    <td class="text-right font-lg"></td>
                    <td class="text-right font-lg">
                        <img src="{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'') }}mail.jpg" alt="Footer" class="" style="width: 20px;">
                    </td>
                    <td class="text-right font-lg">{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right font-lg font-bold">
                        @isset($establishment->web_address)
                            {{ ($establishment->web_address !== '-')? ''.$establishment->web_address : '' }}
                        @endisset
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<hr>
<table class="full-width mt-2 align-top">
    <tr>
        <td width="50%">
            <div class="font-lg">CLIENTE:</div>
            <div class="font-bold font-lg">{{ $customer->name }}
                @if ($customer->internal_code ?? false) <small>{{ $customer->internal_code ?? '' }}</small>
                @endif
            </div>
            <div class="font-lg">{{ $customer->identity_document_type->description }}: {{$customer->number}} </div>
            <div class="font-lg">
                {{ $customer->address }}
                {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
            </div>
        </td>
        <td width="50%" class="text-right">
            <div class="font-bold fc font-xxxxlg">{{ $document->document_type->description }}</div>
            <div class="font-bold fc font-xxxxlg">{{ $document_number }}</div>
            <div class="font-lg">FECHA DE EMISIÓN: {{$document->date_of_issue->format('Y-m-d')}}</div>
            <div class="font-lg">FECHA VENCIMIENTO: @if($invoice) {{$invoice->date_of_due->format('Y-m-d')}} @endif</div>
            <div class="font-lg">CONDICIÓN DE PAGO: 
                @php
                    $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
                @endphp
                {{ $paymentCondition }}
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="font-bold font-lg">
            Guías de remisión
@if ($document->guides)
    @foreach($document->guides as $guide)
            @if(isset($guide->document_type_description))
                {{ $guide->document_type_description }}
            @else
                {{ $guide->document_type_id }}
            @endif
                {{ $guide->number }}
    @endforeach
@endif
@if ($document->reference_guides)
    @if (count($document->reference_guides) > 0)
        @foreach($document->reference_guides as $guide)
            {{ $guide->series }}-{{ $guide->number }}
        @endforeach
    @endif
@endif
        </td>
    </tr>
</table>

<table class="full-width mt-10 mb-10">
    <thead class="">
    <tr class="">
        <th class="text-center py-2 bcc" width="8%">CANT.</th>
        <th class="text-center py-2" width="8%">UNIDAD</th>
        <th class="text-center py-2">DESCRIPCIÓN</th>
        <th class="text-center py-2" width="8%">LOTE</th>
        <th class="text-center py-2" width="8%">SERIE</th>
        <th class="text-center py-2" width="12%">P.UNIT</th>
        <th class="text-center py-2" width="8%">DSCTO.</th>
        <th class="text-right py-2 bcc" width="12%">TOTAL</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        <tr>
            <td class="text-center align-top bcc">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
            <td class="text-left align-top">
                @if($row->name_product_pdf)
                    {!!$row->name_product_pdf!!}
                @else
                    {!!$row->item->description!!}
                @endif

                @if($row->total_isc > 0)
                    <br/><span style="font-size: 9px">ISC : {{ $row->total_isc }} ({{ $row->percentage_isc }}%)</span>
                @endif

                  

                @if($row->total_plastic_bag_taxes > 0)
                    <br/><span style="font-size: 9px">ICBPER : {{ $row->total_plastic_bag_taxes }}</span>
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

                @if($row->charges)
                    @foreach($row->charges as $charge)
                        <br/><span style="font-size: 9px">{{ $document->currency_type->symbol}} {{ $charge->amount}} ({{ $charge->factor * 100 }}%) {{$charge->description }}</span>
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
            <td class="text-center align-top">
                @inject('itemLotGroup', 'App\Services\ItemLotsGroupService')
                {{ $itemLotGroup->getLote($row->item->IdLoteSelected) }}

            </td>
            <td class="text-center align-top">

                @isset($row->item->lots)
                    @foreach($row->item->lots as $lot)
                        @if( isset($lot->has_sale) && $lot->has_sale)
                            <span style="font-size: 9px">{{ $lot->series }}</span><br>
                        @endif
                    @endforeach
                @endisset

            </td>

            @if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf)
                <td class="text-right align-top">{{ $row->generalApplyNumberFormat($row->unit_price, $configuration_decimal_quantity->decimal_quantity_unit_price_pdf) }}</td>
            @else
                <td class="text-right align-top">{{ number_format($row->unit_price, 2) }}</td>
            @endif
            
            <td class="text-right align-top">
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
            <td class="text-right align-top bcc">{{ number_format($row->total, 2) }}</td>
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

        @if ($document->document_type_id === '07')
            @if($document->total_taxed >= 0)
            <tr>
                <td colspan="7" class="text-right font-bold">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
            @endif
        @elseif($document->total_taxed > 0)
            <tr>
                <td colspan="7" class="text-right font-bold">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif

        @if($document->total_plastic_bag_taxes > 0)
            <tr>
                <td colspan="7" class="text-right font-bold">ICBPER: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="7" class="text-right font-bold">IGV: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
        </tr>

        @if($document->total_isc > 0)
        <tr>
            <td colspan="7" class="text-right font-bold">ISC: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_isc, 2) }}</td>
        </tr>
        @endif

        @if($document->total_discount > 0 && $document->subtotal > 0)
        <tr>
            <td colspan="7" class="text-right font-bold">SUBTOTAL: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->subtotal, 2) }}</td>
        </tr>
        @endif

        @if($document->total_discount > 0)
            <tr>
                <td colspan="7" class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'ANTICIPO':'DESCUENTO TOTAL')}}: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        @endif

        @if($document->total_charge > 0)
            @if($document->charges)
                @php
                    $total_factor = 0;
                    foreach($document->charges as $charge) {
                        $total_factor = ($total_factor + $charge->factor) * 100;
                    }
                @endphp
                <tr>
                    <td colspan="7" class="text-right font-bold">CARGOS ({{$total_factor}}%): {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_charge, 2) }}</td>
                </tr>
            @else
                <tr>
                    <td colspan="7" class="text-right font-bold">CARGOS: {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_charge, 2) }}</td>
                </tr>
            @endif
        @endif

        @if($document->perception)
            <tr>
                <td colspan="7" class="text-right font-bold"> IMPORTE TOTAL: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="7" class="text-right font-bold">PERCEPCIÓN: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->perception->amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="7" class="text-right font-bold">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format(($document->total + $document->perception->amount), 2) }}</td>
            </tr>
        @else
            <tr>
                <td colspan="7" class="text-right font-bold">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
            </tr>
        @endif

        @if($balance < 0)

            <tr>
                <td colspan="7" class="text-right font-bold">VUELTO: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format(abs($balance),2, ".", "") }}</td>
            </tr>

        @endif



    </tbody>
</table>
<table class="full-width">
    <tr>
        <td width="65%" style="text-align: top; vertical-align: top; text-left">
            @foreach(array_reverse( (array) $document->legends) as $row)
                @if ($row->code == "1000")
                    <p style="text-transform: capitalize;">Son: <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></p>
                    @if (count((array) $document->legends)>1)
                        <p><span class="font-bold">Leyendas</span></p>
                    @endif
                @else
                    <p> {{$row->code}}: {{ $row->value }} </p>
                @endif

            @endforeach
            <br/>
            @if ($document->detraction)
            <p>
                <span class="font-bold">
                Operación sujeta al Sistema de Pago de Obligaciones Tributarias
                </span>
            </p>
            <br/>
            @endif
            <table width="500px">
                <tr>
                    <td
            style="background: url('{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'table.png') }}'); background-repeat: no-repeat; padding-top: 5px; padding-left: 5px" class="font-lg">
                        @if(in_array($document->document_type->id,['01','03']))
                            @foreach($accounts as $account)
                                <div class="text-left">
                                <span class="font-bold">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                                <span class="font-bold">N°:</span> {{$account->number}}
                                @if($account->cci)
                                <span class="font-bold">CCI:</span> {{$account->cci}}
                                @endif
                                </div>
                            @endforeach
                        @endif
                        <br>
                        <br>
                        <br>
                    </td>
                </tr>
            </table>
        </td>
        <td width="35%" class="text-right">
            <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px;" />
            <p style="font-size: 9px">Código Hash: {{ $document->hash }}</p>
        </td>
    </tr>
</table>
@php
    $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
@endphp
{{-- Condicion de pago  Crédito / Contado --}}


@if($document->payment_method_type_id)
    <table class="full-width">
        <tr>
            <td>
                <strong>MÉTODO DE PAGO: </strong>{{ $document->payment_method_type->description }}
            </td>
        </tr>
    </table>
@endif

@if ($document->payment_condition_id === '01')
    @if($payments->count())
        <table class="full-width">
            <tr>
                <td><strong>PAGOS:</strong></td>
            </tr>
                @php $payment = 0; @endphp
                @foreach($payments as $row)
                    <tr>
                        <td>&#8226; {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
                    </tr>
                @endforeach
            </tr>
        </table>
    @endif
@else
    <table class="full-width">
            @foreach($document->fee as $key => $quote)
                <tr>
                    <td>&#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }} / Fecha: {{ $quote->date->format('d-m-Y') }} / Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
                </tr>
            @endforeach
        </tr>
    </table>
@endif

<br>
<table class="full-width">
    <tr>
        <td>
            <strong>Vendedor:</strong>
        </td>
    </tr>
    <tr>
        @if ($document->seller)
            <td>{{ $document->seller->name }}</td>
        @else
            <td>{{ $document->user->name }}</td>
        @endif
    </tr>
</table>

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
