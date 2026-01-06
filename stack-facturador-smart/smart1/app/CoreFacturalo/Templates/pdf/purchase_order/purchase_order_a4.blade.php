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

    $supplier = $document->supplier;
    $supplier_db = \App\Models\Tenant\Person::select('contact')->find($document->supplier_id);
    $contact = $supplier_db->contact;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $tittle = $document->number_full;
@endphp
<html>

<head>
    {{-- <title>{{ $tittle }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>
    <div class="full-width">
        @if ($company->logo)
            <div width="70%" style="float: left; height: 80px;">
                <div>
                    <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                        alt="{{ $company->name }}" class="company_logo" style="width:100%; height:100%;">
                </div>
            </div>
        @else
            <div width="70%" style="float: left;">
                {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px"> --}}
            </div>
        @endif

        <div width="30%" style="float: right;">
            <div class="border-box full-width">
                <div class="p-2">
                    <div class="text-center"><strong>RUC: {{ $company->number }}</strong></div>
                    <div class="text-center"><strong>ORDEN DE {{$document->type == 'goods' ? 'COMPRA' : 'SERVICIO'}}</strong></div>
                    <div class="text-center"><strong>{{ $tittle }}</strong></div>
                </div>
                <table class="full-width">
                    <tr>
                        <td class="border-box" width="30%" style="border-left: none;border-bottom: none;">
                            <strong>FECHA:</strong>
                        </td>
                        <td class="border-box text-center" style="border-right: none;border-bottom: none;">
                            {{ $document->date_of_issue->format('d/m/Y') }}
                        </td>
                    </tr>
                </table>
            </div>

        </div>

    </div>
    <table class="full-width mt-2">
        <tr>
            <td width="10%" class="p-2 bg-grey border-box desc">
                <strong>OBRA:</strong>
            </td>
            <td width="90%" class="p-2 border-box">
                {{ $document->work_description }}
            </td>
        </tr>
    </table>
    <table class="full-width mt-2 border-box">
        <tr>
            <td width="15%" class="p-1 bg-grey desc">
                <strong>PROVEEDOR:</strong>
            </td>
            <td colspan="3" width="85%" class="p-1 desc">
                {{ $supplier->name }}
            </td>
        </tr>
        <tr>
            <td width="15%" class="p-1 bg-grey desc">
                <strong>RUC:</strong>
            </td>
            <td colspan="3" width="85%" class="p-1 desc">
                {{ $supplier->number }}
            </td>
        </tr>
        <tr>
            <td width="15%" class="p-1 bg-grey desc">
                <strong>DOMICILIO:</strong>
            </td>
            <td colspan="3" width="85%" class="p-1 desc">
                {{ $supplier->address }}
                {{ $supplier->district_id !== '-' ? '' . $supplier->district->description : '' }}
                {{ $supplier->province_id !== '-' ? ', ' . $supplier->province->description : '' }}
                {{ $supplier->department_id !== '-' ? ', ' . $supplier->department->description : '' }}
            </td>
        </tr>
        <tr>
            <td width="15%" class="p-1 bg-grey desc">
                <strong>COTIZACIÓN:</strong>
            </td>
            <td colspan="3" width="85%" class="p-1 desc">
                {{ $document->purchase_quotation }}
            </td>
        </tr>
        <tr>
            <td width="15%" class="p-1 bg-grey desc">
                <strong>CONTACTO:</strong>
            </td>
            <td width="40%" class="p-1 desc">
                @isset($contact)
                    {{ $contact->full_name }}
                @else
                    {{ $supplier->name }}
                @endisset
            </td>
            <td width="20%" class="p-1 bg-grey desc">
                <strong>FORMA DE PAGO:</strong>
            </td>
            <td width="25%" class="p-1 desc">
                {{ $document->payment_method_type->description }}
            </td>
        </tr>
        <tr>
            <td width="15%" class="p-1 bg-grey desc">
                <strong>CORREO:</strong>
            </td>
            <td width="40%" class="p-1 desc">
                {{ $document->mail_purchase_quotation }}
            </td>
            <td width="20%" class="p-1 bg-grey desc">
                <strong>MONEDA:</strong>
            </td>
            <td width="25%" class="p-1 desc">
                {{ $document->currency_type->description }}
            </td>
        </tr>
    </table>
    @php
        $cycle = 20;
        $count_items = count($document->items);
        if ($count_items > 7) {
            $cycle = 0;
        } else {
            $cycle = 20 - $count_items;
        }

    @endphp

    <table class="full-width mt-2   ">
        <thead>
            <tr>
                <th class="border-box bg-grey p-1 desc">
                    ITEM
                </th>
                <th class="border-box bg-grey p-1 desc">
                    DESCRIPCIÓN
                </th>
                <th class="border-box bg-grey p-1 desc">
                    UNIDAD
                </th>
                <th class="border-box bg-grey p-1 desc">
                    CANTIDAD
                </th>
                <th class="border-box bg-grey p-1 desc">
                    P. UNITARIO
                </th>

                <th class="border-box bg-grey p-1 desc">
                    TOTAL
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $idx => $row)
                <tr>
                    <td class="border-box text-center desc">
                        {{ $idx + 1 < 10 ? '0' . ($idx + 1) : $idx + 1 }}
                    </td>
                    <td class="border-box desc">
                        {{ $row->item->description }}
                    </td>
                    <td class="border-box text-center desc">
                        {{ symbol_or_code($row->item->unit_type_id) }}
                    </td>
                    <td class="border-box text-end desc">
                        {{ $row->quantity }}
                    </td>
                    <td class="border-box text-end desc">
                        {{ $row->unit_price }}
                    </td>
                    <td class="border-box text-end desc">
                        {{ $row->total }}
                    </td>
                </tr>
            @endforeach
            @for ($i = 0; $i < $cycle; $i++)
                <tr>
                    <td class="border-box p-1 desc">
                        <br>
                    </td>
                    <td class="border-box"></td>
                    <td class="border-box"></td>
                    <td class="border-box"></td>
                    <td class="border-box"></td>
                    <td class="border-box"></td>
                </tr>
            @endfor
            <tr>
                <td colspan="3" class="border-box p-1 desc">
                    Son: {{ \App\CoreFacturalo\Helpers\Number\NumberLetter::convertToLetter($document->total) }}
                </td>
                <td colspan="2" class="border-box p-1 bg-grey text-end desc">
                    <strong>SUBTOTAL</strong>
                </td>
                <td class="border-box p-1 text-end desc">
                    {{ $document->total_taxed }}
                </td>
            </tr>
            <tr>
                <td colspan="3" class=""></td>
                <td colspan="2" class="border-box p-1 bg-grey text-end desc">
                    <strong>IGV</strong>
                </td>
                <td class="border-box p-1 text-end desc">
                    {{ $document->total_igv }}
                </td>
            </tr>
            <tr>
                <td colspan="3" class=""></td>
                <td colspan="2" class="border-box p-1 bg-grey text-end desc">
                    <strong>TOTAL</strong>
                </td>
                <td class="border-box p-1 text-end desc">
                    {{ $document->total }}
                </td>
            </tr>
        </tbody>
    </table>

    <table class="full-width ">
        <tr>
            <td width="70%" valign="top">
                <table class="full-width border-box">
                    <tr>
                        <td width="25%" class="p-1 bg-grey desc">
                            <strong>BANCO:</strong>
                        </td>
                        <td width="75%" class="p-1 desc">
                            {{ $document->supplier->bank_name }}
                        </td>
                    </tr>
                    <tr>
                        <td width="25%" class="p-1 bg-grey desc">
                            <strong>N° DE CUENTA:</strong>
                        </td>
                        <td width="75%" class="p-1 desc">
                            {{ $document->supplier->bank_account_number }}
                        </td>
                    </tr>
                    
                    <tr>
                        <td width="25%" class="p-1 bg-grey desc">
                            <strong>PLAZO DE ENTREGA:</strong>
                        </td>
                        <td width="75%" class="p-1">
                            {{-- {{ $document->date_of_due ? $document->date_of_due->format('d/m/Y') : '' }} --}}
                            {{ $document->limit_date }}
                        </td>
                    </tr>
                    <tr>
                        <td width="25%" class="p-1 bg-grey desc">
                            <strong>LUGAR DE ENTREGA:</strong>
                        </td>
                        <td width="75%" class="p-1 desc">
                            {{ $document->shipping_address }}
                        </td>
                    </tr>
                </table>
            </td>
            <td width="30%" class="p-1" valign="top">
                <table class="full-width border-box">
                    <tr>
                        <td width="100%" class="p-1 bg-grey desc">
                            <strong>OBSERVACIONES: </strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 105px;" valign="top">
                            {!! $document->observation !!}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="full-width mt-2 border-box">
        <tr>
            <td colspan="2" class="bg-grey p-1 desc border-box">
                <strong>CONFIRMACION DEL PROVEEDOR</strong>
            </td>
        </tr>
        <tr>
            <td width="20%" class="p-1 desc">
                <strong>PROVEEDOR:</strong>
            </td>
            <td width="80%" class="p-1 desc">
                {{ $supplier->name }}
            </td>
        </tr>
        <tr>
            <td class="p-1 desc">
                <strong>RUC:</strong>
            </td>
            <td class="p-1 desc">
                {{ $supplier->number }}
            </td>
        </tr>
        <tr>
            <td class="p-1 desc">
                <strong>DOMICILIO:</strong>
            </td>
            <td class="p-1 desc">
                {{ $supplier->address }}
            </td>
        </tr>
        <tr>
            <td class="p-1 desc">
                <strong>CONTACTO:</strong>
            </td>
            <td class="p-1 desc">
                @isset($contact)
                    {{ $contact->full_name }}
                @else
                    {{ $supplier->name }}
                @endisset
            </td>
        </tr>
    </table>

    <table class="full-width mt-2 border-box">
        <tr>
            <td colspan="2" class="p-1 desc border-box bg-grey">
                <strong>FACTURAR A NOMBRE DE:</strong>
            </td>
            <td width="20%" class="p-1 desc">
                <strong>AUTORIZA:</strong>
            </td>
        </tr>
        <tr>
            <td width="20%" class="p-1 desc">
                <strong>RAZÓN SOCIAL:</strong>
            </td>
            <td width="60%" class="p-1 desc br">
                {{ $company_name }}
            </td>
            <td width="20%" class="p-1" rowspan="4">
                {{-- Espacio para firma --}}
            </td>
        </tr>
        <tr>
            <td class="p-1 desc">
                <strong>RUC:</strong>
            </td>
            <td class="p-1 desc br">
                {{ $company->number }}
            </td>
        </tr>
        <tr>
            <td class="p-1 desc">
                <strong>DOMICILIO LEGAL:</strong>
            </td>
            <td class="p-1 desc br">
                {{ $establishment->address }}
                {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
            </td>
        </tr>
        <tr>
            <td class="p-1 desc">
                <strong>CORREO:</strong>
            </td>
            <td class="p-1 desc br">
                {{ $establishment->email }}
            </td>
        </tr>
    </table>

</body>

</html>
