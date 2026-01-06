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
    $establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $logo = $establishment__->logo ?? $company->logo;

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

    $customer = $document->customer;
    $invoice = $document->invoice;
    $document_base = $document->note ? $document->note : null;

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::all();

    if ($document_base) {
        $affected_document_number = $document_base->affected_document
            ? $document_base->affected_document->series .
                '-' .
                str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT)
            : $document_base->data_affected_document->series .
                '-' .
                str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);
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
    $balance = $document->total - $total_payment - $document->payments->sum('change');

    $months_spanish = [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre',
    ];

    //calculate items
    $allowed_items = 80;
    $quantity_items = $document->items()->count();
    $cycle_items = $allowed_items - $quantity_items * 3;
    $total_weight = 0;

    $establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $logo = $establishment__->logo ?? $company->logo;

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }
    $bg = "storage/uploads/header_images/{$configurations->header_image}";

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

@endphp
<html>

<head>
    {{-- <title>{{ $document_number }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>
    {{-- @if ($document->state_type->id == '11' || $document->state_type->id == '09')
        <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
            <img src="data:{{ mime_content_type(public_path('status_images' . DIRECTORY_SEPARATOR . 'anulado.png')) }};base64, {{ base64_encode(file_get_contents(public_path('status_images' . DIRECTORY_SEPARATOR . 'anulado.png'))) }}"
                alt="anulado" class="" style="opacity: 0.6;">
        </div>
    @endif
    @if ($document->soap_type_id == '01')
        <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
            <img src="data:{{ mime_content_type(public_path('status_images' . DIRECTORY_SEPARATOR . 'demo.png')) }};base64, {{ base64_encode(file_get_contents(public_path('status_images' . DIRECTORY_SEPARATOR . 'demo.png'))) }}"
                alt="anulado" class="" style="opacity: 0.6;">
        </div>
    @endif --}}
    <div style="width: 100%;">
        @if ($company->logo)
            <div style="width: 20%; float: left;">
                <div class="company_logo_box">
                    <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                        alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                </div>
            </div>
        @else
            <div style="width: 20%; float: left;">
            </div>
        @endif
        <div style="width: 45%; float: left; text-align: center;">
            <div style="text-align: center;">
                <h4>{{ $company_name }}</h4>
                @if ($company_owner)
                    De: {{ $company_owner }}
                @endif
                <h5>{{ 'RUC ' . $company->number }}</h5>
                <h6 style="text-transform: uppercase;">
                    {{ $establishment->address !== '-' ? $establishment->address : '' }}
                    {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                    {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                    {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                </h6>

                @isset($establishment->trade_address)
                    <h6>{{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                    </h6>
                @endisset

                <h6>{{ $establishment->telephone !== '-' ? 'Central telefónica: ' . $establishment->telephone : '' }}
                </h6>

                <h6>{{ $establishment->email !== '-' ? 'Email: ' . $establishment->email : '' }}</h6>

                @isset($establishment->web_address)
                    <h6>{{ $establishment->web_address !== '-' ? 'Web: ' . $establishment->web_address : '' }}</h6>
                @endisset

                @isset($establishment->aditional_information)
                    <h6>{{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
                    </h6>
                @endisset
            </div>
        </div>
        <div style="width: 30%; float: left; padding-left: 20px;">
            <div class="border-box rounded-box py-10">
                <div style="text-align: center; font-weight: bold;">{{ 'R.U.C. ' . $company->number }}</div>
                <div style="text-align: center; font-weight: bold; padding: 10px;" class="primary-bg">
                    {{ $document->document_type->description }}</div>
                <div style="text-align: center; font-weight: bold;">Nro. {{ $document_number }}</div>
            </div>
        </div>
    </div>
    <div class="border-box rounded-box py-10 px-10 mt-2">
        <table>
            <tr>
                <td>
                    <strong>Señor(es)</strong>
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ $customer->name }}
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <strong>Vendedor</strong>
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ optional($document->seller)->name }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>R.U.C.</strong>
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ $customer->number }}
                </td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td>
                    <strong>Dirección</strong>
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ $customer->address }} - {{ optional($customer->department)->description }}
                </td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td>
                    <strong>Provincia</strong>
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ optional($customer->province)->description }}
                </td>
                <td>
                    <strong>Distrito</strong>
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ optional($customer->district)->description }}
                </td>
                <td colspan="3"></td>
            </tr>
        </table>
    </div>
    @php
        $percentage = 16.5;
    @endphp
    <div class="border-box rounded-box px-10 mt-2">
        <div
            style="border-right: 1px solid #333; float: left; width: {{ $percentage }}%; text-align: center; height: 35px;">
            <div>
                <strong>Fecha Emisión</strong>
            </div>
            <div style="font-size: 10px;">
                {{ $document->date_of_issue->format('d') }} de
                {{ $months_spanish[$document->date_of_issue->format('m')] }} de
                {{ $document->date_of_issue->format('Y') }}
            </div>
        </div>
        <div
            style="border-right: 1px solid #333; float: left; width: {{ $percentage }}%; text-align: center; height: 35px;">
            <div>
                <strong>Forma de pago</strong>
            </div>
            <div>
                {{ optional($document->payment_condition)->name ?? 'CONTADO' }}
            </div>
        </div>
        <div
            style="border-right: 1px solid #333; float: left; width: {{ $percentage }}%; text-align: center; height: 35px;">
            <div>
                <strong>N° de placa</strong>
            </div>
            <div>
                @if ($document->plate_number)
                    {{ $document->plate_number }}
                @else
                    &nbsp;
                @endif
            </div>
        </div>
        <div
            style="border-right: 1px solid #333; float: left; width: {{ $percentage }}%; text-align: center; height: 35px;">
            <div>
                <strong>Orden de compra</strong>
            </div>
            @if ($document->purchase_order)
                <div>
                    {{ $document->purchase_order }}
                </div>
            @else
                <div>&nbsp;</div>
            @endif
        </div>
        <div
            style="border-right: 1px solid #333; float: left; width: {{ $percentage }}%; text-align: center; height: 35px;">
            <div>
                <strong>Vencimiento</strong>
            </div>
            <div style="font-size: 10px;">
                @if ($document->invoice)
                    {{ $document->invoice->date_of_due->format('d') }} de
                    {{ $months_spanish[$document->invoice->date_of_due->format('m')] }} de
                    {{ $document->invoice->date_of_due->format('Y') }}
                @else
                    &nbsp;
                @endif
            </div>
        </div>
        <div style=" float: left; width: {{ $percentage }}%; text-align: center; height: 35px;">
            <div>
                <strong>N° Guía Remisión</strong>
            </div>
            <div>
                @if ($document->reference_guides)
                    {{ optional($document->reference_guides->first())->number_full }}
                @else
                    &nbsp;
                @endif
            </div>
        </div>
    </div>

    {{-- <table class="full-width mt-3">
        <tr>
            <td width="47%" class="border-box pl-3">
                <table class="full-width">
                    <tr>
                        <td class="font-sm" width="80px">
                            <strong>Razón Social</strong>
                        </td>
                        <td class="font-sm" width="8px">:</td>
                        <td class="font-sm">
                            {{ $customer->name }}
                        </td>
                    </tr>
                    <tr>
                        <td class="font-sm" width="80px">
                            <strong>{{ $customer->identity_document_type->description }}</strong>
                        </td>
                        <td class="font-sm" width="8px">:</td>
                        <td class="font-sm">
                            {{ $customer->number }}
                        </td>
                    </tr>
                    <tr>
                        <td class="font-sm" width="80px">
                            <strong>Dirección</strong>
                        </td>
                        <td class="font-sm" width="8px">:</td>
                        <td class="font-sm">
                            @if ($customer->address !== '')
                                <span style="text-transform: uppercase;">
                                    {{ $customer->address }}
                                    {{ $customer->district_id !== '-' ? ', ' . $customer->district->description : '' }}
                                    {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                                    {{ $customer->department_id !== '-' ? '- ' . $customer->department->description : '' }}
                                </span>
                            @endif
                        </td>
                    </tr>

                    @if (!is_null($document_base))
                        <tr>
                            <td class="font-sm font-bold" width="80px">Doc. Afectado</td>
                            <td class="font-sm" width="8px">:</td>
                            <td class="font-sm">{{ $affected_document_number }}</td>
                        </tr>
                        <tr>
                            <td class="font-sm font-bold" width="80px">Tipo de nota</td>
                            <td class="font-sm">:</td>
                            <td class="font-sm">
                                {{ $document_base->note_type === 'credit' ? $document_base->note_credit_type->description : $document_base->note_debit_type->description }}
                            </td>
                        </tr>
                        <tr>
                            <td class="font-sm font-bold" width="80px">Descripción</td>
                            <td class="font-sm">:</td>
                            <td class="font-sm">{{ $document_base->note_description }}</td>
                        </tr>
                    @endif
                </table>
            </td>
            <td width="3%"></td>
            <td width="50%" class="border-box pl-1 ">
                <table class="full-width">


                    <tr>
                        <td class="font-sm" width="90px">
                            <strong>Fecha Emisión</strong>
                        </td>
                        <td class="font-sm" width="8px">:</td>
                        <td class="font-sm">
                            {{ $document->date_of_issue->format('Y-m-d') }}
                        </td>
                    
                    </tr>

                    <tr>
                        @if ($invoice)
                            <td class="font-sm" width="90px">
                                <strong>Fecha de Vcto</strong>
                            </td>
                            <td class="font-sm" width="8px">:</td>
                            <td class="font-sm">
                                {{ $invoice->date_of_due->format('Y-m-d') }}
                            </td>
                        @endif

                        <td class="font-sm" width="70px">
                            <strong>Moneda</strong>
                        </td>
                        <td class="font-sm" width="8px">:</td>
                        <td class="font-sm">
                            {{ $document->currency_type->description }}
                        </td>
                    </tr>

                    <tr>
                        @if ($document->purchase_order)
                            <td class="font-sm" width="90px">
                                <strong>Orden de compra</strong>
                            </td>
                            <td class="font-sm" width="8px">:</td>
                            <td class="font-sm">
                                {{ $document->purchase_order }}
                            </td>
                        @endif

                        @if ($document->payments()->count() > 0)
                            <td class="font-sm" width="70px">
                                <strong>F. Pago</strong>
                            </td>
                            <td class="font-sm" width="8px">:</td>
                            <td class="font-sm">
                                {{ $document->payments()->first()->payment_method_type->description }} -
                                {{ $document->currency_type_id }} {{ $document->payments()->first()->payment }}
                            </td>
                        @endif
                    </tr>

                    <tr>
                        @if ($document->guides)
                            <td class="font-sm" width="100px">
                                <strong>Guía de Remisión</strong>
                            </td>
                            <td class="font-sm" width="8px">:</td>
                            <td class="font-sm" colspan="4">
                                @foreach ($document->guides as $item)
                                    {{ $item->document_type_description }}: {{ $item->number }}<br>
                                @endforeach
                            </td>
                        @endif

                        @if ($document->reference_guides)
                            @if (count($document->reference_guides) > 0)
                                <td class="font-sm" width="100px">
                                    <strong>Guías de Remisión</strong>
                                </td>
                                <td class="font-sm" width="8px">:</td>
                                <td class="font-sm" colspan="4">
                                    @foreach ($document->reference_guides as $guide)
                                        <span>
                                            {{ $guide->series }}-{{ $guide->number }}
                                        </span>
                                    @endforeach
                                </td>
                            @endif
                        @endif
                    </tr>
                    <tr>
                        @if ($document->detraction)
                            <td class="font-sm" width="100px" style="vertical-align: top">
                                <strong>N. Cta. Detracciones</strong>
                            </td>
                            <td width="8px" style="vertical-align: top">:</td>
                            <td class="font-sm" style="vertical-align: top">{{ $document->detraction->bank_account }}
                            </td>

                            <td class="font-sm" width="70px" style="vertical-align: top">
                                <strong>P. Detracción</strong>
                            </td>
                            <td width="8px" style="vertical-align: top">:</td>
                            <td class="font-sm" style="vertical-align: top">{{ $document->detraction->percentage }}%
                            </td>
                        @endif
                    </tr>
                    <tr>
                        @if ($document->detraction)
                            <td class="font-sm" width="100px" style="vertical-align: top">
                                <strong>Monto detracción</strong>
                            </td>
                            <td width="8px" style="vertical-align: top">:</td>
                            <td class="font-sm" style="vertical-align: top">S/ {{ $document->detraction->amount }}
                            </td>
                        @endif
                    </tr>

                    <tr>
                        @if ($document->retention)
                            <td class="font-sm" colspan="6">
                                <strong>Información de la retención</strong>
                            </td>
                        @endif
                    </tr>
                    @if ($document->retention)
                        <tr>
                            <td class="font-sm" width="100px" style="vertical-align: top">
                                <strong>Base imponible</strong>
                            </td>
                            <td class="font-sm" width="8px" style="vertical-align: top">:</td>
                            <td class="font-sm" style="vertical-align: top">
                                {{ $document->currency_type->symbol }} {{ $document->retention->base }}
                            </td>

                            <td class="font-sm" width="70px" style="vertical-align: top">
                                <strong>Porcentaje</strong>
                            </td>
                            <td class="font-sm" width="8px" style="vertical-align: top">:</td>
                            <td class="font-sm" style="vertical-align: top">
                                {{ $document->retention->percentage * 100 }}%
                            </td>
                        </tr>
                        <tr>
                            <td class="font-sm" width="100px" style="vertical-align: top">
                                <strong>Monto</strong>
                            </td>
                            <td class="font-sm" width="8px" style="vertical-align: top">:</td>
                            <td class="font-sm" style="vertical-align: top">
                                {{ $document->currency_type->symbol }} {{ $document->retention->amount }}
                            </td>
                        </tr>
                    @endif
                </table>
            </td>

        </tr>
    </table> --}}
    <table class="full-width my-2 text-center" border="0">
        <tr>
            <td class="desc"></td>
        </tr>
    </table>

    <!-- TABLA DE ITEMS: CABECERA CON DIVS Y CUERPO CON TABLA -->
    <div
        style="width:100%; border:1px solid #333; font-size:12px; border-radius:10px; overflow:hidden; margin-bottom:10px;">
        <!-- Cabecera (divs, float, pixeles) -->
        <div
            style="background:#808181; font-weight:bold; border-bottom:1px solid #808181; border-top-left-radius:10px; border-top-right-radius:10px; width:100%; position:relative; height: auto;">
            <div style="float:left; width:90px; padding:4px; text-align:center; border-right:1px solid #333;color:#fff">
                CÓDIGO</div>
            <div style="float:left; width:60px; padding:4px; text-align:center; border-right:1px solid #333;color:#fff">
                CANT.</div>
            <div style="float:left; width:60px; padding:4px; text-align:center; border-right:1px solid #333;color:#fff">
                U.M.</div>
            <div style="float:left; width:300px; padding:4px; text-align:left; border-right:1px solid #333;color:#fff">
                DESCRIPCIÓN</div>
            <div style="float:left; width:80px; padding:4px; text-align:right; border-right:1px solid #333;color:#fff">
                P.Unit</div>
            <div style="float:left; width:110px; padding:4px; text-align:right;color:#fff">TOTAL</div>
        </div>
        <!-- Cuerpo de items (divs) -->
        @foreach ($document->items as $index => $row)
            @php
                $is_last = $index === count($document->items) - 1;
            @endphp
            <div
                style="width:100%;  position:relative; @if ($is_last) border-bottom:none;@else border-bottom:1px solid #333; @endif">
                <div style="float:left; width:90px; padding:4px; text-align:center; border-right:1px solid #333;">
                    {{ $row->item->internal_id ?? '' }}</div>
                <div style="float:left; width:60px; padding:4px; text-align:center; border-right:1px solid #333;">
                    @if ((int) $row->quantity != $row->quantity)
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </div>
                <div style="float:left; width:60px; padding:4px; text-align:center; border-right:1px solid #333;">
                    {{ symbol_or_code($row->item->unit_type_id) ?? '' }}</div>
                <div style="float:left; width:300px; padding:4px; text-align:left; border-right:1px solid #333;">
                    @if ($row->name_product_pdf)
                        {{ removePTag($row->name_product_pdf) }}
                    @else
                        {{ $row->item->description ?? '' }}
                    @endif
                    @if ($row->attributes)
                        @foreach ($row->attributes as $attr)
                            @if ($attr->attribute_type_id === '5032')
                                @php $total_weight += $attr->value * $row->quantity; @endphp
                            @endif
                            <br /><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                        @endforeach
                    @endif
                    @if ($row->item->is_set == 1)
                        <br>
                        @inject('itemSet', 'App\\Services\\ItemSetService')
                        {{ join('-', $itemSet->getItemsSet($row->item_id)) }}
                    @endif
                </div>
                <div style="float:left; width:80px; padding:4px; text-align:right; border-right:1px solid #333;">
                    {{ isset($row->unit_price) ? number_format($row->unit_price, 2) : '' }}</div>
                <div style="float:left; width:110px; padding:4px; text-align:right;">
                    {{ isset($row->total) ? number_format($row->total, 2) : '' }}</div>
                <div style="clear:both;"></div>
            </div>
        @endforeach

        <!-- Pie de tabla con totales (divs, igual que cabecera) -->
        {{-- <div style="background:#f5f5f5; font-weight:bold; border-top:1px solid #333; border-bottom-left-radius:10px; border-bottom-right-radius:10px; width:100%; position:relative;">
            <div style="float:left; width:90px; padding:4px; text-align:right; border-right:1px solid #333;"></div>
            <div style="float:left; width:60px; padding:4px; text-align:right; border-right:1px solid #333;"></div>
            <div style="float:left; width:60px; padding:4px; text-align:right; border-right:1px solid #333;"></div>
            <div style="float:left; width:340px; padding:4px; text-align:right; border-right:1px solid #333;"></div>
            <div style="float:left; width:100px; padding:4px; text-align:right; border-right:1px solid #333; font-weight:bold;">TOTAL</div>
            <div style="float:left; width:120px; padding:4px; text-align:right; font-weight:bold; border-right:none;">{{ number_format($document->total, 2) }}</div>
            <div style="clear:both;"></div>
        </div> --}}
    </div>


    <div class="w-100">
        <div class="w-50" style="float:left;">
            <div>
                {{ $document->terms_condition }}
            </div>
            <div class="mt-2">
                <strong>Observaciones:</strong>
                @foreach ($document->additional_information as $item)
                    <div>{{ $item }}</div>
                @endforeach
                @if (isset($document->fee) && count($document->fee) > 0)
                <div class="mt-2">
                    <strong>Cuotas:</strong>
                <table class="full-width desc">
                    @foreach ($document->fee as $key => $quote)
                        <tr>
                            <td class="text-left desc">
                                @if (!$configurations->show_the_first_cuota_document)
                                    &#8226;
                                    {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                                    / Fecha: {{ $quote->date->format('d-m-Y') }} /
                                    Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}
                                @else
                                    @if ($key == 0)
                                        &#8226;
                                        {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                                        / Fecha: {{ $quote->date->format('d-m-Y') }} /
                                        Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}
                                    @endif
                                @endif
        
                            </td>
                        </tr>
                    @endforeach
                    </tr>
                </table>
                </div>
                @endif
            </div>
            @if ($document->detraction)
                <div class="mt-2">
                    <strong>Información de Detracción:</strong>
                    <table class="full-width mt-1">
                        <tr>
                            <td class="text-left desc" width="140px">N. Cta detracciones</td>
                            <td class="text-left desc" width="8px">:</td>
                            <td class="text-left desc">{{ $document->detraction->bank_account }}</td>
                        </tr>
                        <tr>
                            <td class="text-left desc" width="140px">B/S Sujeto a detracción</td>
                            <td class="text-left desc" width="8px">:</td>
                            @inject('detractionType', 'App\Services\DetractionTypeService')
                            <td class="text-left desc">{{ $document->detraction->detraction_type_id }}
                                -
                                {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-left desc" width="140px">Método de pago</td>
                            <td class="text-left desc" width="8px">:</td>
                            <td class="text-left desc">
                                {{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-left desc" width="140px">P. Detracción</td>
                            <td class="text-left desc" width="8px">:</td>
                            <td class="text-left desc">{{ $document->detraction->percentage }}%</td>
                        </tr>
                        <tr>
                            <td class="text-left desc" width="140px">Monto detracción</td>
                            <td class="text-left desc" width="8px">:</td>
                            <td class="text-left desc">
                                @php
                                    $amount_detraction = $document->detraction->amount;
                                @endphp
                                S/ {{ $amount_detraction }}
                            </td>
                        </tr>
                        @if ($document->detraction->pay_constancy)
                            <tr>
                                <td class="text-left desc" width="140px">Constancia de pago</td>
                                <td class="text-left desc" width="8px">:</td>
                                <td class="text-left desc">{{ $document->detraction->pay_constancy }}</td>
                            </tr>
                        @endif
                    </table>

                    @if ($document->detraction && $invoice && $invoice->operation_type_id == '1004')
                        <div class="mt-2">
                            <strong>Detalle - Servicios de transporte de carga:</strong>
                            <table class="full-width mt-1">
                                @if (isset($document->detraction->origin_location_id) && $document->detraction->origin_location_id)
                                    <tr>
                                        <td class="text-left desc" width="140px">Ubigeo origen</td>
                                        <td class="text-left desc" width="8px">:</td>
                                        <td class="text-left desc">{{ $document->detraction->origin_location_id[2] }}
                                        </td>
                                    </tr>
                                @endif
                                @if (isset($document->detraction->origin_address) && $document->detraction->origin_address)
                                    <tr>
                                        <td class="text-left desc" width="140px">Dirección origen</td>
                                        <td class="text-left desc" width="8px">:</td>
                                        <td class="text-left desc">{{ $document->detraction->origin_address }}</td>
                                    </tr>
                                @endif
                                @if (isset($document->detraction->delivery_location_id) && $document->detraction->delivery_location_id)
                                    <tr>
                                        <td class="text-left desc" width="140px">Ubigeo destino</td>
                                        <td class="text-left desc" width="8px">:</td>
                                        <td class="text-left desc">
                                            {{ $document->detraction->delivery_location_id[2] }}</td>
                                    </tr>
                                @endif
                                @if (isset($document->detraction->delivery_address) && $document->detraction->delivery_address)
                                    <tr>
                                        <td class="text-left desc" width="140px">Dirección destino</td>
                                        <td class="text-left desc" width="8px">:</td>
                                        <td class="text-left desc">{{ $document->detraction->delivery_address }}</td>
                                    </tr>
                                @endif
                                @if (isset($document->detraction->reference_value_service) && $document->detraction->reference_value_service)
                                    <tr>
                                        <td class="text-left desc" width="140px">Valor referencial servicio de
                                            transporte</td>
                                        <td class="text-left desc" width="8px">:</td>
                                        <td class="text-left desc">
                                            {{ $document->detraction->reference_value_service }}</td>
                                    </tr>
                                @endif
                                @if (isset($document->detraction->reference_value_effective_load) &&
                                        $document->detraction->reference_value_effective_load)
                                    <tr>
                                        <td class="text-left desc" width="140px">Valor referencia carga efectiva</td>
                                        <td class="text-left desc" width="8px">:</td>
                                        <td class="text-left desc">
                                            {{ $document->detraction->reference_value_effective_load }}</td>
                                    </tr>
                                @endif
                                @if (isset($document->detraction->reference_value_payload) && $document->detraction->reference_value_payload)
                                    <tr>
                                        <td class="text-left desc" width="140px">Valor referencial carga útil</td>
                                        <td class="text-left desc" width="8px">:</td>
                                        <td class="text-left desc">
                                            {{ $document->detraction->reference_value_payload }}</td>
                                    </tr>
                                @endif
                                @if (isset($document->detraction->trip_detail) && $document->detraction->trip_detail)
                                    <tr>
                                        <td class="text-left desc" width="140px">Detalle del viaje</td>
                                        <td class="text-left desc" width="8px">:</td>
                                        <td class="text-left desc">{{ $document->detraction->trip_detail }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    @endif
                </div>
            @endif
            @if ($document->retention)
                <div class="mt-2">
                    <strong>Información de Retención:</strong>
                    <table class="full-width mt-1">
                        <tr>
                            <td class="text-left desc" width="140px">Base imponible</td>
                            <td class="text-left desc" width="8px">:</td>
                            <td class="text-left desc">{{ $document->currency_type->symbol }}
                                {{ $document->retention->base }}</td>
                        </tr>
                        <tr>
                            <td class="text-left desc" width="140px">Porcentaje</td>
                            <td class="text-left desc" width="8px">:</td>
                            <td class="text-left desc">{{ $document->retention->percentage * 100 }}%</td>
                        </tr>
                        <tr>
                            <td class="text-left desc" width="140px">Monto retención</td>
                            <td class="text-left desc" width="8px">:</td>
                            <td class="text-left desc">S/ {{ $document->retention->amount_pen }}</td>
                        </tr>
                    </table>
                </div>
            @endif
        </div>
        <div class="w-50" style="float:left;text-align:right;">

            <div style="float:left;text-align:right; width:29.8%;">
                <br>
            </div>
            <div class="w-70" style="float:left;text-align:right;">
                <div class="w-100 text-right mb-1">

                    <div class="w-50 left">
                        <div class="rounded-box primary-bg border-primary text-center p-2">
                            DESCUENTO (-)
                        </div>
                    </div>
                    <div class="w-5 left text-right">
                        <br>
                    </div>
                    <div class="w-45 left text-right">
                        <div class="rounded-box border-box text-center p-2">
                            <table class="full-width">
                                <tr>
                                    <td width="50%" class="text-left">
                                        {{ $document->currency_type->symbol }}
                                    </td>
                                    <td width="50%" class="text-right">
                                        {{ number_format($document->total_discount, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="w-100 text-right mb-1">

                    <div class="w-50 left">
                        <div class="rounded-box primary-bg border-primary text-center p-2">
                            OP. GRAVADAS
                        </div>
                    </div>
                    <div class="w-5 left text-right">
                        <br>
                    </div>
                    <div class="w-45 left text-right">
                        <div class="rounded-box border-box text-center p-2">
                            <table class="full-width">
                                <tr>
                                    <td width="50%" class="text-left">
                                        {{ $document->currency_type->symbol }}
                                    </td>
                                    <td width="50%" class="text-right">
                                        {{ number_format($document->total_taxed, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="w-100 text-right mb-1">

                    <div class="w-50 left">
                        <div class="rounded-box primary-bg border-primary text-center p-2">
                            OP. EXONERADAS
                        </div>
                    </div>
                    <div class="w-5 left text-right">
                        <br>
                    </div>
                    <div class="w-45 left text-right">
                        <div class="rounded-box border-box text-center p-2">
                            <table class="full-width">
                                <tr>
                                    <td width="50%" class="text-left">
                                        {{ $document->currency_type->symbol }}
                                    </td>
                                    <td width="50%" class="text-right">
                                        {{ number_format($document->total_exonerated, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="w-100 text-right mb-1">

                    <div class="w-50 left">
                        <div class="rounded-box primary-bg border-primary text-center p-2">
                            IGV
                        </div>
                    </div>
                    <div class="w-5 left text-right">
                        <br>
                    </div>
                    <div class="w-45 left text-right">
                        <div class="rounded-box border-box text-center p-2">
                            <table class="full-width">
                                <tr>
                                    <td width="50%" class="text-left">
                                        {{ $document->currency_type->symbol }}
                                    </td>
                                    <td width="50%" class="text-right">
                                        {{ number_format($document->total_igv, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="w-100 text-right mb-1">

                    <div class="w-50 left">
                        <div class="rounded-box primary-bg border-primary text-center p-2">
                            TOTAL
                        </div>
                    </div>
                    <div class="w-5 left text-right">
                        <br>
                    </div>
                    <div class="w-45 left text-right">
                        <div class="rounded-box border-box text-center p-2">
                            <table class="full-width">
                                <tr>
                                    <td width="50%" class="text-left">
                                        {{ $document->currency_type->symbol }}
                                    </td>
                                    <td width="50%" class="text-right">
                                        {{ number_format($document->total, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="full-width border-box rounded-box p-2 mb-2">
        @foreach (array_reverse((array) $document->legends) as $row)
            @if ($row->code == '1000')
                <p style="padding:0px;margin:0px;">Son: <span class="font-bold desc">{{ strtoupper($row->value) }}
                        {{ $document->currency_type->description }}</span></p>
            @else
                <p style="padding:0px;margin:0px;"> {{ $row->code }}: {{ $row->value }} </p>
            @endif
        @endforeach
    </div>
    @php
        $banck_accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', 1)->get();
    @endphp
    <div class="full-width">
        <div style="float:left;width:68%;">
            <table class="full-width border-box p-2"
                style="border-collapse: collapse; ">
                <thead>
                    <tr>
                        <td class="border-box text-center p-2">BANCO</td>
                        <td class="border-box text-center p-2">MONEDA</td>
                        <td class="border-box text-center p-2">N° CUENTA CORRIENTE</td>
                        <td class="border-box text-center p-2">CUENTA INTERBANCARIA (CCI)</td>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $banck_accounts = $banck_accounts->groupBy('bank_id');
                    @endphp
                    @foreach ($banck_accounts as $bank_accounts)
                        @foreach ($bank_accounts as $key => $account)
                            <tr>
                                @if ($key === 0)
                                    <td rowspan="{{ count($bank_accounts) }}" class="border-box text-center p-2">
                                        {{ $account->bank->description }}</td>
                                @endif
                                <td class="border-box text-center p-2">{{ $account->currency_type->description }}</td>
                                <td class="border-box text-center p-2">{{ $account->number }}</td>
                                <td class="border-box text-center p-2">{{ $account->cci }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
            <div class="full-with border-box rounded-box p-2 mt-3">
                <div class="font-bold">
                    CUENTA DE DETRACCIONES
                </div>
                <div>
                    BANCO DE LA NACIÓN: {{$company->detraction_bank_name}}
                </div>
            </div>
        </div>
        <div style="float:left;width:28%;">
            <div class="text-center">
                <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px; width: 150px;" />
                <div style="font-size: 8px;margin:0px;">
                    REPRESENTACION IMPRESA DE {{ strtoupper($document->document_type->description) }}
                </div>
                <div style="font-size: 8px;margin:0px;">
                    RESOLUCION DE SUPERINTENDENCIA N° 155-2017/SUNAT
                </div>
                <p style="font-size: 8px;margin:0px;">{{ $document->hash }}</p>
            </div>
        </div>

    </div>
    <div class="full-width mt-3">
        @if ($configurations->header_image)
        <div class="centered">
            <img src="data:{{ mime_content_type(public_path("{$bg}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$bg}"))) }}"
                alt="anulado" class="order-1" width="100%">
        </div>
    @endif
    </div>

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
                                    @if ($establishment_data->yape_owner)
                                        <strong>
                                            Nombre: {{ $establishment_data->yape_owner }}
                                        </strong>
                                    @endif
                                    @if ($establishment_data->yape_number)
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
                                    @if ($establishment_data->plin_owner)
                                        <strong>
                                            Nombre: {{ $establishment_data->plin_owner }}
                                        </strong>
                                    @endif
                                    @if ($establishment_data->plin_number)
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
