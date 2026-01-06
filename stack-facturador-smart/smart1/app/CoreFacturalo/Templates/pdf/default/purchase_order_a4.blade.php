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
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');

    // Usar number_full que ya maneja la compatibilidad entre registros nuevos y antiguos
    // Debug temporal: verificar valores
    // $debug_info = "Series: {$document->series}, Number: {$document->number}, Prefix: {$document->prefix}, ID: {$document->id}";
    $tittle = $document->number_full;

@endphp
<html>

<head>
    {{-- <title>{{ $tittle }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>
    <table class="full-width">
        <tr>
            @if ($company->logo)
                <td width="20%">
                    <div class="company_logo_box">
                        <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                            alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                    </div>
                </td>
            @else
                <td width="20%">
                    {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px"> --}}
                </td>
            @endif
            <td width="50%" class="pl-3">
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
            <td width="30%" class="border-box py-4 px-2 text-center">
                <h5 class="text-center">Orden de compra</h5>
                <h3 class="text-center">{{ $tittle }}</h3>
            </td>
        </tr>
    </table>
    <table class="full-width mt-5">
        <tr>
            <td width="15%">Proveedor:</td>
            <td width="45%">{{ $supplier->name }}</td>
            <td width="25%">Fecha de emisión:</td>
            <td width="15%">{{ $document->date_of_issue->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td>{{ $supplier->identity_document_type->description }}:</td>
            <td>{{ $supplier->number }}</td>
            @if ($document->date_of_due)
                <td width="25%">Fecha de vencimiento:</td>
                <td width="15%">{{ $document->date_of_due->format('Y-m-d') }}</td>
            @endif
        </tr>
        @if ($supplier->address !== '')
            <tr>
                <td class="align-top">Dirección:</td>
                <td colspan="3">
                    {{ $supplier->address }}
                    {{ $supplier->district_id !== '-' ? ', ' . $supplier->district->description : '' }}
                    {{ $supplier->province_id !== '-' ? ', ' . $supplier->province->description : '' }}
                    {{ $supplier->department_id !== '-' ? '- ' . $supplier->department->description : '' }}
                </td>
            </tr>
        @endif
        @if ($document->shipping_address)
            <tr>
                <td class="align-top">Dir. Envío:</td>
                <td colspan="3">
                    {{ $document->shipping_address }}
                </td>
            </tr>
        @endif
        @if ($supplier->telephone)
            <tr>
                <td class="align-top">Teléfono:</td>
                <td colspan="3">
                    {{ $supplier->telephone }}
                </td>
            </tr>
        @endif
        @if ($document->payment_method_type)
            <tr>
                <td class="align-top">T. Pago:</td>
                <td colspan="3">
                    {{ $document->payment_method_type->description }}
                </td>
            </tr>
        @endif
        <tr>
            <td class="align-top">Vendedor:</td>
            <td colspan="3">
                {{ $document->user->name }}
            </td>
        </tr>
        @if ($document->quotation)
            <tr>
                <td class="align-top">Cotización:</td>
                <td colspan="3">
                    {{ $document->quotation->number_full }}
                </td>
            </tr>
        @endif
        @if ($document->sale_opportunity)
            <tr>
                <td class="align-top">O. Venta:</td>
                <td colspan="3">{{ $document->sale_opportunity->number_full }}</td>
            </tr>
        @endif
    </table>

    <table class="full-width ">
        @if ($document->purchase_quotation)
            <tr>
                <td width="15%" class="align-top">Proforma: </td>
                <td width="85%">{{ $document->purchase_quotation->identifier }}</td>
            </tr>
        @endif
    </table>
    <table class="full-width mt-3">
        @if ($document->description)
            <tr>
                <td width="15%" class="align-top">Descripción: </td>
                <td width="85%">{{ $document->description }}</td>
            </tr>
        @endif
    </table>

    @if ($document->guides)
        <br />
        {{-- <strong>Guías:</strong> --}}
        <table>
            @foreach ($document->guides as $guide)
                <tr>
                    @if (isset($guide->document_type_description))
                        <td>{{ $guide->document_type_description }}</td>
                    @else
                        <td>{{ $guide->document_type_id }}</td>
                    @endif
                    <td>:</td>
                    <td>{{ $guide->number }}</td>
                </tr>
            @endforeach
        </table>
    @endif
    @php
        $colspan = 7;
        if (is_integrate_system()) {
            $colspan = 5;
        }
    @endphp
    <table class="full-width mt-10 mb-10">
        <thead class="">
            <tr class="bg-grey">
                <th class="border-top-bottom text-center py-2" width="8%">Código</th>
                <th class="border-top-bottom text-center py-2" width="8%">Cant.</th>
                <th class="border-top-bottom text-center py-2" width="8%">Unidad</th>
                <th class="border-top-bottom text-left py-2">Descripción</th>
                @if (!is_integrate_system())
                    <th class="border-top-bottom text-left py-2">Modelo</th>
                @endif
                <th class="border-top-bottom text-right py-2" width="12%">
                    @if (is_integrate_system())
                        P.Compra
                    @else
                        V.Unit
                    @endif
                </th>
                @if (!is_integrate_system())
                    <th class="border-top-bottom text-right py-2" width="8%">Dto.</th>
                @endif
                <th class="border-top-bottom text-right py-2" width="12%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
                <tr>
                    <td class="text-center align-top">
                        @isset($row->item->id)
                            @php
                                $internal_id = \App\Models\Tenant\Item::find($row->item->id)->internal_id;
                            @endphp

                            @isset($internal_id)
                                {{ $internal_id }}
                            @endisset
                        @endisset
                    </td>
                    <td class="text-center align-top">

                        {{ number_format($row->quantity, 2) }}
                    </td>

                    <td class="text-center align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="text-left">
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
                    @if (!is_integrate_system())
                        <td class="text-left">{{ $row->item->model ?? '' }}</td>
                    @endif
                    <td class="text-right align-top">

                        @if (!is_integrate_system())
                            {{ number_format($row->unit_value, 2) }}
                        @else
                            {{ number_format($row->unit_price, 2) }}
                        @endif
                    </td>
                    @if (!is_integrate_system())
                        <td class="text-right align-top">
                            @if ($row->discounts)
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
                    @endif
                    <td class="text-right align-top">{{ number_format($row->total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="{{ $colspan + 1 }}" class="border-bottom"></td>
                </tr>
            @endforeach

            @if ($document->total_exportation > 0)
                <tr>
                    <td colspan="{{ $colspan }}" class="text-right font-bold">Op. Exportación:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_free > 0)
                <tr>
                    <td colspan="{{ $colspan }}" class="text-right font-bold">Op. Gratuitas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_unaffected > 0)
                <tr>
                    <td colspan="{{ $colspan }}" class="text-right font-bold">Op. Inafectas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_exonerated > 0)
                <tr>
                    <td colspan="{{ $colspan }}" class="text-right font-bold">Op. Exoneradas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_taxed > 0)
                <tr>
                    <td colspan="{{ $colspan }}" class="text-right font-bold">Op. Gravadas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_discount > 0)
                <tr>
                    <td colspan="{{ $colspan }}" class="text-right font-bold">
                        {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento TOTAL' }}:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="{{ $colspan }}" class="text-right font-bold">IGV:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
            </tr>
            <tr>
                <td colspan="{{ $colspan }}" class="text-right font-bold">Total a pagar:
                    {{ $document->currency_type->symbol }}
                </td>
                <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="{{ $colspan + 1 }}" style="margin: 15px;"></td>
            </tr>

        </tbody>
    </table>
    <br>
    <table class="full-width">
        <tr>
            <td colspan="2" class="align-top text-left font-bold">
                OBSERVACION:
            </td>
            <td colspan="5" class="align-top">
                {!! $document->observation !!}
            </td>
        </tr>
    </table>
</body>

</html>
