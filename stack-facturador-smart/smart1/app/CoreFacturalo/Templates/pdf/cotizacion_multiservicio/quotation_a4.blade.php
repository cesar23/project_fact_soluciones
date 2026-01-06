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
    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

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
    $customer = $document->customer;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $accounts = \App\Models\Tenant\BankAccount::all();
    $tittle = $document->prefix . '-' . str_pad($document->number ?? $document->id, 8, '0', STR_PAD_LEFT);

    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();
    $documment_columns = \App\Models\Tenant\DocumentColumn::where('is_visible', true)->where('type','COT')
        ->orderBy('column_order', 'asc')
        ->get();

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }
    $total_discount_items = 0;
@endphp
<html>

<head>
    {{-- <title>{{ $tittle }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>

    <div class="header" style="margin: 0;">

        <div style="float:left;width:38%;text-align: left;">
            @if ($company->logo)
                <div style="width: 40%;text-align: left; float: left;height: 60px;">
                    <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                        alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                </div>
            @else
                <br>
            @endif

            <div style="width: 60%;text-align: left;float: left;">
                <h4 style="margin: 0px !important;">{{ $company->name }}</h4>
                <div style="margin: 0px !important;" class="desc">RUC: {{ $company->number }}</div>
                <div style="margin: 0px !important;" class="desc">Tienda:
                    {{ $establishment->address !== '-' ? $establishment->address : '' }}
                    {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                    {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                    {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                </div>
            </div>
        </div>
        <div style="float:left;width:2%;">
            <br>
        </div>

        <div style="float:left;width:60%; border-bottom: 1px solid #333; padding-bottom: 7px;">
            <div
                style="
                    color:#AEC49A;
                    font-size: 23px; font-weight: bold; letter-spacing: normal; line-height: 18px; text-align: center;">
                COTIZACIÓN DE RESPUESTOS
            </div>
            <div
                style="
                    color:#AEC49A;
                
                margin-top: 7px;
                font-size: 23px; font-weight: bold; letter-spacing: normal; line-height: 18px; text-align: center;">
                Y DE SERVICIOS
            </div>
        </div>
    </div>
    <table class="full-width mt-2">
        <tr>
            <td width="15%"><strong>CLIENTE:</strong></td>
            <td width="45%">{{ $customer->name }}</td>
            <td width="25%"><strong>CÓDIGO DE COTIZACIÓN:</strong></td>
            <td width="15%">{{ $document->prefix }} - {{ $document->number }}</td>
        </tr>
        <tr>
            <td><strong>{{ strtoupper($customer->identity_document_type->description) }}:</strong></td>
            <td>{{ $customer->number }}</td>
            <td width="25%"><strong>FECHA DE EMISIÓN:</strong></td>
            <td width="15%">{{ $document->date_of_issue->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td>
                <strong>DIRECCIÓN:</strong>
            </td>
            <td>
                {{ $customer->address }}
            </td>
            <td width="25%"><strong>FECHA DE VENCIMIENTO:</strong></td>
            <td width="15%">{{ $document->date_of_due }}</td>
        </tr>

        @if ($document->payment_method_type)
            <tr>
                <td class="align-top"><strong>T. PAGO:</strong></td>
                <td colspan="">
                    {{ $document->payment_method_type->description }}
                </td>
                @if ($document->sale_opportunity)
                    <td width="25%"><strong>O. VENTA:</strong></td>
                    <td width="15%">{{ $document->sale_opportunity->number_full }}</td>
                @endif
            </tr>
        @endif

        <tr>
            <td class="align-top"><strong>CONDICIÓN DE PAGO:</strong></td>
            <td>
                {{ optional($document->payment_condition)->description ?? 'Contado' }}
            </td>
            //header_image
            @php
                $header_image = $configuration->header_image;
            @endphp
            @if ($header_image)
                <td colspan="2">
                    <img style="width: 45%" height="40px" src="data:{{mime_content_type(public_path("storage/uploads/header_images/{$configuration->header_image}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/header_images/{$configuration->header_image}")))}}" alt="image" class="">
                </td>
            @else
                <td colspan="2">
                </td>
            @endif
        </tr>

    </table>




    <table class="full-width mt-2">
        <thead class="">
            <tr class="">
                <th class="bg-green text-center border-top-bottom  desc1  py-2 rounded-t">ITEM
                </th>
                <th class="bg-green text-center border-top-bottom desc1  py-2">CATEGORÍA
                </th>
                <th class="bg-green text-center border-top-bottom desc1  py-2">CODIGO
                </th>
                <th class="bg-green text-center border-top-bottom  desc1  py-2 px-2">
                    DESCRIPCIÓN REPUESTO / SERVICIO</th>
                <th class="bg-green text-center border-top-bottom desc1 py-2 px-2">MARCA</th>
                <th class="bg-green text-center border-top-bottom desc1 py-2 px-2">UN. MEDIDA</th>
                <th class="bg-green text-center border-top-bottom desc1 py-2 px-2">CANTIDAD</th>
                <th class="bg-green text-center border-top-bottom desc1 py-2 px-2">PRECIO UNITARIO (inc. IGV)</th>
                <th class="bg-green text-center border-top-bottom desc1 py-2 px-2">DSCTO X UNIDAD</th>
                <th class="bg-green text-center border-top-bottom desc1 py-2 px-2">PRECIO TOTAL (inc. IGV)</th>
            </tr>
        </thead>
        @php
            $cycle = 30;
            $count_items = count($document->items);
            if ($count_items > 7) {
                $cycle = 0;
            } else {
                $cycle = 30 - $count_items;
            }

        @endphp
        <tbody>
            @foreach ($document->items as $idx => $row)
                <tr>
                    <td class="text-center desc">{{ $idx + 1 }}</td>
                    <td class="desc">{{ $row->item->category }}</td>
                    <td class="desc">{{ $row->item->internal_id }}</td>
                    <td class="desc">{{ $row->item->description }}</td>
                    <td class="desc">{{ $row->item->brand }}</td>
                    <td class="text-center desc">{{ $row->item->unit_type_id }}</td>
                    <td class="text-end desc">{{ number_format($row->quantity, 2) }}</td>
                    <td class="text-end desc">{{ number_format($row->unit_price, 2) }}</td>
                    <td class="text-end desc">{{ number_format($row->total_discount, 2) }}</td>
                    <td class="text-end desc">{{ number_format($row->total, 2) }}</td>

                </tr>
            @endforeach

            @for ($i = 0; $i < $cycle; $i++)
                <tr>
                    <td class="text-center">
                        <br>
                    </td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>
                    <td class="text-center"></td>

                </tr>
            @endfor



        </tbody>
    </table>

    <table class="full-width mt-2" style="border-bottom: 1px solid #333;">
        <thead class="">
            <tr class="">
                <th class="bg-green text-center border-top-bottom  desc1  py-2 rounded-t">VALOR DE VENTA NETO
                </th>
                <th class="bg-green text-center border-top-bottom desc1  py-2">I.G.V
                </th>
                <th class="bg-green text-center border-top-bottom desc1  py-2">PRECIO DE VENTA NETO
                </th>
                <th class="bg-green text-center border-top-bottom  desc1  py-2 px-2">
                    DESCUENTO TOTAL</th>
                <th class="bg-green text-center border-top-bottom desc1  py-2">
                    TOTAL A PAGAR</th>
                <th class="bg-green text-center border-top-bottom desc1  py-2">
                    MONEDA</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">
                    {{ number_format($document->total_value, 2) }}
                </td>
                <td class="text-center">
                    {{ number_format($document->total_igv, 2) }}
                </td>
                <td class="text-center">
                    {{ number_format($document->subtotal, 2) }}
                </td>
                <td class="text-center">
                    {{ number_format($document->total_discount, 2) }}
                </td>
                <td class="text-center">
                    {{ number_format($document->total, 2) }}
                </td>
                <td class="text-center">
                    {{ $document->currency_type->description }}
                </td>

            </tr>
        </tbody>
    </table>
    <div>
        Son:
        {{ App\CoreFacturalo\Helpers\Number\NumberLetter::convertToLetter($document->total, $document->currency_type->description, false, 2) }}
    </div>
    <table class="full-width mt-3">
        <thead>
            <tr>
                <th colspan="8" class="text-left desc">
                    INFORMACIÓN DEL VEHÍCULO:
                </th>
            </tr>
            <tr>
                <th class="desc border-bottom">
                    PLACA
                </th>
                <th class="desc border-bottom">
                    EQUIPO
                </th>
                <th class="desc border-bottom">
                    MODELO
                </th>
                <th class="desc border-bottom">
                    AÑO
                </th>
                <th class="desc border-bottom">
                    MOTOR
                </th>
                <th class="desc border-bottom">
                    CAJA
                </th>
                <th class="desc border-bottom">
                    CHASIS
                </th>
                <th class="desc border-bottom">
                    TRACCION
                </th>
                <th class="desc border-bottom">
                    KMS
                </th>
                <th class="desc border-bottom">
                    HRS
                </th>
                <th class="desc border-bottom">
                    OTROS
                </th>
            </tr>
        </thead>
    </table>
    <table class="full-width mt-3">
        <thead>
            <tr>
                <th colspan="8" class="text-left desc">
                    INFORMACIÓN ADICIONAL:
                </th>
            </tr>

        </thead>
        <tbody>
            <tr>
                <td colspan="8">
                    {{ $document->additional_information }}
                </td>
            </tr>
        </tbody>
    </table>

    <table class="full-width mt-3">
        <thead>
            <tr>
                <th colspan="4" class="text-left desc">
                    CUENTAS BANCARIAS:
                </th>
            </tr>
            <tr>
                <th class="desc border-bottom">

                </th>
                <th class="desc border-bottom">
                    MONEDA
                </th>
                <th class="desc border-bottom">
                    N° DE CUENTA
                </th>
                <th class="desc border-bottom">
                    CODIGO CUENTA INTERBANCARIA
                </th>

            </tr>
        </thead>
        <tbody>
            @foreach ($accounts as $account)
                <tr>
                    <td class="desc text-center" style="font-weight: bold; font-style: italic;">
                        {{ $account->bank->description }}</td>
                    <td class="desc text-center">{{ $account->currency_type->description }}</td>
                    <td class="desc text-center">{{ $account->number }}</td>
                    <td class="desc text-center">{{ $account->cci }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="full-width mt-4">

        <tbody>
            <tr>
                <td colspan="2" class="desc">
                    Atentamente:
                </td>
            </tr>
            <tr>
                <td class="desc" colspan="2">
                    <strong>
                        {{ $document->user->name }}
                    </strong>
                </td>
            </tr>
            <tr>
                <td class="desc" colspan="2">
                    <strong>ASESOR COMERCIAL</strong>
                </td>
            </tr>
            <tr>
                <td class="desc" width="15%">
                    Correo electrónico:
                </td>
                <td class="desc">
                    {{ $document->user->email }}
                </td>
            </tr>
            <tr>
                <td class="desc">
                    Teléfono:
                </td>
                <td class="desc">
                    {{ $document->user->phone }}
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
