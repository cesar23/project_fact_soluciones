@php
    use App\Models\Tenant\Document;
    /** @var Document $document*/
    $establishment = $document->establishment;
$configuration = \App\Models\Tenant\Configuration::first();
$configurations = \App\Models\Tenant\Configuration::first();
$company_name = $company->name;
$company_owner = null;
if ($configurations->trade_name_pdf) {
    $company_name = $company->trade_name;
    $company_owner = $company->name;
}
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
        $invoice = $document->invoice;
        $document_base = ($document->note) ? $document->note : null;
        $optional = $document->optional;

        //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
        $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
        $accounts = \App\Models\Tenant\BankAccount::all();

        if($document_base) {
            $affected_document_number = $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT);
        } else {
            $affected_document_number = null;
        }
        $bank_accounts = \App\Models\Tenant\BankAccount::all();
		$configurations = \App\Models\Tenant\Configuration::first();

$payments = $document->payments;

$company_name = $company->name;
$company_owner = null;
if ($configurations->trade_name_pdf) {
    $company_name = $company->trade_name;
    $company_owner = $company->name;
}

        $attribute = $document->getAttributes();
		$placa = isset($attribute['plate_number'])?$attribute['plate_number']:null;
@endphp
<html>
<head>
    {{-- <title>{{ $document_number }}</title> --}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
<table class="full-width">
    <tr>
        <td width="65%">
            @if($company->logo)
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}"
                         alt="{{$company->name}}"
                         class="company_logo">
                </div>
            @endif
        </td>
        <td width="35%"
            class="border-box p-box-info text-center">
            <h5>{{ 'RUC '.$company->number }}</h5>
            <h5 class="text-center">{{ $document->document_type->description }}</h5>
            <h4 class="text-center font-bold">{{ $document_number }}</h4>
        </td>
    </tr>
</table>
<table class="full-width">
    <tr>
        <td class="pl-3">
            <div class="text-left">
                <p class="font-bold text-upp">{{ $company->name }}</p>
                <p style="text-transform: uppercase;">
                    {{ ($establishment->address !== '-')? $establishment->address : '' }}
                    {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </p>
                <p>{{ ($establishment->email !== '-')? $establishment->email : '' }}</h6>
                <p>{{ ($establishment->telephone !== '-')? $establishment->telephone : '' }}</h6>
            </div>
        </td>
    </tr>
</table>
<table class="full-width pt-10">
    <tr>
        <td><p class="font-bold text-upp">Adquiriente</p></td>
        <td></td>
    </tr>
    <tr>
        <td width="65%">
            <table class="full-width">
                <tr>
                    <td>{{ $customer->identity_document_type->description }}:{{$customer->number}}</td>
                </tr>
                <tr>
                    <td>{{ $customer->name }}</td>
                </tr>
                <tr>
                    <td>
                        @if ($customer->address !== '')
                            {{ $customer->address }}
                            {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                            {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                            {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
                        @endif
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <table class="full-width">
                <tr>
                    <td>Fecha de emisión:</td>
                    <td>{{$document->date_of_issue->format('Y-m-d')}}</td>
                </tr>
                @if($invoice)
                    <tr>
                        <td>Fecha de vencimiento:</td>
                        <td>{{$invoice->date_of_due->format('Y-m-d')}}</td>
                    </tr>
                @endif
                @if(!empty($placa))

                    <tr>
                        <td>PLACA DEL VEHICULO:</td>
                        <td>{{ $placa }}</td>
                    </tr>
                @endif
                @if ($document->purchase_order)
                    <tr>
                        <td>Orden de compra:</td>
                        <td>{{ $document->purchase_order }}</td>
                    </tr>
                @endif
                @if ($document->guides)
                    @foreach($document->guides as $guide)
                        <tr>
                            @if(isset($guide->document_type_description))
                                <td>{{ $guide->document_type_description }}</td>
                            @else
                                <td>{{ $guide->document_type_id }}</td>
                            @endif
                            {{-- <td>{{ \App\Models\Tenant\Catalogs\Code::byCatalogAndCode('01', $guide->document_type_code)->description }}</td>
                            <td>{{ $guide->number }}</td> --}}
                        </tr>
                    @endforeach
                @endif
            </table>
        </td>
    </tr>
</table>

<table class="full-width mt-3">
    @if ($document->quotation_id)
        <tr>
            <td>Cotización</td>
            <td>:</td>
            <td>{{ $document->quotation->identifier }}</td>
        </tr>
    @endif
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
</table>

@if ($document->retention)
    <table class="full-width mt-3">
        <tr>
            <td colspan="3">
                INFORMACIÓN DE LA RETENCIÓN
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
    </table>
@endif

<table class="mt-10 mb-10"
       style="border-collapse: collapse;border-top: 1px solid #333;">
    <tr class="bg-grey">
        <th class="text-left py-2"
            width="22">COD.
        </th>
        <th class="text-center py-2"
            width="5%">Cant.
        </th>
        <th class="text-center py-2"
            width="5%">UM
        </th>
        <th class="text-left py-2">Descripción</th>
        <th class="text-right py-2"
            width="10%">P.Unit
        </th>
        <th class="text-right py-2"
            width="8%">Dto.
        </th>
        <th class="text-right py-2"
            width="10%">TOTAL
        </th>
    </tr>
    <tbody>
    @foreach($document->items as $row)
        <tr>
            <td>
                {{ $row->item->internal_id }}
            </td>
            <td class="text-center align-top">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center align-top">
                {{ symbol_or_code($row->item->unit_type_id) }}
            </td>
            <td class="text-left align-top">
                {!!$row->item->description!!}
                @if (!empty($row->item->presentation))
                    {!!$row->item->presentation->description!!}
                @endif
                @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/><span style="font-size: 7px">{!! $attr->description !!} : {{ $attr->value }}</span>
                    @endforeach
                @endif
                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><span style="font-size: 7px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                    @endforeach
                @endif
            </td>
            <td class="text-right align-top">
                {{ number_format($row->unit_price, 2) }}
            </td>
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
            <td class="text-right align-top">
                {{ number_format($row->total, 2) }}
            </td>
        </tr>
        <tr>
            <td colspan="7"
                class="border-bottom"></td>
        </tr>
    @endforeach
    @if($document->total_exportation > 0)
        <tr>
            <td colspan="6"
                class="text-right font-bold">Op. Exportación: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
        </tr>
    @endif
    @if($document->total_free > 0)
        <tr>
            <td colspan="6"
                class="text-right font-bold">Op. Gratuitas: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
        </tr>
    @endif
    @if($document->total_unaffected > 0)
        <tr>
            <td colspan="6"
                class="text-right font-bold">Op. Inafectas: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
        </tr>
    @endif
    @if($document->total_exonerated > 0)
        <tr>
            <td colspan="6"
                class="text-right font-bold">Op. Exoneradas: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
        </tr>
    @endif
    @if($document->total_taxed > 0)
        <tr>
            <td colspan="6"
                class="text-right font-bold">Op. Gravadas: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
        </tr>
    @endif
    @if($document->total_discount > 0)
        <tr>
            <td colspan="5"
                class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'Anticipo':'Descuento TOTAL')}}
                : {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
        </tr>
    @endif
    @if($document->total_plastic_bag_taxes > 0)
        <tr>
            <td colspan="5"
                class="text-right font-bold">Icbper: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
        </tr>
    @endif
    <tr>
        <td colspan="6"
            class="text-right font-bold">IGV: {{ $document->currency_type->symbol }}</td>
        <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
    </tr>
    <tr>
        <td colspan="6"
            class="text-right font-bold">Total a pagar: {{ $document->currency_type->symbol }}</td>
        <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
    </tr>
    @if(($document->retention || $document->detraction) && $document->total_pending_payment > 0)
        <tr>
            <td colspan="6"
                class="text-right font-bold">M. Pendiente: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_pending_payment, 2) }}</td>
        </tr>
    @endif
    </tbody>
</table>
@php
    $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
@endphp
<table class="full-width">
    <tr>
        <td>
            <p style="text-transform: uppercase;">
                Condición de Pago:
                <strong>
                    {{ $paymentCondition  }}
                </strong>
            </p>
        </td>
    </tr>
</table>
@if ($document->payment_condition_id === '01')
    @if($payments->count())
        <table class="full-width">
            <tr>
                <td>
                    <p style="text-transform: uppercase;">
                        <strong>Pagos:</strong>
                    </p>
                </td>
            </tr>
            @php
                $payment = 0
            @endphp
            @if(isset($payments))
            @foreach($payments as $row)
                <tr>
                    <td>
                        <p style="text-transform: uppercase;">
                            &#8226; {{ $row->payment_method_type->description }}
                            - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}
                        </p>
                    </td>
                </tr>
                @endforeach
                @endif
                </tr>
        </table>
    @endif
@else
    <table class="full-width">
        @foreach($document->fee as $key => $quote)
            <tr>
                <td>
                    <p style="text-transform: uppercase;">
                        &#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }}
                        / Fecha: {{ $quote->date->format('d-m-Y') }} /
                        Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}
                    </p>
                </td>
            </tr>
            @endforeach
            </tr>
    </table>
@endif
<br>
<br>
<table class="full-width">
    <tr>
        <td width="65%"
            style="text-align: top; vertical-align: top;">
            @foreach(array_reverse( (array) $document->legends) as $row)
                @if ($row->code == "1000")
                    <p style="text-transform: uppercase;">Son:
                        <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></p>
                    @if (count((array) $document->legends)>1)
                        <p><span class="font-bold">Leyendas</span></p>
                    @endif
                @else
                    <p> {{$row->code}}: {{ $row->value }} </p>
                @endif

            @endforeach
            <br/>
            @foreach($document->additional_information as $information)
                @if ($information)
                    @if ($loop->first)
                        <p class="font-bold">Información adicional</p>
                    @endif
                    <p>{{ $information }}</p>
                @endif
            @endforeach
            <br>
            @if(in_array($document->document_type->id,['01','03']))
                <p class="font-bold">Cuentas bancarias</p>
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
        <td width="35%"
            class="text-right">
            <img src="data:image/png;base64, {{ $document->qr }}"
                 style="margin-right: -10px;"/>
            <p style="font-size: 9px">Código Hash: {{ $document->hash }}</p>
        </td>
    </tr>
</table>

<table class="full-width">
    @php
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
        $establishment_data = \App\Models\Tenant\Establishment::find($document->establishment_id);
    @endphp
    <tbody>
        <tr>
            @if ($configuration->yape_qr_documents && $establishment_data->yape_logo)
                @php
                    $yape_logo = $establishment_data->yape_logo;
                @endphp
                <td class="text-center">
                    <table>
                        <tr>
                            <td>
                                <strong>
                                    Qr yape
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <img src="data:{{ mime_content_type(public_path("{$yape_logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$yape_logo}"))) }}"
                                    alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                @if($establishment_data->yape_owner)
                                    <strong>
                                        Nombre: {{ $establishment_data->yape_owner }}
                                    </strong>
                                @endif
                                @if($establishment_data->yape_number)
                                    <br>
                                    <strong>
                                        Número: {{ $establishment_data->yape_number }}
                                    </strong>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            @endif
            @if ($configuration->plin_qr_documents && $establishment_data->plin_logo)
                @php
                    $plin_logo = $establishment_data->plin_logo;
                @endphp
                <td class="text-center">
                    <table>
                        <tr>
                            <td>
                                <strong>
                                    Qr plin
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <img src="data:{{ mime_content_type(public_path("{$plin_logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$plin_logo}"))) }}"
                                    alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                @if($establishment_data->plin_owner)
                                    <strong>
                                        Nombre: {{ $establishment_data->plin_owner }}
                                    </strong>
                                @endif
                                @if($establishment_data->plin_number)
                                    <br>
                                    <strong>
                                        Número: {{ $establishment_data->plin_number }}
                                    </strong>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            @endif
        </tr>
    </tbody>
</table>

@if ($document->terms_condition)
        <br>
        <table class="full-width">
            <tr>
                <td>
                    <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                    {!! $document->terms_condition !!}
                </td>
            </tr>
        </table>
    @endif
</body>
</html>
