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
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $left = $document->series ? $document->series : $document->prefix;
    $tittle = $left . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
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
    $accounts = \App\Models\Tenant\BankAccount::all();
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
    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();
    $documment_columns = \App\Models\Tenant\DocumentColumn::where('is_visible', true)->where('type','NV')
        ->orderBy('column_order', 'asc')
        ->get();

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }
    $total_discount_items = 0;
    $plate_number_info = $document->plate_number_info;
@endphp
<html>

<head>
    {{-- <title>{{ $tittle }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>

    <div class="header">

        <div style="float:left;width:20%">
            @if ($company->logo)
                <div class="company_logo_box" style="width: 100%;text-align: center;">
                    <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                        alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                </div>
            @else
                <br>
            @endif
        </div>
        <div style="float:left;width:2%;">
            <br>
        </div>
        <div style="float:left;width:48%;text-align:left;">
            <h4 style="margin: 0px !important;">{{ $company_name }}</h4>
            @if ($company_owner)
                De: {{ $company_owner }}
            @endif
            @if ($configuration->show_company_address)
                <h6 style="text-transform: uppercase;margin: 0px !important;line-height:0px;">
                    {{ $establishment->address !== '-' ? $establishment->address : '' }}
                    {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                    {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                    {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                </h6>
            @endif
            @isset($establishment->trade_address)
                <h6 style="margin: 0px !important;line-height:0px;">
                    {{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                </h6>
            @endisset

            <h6 style="margin: 0px !important;line-height:0px;">
                {{ $establishment->telephone !== '-' ? '' . $establishment->telephone : '' }}
            </h6>
            @if ($configuration->show_email)
                <h6 style="margin: 0px !important;line-height:0px;">
                    {{ $establishment->email !== '-' ? '' . $establishment->email : '' }}</h6>
            @endif
            @isset($establishment->web_address)
                <h6 style="margin: 0px;line-height:0px;">
                    {{ $establishment->web_address !== '-' ? '' . $establishment->web_address : '' }}
                </h6>
            @endisset

            @isset($establishment->aditional_information)
                <h6 style="margin: 0px;line-height:0px;">
                    {{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
                </h6>
            @endisset
        </div>
        <div style="float:left;width:30%;">
            <div style="border-radius:10px;border:1px solid black;text-align:center;width: 100%;height: 70px;">
                <div style="margin-top:8px;">{{ 'RUC ' . $company->number }}</div>
                <div class="text-center" style="margin-top:3px;">{{ get_document_name('sale_note', 'Nota de venta') }}
                </div>
                <div class="text-center" style="margin-top:3px;">{{ $tittle }}</div>
            </div>
        </div>
    </div>

    <table class="full-width mt-4">
        <tr>
            @if ($configuration->info_customer_pdf)
                <td class="text-left desc" width="10%">Cliente:</td>
                <td class="text-left desc" width="65%">{{ $customer->name }}</td>
            @endif
            <td class="text-left desc" width="15%">Fecha de emisión:</td>
            <td class="text-left desc" width="10%">{{ $document->date_of_issue->format('Y-m-d') }}</td>
        </tr>
        <tr>
            @if ($configuration->info_customer_pdf)
                <td class="text-left desc">{{ $customer->identity_document_type->description }}:</td>
                <td class="text-left desc">{{ $customer->number }}</td>
            @endif
            @if ($document->due_date)
                @if ($configuration->date_of_due_pdf)
                    <td class="text-left desc align-top">Fecha Vencimiento:</td>
                    <td class="text-left desc">{{ $document->getFormatDueDate() }}</td>
                @endif
            @endif

        </tr>
        @if ($customer->address !== '')
            @if ($configuration->info_customer_pdf)
                <tr>
                    <td class="text-left desc align-top">Dirección:</td>
                    <td class="text-left desc" colspan="3">
                        {{ strtoupper($customer->address) }}
                        {{ $customer->district_id !== '-' ? ', ' . strtoupper($customer->district->description) : '' }}
                        {{ $customer->province_id !== '-' ? ', ' . strtoupper($customer->province->description) : '' }}
                        {{ $customer->department_id !== '-' ? '- ' . strtoupper($customer->department->description) : '' }}
                    </td>
                </tr>
            @endif
        @endif
        <tr>
            <td class="text-left desc">Teléfono:</td>
            <td class="text-left desc">{{ $customer->telephone }}</td>
            <td class="text-left desc">Vendedor:</td>
            <td class="text-left desc">
                @if ($document->seller_id != 0)
                    {{ $document->seller->name }}
                @else
                    {{ $document->user->name }}
                @endif
            </td>
        </tr>
        @if ($document->plate_number !== null)
            <tr>
                <td class="text-left desc" width="10%">N° Placa:</td>
                <td class="text-left desc" width="85%">{{ $document->plate_number }}</td>
            </tr>
        @endif
        @if ($document->total_canceled)
            <tr>
                <td class="text-left desc align-top">Estado:</td>
                <td class="text-left desc" colspan="3">Cancelado</td>
            </tr>
        @else
            <tr>
                <td class="text-left desc align-top">Estado:</td>
                <td class="text-left desc" colspan="3">Pendiente de pago</td>
            </tr>
        @endif
        @if ($document->observation)
            <tr>
                <td class="text-left desc align-top">Observación:</td>
                <td class="text-left desc" colspan="3">{{ $document->observation }}</td>
            </tr>
        @endif
        @if ($document->reference_data)
            <tr>
                <td class="text-left desc align-top">D. Referencia:</td>
                <td class="text-left desc" colspan="3">{{ $document->reference_data }}</td>
            </tr>
        @endif
        @if ($document->purchase_order)
            <tr>
                <td class="text-left desc align-top">Orden de compra:</td>
                <td class="text-left desc" colspan="3">{{ $document->purchase_order }}</td>
            </tr>
        @endif
        @if ($plate_number_info)
            <tr>
                <td class="text-left desc align-top">N° Placa:</td>
                <td class="text-left desc">{{ $plate_number_info['description'] }}</td>
                <td class="text-left desc align-top">Año:</td>
                <td class="text-left desc">{{ $plate_number_info['year'] }}</td>
            </tr>
            <tr>
                <td class="text-left desc align-top">Marca:</td>
                <td class="text-left desc">{{ $plate_number_info['brand'] }}</td>
                <td class="text-left desc align-top">Modelo:</td>
                <td class="text-left desc">{{ $plate_number_info['model'] }}</td>
            </tr>
            <tr>
                <td class="text-left desc align-top">Color:</td>
                <td class="text-left desc">{{ $plate_number_info['color'] }}</td>
                <td class="text-left desc align-top">Tipo:</td>
                <td class="text-left desc">{{ $plate_number_info['type'] }}</td>
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
        $width_description = 50;
        $width_column = 12;
        if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf) {
            if (
                $configuration_decimal_quantity->decimal_quantity_unit_price_pdf > 6 &&
                $configuration_decimal_quantity->decimal_quantity_unit_price_pdf <= 8
            ) {
                $width_column = 13;
            } elseif ($configuration_decimal_quantity->decimal_quantity_unit_price_pdf > 8) {
                $width_column = 15;
            } else {
                $width_column = 12;
            }
            $width_description = 50 - $width_column;
        }
    @endphp

    @php
        $colspan = 9;
        if ($configurations->document_columns) {
            $colspan = count($documment_columns) + 3;
        }
    @endphp

    <div class="border-box mb-10 mt-2">
        <table class="full-width">
            <thead class="">
                <tr class="">
                    <th class="border-bottom  desc text-center py-2 rounded-t" width="8%">Cant.
                    </th>
                    <th class="border-bottom border-left desc text-center py-2" width="8%">Unidad
                    </th>
                    <th class="border-bottom border-left desc text-left py-2 px-2" width="{{ $width_description }}%">
                        Descripción</th>
                    @if (!$configurations->document_columns)

                        <th class="border-bottom border-left desc text-right desc py-2 px-2"
                            width="{{ $width_column }}%">P.Unit</th>
                        <th class="border-bottom border-left desc text-right desc py-2 px-2" width="8%">Dto.</th>
                        <th class="border-bottom border-left desc text-right desc py-2 px-2" width="12%">Total </th>
                    @else
                        @foreach ($documment_columns as $column)
                            <th class="border-bottom border-left desc text-center py-2 px-2"
                                width="{{ $column->width }}%">
                                {{ $column->name }}</th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            @php
                $cycle = 10;
                $count_items = count($document->items);
                if ($count_items > 4) {
                    $cycle = 0;
                } else {
                    $cycle = 10 - $count_items;
                }

            @endphp
            <tbody>
                @foreach ($document->items as $row)
                    <tr>
                        <td class="text-left desc" class="text-center desc align-top" width="8%">
                            @if ((int) $row->quantity != $row->quantity)
                                {{ $row->quantity }}
                            @else
                                {{ number_format($row->quantity, 0) }}
                            @endif
                        </td>
                        <td class="text-left desc" class="text-center desc align-top border-left" width="8%">
                            {{-- {{ $row->item->unit_type_id }} --}}
                            {{ symbol_or_code($row->item->unit_type_id) }}</td>
                        <td class="text-left desc" class="text-left desc align-top border-left px-2"
                            width="{{ $width_description }}%">
                            @php
                                $description = $row->name_product_pdf ?? $row->item->description;
                                $description = trim($description);
                                //remove all '&nbsp;' text literals
                                $symbols = ['&nbsp;', '&amp;', '&quot;', '&lt;', '&gt;'];
                                $replacements = [' ', '&', '"', '<', '>'];

                                $description = str_replace($symbols, $replacements, $description);
                                $description = removePTag($description);
                            @endphp

                            <span style="font-size: 12px;margin-top: 0px;padding-top: 0px;">
                                {{-- $description --}} {!! $description !!}
                            </span>
                            {{-- @if ($row->name_product_pdf)
                                {!! $row->name_product_pdf !!}
                            @else
                                {!! $row->item->description !!}
                            @endif --}}
                            @if ($configurations->name_pdf)
                                @php
                                    $item_name = \App\Models\Tenant\Item::select('name')
                                        ->where('id', $row->item_id)
                                        ->first();
                                @endphp
                                @if ($item_name->name)
                                    <div>
                                        <span style="font-size: 9px">{{ $item_name->name }}</span>
                                    </div>
                                @endif
                            @endif
                            @if (
                                $configurations->presentation_pdf &&
                                    isset($row->item->presentation) &&
                                    isset($row->item->presentation->description))
                                <div>
                                    <span style="font-size: 9px">{{ $row->item->presentation->description }}</span>
                                </div>
                            @endif
                            @if ($row->total_isc > 0)
                                <br /><span style="font-size: 9px">ISC : {{ $row->total_isc }}
                                    ({{ $row->percentage_isc }}%)</span>
                            @endif

                            {{-- 
       
                            --}}

                            @if ($row->total_plastic_bag_taxes > 0)
                                <br /><span style="font-size: 9px">ICBPER :
                                    {{ $row->total_plastic_bag_taxes }}</span>
                            @endif

                            @if ($row->attributes)
                                @foreach ($row->attributes as $attr)
                                    <br /><span style="font-size: 9px">{!! $attr->description !!} :
                                        {{ $attr->value }}</span>
                                @endforeach
                            @endif
                            @if ($row->discounts)
                                @foreach ($row->discounts as $dtos)
                                    @if ($dtos->is_amount == false)
                                        <br /><span style="font-size: 9px">{{ $dtos->factor * 100 }}%
                                            {{ $dtos->description }}</span>
                                    @endif
                                @endforeach
                            @endif
                            @isset($row->item->sizes_selected)
                                @if (count($row->item->sizes_selected) > 0)
                                    @foreach ($row->item->sizes_selected as $size)
                                        <br> <small> {{ $size->size }} | {{ $size->qty }}
                                            {{ symbol_or_code($row->item->unit_type_id) }}</small>
                                    @endforeach
                                @endif
                            @endisset
                            @if ($row->charges)
                                @foreach ($row->charges as $charge)
                                    <br /><span style="font-size: 9px">{{ $document->currency_type->symbol }}
                                        {{ $charge->amount }} ({{ $charge->factor * 100 }}%)
                                        {{ $charge->description }}</span>
                                @endforeach
                            @endif

                            @if ($row->item->is_set == 1 && $configurations->show_item_sets)
                                <br>
                                @inject('itemSet', 'App\Services\ItemSetService')
                                @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                                    {{ $item }}<br>
                                @endforeach
                            @endif

                            @if ($row->item->used_points_for_exchange ?? false)
                                <br>
                                <span style="font-size: 9px">*** Canjeado por
                                    {{ $row->item->used_points_for_exchange }}
                                    puntos ***</span>
                            @endif

                            @if ($document->has_prepayment)
                                <br>
                                *** Pago Anticipado ***
                            @endif
                        </td>.
                        @php
                            // Mover el cálculo de descuentos fuera del if
                            $total_discount_line = 0;
                            if ($row->discounts) {
                                foreach ($row->discounts as $disto) {
                                    $amount = $disto->amount;
                                    if (isset($disto->is_split)) {
                                        $amount = $amount * 1.18;
                                    }
                                    $total_discount_line = $total_discount_line + $amount;
                                    $total_discount_items += $total_discount_line;
                                }
                            }
                        @endphp
                        @if (!$configurations->document_columns)


                            <td class="text-left desc" class="text-right desc desc align-top border-left px-2"
                                width="{{ $width_column }}%">
                                @if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf)
                                    {{ $row->generalApplyNumberFormat($row->unit_price, $configuration_decimal_quantity->decimal_quantity_unit_price_pdf) }}
                                @else
                                    {{ number_format($row->unit_price, 2) }}
                                @endif
                            </td>



                            <td class="text-left desc" class="text-right desc desc align-top border-left px-2"
                                width="8%">
                                @if ($configurations->discounts_acc)
                                    @if ($row->discounts_acc)
                                        @php
                                            $discounts_acc = (array) $row->discounts_acc;
                                        @endphp
                                        @foreach ($discounts_acc as $key => $disto)
                                            <span style="font-size: 9px">{{ $disto->percentage }}%
                                                @if ($key + 1 != count($discounts_acc))
                                                    +
                                                @endif
                                            </span>
                                        @endforeach
                                    @endif
                                @else
                                    {{ number_format($total_discount_line, 2) }}
                                @endif
                            </td>
                            <td class="text-left desc" class="text-right desc desc align-top border-left px-2"
                                width="12%">
                                @if (isDacta())
                                    {{ number_format($row->total_value + $row->total_igv + $row->total_isc, 2) }}
                                @else
                                    {{ number_format($row->total, 2) }}
                                @endif
                            </td>
                        @else
                            @foreach ($documment_columns as $column)
                                <td class="text-left desc" class="text-right desc desc align-top border-left px-2"
                                    style="text-align: {{ $column->column_align }};" width="{{ $column->width }}%">
                                    @php
                                        $value = $column->getValudDocumentItem($row, $column->value);
                                    @endphp
                                    @if ($column->value == 'image')
                                        <img src="data:{{ mime_content_type(public_path(parse_url($value, PHP_URL_PATH))) }};base64, {{ base64_encode(file_get_contents(public_path(parse_url($value, PHP_URL_PATH)))) }}"
                                            alt="Imagen" style="width: 150px; height: auto;">
                                    @elseif($column->value == 'info_link' && $value)
                                        <a href="{{ $value }}" target="_blank">{{substr($value, 0, 20) }}...</a>
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            @endforeach
                        @endif
                    </tr>
                    <tr>
                        @php
                            $colspan = 9;
                            if ($configurations->document_columns) {
                                $colspan = count($documment_columns) + 3;
                            }
                        @endphp
                        {{-- <td class="text-left desc" colspan="{{ $colspan }}" class="border-bottom desc"></td> --}}
                    </tr>
                @endforeach

                @for ($i = 0; $i < $cycle; $i++)
                    <tr>
                        <td class="text-left desc" class="text-center desc align-top">
                            <br>
                        </td>
                        <td class="text-left desc" class="text-center desc align-top border-left"></td>
                        <td class="text-left desc" class="text-left desc align-top border-left"></td>
                        @if (!$configurations->document_columns)
                            <td class="text-left desc" class="text-right desc desc align-top border-left"></td>
                            <td class="text-left desc" class="text-right desc desc align-top border-left"></td>
                            <td class="text-left desc" class="text-right desc desc align-top border-left"></td>
                        @else
                            @foreach ($documment_columns as $column)
                                <td class="text-left desc" class="text-right desc desc align-top border-left"></td>
                            @endforeach
                        @endif
                    </tr>
                @endfor



            </tbody>
        </table>
    </div>
    <table class="full-width">

        @if ($document->prepayments)
            @foreach ($document->prepayments as $p)
                <tr>
                    <td class="text-left desc" class="text-center desc align-top">1</td>
                    <td class="text-left desc" class="text-center desc align-top">NIU</td>
                    <td class="text-left desc" class="text-left desc align-top">
                        Anticipo: {{ $p->document_type_id == '02' ? 'Factura' : 'Boleta' }} Nro.
                        {{ $p->number }}
                    </td>

                    <td class="text-left desc" class="text-right desc desc align-top">
                        -{{ number_format($p->total, 2) }}</td>
                    <td class="text-left desc" class="text-right desc desc align-top">0</td>
                    <td class="text-left desc" class="text-right desc desc align-top">
                        -{{ number_format($p->total, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-left desc" colspan="7" class="border-bottom"></td>
                </tr>
            @endforeach
        @endif
        @if ($configuration->taxed_igv_visible_nv)
            @if ($document->total_exportation > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">Op.
                        Exportación:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">
                        {{ number_format($document->total_exportation, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_free > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">Op.
                        Gratuitas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">
                        {{ number_format($document->total_free, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_unaffected > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">Op.
                        Inafectas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">
                        {{ number_format($document->total_unaffected, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_exonerated > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">Op.
                        Exoneradas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">
                        {{ number_format($document->total_exonerated, 2) }}</td>
                </tr>
            @endif

            @if ($document->document_type_id === '07')
                @if ($document->total_taxed >= 0)
                    <tr>
                        <td class="text-left desc" colspan="6" class="text-right desc">Op.
                            Gravadas:
                            {{ $document->currency_type->symbol }}
                        </td>
                        <td class="text-left desc" class="text-right desc">
                            {{ number_format($document->total_taxed, 2) }}</td>
                    </tr>
                @endif
            @elseif($document->total_taxed > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc">Op. Gravadas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc">
                        {{ number_format($document->total_taxed, 2) }}</td>
                </tr>
            @endif

            @if ($document->total_plastic_bag_taxes > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                        Icbper:
                        {{ $document->currency_type->symbol }}
                    </td>
                    <td class="text-left desc" class="text-right desc font-bold desc">
                        {{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc">IGV:
                    {{ $document->currency_type->symbol }}
                </td>
                <td class="text-left desc" class="text-right desc">{{ number_format($document->total_igv, 2) }}</td>
            </tr>
            @if ($document->total_isc > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                        ISC:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">
                        {{ number_format($document->total_isc, 2) }}</td>
                </tr>
            @endif

            @if ($document->total_discount > 0 && $document->subtotal > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                        Subtotal:
                        {{ $document->currency_type->symbol }}
                    </td>
                    <td class="text-left desc" class="text-right desc font-bold desc">
                        {{ number_format($document->subtotal, 2) }}</td>
                </tr>
            @endif

            @if ($document->total_discount > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                        {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento total' }}
                        : {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">

                        @php
                            $total_discount = $document->total_discount;
                            $discounts = $document->discounts;
                            $igv_prepayment = 1;
                            if ($document->total_prepayment > 0) {
                                $item = $document->items->first();
                                $has_affected = $item->affectation_igv_type_id < 20;
                                if ($has_affected) {
                                    $igv_prepayment = 1.18;
                                }
                            }
                            if ($discounts) {
                                $discounts = get_object_vars($document->discounts);
                                isset($discounts[0]) ? $discounts[0] : null;
                                $is_split = isset($discount->is_split) ? $discount->is_split : false;
                                if ($is_split) {
                                    $total_discount = $total_discount * 1.18;
                                }
                            } else {
                                $total_discount = $total_discount_items;
                            }

                        @endphp

                        {{ number_format($total_discount * $igv_prepayment, 2) }}</td>
                </tr>
            @endif

            @if ($document->total_charge > 0)
                @if ($document->charges)
                    @php
                        $total_factor = 0;
                        foreach ($document->charges as $charge) {
                            $total_factor = ($total_factor + $charge->factor) * 100;
                        }
                    @endphp
                    <tr>
                        <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">Cargos
                            ({{ $total_factor }}
                            %): {{ $document->currency_type->symbol }}</td>
                        <td class="text-left desc" class="text-right desc font-bold desc">
                            {{ number_format($document->total_charge, 2) }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">Cargos:
                            {{ $document->currency_type->symbol }}</td>
                        <td class="text-left desc" class="text-right desc font-bold desc">
                            {{ number_format($document->total_charge, 2) }}</td>
                    </tr>
                @endif
            @endif
        @endif

        @if ($document->perception)
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                    Importe total:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc font-bold desc" width="12%">
                    {{ number_format($document->total, 2) }}</td>
            </tr>
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                    Percepción:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc font-bold desc">
                    {{ number_format($document->perception->amount, 2) }}</td>
            </tr>
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                    Total a pagar:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc font-bold desc">
                    {{ number_format($document->total + $document->perception->amount, 2) }}</td>
            </tr>
        @elseif($document->retention)
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                    style="font-size: 16px;">Importe
                    total:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc font-bold desc" style="font-size: 16px;">
                    {{ number_format($document->total, 2) }}</td>
            </tr>
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc">Total
                    retención
                    ({{ $document->retention->percentage * 100 }}
                    %): {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc" width="12%">
                    {{ number_format($document->retention->amount, 2) }}</td>
            </tr>
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc">Importe neto:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc" width="12%">
                    {{ number_format($document->total - $document->retention->amount, 2) }}
                </td>
            </tr>
        @else
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                    Total a pagar:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc font-bold desc" width="12%">
                    @if (isDacta())
                        {{ number_format($document->total_value + $document->total_igv + $document->total_isc, 2) }}
                    @else
                        {{ number_format($document->total, 2) }}
                    @endif
                </td>
            </tr>
        @endif

        @if (($document->retention || $document->detraction) && $document->total_pending_payment > 0)
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">M.
                    Pendiente:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc font-bold desc">
                    {{ number_format($document->total_pending_payment, 2) }}</td>
            </tr>
        @endif
    </table>
    <table class="full-width">
        <tr>
            <td width="65%" style="text-align: top; vertical-align: top;">
                <br>
                @foreach ($accounts as $account)
                    <p>
                        <span class="font-bold">{{ $account->bank->description }}</span>
                        {{ $account->currency_type->description }}
                        <span class="font-bold">N°:</span> {{ $account->number }}
                        @if ($account->cci)
                            - <span class="font-bold">CCI:</span> {{ $account->cci }}
                        @endif
                    </p>
                @endforeach
            </td>
        </tr>
    </table>
    <br>
    @if ($document->payment_method_type_id && $payments->count() == 0)
        <table class="full-width">
            <tr>
                <td>
                    <strong>Pago: </strong>{{ $document->payment_method_type->description }}
                </td>
            </tr>
        </table>
    @endif

    @if ($payments->count())
        <table class="full-width">
            <tr>
                <td class="text-left desc">
                    <strong>Pagos:</strong>
                </td>
            </tr>
            @php
                $payment = 0;
            @endphp
            @foreach ($payments as $row)
                <tr>
                    <td class="text-left desc">- {{ $row->date_of_payment->format('d/m/Y') }} -
                        {{ $row->payment_method_type->description }}
                        - {{ $row->reference ? $row->reference . ' - ' : '' }}
                        {{ $document->currency_type->symbol }}
                        {{ $row->payment + $row->change }}</td>
                </tr>
                @php
                    $payment += (float) $row->payment;
                @endphp
            @endforeach
            <tr>
                <td class="text-left desc"><strong>Saldo:</strong> {{ $document->currency_type->symbol }}
                    {{ number_format($document->total - $payment, 2) }}</td>
            </tr>

        </table>
    @endif
    @if ($document->terms_condition)
        <br>
        <table class="full-width">
            <tr>
                <td class="text-left desc">
                    <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                    {!! $document->terms_condition !!}
                </td>
            </tr>
        </table>
    @endif
</body>

</html>
