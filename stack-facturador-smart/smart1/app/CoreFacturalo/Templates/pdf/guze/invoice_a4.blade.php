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
        <div style="width: 30%; float: left; padding-left: 20px;padding-top: 10px;">
            <div class="border-box rounded-box">
                <div style="text-align: center; font-weight: bold;">{{ 'R.U.C.' }}</div>
                <div style="text-align: center; font-weight: bold;">{{ $company->number }}</div>
                <div style="text-align: center; font-weight: bold; padding: 3px;" class="primary-bg">
                    {{ $document->document_type->description }}</div>
                <div style="text-align: center; font-weight: bold;">{{ $document_number }}</div>
            </div>
        </div>
    </div>
    <div class="border-box rounded-box py-10 px-10 mt-2">
        <table>
            <tr>
                <td>
                    <strong>Emisión</strong>
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ $document->date_of_issue->format('d/m/Y') }}
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <strong>Guía Nro</strong>
                </td>
                <td>
                    :
                </td>
                <td>
                    @if ($document->reference_guides)
                        {{ optional($document->reference_guides->first())->number_full }}
                    @else
                        &nbsp;
                    @endif
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Cliente</strong>
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ $customer->name }}
                </td>
                <td colspan="3"></td>
                <td>
                    <strong>O/C</strong>
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ $document->purchase_order }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>RUC</strong>
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ $customer->number }}
                </td>
                <td colspan="3"></td>
                <td>
                    <strong>Vencimiento</strong>
                </td>
                <td>
                    :
                </td>
                <td>
                    {{ optional($document->invoice->date_of_due)->format('d/m/Y') }}
                </td>

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
                <td colspan="3"></td>
                <td>
                    <strong>Cond. Pago</strong>
                </td>
                <td>
                    :
                </td>
                <td style="text-transform: uppercase;">
                    {{ $document->payment_condition->name ?? 'CONTADO' }}
                </td>
            </tr>
        </table>
    </div>
    @php
        $percentage = 16.5;
        $banck_accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', 1)->get();
    @endphp



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
            style=" font-weight:bold; border-bottom:1px solid #808181; border-top-left-radius:10px; border-top-right-radius:10px; width:100%; position:relative; height: auto;">

            <div
                style="float:left; width:60px; padding:4px; text-align:center; border-right:1px solid #333;height: 28px;line-height: 28px;">
                CANT.</div>

            <div
                style="float:left; width:500px; padding:4px; text-align:left; border-right:1px solid #333; height: 28px;line-height: 28px;">
                DESCRIPCIÓN</div>
            <div style="float:left; width:80px; padding:2px; text-align:center; border-right:1px solid #333;">
                PRECIO
                <div class="text-center">
                    UNIT
                </div>
            </div>

            <div style="float:left; width:80px; padding:4px; text-align:right; height: 28px; line-height: 28px;">IMPORTE
            </div>
        </div>
        @php
            $allowed_items = 35;
            $to_rest = 8;
            $sum_bank_accounts = $banck_accounts->count() * 3;
            $sum_terms_condition = $document->terms_condition ? $to_rest : 0;
            $sum_additional_information = count($document->additional_information) * $to_rest;
            $sum_detraction = $document->detraction ? $to_rest : 0;
            $sum_retention = $document->retention ? $to_rest : 0; 
            $allowed_items = $allowed_items - $sum_terms_condition - $sum_additional_information - $sum_detraction - $sum_retention - $sum_bank_accounts;
            $quantity_items = $document->items()->count();
            $cycle_items = $allowed_items - $quantity_items * 3;
            $total_weight = 0;
        @endphp

        <!-- Cuerpo de items (divs) -->
        @foreach ($document->items as $index => $row)
            @php
                $is_last = $index === count($document->items) - 1;
            @endphp
            <div style="width:100%;  position:relative; ">

                <div style="float:left; width:60px; padding:4px; text-align:right; border-right:1px solid #333;">
                    @if ((int) $row->quantity != $row->quantity)
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </div>

                <div style="float:left; width:500px; padding:4px; text-align:left; border-right:1px solid #333;">
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
                <div
                    style="float:left; width:80px; padding:2px; text-align:right; border-right:1px solid #333; line-height: 20px;">
                    {{ isset($row->unit_price) ? number_format($row->unit_price, 2) : '' }}</div>
                <div style="float:left; width:80px; padding:2px; text-align:right;">
                    {{ isset($row->total) ? number_format($row->total, 2) : '' }}</div>
            </div>
        @endforeach
        @if ($cycle_items > 0)
            @for ($i = 0; $i < $cycle_items; $i++)
                <div style="float:left; width:60px; padding:4px; text-align:right; border-right:1px solid #333;line-height: 20px;">
                    &nbsp;
                </div>
                <div style="float:left; width:500px; padding:4px; text-align:left; border-right:1px solid #333;line-height: 20px;">
                    &nbsp;
                </div>
                <div style="float:left; width:76px; padding:4px; text-align:right; border-right:1px solid #333;line-height: 20px;">
                    &nbsp;
                </div>
                <div style="float:left; width:80px; padding:4px; text-align:right;line-height: 20px;">
                    &nbsp;
                </div>
            @endfor
        @endif

    </div>
    <div class="full-width">
        @foreach (array_reverse((array) $document->legends) as $row)
            @if ($row->code == '1000')
                <p style="padding:0px;margin:0px;">Son: <span class="font-bold desc">{{ strtoupper($row->value) }}
                        {{ $document->currency_type->description }}</span></p>
            @else
                <p style="padding:0px;margin:0px;"> {{ $row->code }}: {{ $row->value }} </p>
            @endif
        @endforeach
    </div>
    <div class="full-width">
        <div style="float:left; width: 18%;">
            @if ($document->qr)
                <img src="data:image/png;base64, {{ $document->qr }}" style=" width: 100px;" />
            @endif
        </div>
        <div style="float:left; width: 40%;margin-top: 10px;">
            <div class="border-box rounded-box p-2">

                <div>
                    Recibido por:
                </div>
                <div>DNI:</div>
                <div>Firma:</div>
                <div>Fecha:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/
                </div>
            </div>

        </div>

        <div style="float:left; width: 5%;">
            &nbsp;
        </div>
        <div style="float:left; width: 35%;">
            <div style="border: 2px solid #000; border-radius: 10px; overflow: hidden; width: 100%; font-size: 13px;">
                <div style="float:left; width: 64%;">
                    <div style="border-bottom: 1px solid #000; padding: 2px 8px;"><strong>OP. GRAVADA&nbsp;&nbsp;(S/)</strong></div>
                    <div style="border-bottom: 1px solid #000; padding: 2px 8px;"><strong>TOTAL IGV&nbsp;&nbsp;(S/)</strong></div>
                    <div style="padding: 2px 8px;"><strong>IMPORTE TOTAL&nbsp;&nbsp;(S/)</strong></div>
                </div>
                <div style="float:left; width: 35%; text-align: right; border-left: 1px solid #000;">
                    <div style="border-bottom: 1px solid #000; padding: 2px 8px;">{{ number_format($document->total_taxed, 2) }}</div>
                    <div style="border-bottom: 1px solid #000; padding: 2px 8px;">{{ number_format($document->total_igv, 2) }}</div>
                    <div style="padding: 2px 8px;">{{ number_format($document->total, 2) }}</div>
                </div>
                <div style="clear:both;"></div>
            </div>
        </div>
    </div>
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

        
        </div>
    </div>


    
    @if($banck_accounts->count() > 0)
    <div class="full-width">
        <div style="float:left;width:68%;">
            <table class="full-width border-box p-2" style="border-collapse: collapse; ">
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
            
        </div>
    </div>
    @endif
    



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
