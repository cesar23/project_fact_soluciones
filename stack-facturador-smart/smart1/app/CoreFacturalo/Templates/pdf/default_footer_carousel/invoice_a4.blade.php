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
    $balance = $document->total - $total_payment - $document->payments->sum('change');

    //calculate items
    // $allowed_items = 35 - \App\Models\Tenant\BankAccount::all()->count() * 3;
    $quantity_items = $document->items()->count();
    // $cycle_items = $allowed_items - $quantity_items * 3;
    $cycle_items = 36;
    $total_weight = 0;

    // Condicion de pago
    $condition = TemplateHelper::getDocumentPaymentCondition($document);
    // Pago/Coutas detalladas
    $paymentDetailed = TemplateHelper::getDetailedPayment($document);

    $establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $logo = $establishment__->logo ?? $company->logo;

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }

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
    @if ($document->state_type->id == '11' || $document->state_type->id == '09')
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
    @endif
    {{-- <table class="full-width">
        <tr>
            @if ($company->logo)
                <td width="20%">
                    <div class="company_logo_box">
                        <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                            alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                    </div>
                </td>
            @else
                <td width="20%">
                </td>
            @endif
            <td width="40%" class="pl-3">
                <div class="text-left">
                    <h4 class="">{{ $company_name }}</h4>
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
            </td>
            <td width="40%" class="border-box py-2 px-2 text-center">
                <h3 class="font-bold">{{ 'R.U.C. ' . $company->number }}</h3>
                <h3 class="text-center font-bold">{{ $document->document_type->description }}</h3>
                <br>
                <h3 class="text-center font-bold">{{ $document_number }}</h3>
            </td>
        </tr>
    </table> --}}
    <div class="w-100" style="height: 100px;">
        <div style="float:left;width:30%;">
            <div class="company-logo text-center">
                <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                    alt="{{ $company->name }}" class="company_logo" style="max-width: 550px;">
            </div>
        </div>
        <div style="float:left;width:40%;">
            <div class="text-center">
                <h2 class="font-bold">{{ $company->name }}</h2>
                <h4 style="text-transform: uppercase;">
                    {{ $establishment->address !== '-' ? $establishment->address : '' }}
                    {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                    {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                    {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                </h4>

                @isset($establishment->trade_address)
                    <h5>{{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                    </h5>
                @endisset

                @isset($establishment->aditional_information)
                        <h5>{{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
                        </h5>
                @endisset

                @isset($establishment->web_address)
                    <h5>{{ $establishment->web_address !== '-' ? ' ' . $establishment->web_address : '' }}</h5>
                @endisset
                <h5>{{ $establishment->telephone !== '-' ? 'CEL. ' . $establishment->telephone : '' }}
                </h5>
            </div>
        </div>
        <div style="float:left;width:5%;">
<br>

        </div>
        <div style="float:left;width:25%;">
            <div class="w-100 border-box border-radius text-center" style="height: 100px">
                <h2 class="text-center mt-2">
                    RUC: {{ $company->number }}
                </h2>
                <h3 class="text-center w-100 bg-black text-upp">{{ $document->document_type->description }}</h3>
                <h2 class="text-center mt-2">{{ $document_number }}</h2>
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
                        <td class="font-sm" width="70px">
                            <strong>H. Emisión</strong>
                        </td>
                        <td class="font-sm" width="8px">:</td>
                        <td class="font-sm">
                            {{ $document->time_of_issue }}
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
                                {{ $document->payments()->first()->payment_method_type->description }}
                                - {{ $document->currency_type_id }} {{ $document->payments()->first()->payment }}
                            </td>
                        @endif
                    </tr>

                    <tr>
                        <td class="font-sm" width="100px">
                            <strong>Condición de pago</strong>
                        </td>
                        <td class="font-sm" width="8px">:</td>
                        <td class="font-sm" colspan="4">
                            {{ $condition }}
                        </td>
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
                    </tr>


                </table>
            </td>
            
        </tr>
    </table>
    <table class="full-width my-2 text-center" border="0">
        <tr>
            <td class="desc"></td>
        </tr>
    </table> --}}
    <div class="border-box border-radius w-100 mt-1">
        <table class="w-100">
            <tr>
                <td class="bb" width="60%" valign="top">
                    <table class="w-100 ctn">
                        <tr>
                            <td width="70%" valign="top">
                                <strong>Señores:</strong>
                                {{ $customer->name }}
                            </td>
                            <td valign="top">
                                <strong>RUC:</strong>
                                {{ $customer->number }}
                            </td>
                        </tr>
                        <tr>

                            <td colspan="2" valign="top">
                                <strong>Dirección</strong>
                                {{ $customer->address }}
                                {{ $customer->district_id !== '-' ? ', ' . $customer->district->description : '' }}
                                {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                                {{ $customer->department_id !== '-' ? '- ' . $customer->department->description : '' }}
                            </td>
                        </tr>
                        <tr>
                            <td width="70%" valign="top">
                                <strong>Guia de remisión:</strong>
                                @isset($document->guides)
                                    @foreach ($document->guides as $guide)
                                        {{ $guide->number }}
                                    @endforeach
                                @endisset

                            </td>
                            <td valign="top">
                                <strong>O/Compra:</strong>
                                {{ $document->purchase_order }}
                            </td>
                        </tr>

                    </table>
                </td>
                <td class="bl bb" width="40%" valign="top">
                    <table class="w-100 ctn">
                        <tr>
                            <td colspan="2" valign="top">
                                <strong>Fecha de emisión:</strong>
                                {{ $document->date_of_issue->format('Y-m-d') }} {{ $document->time_of_issue }}
                            </td>

                        </tr>
                        <tr>

                            <td colspan="2" valign="top">
                                <strong>Tipo Moneda:</strong>
                                {{ $document->currency_type->description }}
                            </td>
                        </tr>

                        <tr>
                        @php
                            $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
                        @endphp
                            )
                            <td colspan="2" valign="top">
                                <strong>Forma de pago:</strong>
                                {{ $paymentCondition }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <table class="full-width border-box mt-1">
        <thead>
            <tr class="bg-black">
                <th class="text-white text-center py-2" width="5%">Item</th>
                <th class="text-white text-center py-2" width="8%">CODIGO</th>
                <th class="text-white text-center py-2" width="55%">DESCRIPTION</th>
                <th class="text-white text-center py-2" width="8%">UM</th>
                <th class="text-white text-center py-2" width="8%">CANT.</th>
                <th class="text-white text-center py-2" width="8%">P.UNIT</th>
                <th class="text-white text-center py-2" width="8%">IMPORTE</th>
            </tr>
        </thead>
        @php
            $all_items = $document->items->count();
            $rest_items_count = 38 - $all_items;
    
        @endphp
        <tbody class="">

            @foreach ($document->items as $idx => $row)
                <tr>
                    <td class="text-center align-top br">
                        {{ $idx + 1 }}
                    </td>
                    <td class="text-left br text-upp">
                        {{ $row->item->internal_id }}</td>
                    <td class="text-left br text-upp">
                        @php
                            if ($row->name_product_pdf) {
                                $description = strtoupper($row->name_product_pdf);
                            } else {
                                $description = strtoupper($row->item->description);
                            }
                        @endphp
                        {!! $description !!}
                        @isset($row->item->lots)
                            @foreach ($row->item->lots as $lot)
                                @if (isset($lot->has_sale) && $lot->has_sale)
                                    <span style="font-size: 9px">
                                        {{ $lot->series }}
                                        @if (!$loop->last)
                                            -
                                        @endif
                                    </span>
                                @endif
                            @endforeach
                        @endisset
                        @if ($row->attributes)
                            @foreach ($row->attributes as $attr)
                                @if ($attr->attribute_type_id === '5032')
                                    @php
                                        $total_weight += $attr->value * $row->quantity;
                                    @endphp
                                @endif
                                <br /><span style="font-size: 9px">{!! $attr->description !!} :
                                    {{ $attr->value }}</span>
                            @endforeach
                        @endif
                        @if ($row->item->is_set == 1)
                            <br>
                            @inject('itemSet', 'App\Services\ItemSetService')
                            {{ join('-', $itemSet->getItemsSet($row->item_id)) }}
                        @endif
                    </td>
                    <td class="text-center align-top br">
                        {{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="text-center align-top br">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>


                    <td class="text-right align-top br">
                        {{ number_format($row->unit_price, 2) }}
                    </td>

                    <td class="text-right align-top br">
                        {{ number_format($row->total, 2) }}</td>
                </tr>
            @endforeach

            @for ($idx = 0; $idx < $rest_items_count; $idx++)
                <tr>

                    <td class="text-center align-top br">
                        <br>
                    </td>

                    </td>
                    <td class="text-left br text-upp">


                    </td>
                    <td class="text-center align-top br"></td>
                    <td class="text-center align-top br">


                    <td class="text-right align-top br"></td>

                    <td class="text-right align-top br"></td>
                </tr>
                <tr>
                    <td class="br"></td>
                    <td class="br"></td>
                    <td class="br"></td>
                    <td class="br"></td>
                    <td class="br"></td>
                    <td class="br"></td>
                </tr>
            @endfor

            {{-- <tr>
                <td class="p-1 text-left align-top desc cell-solid" colspan="3"><strong>
                        Vendedor:</strong>
                    @if ($document->seller)
                        {{ $document->seller->name }}
                    @else
                        {{ $document->user->name }}
                    @endif
                </td>
                <td class="p-1 text-left align-top desc cell-solid font-bold">
                    SON:
                    @foreach (array_reverse((array) $document->legends) as $row)
                        @if ($row->code == '1000')
                            {{ $row->value }} {{ $document->currency_type->description }}
                        @else
                            {{ $row->code }}: {{ $row->value }}
                        @endif
                    @endforeach
                </td>
                <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                    OP. GRAVADA {{ $document->currency_type->symbol }}
                </td>
                <td class="p-1 text-right align-top desc cell-solid font-bold">
                    {{ number_format($document->total_taxed, 2) }}</td>
            </tr>

            <tr>
                <td class="p-1 text-left align-top desc cell-solid" colspan="3" rowspan="6">
                    @php
                        $total_packages = $document->items()->sum('quantity');

                    @endphp


                    @if (!empty($paymentDetailed))
                        @foreach ($paymentDetailed as $detailed)
                            <strong> {{ isset($paymentDetailed['PAGOS']) ? 'Pagos:' : 'Cuotas:' }}</strong>
                            <br>
                            @foreach ($detailed as $row)
                                {{ $row['description'] }} -
                                {{ $row['reference'] }}
                                {{ $row['symbol'] }}
                                {{ $row['amount'] }}
                                <br>
                            @endforeach
                        @endforeach
                        <br>
                    @endif
                    <strong> Total bultos:</strong>
                    @if ((int) $total_packages != $total_packages)
                        {{ $total_packages }}
                    @else
                        {{ number_format($total_packages, 0) }}
                    @endif
                    <br>

                    <strong> Total Peso:</strong>
                    {{ $total_weight }} KG
                    <br>

                    <strong> Observación:</strong>
                    @foreach ($document->additional_information as $information)
                        @if ($information)
                            {{ $information }} <br>
                        @endif
                    @endforeach

                    <br>
                </td>
                <td class="p-1 text-center align-top desc cell-solid " rowspan="6">

                    <img src="data:image/png;base64, {{ $document->qr }}" class="p-0 m-0" style="width: 120px;" /><br>
                    Código Hash: {{ $document->hash }}

                </td>

                <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                    OP. INAFECTAS {{ $document->currency_type->symbol }}
                </td>
                <td class="p-1 text-right align-top desc cell-solid font-bold">
                    {{ number_format($document->total_unaffected, 2) }}</td>
            </tr>


            <tr>
                <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                    OP. EXONERADAS {{ $document->currency_type->symbol }}
                </td>
                <td class="p-1 text-right align-top desc cell-solid font-bold">
                    {{ number_format($document->total_exonerated, 2) }}</td>
            </tr>

            <tr>
                <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                    OP. GRATUITAS {{ $document->currency_type->symbol }}
                </td>
                <td class="p-1 text-right align-top desc cell-solid font-bold">
                    {{ number_format($document->total_free, 2) }}</td>
            </tr>
            <tr>
                <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                    TOTAL DCTOS. {{ $document->currency_type->symbol }}
                </td>
                <td class="p-1 text-right align-top desc cell-solid font-bold">
                    {{ number_format($document->total_discount, 2) }}</td>
            </tr>
            <tr>

                <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                    I.G.V. {{ $document->currency_type->symbol }}
                </td>
                <td class="p-1 text-right align-top desc cell-solid font-bold">
                    {{ number_format($document->total_igv, 2) }}</td>
            </tr>
            <tr>
                <td class="p-1 text-right align-top desc cell-solid font-bold" colspan="2">
                    TOTAL A PAGAR. {{ $document->currency_type->symbol }}
                </td>
                <td class="p-1 text-right align-top desc cell-solid font-bold">
                    {{ number_format($document->total, 2) }}</td>
            </tr> --}}
        </tbody>

    </table>
                @foreach ($document->additional_information as $information)
                    @if ($information)
                        @if ($loop->first)
                            <strong>Observación:</strong>
                        @endif
                        
                            @if (\App\CoreFacturalo\Helpers\Template\TemplateHelper::canShowNewLineOnObservation())
                                {!! \App\CoreFacturalo\Helpers\Template\TemplateHelper::SetHtmlTag($information) !!}
                            @else
                                {{ $information }}
                            @endif
                        
                    @endif
                @endforeach
    <div>
        @php
            $value = \App\CoreFacturalo\Helpers\Number\NumberLetter::convertToLetter($document->total);
        @endphp
        <span class="font-bold text-upp font-sm">SON:{{ $value }}</span>
    </div>
    <div>
        <div style="float:left;width:20%">
            <div class="border-radius border-box text-center" >
                <img src="data:image/png;base64, {{ $document->qr }}"style="height: 90px;" />
                <p class="font-xs m-0 p-0">
                    {{ $document->hash }}
                </p>
            </div>
        </div>
        <div style="float:left;width:2%">
            <br>
        </div>

        <div style="float:left;width:46%">
            <div class="border-radius border-box p-2">
                <table class="w-100">
                    <tbody>
                        <tr>
                            <td class="font-bold text-upp text-center" colspan="2">
                                {{ $company->name }}
                            </td>
                        </tr>
                        @foreach ($accounts as $account)
                            <tr>
                                <td class="text-upp font-bold bb" colspan="2">
                                    {{ $account->bank->description }} - {{ $account->currency_type->description }}
                                </td>
                            </tr>
                            <tr>
                                <td>

                                    <span class="font-bold">N°:</span> {{ $account->number }}

                                </td>
                                <td>
                                    @if ($account->cci)
                                        <span class="font-bold">CCI:</span> {{ $account->cci }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
        <div style="float:left;width:2%"><br></div>
        <div style="float:left;width:30%">
            <table class="w-100 border-box">
                <tr>
                    <td class="bg-black font-lg font-bold p-2" width="60%">
                        TOTAL OP. GRAV.
                    </td>
                    <td class="bg-black font-lg font-bold p-2" width="10%">{{ $document->currency_type->symbol }}
                    </td>
                    <td width="30%" class=" font-lg font-bold p-2 bb text-end">{{ $document->total_value }}</td>
                </tr>
                <tr>
                    <td class="bg-black font-lg font-bold p-2" width="60%">
                        ICBPER
                    </td>
                    <td class="bg-black font-lg font-bold p-2" width="10%">{{ $document->currency_type->symbol }}
                    </td>
                    <td width="30%" class=" font-lg font-bold p-2 bb text-end">
                        {{ $document->total_plastic_bag_taxes }}</td>
                </tr>
                <tr>
                    <td class="bg-black font-lg font-bold p-2" width="60%">
                        TOTAL IGV 18%
                    </td>
                    <td class="bg-black font-lg font-bold p-2" width="10%">{{ $document->currency_type->symbol }}
                    </td>
                    <td width="30%" class=" font-lg font-bold p-2 bb text-end">{{ $document->total_taxes }}</td>
                </tr>
                <tr>
                    <td class="bg-black font-lg font-bold p-2" width="60%">
                        TOTAL A PAGAR
                    </td>
                    <td class="bg-black font-lg font-bold p-2" width="10%">{{ $document->currency_type->symbol }}
                    </td>
                    <td width="30%" class=" font-lg font-bold p-2 bb text-end">{{ number_format($document->total, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
    {{-- @if ($document != null)

        <table class="full-width border-box my-2">
            <tr>
                <th class="p-1" width="25%">Banco</th>
                <th class="p-1">Moneda</th>
                <th class="p-1" width="30%">Código de Cuenta Interbancaria</th>
                <th class="p-1" width="25%">Código de Cuenta</th>
            </tr>
            @foreach ($accounts as $account)
                <tr>
                    <td class="text-center">{{ $account->bank->description }}</td>
                    <td class="text-center text-upp">{{ $account->currency_type->description }}</td>
                    <td class="text-center">
                        @if ($account->cci)
                            {{ $account->cci }}
                        @endif
                    </td>
                    <td class="text-center">{{ $account->number }}</td>
                </tr>
            @endforeach
        </table>
    @endif --}}
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

    <table class="full-width border-box my-2">


        <tr>
            @if ($document->detraction)
                <td width="80px">

                <td width="80px">Leyenda: Operación sujeta al Sistema de Pago de Obligaciones Tributarias con el
                    Gobierno Central</td>
            @endif
    </table>

    <table class="full-width border-box my-2">
        <tr>
            @if ($document->detraction)
                <td width="210x">Bien o Servicio Sujeto a detracción</td>
                <td width="8px">:</td>
                @inject('detractionType', 'App\Services\DetractionTypeService')
                <td width="220px">{{ $document->detraction->detraction_type_id }}
                    - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id) }}
                </td>
            @endif

            @if ($document->detraction)
                <td width="70px">Nro. Cta detracciones</td>
                <td width="8px">:</td>
                <td>{{ $document->detraction->bank_account }}</td>
            @endif
        </tr>

        <tr>

            @if ($document->detraction)
                <td width="210x">Porcentaje Detracción</td>
                <td width="8px">:</td>
                <td>{{ $document->detraction->percentage }}%</td>
            @endif

            @if ($document->detraction)
                <td width="70px">Monto detracción</td>
                <td width="8px">:</td>
                <td>S/ {{ $document->detraction->amount }}</td>
            @endif


        </tr>

        <tr>
            @if ($document->detraction)
                <td width="210x">Medio de pago</td>
                <td width="8px">:</td>
                <td width="220px">
                    {{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id) }}
                </td>
            @endif

            @if ($document->detraction)
                <td width="120px"></td>
                <td width="8px"></td>
                <td width="20px">

                </td>
            @endif
        </tr>

        @if ($document->detraction && $invoice->operation_type_id == '1004')
            <tr>
                <td colspan="4"><strong>Detalle - Servicios de transporte de carga</strong></td>
            </tr>
            <tr>
                <td class="align-top">Ubigeo origen</td>
                <td>:</td>
                <td>{{ $document->detraction->origin_location_id[2] }}</td>

                <td width="120px">Dirección origen</td>
                <td width="8px">:</td>
                <td>{{ $document->detraction->origin_address }}</td>
            </tr>
            <tr>
                <td class="align-top">Ubigeo destino</td>
                <td>:</td>
                <td>{{ $document->detraction->delivery_location_id[2] }}</td>

                <td width="120px">Dirección destino</td>
                <td width="8px">:</td>
                <td>{{ $document->detraction->delivery_address }}</td>
            </tr>
            <tr>
                <td class="align-top" width="170px">Valor referencial servicio de transporte</td>
                <td>:</td>
                <td>{{ $document->detraction->reference_value_service }}</td>

                <td width="170px">Valor referencia carga efectiva</td>
                <td width="8px">:</td>
                <td>{{ $document->detraction->reference_value_effective_load }}</td>
            </tr>
            <tr>
                <td class="align-top">Valor referencial carga útil</td>
                <td>:</td>
                <td>{{ $document->detraction->reference_value_payload }}</td>

                <td width="120px">Detalle del viaje</td>
                <td width="8px">:</td>
                <td>{{ $document->detraction->trip_detail }}</td>
            </tr>
        @endif

    </table>
    

</body>

</html>
