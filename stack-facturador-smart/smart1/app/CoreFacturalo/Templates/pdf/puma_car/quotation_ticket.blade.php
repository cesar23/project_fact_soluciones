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
    $customer = $document->customer;
    $invoice = $document->invoice;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $accounts = \App\Models\Tenant\BankAccount::all();
    $tittle = $document->prefix . '-' . str_pad($document->number ?? $document->id, 8, '0', STR_PAD_LEFT);
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
    
    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();
    $documment_columns = \App\Models\Tenant\DocumentColumn::where('is_visible', true)->where('type','COT')
        ->orderBy('column_order', 'asc')
        ->get();
    $total_discount_items = 0;
    $plate_number_info = $document->plate_number_info;
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
        {{-- @else --}}
        {{-- <div class="text-center company_logo_box pt-5"> --}}
        {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo_ticket contain"> --}}
        {{-- </div> --}}
    @endif
    <table class="full-width">
        <tr>
            <td class="text-center">
                <h5>{{ $company_name }}</h5>
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
        <tr>
            <td class="text-center">
                @if ($configuration->show_company_address)
                    {{ $establishment->address !== '-' ? $establishment->address : '' }}
                    {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                    {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                    {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                @endif
                @isset($establishment->trade_address)
                    <h6>{{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                    </h6>
                @endisset
                <h6>{{ $establishment->telephone !== '-' ? 'Central telefónica: ' . $establishment->telephone : '' }}
                </h6>
                @if ($configuration->show_email)
                    <h6>{{ $establishment->email !== '-' ? 'Email: ' . $establishment->email : '' }}</h6>
                @endif
                @isset($establishment->web_address)
                    <h6>{{ $establishment->web_address !== '-' ? 'Web: ' . $establishment->web_address : '' }}</h6>
                @endisset

                @isset($establishment->aditional_information)
                    <h6>{{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
                    </h6>
                @endisset
            </td>
        </tr>
        <tr>
            <td class="text-center">{{ $establishment->email !== '-' ? $establishment->email : '' }}</td>
        </tr>
        <tr>
            <td class="text-center pb-3">{{ $establishment->telephone !== '-' ? $establishment->telephone : '' }}</td>
        </tr>
        <tr>
            <td class="text-center pt-3 border-top">
                <h4>{{ get_document_name('quotation', 'Cotización') }}</h4>
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
                    <p class="desc">T. Validez:</p>
                </td>
                <td width="" class="">
                    <p class="desc">{{ $document->date_of_due }}</p>
                </td>
            </tr>
        @endif

        @if ($document->delivery_date)
            <tr>
                <td width="" class="">
                    <p class="desc">T. Entrega:</p>
                </td>
                <td width="" class="">
                    <p class="desc">{{ $document->delivery_date }}</p>
                </td>
            </tr>
        @endif
        @if ($configuration->info_customer_pdf)
            <tr>
                <td class="align-top">
                    <p class="desc">Cliente:</p>
                </td>
                <td>
                    <p class="desc">{{ $customer->name }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">{{ $customer->identity_document_type->description }}:</p>
                </td>
                <td>
                    <p class="desc">{{ $customer->number }}</p>
                </td>
            </tr>
            @if ($customer->address !== '')
                <tr>
                    <td class="align-top">
                        <p class="desc">Dirección:</p>
                    </td>
                    <td>
                        <p class="desc">
                            {{ $customer->address }}
                            {{ $customer->district_id !== '-' ? ', ' . $customer->district->description : '' }}
                            {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                            {{ $customer->department_id !== '-' ? '- ' . $customer->department->description : '' }}
                        </p>
                    </td>
                </tr>
            @endif
        @endif
        @if (isset($customer->location) && $customer->location)
            <tr>
                <td class="align-top">
                    <p class="desc">Ubicación:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $customer->location }}
                    </p>
                </td>
            </tr>
        @endif
        @if ($document->shipping_address)
            <tr>
                <td class="align-top">
                    <p class="desc">Dir. Envío:</p>
                </td>
                <td colspan="3">
                    <p class="desc">
                        {{ $document->shipping_address }}
                    </p>
                </td>
            </tr>
        @endif

        @if ($customer->telephone)
            <tr>
                <td class="align-top">
                    <p class="desc">Teléfono:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $customer->telephone }}
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

        @if ($document->account_number)
            <tr>
                <td class="align-top">
                    <p class="desc">N° Cuenta:</p>
                </td>
                <td colspan="">
                    <p class="desc">
                        {{ $document->account_number }}
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
                    <p class="desc">
                        {{ $document->sale_opportunity->number_full }}
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
                    @if ($document->seller->name)
                        {{ $document->seller->name }}
                    @else
                        {{ $document->user->name }}
                    @endif
                </p>
            </td>
        </tr>
        @if ($document->description && !is_integrate_system())
            <tr>
                <td class="align-top">
                    <p class="desc">Observación:</p>
                </td>
                <td>
                    <p class="desc">{!! str_replace("\n", '<br/>', $document->description) !!}</p>
                </td>
                {{-- <td><p class="desc">{{ $document->description }}</p></td> --}}
            </tr>
        @endif

        @if ($document->contact)
            <tr>
                <td class="align-top">
                    <p class="desc">Contacto:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->contact }}</p>
                </td>
            </tr>
        @endif
        @if ($document->phone)
            <tr>
                <td class="align-top">
                    <p class="desc">Telf. Contacto:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->phone }}</p>
                </td>
            </tr>
        @endif

        @if ($document->purchase_order)
            <tr>
                <td>
                    <p class="desc">Orden de compra:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->purchase_order }}</p>
                </td>
            </tr>
        @endif
        @if ($document->quotation_id)
            <tr>
                <td>
                    <p class="desc">Cotización:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->quotation->identifier }}</p>
                </td>
            </tr>
        @endif
        @if ($plate_number_info)
        <tr>
            <td>
                <p class="desc">N° Placa:</p>
            </td>
            <td>
                <p class="desc">{{ $plate_number_info['description'] }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p class="desc">Año:</p>
            </td>
            <td>
                <p class="desc">{{ $plate_number_info['year'] }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p class="desc">Marca:</p>
            </td>
            <td>
                <p class="desc">{{ $plate_number_info['brand'] }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p class="desc">Modelo:</p>
            </td>
            <td>
                <p class="desc">{{ $plate_number_info['model'] }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p class="desc">Color:</p>
            </td>
            <td>
                <p class="desc">{{ $plate_number_info['color'] }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p class="desc">Tipo:</p>
            </td>
            <td>
                <p class="desc">{{ $plate_number_info['type'] }}</p>
            </td>
        </tr>
        @endif
    </table>

    @php

        $colspanitem = 5;
        if ($configuration->discount_unit_document) {
            $colspanitem = 6;
        }
    @endphp
    @php
        $width_description = 40;
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
            $width_description = 40 - $width_column;
        }
    @endphp

    @php
        $colspan = 9;
        if ($configurations->document_columns) {
            $colspan = count($documment_columns) + 3;
        }
    @endphp
    <div class="border-box mt-2">
        <table class="full-width">
            <thead class="">
                <tr class="">
                    <th class="border-bottom  desc text-center py-2 rounded-t" width="8%">Cant
                    </th>
                    <th class="border-bottom border-left desc text-center py-2" width="8%">Unid
                    </th>
                    <th class="border-bottom border-left desc text-left py-2 px-2"
                        width="{{ $width_description }}%">Descripción</th>
                    @if (!$configurations->document_columns)

                        <th class="border-bottom border-left desc text-right desc py-2 px-2"
                            width="{{ $width_column }}%">P.U.</th>
                        <th class="border-bottom border-left desc text-right desc py-2 px-2"
                            width="10%">Dct</th>
                        <th class="border-bottom border-left desc text-right desc py-2 px-2"
                            width="12%">Total </th>
                    @else
                        @foreach ($documment_columns as $column)
                            <th class="border-bottom border-left desc text-center py-2 px-2"
width="{{ $column->width }}%">
                                {{ $column->name }}</th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            {{-- @php
                $cycle = 10;
                $count_items = count($document->items);
                if ($count_items > 4) {
                    $cycle = 0;
                } else {
                    $cycle = 10 - $count_items;
                }

            @endphp--}}
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

                            <span style="font-size: 10px;margin-top: 0px;padding-top: 0px;">
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
                                        <br> <small> Característica {{ $size->size }} | {{ $size->qty }}
                                            und.</small> <br>
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
                                <div>*** Pago Anticipado ***</div>
                            
                            @endif
                        </td>.
                        @php
                        // Mover el cálculo de descuentos fuera del if
                        $total_discount_line = 0;
                        if($row->discounts) {
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
                                width="10%">
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
width="{{ $column->width }}%">
                                    @php
                                        $value = $column->getValudDocumentItem($row, $column->value);
                                    @endphp
                                @if($column->value == 'image')
                                <img src="data:{{ mime_content_type(public_path(parse_url($value, PHP_URL_PATH))) }};base64, {{ base64_encode(file_get_contents(public_path(parse_url($value, PHP_URL_PATH)))) }}" alt="Imagen" style="width: 80px; height: auto;">
                            @else
                                {{ $value }}
                            @endif
                                </td>
                            @endforeach
                        @endif
                    </tr>
                
                @endforeach

                {{-- @for ($i = 0; $i < $cycle; $i++)--}}
                    
                {{-- @endfor--}}



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
@if ($configuration->taxed_igv_visible_cot)
    @if ($document->total_exportation > 0)
        <tr>
            <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">Op.
                Exportación:
                {{ $document->currency_type->symbol }}</td>
            <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                {{ number_format($document->total_exportation, 2) }}</td>
        </tr>
    @endif
    @if ($document->total_free > 0)
        <tr>
            <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">Op.
                Gratuitas:
                {{ $document->currency_type->symbol }}</td>
            <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                {{ number_format($document->total_free, 2) }}</td>
        </tr>
    @endif
    @if ($document->total_unaffected > 0)
        <tr>
            <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">Op.
                Inafectas:
                {{ $document->currency_type->symbol }}</td>
            <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                {{ number_format($document->total_unaffected, 2) }}</td>
        </tr>
    @endif
    @if ($document->total_exonerated > 0)
        <tr>
            <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">Op.
                Exoneradas:
                {{ $document->currency_type->symbol }}</td>
            <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                {{ number_format($document->total_exonerated, 2) }}</td>
        </tr>
    @endif

    @if ($document->document_type_id === '07')
        @if ($document->total_taxed >= 0)
            <tr>
                <td class="text-left desc" colspan="5" class="text-right desc">Op.
                    Gravadas:
                    {{ $document->currency_type->symbol }}
                </td>
                <td class="text-left desc" colspan="2" class="text-right desc">
                    {{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif
    @elseif($document->total_taxed > 0)
        <tr>
            <td class="text-left desc" colspan="5" class="text-right desc">Op. Gravadas:
                {{ $document->currency_type->symbol }}</td>
            <td class="text-left desc" colspan="2" class="text-right desc">
                {{ number_format($document->total_taxed, 2) }}</td>
        </tr>
    @endif

    @if ($document->total_plastic_bag_taxes > 0)
        <tr>
            <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">
                Icbper:
                {{ $document->currency_type->symbol }}
            </td>
            <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                {{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
        </tr>
    @endif
    <tr>
        <td class="text-left desc" colspan="5" class="text-right desc">IGV:
            {{ $document->currency_type->symbol }}
        </td>
        <td class="text-left desc" colspan="2" class="text-right desc">
            {{ number_format($document->total_igv, 2) }}</td>
    </tr>
    @if ($document->total_isc > 0)
        <tr>
            <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">
                ISC:
                {{ $document->currency_type->symbol }}</td>
            <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                {{ number_format($document->total_isc, 2) }}</td>
        </tr>
    @endif

    @if ($document->total_discount > 0 && $document->subtotal > 0)
        <tr>
            <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">
                Subtotal:
                {{ $document->currency_type->symbol }}
            </td>
            <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                {{ number_format($document->subtotal, 2) }}</td>
        </tr>
    @endif

    @if ($document->total_discount > 0)
        <tr>
            <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">
                {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento total' }}
                : {{ $document->currency_type->symbol }}</td>
            <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">

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
                <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">Cargos
                    ({{ $total_factor }}
                    %): {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                    {{ number_format($document->total_charge, 2) }}</td>
            </tr>
        @else
            <tr>
                <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">Cargos:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                    {{ number_format($document->total_charge, 2) }}</td>
            </tr>
        @endif
    @endif
@endif
@if ($document->perception)
    <tr>
        <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">
            Importe total:
            {{ $document->currency_type->symbol }}</td>
        <td class="text-left desc" colspan="2" class="text-right desc font-bold desc" width="12%">
            {{ number_format($document->total, 2) }}</td>
    </tr>
    <tr>
        <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">
            Percepción:
            {{ $document->currency_type->symbol }}</td>
        <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
            {{ number_format($document->perception->amount, 2) }}</td>
    </tr>
    <tr>
        <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">
            Total a pagar:
            {{ $document->currency_type->symbol }}</td>
        <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
            {{ number_format($document->total + $document->perception->amount, 2) }}</td>
    </tr>
@elseif($document->retention)
    <tr>
        <td class="text-left desc" colspan="5" class="text-right desc font-bold desc"
            style="font-size: 16px;">Importe
            total:
            {{ $document->currency_type->symbol }}</td>
        <td class="text-left desc" colspan="2" class="text-right desc font-bold desc" style="font-size: 16px;">
            {{ number_format($document->total, 2) }}</td>
    </tr>
    <tr>
        <td class="text-left desc" colspan="5" class="text-right desc">Total
            retención
            ({{ $document->retention->percentage * 100 }}
            %): {{ $document->currency_type->symbol }}</td>
        <td class="text-left desc" colspan="2" class="text-right desc" width="12%">
            {{ number_format($document->retention->amount, 2) }}</td>
    </tr>
    <tr>
        <td class="text-left desc" colspan="5" class="text-right desc">Importe neto:
            {{ $document->currency_type->symbol }}</td>
        <td class="text-left desc" colspan="2" class="text-right desc" width="12%">
            {{ number_format($document->total - $document->retention->amount, 2) }}
        </td>
    </tr>
@else
    <tr>
        <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">
            Total a pagar:
            {{ $document->currency_type->symbol }}</td>
        <td class="text-left desc" colspan="2" class="text-right desc font-bold desc" width="12%">
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
        <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">M.
            Pendiente:
            {{ $document->currency_type->symbol }}</td>
        <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
            {{ number_format($document->total_pending_payment, 2) }}</td>
    </tr>
@endif

</table>
    @if ($document->description && is_integrate_system())
        <table class="full-width">
            <tr>
                <td class="align-top">
                    <p class="desc">Observación:</p>
                </td>
                <td>
                    <p class="desc">{!! str_replace("\n", '<br/>', $document->description) !!}</p>
                </td>
                {{-- <td><p class="desc">{{ $document->description }}</p></td> --}}
            </tr>
        </table>
    @endif
    <table class="full-width">
        <tr>

            @foreach (array_reverse((array) $document->legends) as $row)
        <tr>
            @if ($row->code == '1000')
                <td class="desc pt-3" style="text-transform: uppercase;">Son: <span
                        class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></td>
                @if (count((array) $document->legends) > 1)
        <tr>
            <td class="desc pt-3"><span class="font-bold">Leyendas</span></td>
        </tr>
        @endif
    @else
        <td class="desc pt-3">{{ $row->code }}: {{ $row->value }}</td>
        @endif
        </tr>
        @endforeach
        </tr>

        <tr>
            <td class="desc pt-3">
                <br>
                @foreach ($accounts as $account)
                    <span class="font-bold">{{ $account->bank->description }}</span>
                    {{ $account->currency_type->description }}
                    <br>
                    <span class="font-bold">N°:</span> {{ $account->number }}
                    @if ($account->cci)
                        - <span class="font-bold">CCI:</span> {{ $account->cci }}
                    @endif
                    <br>
                @endforeach

            </td>
        </tr>

    </table>
    <br>

    <table class="full-width">
        <tr>
            <td class="desc pt-3">
                <strong>Pagos:</strong>
            </td>
        </tr>
        @php
            $payment = 0;
        @endphp
        @foreach ($document->payments as $row)
            <tr>
                <td class="desc ">- {{ $row->payment_method_type->description }} -
                    {{ $row->reference ? $row->reference . ' - ' : '' }} {{ $document->currency_type->symbol }}
                    {{ $row->payment }}</td>
            </tr>
            @php
                $payment += (float) $row->payment;
            @endphp
        @endforeach
        <tr>
            <td class="desc pt-3"><strong>Saldo:</strong> {{ $document->currency_type->symbol }}
                {{ number_format($document->total - $payment, 2) }}</td>
        </tr>

        @if ($document->terms_condition)
            <tr>
                <td class="desc pt-5 ">
                    {!! $document->terms_condition !!}
                </td>
            </tr>
        @endif
    </table>
    @if ($document->fee && count($document->fee) > 0)

    @foreach ($document->fee as $key => $quote)
    <div class="desc">
        <strong>Cuotas:</strong>
    </div>
        @if (!$configuration->show_the_first_cuota_document)
            <div class="desc">
                <span>&#8226;
                    {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                    / Fecha: {{ $quote->date }} / Monto:
                    {{ $quote->currency_type->symbol }}{{ $quote->amount }}</span>
            </div>
        @else
            @if ($key == 0)
                <div class="desc">
                    <span>&#8226;
                        {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                        / Fecha: {{ $quote->date }} / Monto:
                        {{ $quote->currency_type->symbol }}{{ $quote->amount }}</span>
                </div>
            @endif
        @endif
    @endforeach
@endif
    <table class="full-width">
        @php
            $establishment_data = \App\Models\Tenant\Establishment::find($document->establishment_id);
        @endphp
        <tbody>
            <tr>
                @if ($configuration->yape_qr_quotations && $establishment_data->yape_logo)
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
                @if ($configuration->plin_qr_quotations && $establishment_data->plin_logo)
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

</html>
