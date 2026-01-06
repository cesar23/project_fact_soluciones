@php
    $establishment = $document->establishment;
    $configuration = \App\Models\Tenant\Configuration::getConfig();
    $configurations = \App\Models\Tenant\Configuration::first();
    $company_name = $company->name;
    $company_owner = null;
    if ($configuration->trade_name_pdf) {
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

    // Usar number_full que ya maneja la compatibilidad entre registros nuevos y antiguos
    $tittle = $document->number_full;

@endphp
<html>

<head>
    {{-- <title>{{ $tittle }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>

    @if ($company->logo)
        <div class="text-center company_logo_box pt-5">
            <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                alt="{{ $company->name }}" class="company_logo_ticket contain">
        </div>
    @endif
    <table class="full-width">
        <tr>
            <td class="text-center">
                <h4>{{ $company_name }}</h4>
            </td>
        </tr>
        @if ($company_owner)
            <tr>
                <td class="text-center">
                    <h5>De: {{ $company->name }}</h5>
                </td>
            </tr>
        @endif
        <tr>
            <td class="text-center">
                <h5>{{ 'RUC ' . $company->number }}</h5>
            </td>
        </tr>
        @if ($configuration->show_company_address ?? true)
            <tr>
                <td class="text-center" style="text-transform: uppercase;">
                    {{ $establishment->address !== '-' ? $establishment->address : '' }}
                    {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                    {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                    {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                </td>
            </tr>
        @endif
        @if ($configuration->show_email ?? true)
            <tr>
                <td class="text-center">
                    {{ $establishment->email !== '-' ? $establishment->email : '' }}
                </td>
            </tr>
        @endif
        <tr>
            <td class="text-center pb-3">
                {{ $establishment->telephone !== '-' ? $establishment->telephone : '' }}
            </td>
        </tr>
        <tr>
            <td class="text-center pt-3 border-top">
                <h4>Orden de compra</h4>
            </td>
        </tr>
        <tr>
            <td class="text-center pb-3 border-bottom">
                <h3>{{ $tittle }}</h3>
            </td>
        </tr>
    </table>
    <table class="full-width">
        <tr>
            <td width="" class="pt-3">
                <p class="desc">F. Emisión:</p>
            </td>
            <td width="" class="pt-3">
                <p class="desc">{{ $document->date_of_issue->format('Y-m-d') }}</p>
            </td>
        </tr>

        @if ($document->date_of_due)
            <tr>
                <td width="" class="">
                    <p class="desc">F. Vencimiento:</p>
                </td>
                <td width="" class="">
                    <p class="desc">{{ $document->date_of_due->format('Y-m-d') }}</p>
                </td>
            </tr>
        @endif

        <tr>
            <td class="align-top">
                <p class="desc">Proveedor:</p>
            </td>
            <td>
                <p class="desc">{{ $supplier->name }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p class="desc">{{ $supplier->identity_document_type->description }}:</p>
            </td>
            <td>
                <p class="desc">{{ $supplier->number }}</p>
            </td>
        </tr>
        @if ($supplier->address !== '')
            <tr>
                <td class="align-top">
                    <p class="desc">Dirección:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $supplier->address }}
                        {{ $supplier->district_id !== '-' ? ', ' . $supplier->district->description : '' }}
                        {{ $supplier->province_id !== '-' ? ', ' . $supplier->province->description : '' }}
                        {{ $supplier->department_id !== '-' ? '- ' . $supplier->department->description : '' }}
                    </p>
                </td>
            </tr>
        @endif
        @if ($document->shipping_address)
            <tr>
                <td class="align-top">
                    <p class="desc">Dir. Envío:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $document->shipping_address }}
                    </p>
                </td>
            </tr>
        @endif

        @if ($supplier->telephone)
            <tr>
                <td class="align-top">
                    <p class="desc">Teléfono:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $supplier->telephone }}
                    </p>
                </td>
            </tr>
        @endif
        @if ($document->payment_method_type)
            <tr>
                <td class="align-top">
                    <p class="desc">T. Pago:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $document->payment_method_type->description }}
                    </p>
                </td>
            </tr>
        @endif

        <tr>
            <td class="align-top">
                <p class="desc">Vendedor:</p>
            </td>
            <td>
                <p class="desc">
                    {{ $document->user->name }}
                </p>
            </td>
        </tr>
        @if ($document->quotation)
            <tr>
                <td class="align-top">
                    <p class="desc">Cotización:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $document->quotation->number_full }}
                    </p>
                </td>
            </tr>
        @endif
        @if ($document->sale_opportunity)
            <tr>
                <td class="align-top">
                    <p class="desc">O. Venta:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->sale_opportunity->number_full }}</p>
                </td>
            </tr>
        @endif
        @if ($document->purchase_quotation)
            <tr>
                <td class="align-top">
                    <p class="desc">Proforma:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->purchase_quotation->identifier }}</p>
                </td>
            </tr>
        @endif
        @if ($document->description)
            <tr>
                <td class="align-top">
                    <p class="desc">Descripción:</p>
                </td>
                <td>
                    <p class="desc">{!! str_replace("\n", '<br/>', $document->description) !!}</p>
                </td>
            </tr>
        @endif
    </table>

    @if ($document->guides)
        <table class="full-width">
            @foreach ($document->guides as $guide)
                <tr>
                    <td>
                        <p class="desc">
                            @if (isset($guide->document_type_description))
                                {{ $guide->document_type_description }}
                            @else
                                {{ $guide->document_type_id }}
                            @endif
                            : {{ $guide->number }}
                        </p>
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    @php
        $colspan = 5;
        if (is_integrate_system()) {
            $colspan = 4;
        }
    @endphp

    <div class="border-box mt-2">
        <table class="full-width">
            <thead class="">
                <tr class="">
                    <th class="border-bottom desc text-center py-2 rounded-t" width="10%">Cant</th>
                    <th class="border-bottom border-left desc text-center py-2" width="10%">Unid</th>
                    <th class="border-bottom border-left desc text-left py-2 px-2">Descripción</th>
                    @if (!is_integrate_system())
                        <th class="border-bottom border-left desc text-right py-2 px-2" width="12%">V.Unit</th>
                        <th class="border-bottom border-left desc text-right py-2 px-2" width="10%">Dto</th>
                    @else
                        <th class="border-bottom border-left desc text-right py-2 px-2" >P.Compra</th>
                    @endif
                    <th class="border-bottom border-left desc text-right py-2 px-2" >Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($document->items as $row)
                    <tr>
                        <td class="text-center desc align-top" width="10%">
                            {{ number_format($row->quantity, 2) }}
                        </td>
                        <td class="text-center desc align-top border-left" width="10%">
                            {{ symbol_or_code($row->item->unit_type_id) }}
                        </td>
                        <td class="text-left desc align-top border-left px-2">
                            @isset($row->item->id)
                                @php
                                    $internal_id = \App\Models\Tenant\Item::find($row->item->id)->internal_id;
                                @endphp
                                @isset($internal_id)
                                    <span style="font-size: 9px">Código: {{ $internal_id }}</span><br>
                                @endisset
                            @endisset
                            <span style="font-size: 10px;">
                                {!! $row->item->description !!}
                            </span>
                            @if (!is_integrate_system())
                                @if($row->item->model ?? '')
                                    <br><span style="font-size: 9px">Modelo: {{ $row->item->model }}</span>
                                @endif
                            @endif
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
                            <td class="text-right desc align-top border-left px-2" width="12%">
                                {{ number_format($row->unit_value, 2) }}
                            </td>
                            <td class="text-right desc align-top border-left px-2" width="10%">
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
                        @else
                            <td class="text-right desc align-top border-left px-2" width="17%">
                                {{ number_format($row->unit_price, 2) }}
                            </td>
                        @endif
                        <td class="text-right desc align-top border-left px-2" width="17%">
                            {{ number_format($row->total, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <table class="full-width">
        @if ($document->total_exportation > 0)
            <tr>
                <td class="text-left desc" colspan="4" class="text-right desc font-bold desc">Op. Exportación:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                    {{ number_format($document->total_exportation, 2) }}</td>
            </tr>
        @endif
        @if ($document->total_free > 0)
            <tr>
                <td class="text-left desc" colspan="4" class="text-right desc font-bold desc">Op. Gratuitas:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                    {{ number_format($document->total_free, 2) }}</td>
            </tr>
        @endif
        @if ($document->total_unaffected > 0)
            <tr>
                <td class="text-left desc" colspan="4" class="text-right desc font-bold desc">Op. Inafectas:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                    {{ number_format($document->total_unaffected, 2) }}</td>
            </tr>
        @endif
        @if ($document->total_exonerated > 0)
            <tr>
                <td class="text-left desc" colspan="4" class="text-right desc font-bold desc">Op. Exoneradas:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                    {{ number_format($document->total_exonerated, 2) }}</td>
            </tr>
        @endif
        @if ($document->total_taxed > 0)
            <tr>
                <td class="text-left desc" colspan="4" class="text-right desc">Op. Gravadas:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" colspan="2" class="text-right desc">
                    {{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif
        @if ($document->total_discount > 0)
            <tr>
                <td class="text-left desc" colspan="4" class="text-right desc font-bold desc">
                    {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento TOTAL' }}:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                    {{ number_format($document->total_discount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td class="text-left desc" colspan="4" class="text-right desc">IGV:
                {{ $document->currency_type->symbol }}
            </td>
            <td class="text-left desc" colspan="2" class="text-right desc">
                {{ number_format($document->total_igv, 2) }}</td>
        </tr>
        <tr>
            <td class="text-left desc" colspan="4" class="text-right desc font-bold desc">
                Total a pagar:
                {{ $document->currency_type->symbol }}</td>
            <td class="text-left desc" colspan="2" class="text-right desc font-bold desc" width="12%">
                {{ number_format($document->total, 2) }}
            </td>
        </tr>
    </table>

    @if ($document->observation)
        <table class="full-width">
            <tr>
                <td class="align-top pt-3">
                    <p class="desc"><strong>OBSERVACIÓN:</strong></p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">{!! $document->observation !!}</p>
                </td>
            </tr>
        </table>
    @endif

</body>

</html>
