@php
    $establishment = $document->establishment;
    $configuration = \App\Models\Tenant\Configuration::first();
    $company_name = $company->name;
    $company_owner = null;
    if ($configuration->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }
    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

    $establishment_data = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $customer = $document->customer;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
    $tittle = $document->prefix . '-' . str_pad($document->number ?? $document->id, 8, '0', STR_PAD_LEFT);

    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();
    $documment_columns = \App\Models\Tenant\DocumentColumn::where('is_visible', true)
        ->where('type', 'COT')
        ->orderBy('column_order', 'asc')
        ->get();
    $font_size_quotation_a4 = \App\Models\Tenant\FontToDocumentsPdf::where('document_type', 'quotation')
        ->where('format', 'a4')
        ->first();
    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }
    $total_discount_items = 0;

    // Variables dinámicas para tamaños de fuente - temporal (luego desde DB)
    // Variable global para sobrescribir todos los tamaños (null = usar tamaños individuales)
    $global_font_size = null; // Ejemplo: 10 para hacer todo el texto de 10px (cambiar a null para usar tamaños individuales)
    if ($font_size_quotation_a4) {
        $global_font_size = $font_size_quotation_a4->font_size;
    }

    // Tamaños individuales por defecto
    $font_size_primary_default = 12; // Texto principal de descripción
    $font_size_secondary_default = 9; // Texto secundario (nombres, impuestos, etc.)
    $font_size_emphasis_default = 16; // Texto de totales con énfasis
    $font_size_company_default = 14; // Nombre de empresa (H4)
    $font_size_info_default = 12; // Información general (H6, direcciones, teléfonos)
    $font_size_document_default = 12; // Sección de RUC/documento
    $font_size_table_default = 12; // Tablas de cliente y totales

    // Aplicar tamaño global o usar individuales
    $font_size_primary = $global_font_size ?? $font_size_primary_default;
    $font_size_secondary = $global_font_size ?? $font_size_secondary_default;
    $font_size_emphasis = $global_font_size ?? $font_size_emphasis_default;
    $font_size_company = $global_font_size ?? $font_size_company_default;
    $font_size_info = $global_font_size ?? $font_size_info_default;
    $font_size_document = $global_font_size ?? $font_size_document_default;
    $font_size_table = $global_font_size ?? $font_size_table_default;
    $plate_number_info = $document->plate_number_info;
    $technician_name =
        $document->quotationTechnician && $document->quotationTechnician->quotationTechnician
            ? $document->quotationTechnician->quotationTechnician->name
            : null;
    $technical_service_car = null;
    $document_technical_service = $document->technical_service;
    $detail = [];

    if ($document_technical_service) {
        if ($document_technical_service->technical_service->repair) {
            $detail[] = 'REPARACIÓN';
        }
        if ($document_technical_service->technical_service->warranty) {
            $detail[] = 'GARANTÍA';
        }
        if ($document_technical_service->technical_service->maintenance) {
            $detail[] = 'MANTENIMIENTO';
        }
        if ($document_technical_service->technical_service->diagnosis) {
            $detail[] = 'DIAGNÓSTICO';
        }
        if ($document_technical_service->technical_service->ironing_and_painting) {
            $detail[] = 'PLANCHADO Y PINTURA';
        }
        if ($document_technical_service->technical_service->equipments) {
            $detail[] = 'EQUIPAMIENTO';
        }
        if ($document_technical_service->technical_service->preventive_maintenance) {
            $detail[] = 'MANTENIMIENTO PREVENTIVO';
        }
        if ($document_technical_service->technical_service->corrective_maintenance) {
            $detail[] = 'MANTENIMIENTO CORRECTIVO';
        }
        $detail = !empty($detail) ? implode(' / ', $detail) : null;
        $technical_service_car = $document_technical_service->technical_service->technical_service_car;
    }
@endphp
<html>

<head>
    {{-- <title>{{ $tittle }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body style="font-size: {{ $font_size_table }}px;">


    <div class="header">

        <div style="float:left;width:20%">
            @if ($company->logo && file_exists(public_path("{$logo}")))
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
            <h4 style="margin: 0px !important;font-size: {{ $font_size_company }}px;">{{ $company_name }}</h4>
            @if ($company_owner)
                <span style="font-size: {{ $font_size_info }}px;">De: {{ $company_owner }}</span>
            @endif
            @if ($configuration->show_company_address)
                <h6
                    style="text-transform: uppercase;margin: 0px !important;line-height:0px;font-size: {{ $font_size_info }}px;">
                    {{ $establishment->address !== '-' ? $establishment->address : '' }}
                    {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                    {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                    {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                </h6>
            @endif
            @isset($establishment->trade_address)
                <h6 style="margin: 0px !important;line-height:0px;font-size: {{ $font_size_info }}px;">
                    {{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                </h6>
            @endisset

            <h6 style="margin: 0px !important;line-height:0px;">
                {{ $establishment->telephone !== '-' ? '' . $establishment->telephone : '' }}
            </h6>
            @if ($configuration->show_email)
                <h6 style="margin: 0px !important;line-height:0px;font-size: {{ $font_size_info }}px;">
                    {{ $establishment->email !== '-' ? '' . $establishment->email : '' }}</h6>
            @endif
            @isset($establishment->web_address)
                <h6 style="margin: 0px;line-height:0px;font-size: {{ $font_size_info }}px;">
                    {{ $establishment->web_address !== '-' ? '' . $establishment->web_address : '' }}
                </h6>
            @endisset

            @isset($establishment->aditional_information)
                <h6 style="margin: 0px;line-height:0px;font-size: {{ $font_size_info }}px;">
                    {{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
                </h6>
            @endisset
        </div>
        <div style="float:left;width:30%;">
            <div style="border-radius:10px;border:1px solid black;text-align:center;width: 100%;height: 80px;">
                <div style="margin-top:12px;font-size: {{ $font_size_document }}px;">{{ 'RUC ' . $company->number }}
                </div>
                <div class="text-center" style="margin-top:3px;font-size: {{ $font_size_document }}px;">
                    {{ get_document_name('quotation', 'Cotización') }}
                </div>
                <div class="text-center" style="margin-top:3px;font-size: {{ $font_size_document }}px;">
                    {{ $tittle }}</div>
            </div>
        </div>
    </div>
    <table class="full-width mt-5">
        <tr>
            @if ($configuration->info_customer_pdf)
                <td width="15%" style="font-size: {{ $font_size_table }}px;">Cliente:</td>
                <td width="45%" style="font-size: {{ $font_size_table }}px;">{{ $customer->name }}</td>
            @endif
            <td width="25%" style="font-size: {{ $font_size_table }}px;">Fecha de emisión:</td>
            <td width="15%" style="font-size: {{ $font_size_table }}px;">
                {{ $document->date_of_issue->format('Y-m-d') }}</td>
        </tr>
        <tr>
            @if ($configuration->info_customer_pdf)
                <td style="font-size: {{ $font_size_table }}px;">{{ $customer->identity_document_type->description }}:
                </td>
                <td style="font-size: {{ $font_size_table }}px;">{{ $customer->number }}</td>
            @endif
            @if ($document->date_of_due)
                <td width="25%" style="font-size: {{ $font_size_table }}px;">Tiempo de Validez:</td>
                <td width="15%" style="font-size: {{ $font_size_table }}px;">{{ $document->date_of_due }}</td>
            @endif
        </tr>

        @if ($customer->address !== '')
            <tr>
                @if ($configuration->info_customer_pdf)
                    <td class="align-top" style="font-size: {{ $font_size_table }}px;">Dirección:</td>
                    <td colspan="" style="font-size: {{ $font_size_table }}px;">
                        {{ $customer->address }}
                        {{ $customer->district_id !== '-' ? ', ' . $customer->district->description : '' }}
                        {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                        {{ $customer->department_id !== '-' ? '- ' . $customer->department->description : '' }}
                    </td>
                @endif
                @if ($document->delivery_date)
                    <td width="25%" style="font-size: {{ $font_size_table }}px;">Tiempo de Entrega:</td>
                    <td width="15%" style="font-size: {{ $font_size_table }}px;">{{ $document->delivery_date }}
                    </td>
                @endif
            </tr>
        @endif
        @if (isset($customer->location) && $customer->location != '')
            <tr>
                <td class="align-top" style="font-size: {{ $font_size_table }}px;">Ubicación:</td>
                <td colspan="3" style="font-size: {{ $font_size_table }}px;">{{ $customer->location }}</td>
            </tr>
        @endif
        @if ($document->payment_method_type)
            <tr>
                <td class="align-top" style="font-size: {{ $font_size_table }}px;">T. Pago:</td>
                <td colspan="" style="font-size: {{ $font_size_table }}px;">
                    {{ $document->payment_method_type->description }}
                </td>
                @if ($document->sale_opportunity)
                    <td width="25%" style="font-size: {{ $font_size_table }}px;">O. Venta:</td>
                    <td width="15%" style="font-size: {{ $font_size_table }}px;">
                        {{ $document->sale_opportunity->number_full }}</td>
                @endif
            </tr>
        @endif
        @if ($document->account_number)
            <tr>
                <td class="align-top" style="font-size: {{ $font_size_table }}px;">N° Cuenta:</td>
                <td colspan="3" style="font-size: {{ $font_size_table }}px;">
                    {{ $document->account_number }}
                </td>
            </tr>
        @endif
        @if ($document->shipping_address)
            <tr>
                <td class="align-top" style="font-size: {{ $font_size_table }}px;">Dir. Envío:</td>
                <td colspan="3" style="font-size: {{ $font_size_table }}px;">
                    {{ $document->shipping_address }}
                </td>
            </tr>
        @endif
        @if ($customer->telephone)
            <tr>
                <td class="align-top" style="font-size: {{ $font_size_table }}px;">Teléfono:</td>
                <td colspan="3" style="font-size: {{ $font_size_table }}px;">
                    {{ $customer->telephone }}
                </td>
            </tr>
        @endif
        <tr>
            <td class="align-top" style="font-size: {{ $font_size_table }}px;">
                Vendedor:</td>
            <td colspan="3" style="font-size: {{ $font_size_table }}px;">
                @if ($document->seller->name)
                    {{ $document->seller->name }}
                @else
                    {{ $document->user->name }}
                @endif
            </td>
        </tr>
        @if ($document->quotations_optional)
            <tr>
                <td class="align-top" style="font-size: {{ $font_size_table }}px;">
                    {{ $document->quotations_optional }}:</td>
                <td colspan="3" style="font-size: {{ $font_size_table }}px;">

                    {{ $document->quotations_optional_value }}

                </td>
            </tr>
        @endif

        @if ($document->contact)
            <tr>
                <td class="align-top" style="font-size: {{ $font_size_table }}px;">Contacto:</td>
                <td colspan="3" style="font-size: {{ $font_size_table }}px;">
                    {{ $document->contact }}
                </td>
            </tr>
        @endif
        @if ($document->phone)
            <tr>
                <td class="align-top" style="font-size: {{ $font_size_table }}px;">Telf. Contacto:</td>
                <td colspan="3" style="font-size: {{ $font_size_table }}px;">
                    {{ $document->phone }}
                </td>
            </tr>
        @endif
        @if ($technician_name)
            <tr>
                <td class="align-top" style="font-size: {{ $font_size_table }}px;">Técnico:</td>
                <td colspan="3" style="font-size: {{ $font_size_table }}px;">{{ $technician_name }}</td>
            </tr>
        @endif
        @if ($plate_number_info)
            <tr>
                <td class="align-top" style="font-size: {{ $font_size_table }}px;">N° Placa:</td>
                <td style="font-size: {{ $font_size_table }}px;">{{ $plate_number_info['description'] }}</td>
                <td style="font-size: {{ $font_size_table }}px;">Año:</td>
                <td style="font-size: {{ $font_size_table }}px;">{{ $plate_number_info['year'] }}</td>
            </tr>
            <tr>
                <td style="font-size: {{ $font_size_table }}px;">Marca:</td>
                <td style="font-size: {{ $font_size_table }}px;">{{ $plate_number_info['brand'] }}</td>
                <td style="font-size: {{ $font_size_table }}px;">Modelo:</td>
                <td style="font-size: {{ $font_size_table }}px;">{{ $plate_number_info['model'] }}</td>
            </tr>
            <tr>
                <td style="font-size: {{ $font_size_table }}px;">Color:</td>
                <td style="font-size: {{ $font_size_table }}px;">{{ $plate_number_info['color'] }}</td>
                <td style="font-size: {{ $font_size_table }}px;">Tipo:</td>
                <td style="font-size: {{ $font_size_table }}px;">{{ $plate_number_info['type'] }}</td>
            </tr>
        @endif
        @if ($technical_service_car)
            <tr>
                <td style="font-size: {{ $font_size_table }}px;">Placa:</td>
                <td style="font-size: {{ $font_size_table }}px;">{{ $technical_service_car->plate_number }}</td>
                <td style="font-size: {{ $font_size_table }}px;">Marca:</td>
                <td style="font-size: {{ $font_size_table }}px;">{{ $technical_service_car->brand }}</td>
            </tr>
            <tr>
                <td style="font-size: {{ $font_size_table }}px;">Modelo:</td>
                <td style="font-size: {{ $font_size_table }}px;">{{ $technical_service_car->model }}</td>
                <td style="font-size: {{ $font_size_table }}px;">Kilometraje:</td>
                <td style="font-size: {{ $font_size_table }}px;">{{ $technical_service_car->km }}</td>
            </tr>
            <tr>
                <td style="font-size: {{ $font_size_table }}px;">Tipo Atención:</td>
                <td style="font-size: {{ $font_size_table }}px;">{{ $detail }}</td>
                <td style="font-size: {{ $font_size_table }}px;">Operador:</td>
                <td style="font-size: {{ $font_size_table }}px;">
                    {{ optional($technical_service_car->technical_service->seller)->name }}</td>
            </tr>
        @endif
    </table>

    <table class="full-width mt-3">
        @if ($document->description && !is_integrate_system())
            <tr>
                <td width="15%" class="align-top" style="font-size: {{ $font_size_table }}px;">Observación:
                </td>
                <td width="85%" style="font-size: {{ $font_size_table }}px;">
                    {!! nl2br(e($document->description)) !!}
                </td>
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
                        <td style="font-size: {{ $font_size_table }}px;">{{ $guide->document_type_description }}
                        </td>
                    @else
                        <td style="font-size: {{ $font_size_table }}px;">{{ $guide->document_type_id }}</td>
                    @endif
                    <td style="font-size: {{ $font_size_table }}px;">:</td>
                    <td style="font-size: {{ $font_size_table }}px;">{{ $guide->number }}</td>
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
    <div class="border-box mb-10 mt-2">
        @if ($document->technical_service)

            @if ($document->items->where('item.unit_type_id', 'NIU')->count() > 0)
                <table class="full-width">
                    <thead class="">
                        <tr class="">
                            <th class="border-bottom  desc text-center py-2 rounded-t" width="8%"
                                style="font-size: {{ $font_size_table }}px;">Cant.
                            </th>
                            <th class="border-bottom border-left desc text-center py-2" width="8%"
                                style="font-size: {{ $font_size_table }}px;">Unidad
                            </th>
                            <th class="border-bottom border-left desc text-left py-2 px-2"
                                width="{{ $width_description }}%" style="font-size: {{ $font_size_table }}px;">
                                Descripción</th>
                            @if (!$configuration->document_columns)

                                <th class="border-bottom border-left desc text-right desc py-2 px-2"
                                    width="{{ $width_column }}%" style="font-size: {{ $font_size_table }}px;">
                                    P.Unit</th>
                                <th class="border-bottom border-left desc text-right desc py-2 px-2" width="8%"
                                    style="font-size: {{ $font_size_table }}px;">Dto.
                                </th>
                                <th class="border-bottom border-left desc text-right desc py-2 px-2" width="12%"
                                    style="font-size: {{ $font_size_table }}px;">
                                    Total </th>
                            @else
                                @foreach ($documment_columns as $column)
                                    <th class="border-bottom border-left desc text-center py-2 px-2"
                                        width="{{ $column->width }}%"
                                        style="font-size: {{ $font_size_table }}px;">
                                        {{ $column->name }}</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>

                    @php
                        $cycle = 0;
                    @endphp
                    <tbody>
                        @foreach ($document->items->where('item.unit_type_id', 'NIU') as $row)
                            <tr>
                                <td class="text-left desc" class="text-center desc align-top" width="8%"
                                    style="font-size: {{ $font_size_table }}px;">
                                    @if ((int) $row->quantity != $row->quantity)
                                        {{ $row->quantity }}
                                    @else
                                        {{ number_format($row->quantity, 0) }}
                                    @endif
                                </td>
                                <td class="text-left desc" class="text-center desc align-top border-left"
                                    width="8%" style="font-size: {{ $font_size_table }}px;">
                                    {{-- {{ $row->item->unit_type_id }} --}}
                                    {{ symbol_or_code($row->item->unit_type_id) }}</td>
                                <td class="text-left desc" class="text-left desc align-top border-left px-2"
                                    width="{{ $width_description }}%"
                                    style="font-size: {{ $font_size_table }}px;">
                                    @php
                                        $description = $row->name_product_pdf ?? $row->item->description;
                                        $description = trim($description);
                                        //remove all '&nbsp;' text literals
                                        $symbols = ['&nbsp;', '&amp;', '&quot;', '&lt;', '&gt;'];
                                        $replacements = [' ', '&', '"', '<', '>'];

                                        $description = str_replace($symbols, $replacements, $description);
                                        $description = removePTag($description);
                                    @endphp

                                    <span
                                        style="font-size: {{ $font_size_primary }}px;margin-top: 0px;padding-top: 0px;">
                                        {{-- $description --}} {!! $description !!}


                                    </span>
                                    {{-- @if ($row->name_product_pdf)
                                {!! $row->name_product_pdf !!}
                            @else
                                {!! $row->item->description !!}
                            @endif --}}
                                    @if ($configuration->name_pdf)
                                        @php
                                            $item_name = \App\Models\Tenant\Item::select('name')
                                                ->where('id', $row->item_id)
                                                ->first();
                                        @endphp
                                        @if ($item_name->name)
                                            <div>
                                                <span
                                                    style="font-size: {{ $font_size_secondary }}px">{{ $item_name->name }}</span>
                                            </div>
                                        @endif
                                    @endif
                                    @if (
                                        !is_integrate_system() &&
                                            $configuration->presentation_pdf &&
                                            isset($row->item->presentation) &&
                                            isset($row->item->presentation->description))
                                        <div>
                                            <span
                                                style="font-size: {{ $font_size_secondary }}px">{{ $row->item->presentation->description }}</span>
                                        </div>
                                    @endif
                                    @if ($row->total_isc > 0)
                                        <br /><span style="font-size: {{ $font_size_secondary }}px">ISC :
                                            {{ $row->total_isc }}
                                            ({{ $row->percentage_isc }}%)
                                        </span>
                                    @endif

                                    {{-- 
       
                            --}}

                                    @if ($row->total_plastic_bag_taxes > 0)
                                        <br /><span style="font-size: {{ $font_size_secondary }}px">ICBPER :
                                            {{ $row->total_plastic_bag_taxes }}</span>
                                    @endif

                                    @if ($row->attributes)
                                        @foreach ($row->attributes as $attr)
                                            <br /><span
                                                style="font-size: {{ $font_size_secondary }}px">{!! $attr->description !!}
                                                :
                                                {{ $attr->value }}</span>
                                        @endforeach
                                    @endif
                                    @if ($row->discounts)
                                        @foreach ($row->discounts as $dtos)
                                            @if ($dtos->is_amount == false)
                                                <br /><span
                                                    style="font-size: {{ $font_size_secondary }}px">{{ $dtos->factor * 100 }}%
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
                                            <br /><span
                                                style="font-size: {{ $font_size_secondary }}px">{{ $document->currency_type->symbol }}
                                                {{ $charge->amount }} ({{ $charge->factor * 100 }}%)
                                                {{ $charge->description }}</span>
                                        @endforeach
                                    @endif

                                    @if ($row->item->is_set == 1 && $configuration->show_item_sets)
                                        <br>
                                        @inject('itemSet', 'App\Services\ItemSetService')
                                        @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                                            {{ $item }}<br>
                                        @endforeach
                                    @endif

                                    @if ($row->item->used_points_for_exchange ?? false)
                                        <br>
                                        <span style="font-size: {{ $font_size_secondary }}px">*** Canjeado por
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
                                @if (!$configuration->document_columns)
                                    <td class="text-left desc" class="text-right desc desc align-top border-left px-2"
                                        width="{{ $width_column }}%" style="font-size: {{ $font_size_table }}px;">
                                        @if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf)
                                            {{ $row->generalApplyNumberFormat($row->unit_price, $configuration_decimal_quantity->decimal_quantity_unit_price_pdf) }}
                                        @else
                                            {{ number_format($row->unit_price, 2) }}
                                        @endif
                                    </td>



                                    <td class="text-left desc" class="text-right desc desc align-top border-left px-2"
                                        width="8%" style="font-size: {{ $font_size_table }}px;">
                                        @if ($configuration->discounts_acc)
                                            @if ($row->discounts_acc)
                                                @php
                                                    $discounts_acc = (array) $row->discounts_acc;
                                                @endphp
                                                @foreach ($discounts_acc as $key => $disto)
                                                    <span
                                                        style="font-size: {{ $font_size_secondary }}px">{{ $disto->percentage }}%
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
                                        width="12%" style="font-size: {{ $font_size_table }}px;">
                                        @if (isDacta())
                                            {{ number_format($row->total_value + $row->total_igv + $row->total_isc, 2) }}
                                        @else
                                            {{ number_format($row->total, 2) }}
                                        @endif
                                    </td>
                                @else
                                    @foreach ($documment_columns as $column)
                                        <td class="text-left desc"
                                            class="text-right desc desc align-top border-left px-2"
                                            style="text-align: {{ $column->column_align }}; width: {{ $column->width }}%"
                                            style="font-size: {{ $font_size_table }}px;">
                                            @php
                                                $value = $column->getValudDocumentItem($row, $column->value);

                                            @endphp

                                            @if ($column->value == 'image')
                                                @if (file_exists(public_path(parse_url($value, PHP_URL_PATH))))
                                                    <img src="data:{{ mime_content_type(public_path(parse_url($value, PHP_URL_PATH))) }};base64, {{ base64_encode(file_get_contents(public_path(parse_url($value, PHP_URL_PATH)))) }}"
                                                        alt="Imagen" style="width: 150px; height: auto;">
                                                @endif
                                            @elseif($column->value == 'info_link' && $value)
                                                <a href="{{ $value }}"
                                                    target="_blank">{{ substr($value, 0, 20) }}...</a>
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
                                    if ($configuration->document_columns) {
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
                                @if (!$configuration->document_columns)
                                    <td class="text-left desc" class="text-right desc desc align-top border-left">
                                    </td>
                                    <td class="text-left desc" class="text-right desc desc align-top border-left">
                                    </td>
                                    <td class="text-left desc" class="text-right desc desc align-top border-left">
                                    </td>
                                @else
                                    @foreach ($documment_columns as $column)
                                        <td class="text-left desc" class="text-right desc desc align-top border-left">
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                        @endfor



                    </tbody>
                </table>

            @endif
            @if (
                $document->items->where('item.unit_type_id', 'NIU')->count() > 0 &&
                    $document->items->where('item.unit_type_id', 'ZZ')->count() > 0)
                <div
                    style="height: 10px;
    border-top: 1px solid #000;
    border-left: 1px solid #fff;
    border-right: 1px solid #fff;
    ">
                </div>
            @endif

            @if ($document->items->where('item.unit_type_id', 'ZZ')->count() > 0)

                <table class="full-width">
                    <thead class="">
                        <tr class="">
                            <th class="border-bottom  border-top desc text-center py-2 rounded-t" width="8%"
                                style="font-size: {{ $font_size_table }}px;">Cant.
                            </th>
                            <th class="border-bottom border-left border-top desc text-center py-2" width="8%"
                                style="font-size: {{ $font_size_table }}px;">
                                Unidad
                            </th>
                            <th class="border-bottom border-left border-top desc text-left py-2 px-2"
                                width="{{ $width_description }}%" style="font-size: {{ $font_size_table }}px;">
                                Descripción</th>
                            @if (!$configuration->document_columns)

                                <th class="border-bottom border-left border-top desc text-right desc py-2 px-2"
                                    width="{{ $width_column }}%" style="font-size: {{ $font_size_table }}px;">
                                    P.Unit</th>
                                <th class="border-bottom border-left border-top desc text-right desc py-2 px-2"
                                    width="8%" style="font-size: {{ $font_size_table }}px;">Dto.</th>
                                <th class="border-bottom border-left border-top desc text-right desc py-2 px-2"
                                    width="12%" style="font-size: {{ $font_size_table }}px;">Total </th>
                            @else
                                @foreach ($documment_columns as $column)
                                    <th class="border-bottom border-left border-top desc text-center py-2 px-2"
                                        width="{{ $column->width }}%"
                                        style="font-size: {{ $font_size_table }}px;">
                                        {{ $column->name }}</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>

                    @php
                        $cycle = 0;
                    @endphp
                    <tbody>
                        @foreach ($document->items->where('item.unit_type_id', 'ZZ') as $row)
                            <tr>
                                <td class="text-left desc" class="text-center desc align-top" width="8%"
                                    style="font-size: {{ $font_size_table }}px;">
                                    @if ((int) $row->quantity != $row->quantity)
                                        {{ $row->quantity }}
                                    @else
                                        {{ number_format($row->quantity, 0) }}
                                    @endif
                                </td>
                                <td class="text-left desc" class="text-center desc align-top border-left"
                                    width="8%" style="font-size: {{ $font_size_table }}px;">
                                    {{-- {{ $row->item->unit_type_id }} --}}
                                    {{ symbol_or_code($row->item->unit_type_id) }}</td>
                                <td class="text-left desc" class="text-left desc align-top border-left px-2"
                                    width="{{ $width_description }}%"
                                    style="font-size: {{ $font_size_table }}px;">
                                    @php
                                        $description = $row->name_product_pdf ?? $row->item->description;
                                        $description = trim($description);
                                        //remove all '&nbsp;' text literals
                                        $symbols = ['&nbsp;', '&amp;', '&quot;', '&lt;', '&gt;'];
                                        $replacements = [' ', '&', '"', '<', '>'];

                                        $description = str_replace($symbols, $replacements, $description);
                                        $description = removePTag($description);
                                    @endphp

                                    <span
                                        style="font-size: {{ $font_size_primary }}px;margin-top: 0px;padding-top: 0px;">
                                        {{-- $description --}} {!! $description !!}

                                        @if (is_integrate_system() && isset($row->item->presentation) && isset($row->item->presentation->description))
                                            {{ $row->item->presentation->description }}
                                        @endif
                                    </span>
                                    {{-- @if ($row->name_product_pdf)
                                {!! $row->name_product_pdf !!}
                            @else
                                {!! $row->item->description !!}
                            @endif --}}
                                    @if ($configuration->name_pdf)
                                        @php
                                            $item_name = \App\Models\Tenant\Item::select('name')
                                                ->where('id', $row->item_id)
                                                ->first();
                                        @endphp
                                        @if ($item_name->name)
                                            <div>
                                                <span
                                                    style="font-size: {{ $font_size_secondary }}px">{{ $item_name->name }}</span>
                                            </div>
                                        @endif
                                    @endif
                                    @if (
                                        !is_integrate_system() &&
                                            $configuration->presentation_pdf &&
                                            isset($row->item->presentation) &&
                                            isset($row->item->presentation->description))
                                        <div>
                                            <span
                                                style="font-size: {{ $font_size_secondary }}px">{{ $row->item->presentation->description }}</span>
                                        </div>
                                    @endif
                                    @if ($row->total_isc > 0)
                                        <br /><span style="font-size: {{ $font_size_secondary }}px">ISC :
                                            {{ $row->total_isc }}
                                            ({{ $row->percentage_isc }}%)
                                        </span>
                                    @endif

                                    {{-- 
       
                            --}}

                                    @if ($row->total_plastic_bag_taxes > 0)
                                        <br /><span style="font-size: {{ $font_size_secondary }}px">ICBPER :
                                            {{ $row->total_plastic_bag_taxes }}</span>
                                    @endif

                                    @if ($row->attributes)
                                        @foreach ($row->attributes as $attr)
                                            <br /><span
                                                style="font-size: {{ $font_size_secondary }}px">{!! $attr->description !!}
                                                :
                                                {{ $attr->value }}</span>
                                        @endforeach
                                    @endif
                                    @if ($row->discounts)
                                        @foreach ($row->discounts as $dtos)
                                            @if ($dtos->is_amount == false)
                                                <br /><span
                                                    style="font-size: {{ $font_size_secondary }}px">{{ $dtos->factor * 100 }}%
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
                                            <br /><span
                                                style="font-size: {{ $font_size_secondary }}px">{{ $document->currency_type->symbol }}
                                                {{ $charge->amount }} ({{ $charge->factor * 100 }}%)
                                                {{ $charge->description }}</span>
                                        @endforeach
                                    @endif

                                    @if ($row->item->is_set == 1 && $configuration->show_item_sets)
                                        <br>
                                        @inject('itemSet', 'App\Services\ItemSetService')
                                        @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                                            {{ $item }}<br>
                                        @endforeach
                                    @endif

                                    @if ($row->item->used_points_for_exchange ?? false)
                                        <br>
                                        <span style="font-size: {{ $font_size_secondary }}px">*** Canjeado por
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
                                @if (!$configuration->document_columns)
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
                                        @if ($configuration->discounts_acc)
                                            @if ($row->discounts_acc)
                                                @php
                                                    $discounts_acc = (array) $row->discounts_acc;
                                                @endphp
                                                @foreach ($discounts_acc as $key => $disto)
                                                    <span
                                                        style="font-size: {{ $font_size_secondary }}px">{{ $disto->percentage }}%
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
                                        <td class="text-left desc"
                                            class="text-right desc desc align-top border-left px-2"
                                            style="text-align: {{ $column->column_align }};"
                                            width="{{ $column->width }}%">
                                            @php
                                                $value = $column->getValudDocumentItem($row, $column->value);
                                            @endphp

                                            @if ($column->value == 'image')
                                                @if(file_exists(public_path(parse_url($value, PHP_URL_PATH))))
                                                <img src="data:{{ mime_content_type(public_path(parse_url($value, PHP_URL_PATH))) }};base64, {{ base64_encode(file_get_contents(public_path(parse_url($value, PHP_URL_PATH)))) }}"
                                                    alt="Imagen" style="width: 150px; height: auto;">
                                                @endif
                                            @elseif($column->value == 'info_link' && $value)
                                                <a href="{{ $value }}"
                                                    target="_blank">{{ substr($value, 0, 20) }}...</a>
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
                                    if ($configuration->document_columns) {
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
                                @if (!$configuration->document_columns)
                                    <td class="text-left desc" class="text-right desc desc align-top border-left">
                                    </td>
                                    <td class="text-left desc" class="text-right desc desc align-top border-left">
                                    </td>
                                    <td class="text-left desc" class="text-right desc desc align-top border-left">
                                    </td>
                                @else
                                    @foreach ($documment_columns as $column)
                                        <td class="text-left desc" class="text-right desc desc align-top border-left">
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                        @endfor



                    </tbody>
                </table>
            @endif
        @else
            <table class="full-width">
                <thead class="">
                    <tr class="">
                        <th class="border-bottom  desc text-center py-2 rounded-t" width="8%"
                            style="font-size: {{ $font_size_table }}px;">Cant.
                        </th>
                        <th class="border-bottom border-left desc text-center py-2" width="8%"
                            style="font-size: {{ $font_size_table }}px;">Unidad
                        </th>
                        <th class="border-bottom border-left desc text-left py-2 px-2"
                            width="{{ $width_description }}%" style="font-size: {{ $font_size_table }}px;">
                            Descripción</th>
                        @if (!$configuration->document_columns)

                            <th class="border-bottom border-left desc text-right desc py-2 px-2"
                                width="{{ $width_column }}%" style="font-size: {{ $font_size_table }}px;">P.Unit
                            </th>
                            <th class="border-bottom border-left desc text-right desc py-2 px-2" width="8%"
                                style="font-size: {{ $font_size_table }}px;">Dto.
                            </th>
                            <th class="border-bottom border-left desc text-right desc py-2 px-2" width="12%"
                                style="font-size: {{ $font_size_table }}px;">Total
                            </th>
                        @else
                            @foreach ($documment_columns as $column)
                                <th class="border-bottom border-left desc text-center py-2 px-2"
                                    width="{{ $column->width }}%" style="font-size: {{ $font_size_table }}px;">
                                    {{ $column->name }}</th>
                            @endforeach
                        @endif
                    </tr>
                </thead>

                @php
                    $cycle = 0;
                @endphp
                <tbody>
                    @foreach ($document->items as $row)
                        <tr>
                            <td class="text-left desc" class="text-center desc align-top" width="8%"
                                style="font-size: {{ $global_font_size }}px;">
                                @if ((int) $row->quantity != $row->quantity)
                                    {{ $row->quantity }}
                                @else
                                    {{ number_format($row->quantity, 0) }}
                                @endif
                            </td>
                            <td class="text-left desc" class="text-center desc align-top border-left" width="8%"
                                style="font-size: {{ $global_font_size }}px;">
                                {{-- {{ $row->item->unit_type_id }} --}}
                                {{ symbol_or_code($row->item->unit_type_id) }}</td>
                            <td class="text-left desc" class="text-left desc align-top border-left px-2"
                                width="{{ $width_description }}%" style="font-size: {{ $global_font_size }}px;">
                                @php
                                    $description = $row->name_product_pdf ?? $row->item->description;
                                    $description = trim($description);
                                    //remove all '&nbsp;' text literals
                                    $symbols = ['&nbsp;', '&amp;', '&quot;', '&lt;', '&gt;'];
                                    $replacements = [' ', '&', '"', '<', '>'];

                                    $description = str_replace($symbols, $replacements, $description);
                                    // $description = removePTag($description);
                                    $description = set80pxToImage($description);
                                    if ($font_size_table !== null) {
                                        $description = setFontSizeToElements($description, $font_size_table);
                                    }
                                @endphp

                                <span style="font-size: {{ $global_font_size }}px;margin-top: 0px;padding-top: 0px;">

                                    {{-- $description --}} {!! $description !!}

                                    @if (is_integrate_system() && isset($row->item->presentation) && isset($row->item->presentation->description))
                                        {{ $row->item->presentation->description }}
                                    @endif
                                </span>
                                {{-- @if ($row->name_product_pdf)
                                {!! $row->name_product_pdf !!}
                            @else
                                {!! $row->item->description !!}
                            @endif --}}
                                @if ($configuration->name_pdf)
                                    @php
                                        $item_name = \App\Models\Tenant\Item::select('name')
                                            ->where('id', $row->item_id)
                                            ->first();
                                    @endphp
                                    @if ($item_name->name)
                                        <div>
                                            <span
                                                style="font-size: {{ $font_size_secondary }}px">{{ $item_name->name }}</span>
                                        </div>
                                    @endif
                                @endif
                                @if (
                                    !is_integrate_system() &&
                                        $configuration->presentation_pdf &&
                                        isset($row->item->presentation) &&
                                        isset($row->item->presentation->description))
                                    <div>
                                        <span
                                            style="font-size: 9px">{{ $row->item->presentation->description }}</span>
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
                                        <br /><span
                                            style="font-size: {{ $font_size_secondary }}px">{!! $attr->description !!} :
                                            {{ $attr->value }}</span>
                                    @endforeach
                                @endif
                                @if ($row->discounts)
                                    @foreach ($row->discounts as $dtos)
                                        @if ($dtos->is_amount == false)
                                            <br /><span
                                                style="font-size: {{ $font_size_secondary }}px">{{ $dtos->factor * 100 }}%
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
                                        <br /><span
                                            style="font-size: {{ $font_size_secondary }}px">{{ $document->currency_type->symbol }}
                                            {{ $charge->amount }} ({{ $charge->factor * 100 }}%)
                                            {{ $charge->description }}</span>
                                    @endforeach
                                @endif

                                @if ($row->item->is_set == 1 && $configuration->show_item_sets)
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
                            @if (!$configuration->document_columns)
                                <td class="text-left desc" class="text-right desc desc align-top border-left px-2"
                                    width="{{ $width_column }}%">
                                    @if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf)
                                        {{ $row->generalApplyNumberFormat($row->unit_price, $configuration_decimal_quantity->decimal_quantity_unit_price_pdf) }}
                                    @else
                                        {{ number_format($row->unit_price, 2) }}
                                    @endif
                                </td>



                                <td class="text-left desc" class="text-right desc desc align-top border-left px-2"
                                    width="8%" style="font-size: {{ $font_size_table }}px;">
                                    @if ($configuration->discounts_acc)
                                        @if ($row->discounts_acc)
                                            @php
                                                $discounts_acc = (array) $row->discounts_acc;
                                            @endphp
                                            @foreach ($discounts_acc as $key => $disto)
                                                <span
                                                    style="font-size: {{ $font_size_secondary }}px">{{ $disto->percentage }}%
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
                                    width="12%" style="font-size: {{ $font_size_table }}px;">
                                    @if (isDacta())
                                        {{ number_format($row->total_value + $row->total_igv + $row->total_isc, 2) }}
                                    @else
                                        {{ number_format($row->total, 2) }}
                                    @endif
                                </td>
                            @else
                                @foreach ($documment_columns as $column)
                                    <td class="text-left desc" class="text-right desc desc align-top border-left px-2"
                                        style="text-align: {{ $column->column_align }};"
                                        width="{{ $column->width }}%"
                                        style="font-size: {{ $font_size_table }}px;">
                                        @php
                                            $value = $column->getValudDocumentItem($row, $column->value);
                                        @endphp

                                        @if ($column->value == 'image')
                                            @if(file_exists(public_path(parse_url($value, PHP_URL_PATH))))
                                            <img src="data:{{ mime_content_type(public_path(parse_url($value, PHP_URL_PATH))) }};base64, {{ base64_encode(file_get_contents(public_path(parse_url($value, PHP_URL_PATH)))) }}"
                                                alt="Imagen" style="width: 150px; height: auto;">
                                            @endif
                                        @elseif($column->value == 'info_link' && $value)
                                            <a href="{{ $value }}"
                                                target="_blank">{{ substr($value, 0, 20) }}...</a>
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
                                if ($configuration->document_columns) {
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
                            @if (!$configuration->document_columns)
                                <td class="text-left desc" class="text-right desc desc align-top border-left"></td>
                                <td class="text-left desc" class="text-right desc desc align-top border-left"></td>
                                <td class="text-left desc" class="text-right desc desc align-top border-left"></td>
                            @else
                                @foreach ($documment_columns as $column)
                                    <td class="text-left desc" class="text-right desc desc align-top border-left">
                                    </td>
                                @endforeach
                            @endif
                        </tr>
                    @endfor



                </tbody>
            </table>
        @endif
    </div>
    <table class="full-width">

        @if ($document->prepayments)
            @foreach ($document->prepayments as $p)
                <tr>
                    <td class="text-left desc" class="text-center desc align-top"
                        style="font-size: {{ $global_font_size }}px;">1</td>
                    <td class="text-left desc" class="text-center desc align-top"
                        style="font-size: {{ $global_font_size }}px;">NIU</td>
                    <td class="text-left desc" class="text-left desc align-top"
                        style="font-size: {{ $global_font_size }}px;">
                        Anticipo: {{ $p->document_type_id == '02' ? 'Factura' : 'Boleta' }} Nro.
                        {{ $p->number }}
                    </td>

                    <td class="text-left desc" class="text-right desc desc align-top"
                        style="font-size: {{ $global_font_size }}px;">
                        -{{ number_format($p->total, 2) }}</td>
                    <td class="text-left desc" class="text-right desc desc align-top"
                        style="font-size: {{ $global_font_size }}px;">0</td>
                    <td class="text-left desc" class="text-right desc desc align-top"
                        style="font-size: {{ $global_font_size }}px;">
                        -{{ number_format($p->total, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-left desc" colspan="7" class="border-bottom"
                        style="font-size: {{ $global_font_size }}px;"></td>
                </tr>
            @endforeach
        @endif
        @if ($configuration->taxed_igv_visible_cot)
            @if ($document->total_exportation > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">Op.
                        Exportación:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">
                        {{ number_format($document->total_exportation, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_free > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">Op.
                        Gratuitas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">
                        {{ number_format($document->total_free, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_unaffected > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">Op.
                        Inafectas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">
                        {{ number_format($document->total_unaffected, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_exonerated > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">Op.
                        Exoneradas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">
                        {{ number_format($document->total_exonerated, 2) }}</td>
                </tr>
            @endif

            @if ($document->document_type_id === '07')
                @if ($document->total_taxed >= 0)
                    <tr>
                        <td class="text-left desc" colspan="6" class="text-right desc"
                            style="font-size: {{ $global_font_size }}px;">Op.
                            Gravadas:
                            {{ $document->currency_type->symbol }}
                        </td>
                        <td class="text-left desc" class="text-right desc"
                            style="font-size: {{ $global_font_size }}px;">
                            {{ number_format($document->total_taxed, 2) }}</td>
                    </tr>
                @endif
            @elseif($document->total_taxed > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc"
                        style="font-size: {{ $global_font_size }}px;">Op. Gravadas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc"
                        style="font-size: {{ $global_font_size }}px;">
                        {{ number_format($document->total_taxed, 2) }}</td>
                </tr>
            @endif

            @if ($document->total_plastic_bag_taxes > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">
                        Icbper:
                        {{ $document->currency_type->symbol }}
                    </td>
                    <td class="text-left desc" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">
                        {{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc"
                    style="font-size: {{ $global_font_size }}px;">IGV:
                    {{ $document->currency_type->symbol }}
                </td>
                <td class="text-left desc" class="text-right desc" style="font-size: {{ $global_font_size }}px;">
                    {{ number_format($document->total_igv, 2) }}</td>
            </tr>
            @if ($document->total_isc > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">
                        ISC:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">
                        {{ number_format($document->total_isc, 2) }}</td>
                </tr>
            @endif

            @if ($document->total_discount > 0 && $document->subtotal > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">
                        Subtotal:
                        {{ $document->currency_type->symbol }}
                    </td>
                    <td class="text-left desc" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">
                        {{ number_format($document->subtotal, 2) }}</td>
                </tr>
            @endif

            @if ($document->total_discount > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">
                        {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento total' }}
                        : {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc"
                        style="font-size: {{ $global_font_size }}px;">

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
                        <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                            style="font-size: {{ $global_font_size }}px;">Cargos
                            ({{ $total_factor }}
                            %): {{ $document->currency_type->symbol }}</td>
                        <td class="text-left desc" class="text-right desc font-bold desc"
                            style="font-size: {{ $global_font_size }}px;">
                            {{ number_format($document->total_charge, 2) }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                            style="font-size: {{ $global_font_size }}px;">Cargos:
                            {{ $document->currency_type->symbol }}</td>
                        <td class="text-left desc" class="text-right desc font-bold desc"
                            style="font-size: {{ $global_font_size }}px;">
                            {{ number_format($document->total_charge, 2) }}</td>
                    </tr>
                @endif
            @endif
        @endif
        @if ($document->perception)
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                    style="font-size: {{ $global_font_size }}px;">
                    Importe total:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc font-bold desc" width="12%"
                    style="font-size: {{ $global_font_size }}px;">
                    {{ number_format($document->total, 2) }}</td>
            </tr>
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                    style="font-size: {{ $global_font_size }}px;">
                    Percepción:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc font-bold desc"
                    style="font-size: {{ $global_font_size }}px;">
                    {{ number_format($document->perception->amount, 2) }}</td>
            </tr>
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                    style="font-size: {{ $global_font_size }}px;">
                    Total a pagar:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc font-bold desc"
                    style="font-size: {{ $global_font_size }}px;">
                    {{ number_format($document->total + $document->perception->amount, 2) }}</td>
            </tr>
        @elseif($document->retention)
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                    style="font-size: {{ $global_font_size }}px;">Importe
                    total:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc font-bold desc"
                    style="font-size: {{ $global_font_size }}px;">
                    {{ number_format($document->total, 2) }}</td>
            </tr>
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc"
                    style="font-size: {{ $global_font_size }}px;">Total
                    retención
                    ({{ $document->retention->percentage * 100 }}
                    %): {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc" width="12%"
                    style="font-size: {{ $global_font_size }}px;">
                    {{ number_format($document->retention->amount, 2) }}</td>
            </tr>
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc"
                    style="font-size: {{ $global_font_size }}px;">Importe neto:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc" width="12%"
                    style="font-size: {{ $global_font_size }}px;">
                    {{ number_format($document->total - $document->retention->amount, 2) }}
                </td>
            </tr>
        @else
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                    style="font-size: {{ $global_font_size }}px;">
                    Total a pagar:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc font-bold desc" width="12%"
                    style="font-size: {{ $global_font_size }}px;">
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
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc"
                    style="font-size: {{ $global_font_size }}px;">M.
                    Pendiente:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc font-bold desc"
                    style="font-size: {{ $global_font_size }}px;">
                    {{ number_format($document->total_pending_payment, 2) }}</td>
            </tr>
        @endif

        {{-- @if ($balance < 0)
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                    Vuelto:
                    {{ $document->currency_type->symbol }}
                </td>
                <td class="text-left desc" class="text-right desc font-bold desc">
                    {{ number_format(abs($balance), 2, '.', '') }}</td>
            </tr>
        @endif --}}
    </table>

    <table class="full-width mt-3">
        @if ($document->description && is_integrate_system())
            <tr>
                <td style="font-weight: bold;text-transform:uppercase;" width="15%" class="align-top">Observación:
                </td>
                <td style="font-weight: bold;text-transform:uppercase;" width="85%">{!! str_replace("\n", '<br/>', $document->description) !!}</td>
                {{-- <td width="85%">{{ $document->description }}</td> --}}
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
        <tr>
            {{-- <td width="65%">
            @foreach ($document->legends as $row)
                <p>Son: <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></p>
            @endforeach
            <br/>
            <strong>Información adicional</strong>
            @foreach ($document->additional_information as $information)
                <p>{{ $information }}</p>
            @endforeach
        </td> --}}
        </tr>
    </table>
    <br>
    <table class="full-width">
        <tr>
            <td style="font-size: {{ $font_size_table }}px;">
                <strong>Pagos:</strong>
            </td>
        </tr>
        @php
            $payment = 0;
        @endphp
        @foreach ($document->payments as $row)
            <tr>
                <td style="font-size: {{ $font_size_table }}px;">- {{ $row->payment_method_type->description }} -
                    {{ $row->reference ? $row->reference . ' - ' : '' }}
                    {{ $document->currency_type->symbol }} {{ $row->payment }}</td>
            </tr>
            @php
                $payment += (float) $row->payment;
            @endphp
        @endforeach
        <tr>
            <td style="font-size: {{ $font_size_table }}px;"><strong>Saldo:</strong>
                {{ $document->currency_type->symbol }}
                {{ number_format($document->total - $payment, 2) }}</td>
        </tr>

    </table>
    @if ($document->fee->count())
        <table class="full-width">
            @foreach ($document->fee as $key => $quote)
                <tr>
                    <td style="font-size: {{ $font_size_table }}px;">
                        @if (!$configuration->show_the_first_cuota_document)
                            &#8226;
                            {{ 'Cuota #' . ($key + 1) }}
                            / Fecha: {{ $quote->date }} /
                            Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}
                        @else
                            @if ($key == 0)
                                &#8226;
                                {{ 'Cuota #' . ($key + 1) }}
                                / Fecha: {{ $quote->date }} /
                                Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}
                            @endif
                        @endif

                    </td>
                </tr>
            @endforeach
            </tr>
        </table>
    @endif
    <table class="full-width">

        <tbody>
            <tr>
                @if ($configuration->yape_qr_quotations && $establishment_data->yape_logo)
                    @php
                        $yape_logo = $establishment_data->yape_logo;
                    @endphp
                    <td class="text-center">
                        <table>
                            <tr>
                                <td style="font-size: {{ $font_size_table }}px;">
                                    <strong>
                                        Qr yape
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: {{ $font_size_table }}px;">
                                    <img src="data:{{ mime_content_type(public_path("{$yape_logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$yape_logo}"))) }}"
                                        alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: {{ $font_size_table }}px;">
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
                                <td style="font-size: {{ $font_size_table }}px;">
                                    <strong>
                                        Qr plin
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: {{ $font_size_table }}px;">
                                    <img src="data:{{ mime_content_type(public_path("{$plin_logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$plin_logo}"))) }}"
                                        alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: {{ $font_size_table }}px;">
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
</body>

</html>
