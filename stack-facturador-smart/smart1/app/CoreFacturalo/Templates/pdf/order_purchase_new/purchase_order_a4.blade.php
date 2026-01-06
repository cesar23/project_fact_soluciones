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
    $logo_purchase_order = "storage/uploads/header_images/{$configurations->order_purchase_logo}";
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $tittle = $document->prefix . '-' . str_pad($document->id, 8, '0', STR_PAD_LEFT);
    $bank_account = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
@endphp
<html>

<head>
    {{-- <title>{{ $tittle }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>
    <div class="full-width text-center">
        <div class="company_logo_box">
            <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
        </div>
    </div>
    <div class="text-center">
        <h3>ORDEN DE COMPRA {{ $tittle }}</h3>
    </div>
    <table class="full_width">
        <tr>
            <td width="30%">
                DE
            </td>
            <td width="5%">
                :
            </td>
            <td width="65%">
                {{ $company_name }}
            </td>
        </tr>
        <tr>
            <td width="30%">
                RAZON SOCIAL
            </td>
            <td width="5%">
                :
            </td>
            <td width="65%">
                {{ $supplier->name }}
            </td>
        </tr>
        <tr>
            <td width="30%">
                RUC
            </td>
            <td width="5%">
                :
            </td>
            <td width="65%">
                {{ $supplier->number }}
            </td>
        </tr>
        <tr>
            <td width="30%">
                ASUNTO
            </td>
            <td width="5%">
                :
            </td>
            <td width="65%">
                {{ removePTag($document->client_internal_id) }}

            </td>
        </tr>
        <tr>
            <td width="30%">
                ÁREA SOLICITANTE
            </td>
            <td width="5%">
                :
            </td>
            <td width="65%">
                {{ removePTag($document->sale_opportunity_number) }}
            </td>
        </tr>
        <tr>
            <td width="30%">
                FECHA EMISION
            </td>
            <td width="5%">
                :
            </td>
            <td width="65%">
                {{ $document->date_of_issue->format('Y-m-d') }}
            </td>
        </tr>
        <tr>
            <td width="30%">
                FECHA ENTREGA
            </td>
            <td width="5%">
                :
            </td>
            <td width="65%">
                {{ $document->date_of_due->format('Y-m-d') }}
            </td>

        </tr>
    </table>


    <div class="full-width">
        Por medio de la presente solicitamos la venta de los siguientes productos:
    </div>



    <table class="full-width mt-10 mb-10">
        <thead class="">
            <tr class="bg-primary">
                <th class="text-white border-box text-center py-2" width="8%">ITEM</th>
                <th class="text-white border-box text-center py-2" width="10%">CÓDIGO</th>
                <th class="text-white border-box text-center py-2" width="8%">CANT.</th>
                <th class="text-white border-box text-center py-2" width="8%">U.M.</th>
                <th class="text-white border-box text-center py-2">DESCRIPCION</th>
                <th class="text-white border-box text-center py-2" width="12%">PRECIO UNIT

                </th>
                <th class="text-white border-box text-center py-2" width="12%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $idx => $row)
                <tr>
                    <td class="text-center align-top border-box p-1">{{ $idx + 1 }}</td>
                    @php
                        $item_db = \App\Models\Tenant\Item::find($row->item_id);
                    @endphp
                    <td class="text-center align-top border-box p-1">{{ $item_db->internal_id }}</td>
                    <td class="text-center align-top border-box p-1">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                    <td class="text-center align-top border-box p-1">
                        {{ symbol_or_code(symbol_or_code($row->item->unit_type_id)) }}</td>
                    <td class="text-left border-box p-1">
                        {!! $row->item->description !!}
                        @if ($row->attributes)
                            @foreach ($row->attributes as $attr)
                                <br /><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                            @endforeach
                        @endif
                        @if ($row->discounts)
                            @foreach ($row->discounts as $dtos)
                                <br /><span style="font-size: 9px">{{ $dtos->factor * 100 }}%
                                    {{ $dtos->description }}</span>
                            @endforeach
                        @endif
                    </td>
                    <td class="text-right align-top border-box p-1">{{ number_format($row->unit_price, 2) }}</td>

                    <td class="text-right align-top border-box p-1">{{ number_format($row->total, 2) }}</td>
                </tr>
            @endforeach
            @if ($document->total_exportation > 0)
                <tr>
                    <td colspan="6" class="text-right font-bold">Op. Exportación:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold border-box">{{ number_format($document->total_exportation, 2) }}
                    </td>
                </tr>
            @endif
            @if ($document->total_free > 0)
                <tr>
                    <td colspan="6" class="text-right font-bold">Op. Gratuitas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold border-box">{{ number_format($document->total_free, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_unaffected > 0)
                <tr>
                    <td colspan="6" class="text-right font-bold">Op. Inafectas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold border-box">{{ number_format($document->total_unaffected, 2) }}
                    </td>
                </tr>
            @endif
            @if ($document->total_exonerated > 0)
                <tr>
                    <td colspan="6" class="text-right font-bold">Op. Exoneradas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold border-box">{{ number_format($document->total_exonerated, 2) }}
                    </td>
                </tr>
            @endif
            @if ($document->total_taxed > 0)
                <tr>
                    <td colspan="6" class="text-right font-bold">Op. Gravadas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold border-box">{{ number_format($document->total_taxed, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_discount > 0)
                <tr>
                    <td colspan="6" class="text-right font-bold">
                        {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento total' }}:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold border-box">{{ number_format($document->total_discount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="6" class="text-right font-bold">IGV: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold border-box">{{ number_format($document->total_igv, 2) }}</td>
            </tr>
            <tr>
                <td colspan="6" class="text-right font-bold">Total a pagar: {{ $document->currency_type->symbol }}
                </td>
                <td class="text-right font-bold border-box">{{ number_format($document->total, 2) }}</td>
            </tr>
        </tbody>
    </table>
    <div class="w-70">
        <div class="full-width">
            <div class="full-width">
                <div class="title">
                    DATOS DE LA FACTURACIÓN:
                </div>
            </div>
            <table>
                <tr>
                    <td width="40%">
                        RAZON SOCIAL
                    </td>
                    <td width="60%">
                        : {{ $company_name }}
                    </td>
                </tr>
                <tr>
                    <td width="40%">
                        RUC
                    </td>
                    <td width="60%">
                        : {{ $company->number }}
                    </td>
                </tr>
                <tr>
                    <td width="40%">
                        DIRECCION
                    </td>
                    <td width="60%">
                        : {{ $establishment__->address }}
                    </td>
                </tr>
            </table>
        </div>
        <div class="full-width mt-2">
            <div class="full-width">
                <div class="title">
                    N° DE CTA PROVEEDOR:
                </div>
            </div>
            <div class="full-width">
                {{ $document->supplier_relation->bank_name }} -
                {{ $document->supplier_relation->bank_account_number }}
            </div>

        </div>
        <div class="full-width mt-2">
            <div class="full-width">
                <div>
                    <span class="title">
                        CONDICIONES DE PAGO:
                    </span>
                    <span style="margin-left: 25px;">
                        CONTADO
                    </span>
                </div>
            </div>
        </div>
        <div class="full-width mt-2">
            <div class="full-width">
                <div>
                    <span class="title">
                        DIRECCION DE ENTREGA:
                    </span>
                    <span style="margin-left: 25px;">
                        {{ $establishment__->address }}
                    </span>
                </div>
            </div>
        </div>
        <div class="full-width mt-2">
            <div class="full-width">
                <div class="title">
                    DOCUMENTOS PARA LA RECEPCION:
                </div>
            </div>
            <div class="full-width mt-1">
                <div>
                    <div class="p-3">
                        <span>
                            - ORDEN DE COMPRA
                        </span>
                    </div>
                    <div class="p-3">
                        <span>
                            - FACTURA
                        </span>
                    </div>
                    <div class="p-3">
                        <span>
                            - GUIA
                        </span>
                    </div>
                    <div class="p-3">
                        <span>
                            - CERTIFICADO DE CALIDAD
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @if ($document->observation)
    <div class="full-width mt-2">
        <div class="full-width">
            <div class="title">
                OBSERVACIONES:
            </div>
        </div>
        <div class="full-width">
            {!! $document->observation !!}
        </div>

    </div>
    @endif
    <div class="full-width">
        <div class="w-45 float-left">
            <div class="full-width mt-2">
                <div class="full-width">
                    <div class="title">
                        HORARIO DE ATENCIÓN:
                    </div>
                </div>
                <div class="full-width mt-1">
                    <div class="p-3">
                        LUNES A VIERNES
                    </div>
                    <div class="p-3">
                        9:00 AM - 13:00 PM
                    </div>
                </div>
            </div>

        </div>
        <div class="w-50 float-left">
            <div class="full-width mt-2 text-right">
                @if ($configurations->order_purchase_logo)
                    <div class="centered">
                        <img src="data:{{ mime_content_type(public_path("{$logo_purchase_order}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo_purchase_order}"))) }}"
                            alt="anulado" class="order-1" style="width: 150px; height: 150px;">
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>

</html>
