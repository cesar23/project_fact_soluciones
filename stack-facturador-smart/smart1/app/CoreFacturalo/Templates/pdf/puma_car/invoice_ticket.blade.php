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
    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();
    $customer = $document->customer;
    $total_discount_items = 0;
    $invoice = $document->invoice;
    $has_transport_dispatch = false;
    //$path_style =
    app_path(
        'CoreFacturalo' .
            DIRECTORY_SEPARATOR .
            'Templates' .
            DIRECTORY_SEPARATOR .
            'pdf' .
            DIRECTORY_SEPARATOR .
            'style.css',
    );
    $document_number = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
    $document_base = $document->note ? $document->note : null;
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
    $is_transport = \Modules\BusinessTurn\Models\BusinessTurn::isTransport();
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
    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = $document->total - $total_payment - $document->payments->sum('change');

    $establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $logo = $establishment__->logo ?? $company->logo;
    if ($logo === null && !file_exists(public_path("$logo"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
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
    $documment_columns = \App\Models\Tenant\DocumentColumn::where('is_visible', true)->where('type','DOC')
        ->orderBy('column_order', 'asc')
        ->get();
    $plate_number_info = $document->plate_number_info;
@endphp
<html>

<head>
    {{-- <title>{{ $document_number }}</title> --}}
    {{--
    <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body class="ticket">

    @if ($logo && file_exists(public_path("{$logo}")))
        <div class="text-center company_logo_box {{ !$is_transport ? 'pt-4' : '' }}">
            <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                alt="{{ $company->name }}" class="company_logo_ticket contain">
        </div>
    @endif

    @if ($document->state_type->id == '11' || $document->state_type->id == '09' || $document->state_type->id == '55')
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
    <table class="full-width">
        {{-- <tr>
            <td class="text-center" style="text-transform:uppercase;">{{ $company_name }} </td>
        </tr>
        <tr>
            @if ($company_owner)
                <td class="text-center" style="text-transform:uppercase;">De: {{ $company_owner }}</td>
            @endif
        </tr>
        <tr>
            <td class="text-center">{{ 'RUC ' . $company->number }}</td>
        </tr> --}}
        <tr>
            <td class="text-center f9" style="text-transform: capitalize;">
                Domicilio fiscal:
                {{ $establishment->address !== '-' ? $establishment->address : '' }}
                {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                {{ $establishment->department_id !== '-' ? ', ' . $establishment->department->description : '' }}
            </td>
        </tr>

        {{-- @isset($establishment->trade_address)
                <tr>
                    <td class="text-center ">
                        {{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                    </td>
                </tr>
            @endisset --}}
        <tr>
            <td class="text-center f9">
                Tlf: {{ $establishment->telephone !== '-' ? 'Central telefónica: ' . $establishment->telephone : '' }}
            </td>
        </tr>
        <tr>
            <td class="text-center f9">
                Películas de Seguridad - Accesorios para Automóviles - Aire Acondicionado
            </td>
        </tr>

        <tr>
            <td class="f13 text-center">
                <strong>RUC: {{ $company->number }}</strong>
            </td>
        </tr>

        <tr>
            <td style="vertical-align: top; text-transform: uppercase;" class="font-bold text-center f10">
                <h5>{{ $document->document_type->description }} {{ $document_number }}</h5>
            </td>
        </tr>

    </table>
    <br>
    <table class="full-width">
        <tr>
            <td class="f9">
                <strong>CLIENTE:</strong>
            </td>
            <td class="f9">
                <strong>:</strong>
            </td>
            <td colspan="4" class="f9">
                {{ $customer->name }}
            </td>

        </tr>
        <tr>
            <td class="f9">
                <strong>RUC:</strong>
            </td>
            <td class="f9">
                <strong>:</strong>
            </td>
            <td colspan="4" class="f9">
                {{ $customer->number }}
            </td>
        </tr>
        <tr>
            <td class="f9">
                <strong>DIRECCION:</strong>
            </td>
            <td class="f9">
                <strong>:</strong>
            </td>
            <td colspan="4" class="f9">
                {{ $customer->address }}
            </td>
        </tr>
        <tr>
            <td class="f9">
                <strong>FECHA EMISIÓN</strong>
            </td>
            <td class="f9">
                <strong>:</strong>
            </td>
            <td class="f9" width="25%">
                {{ $document->date_of_issue->format('d/m/Y') }}
            </td>
            <td class="f9">
                <strong>FECHA VENC.</strong>
            </td>
            <td class="f9">
                <strong>:</strong>
            </td>
            <td class="f9">
                {{ $document->invoice->date_of_due->format('d/m/Y') }}
            </td>

        </tr>
        <tr>
            <td class="f9">
                <strong>FORMA DE PAGO</strong>
            </td>
            <td class="f9">
                <strong>:</strong>
            </td>
            <td class="f9" width="25%">
                {{ $document->payment_condition->description }}
            </td>
            <td class="f9">
                <strong>MONEDA</strong>
            </td>
            <td class="f9">
                <strong>:</strong>
            </td>
            <td class="f9">
                {{ $document->currency_type_id }}
            </td>
        </tr>
    </table>





    @if (!is_null($document_base))
        <table>
            <tr>
                <td class="desc">Documento Afectado:</td>
                <td class="desc">{{ $affected_document_number }}</td>
            </tr>
            <tr>
                <td class="desc">Tipo de nota:</td>
                <td class="desc">
                    {{ $document_base->note_type === 'credit'
                        ? $document_base->note_credit_type->description
                        : $document_base->note_debit_type->description }}
                </td>
            </tr>
            <tr>
                <td class="align-top desc">Descripción:</td>
                <td class="text-left desc">{{ $document_base->note_description }}</td>
            </tr>
        </table>
    @endif
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

                    <th class="border-bottom border-left desc text-left py-2 px-2" width="{{ $width_description }}%">
                        Descripción</th>

                    <th class="border-bottom border-left desc text-right desc py-2 px-2" width="{{ $width_column }}%">
                        Precio</th>

                    <th class="border-bottom border-left desc text-right desc py-2 px-2" width="12%">Importe</th>

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

            @endphp --}}
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
                            @isset($row->item->type_discount)
                                <br>
                                <span style="font-size: 9px">Dscto: {{ $row->item->type_discount }}</span>
                            @endisset
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
                                <div>*** Pago Anticipado ***</div>
                            @endif
                        </td>.



                        <td class="text-left desc" class="text-right desc desc align-top border-left px-2"
                            width="{{ $width_column }}%">
                            @if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf)
                                {{ $row->generalApplyNumberFormat($row->unit_price, $configuration_decimal_quantity->decimal_quantity_unit_price_pdf) }}
                            @else
                                {{ number_format($row->unit_price, 2) }}
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

                    </tr>
                @endforeach

                {{-- @for ($i = 0; $i < $cycle; $i++) --}}

                {{-- @endfor --}}



            </tbody>
        </table>
    </div>
    <div class="full-width f10">
        @foreach (array_reverse((array) $document->legends) as $row)
            Son: <span class="font-bold" style="text-transform: uppercase;">{{ $row->value }}
                {{ $document->currency_type->description }}
        @endforeach
    </div>
    <div class="text-center">
        <img class="" style="width: 180px" src="data:image/png;base64, {{ $document->qr }}" />
    </div>
    <table class="full-width">
        <tr>
            <td class="text-left f9">Operación gravada</td>
            <td class="text-right f9">
                {{ $document->total_taxed }}
            </td>
            <td width="10px">
                <br>
            </td>
            <td class="text-left f9">
                Operación exonerada
            </td>
            <td class="text-right f9">
                {{ $document->total_exonerated }}
            </td>
        </tr>
        <tr>
            <td class="text-left f9">
                Operación inafecta
            </td>
            <td class="text-right f9">
                {{ $document->total_unaffected }}
            </td>
            <td width="10px">
                <br>
            </td>
            <td class="text-left f9">
                Operación gratuita
            </td>
            <td class="text-right f9">
                {{ $document->total_free }}
            </td>


        </tr>
        <tr>
            <td class="text-left f9">
                Total descuento
            </td>
            <td class="text-right f9">
                {{ $document->total_discount }}
            </td>
            <td width="10px">
                <br>
            </td>
            <td class="text-left f9">
                ISC
            </td>
            <td class="text-right f9">
                {{ $document->total_isc }}
            </td>

        </tr>
        <tr>
            <td class="text-left f9">
                IGV
            </td>
            <td class="text-right f9">
                {{ $document->total_igv }}
            </td>
            <td width="10px">
                <br>
            </td>
            <td class="text-left f9">
                Importe total
            </td>
            <td class="text-right f9">
                {{ $document->total }}
            </td>
        </tr>
    </table>


    @php
        $plate_number_document = $document->plateNumberDocument;
        $plate_number = null;
        if ($plate_number_document) {
            $plate_number = $plate_number_document->plateNumber;
        }
    @endphp
    <table class="full-width mt-1 mb-2">
        <tr>
            <td class="text-left f9">
                <strong>PLACA</strong>
            </td>
            <td class="text-left f9">
                {{ $plate_number ? $plate_number->description : '' }}
            </td>
            <td class="text-left f9" width="10px">
            </td>
            <td class="text-left f9">

            </td>
            <td class="text-left f9">

            </td>
        </tr>
        <tr>
            <td class="text-left f9">
                <strong>MARCA</strong>
            </td>
            <td class="text-left f9">
                {{ $plate_number ? optional($plate_number->brand)->description ?? '' : '' }}
            </td>
            <td class="text-left f9" width="10px">
            </td>
            <td class="text-left f9">
                <strong>MODELO</strong>
            </td>
            <td class="text-left f9">
                {{ $plate_number ? optional($plate_number->model)->description ?? '' : '' }}
            </td>
        </tr>
    </table>

    @foreach ($accounts as $account)
        <div style="float: left;width: 50%;">
            <div>
                <strong style="font-size: 9px;">CCTA CTE {{ $account->description }}
                    {{ $account->currency_type->symbol }}
                    {{ $account->number }}</strong>
            </div>

        </div>
    @endforeach

    <div class="full-width text-center mt-2">
        <div style="font-size: 9px; margin-top: 5px; text-align: center;">
            Representación impresa de la Factura Electrónica
        </div>
        <div style="font-size: 9px; text-align: center;">
            Vea una copia en : http://www.sunat.gob.pe usando la clave sol
        </div>
        <div style="font-size: 9px; text-align: center;">
            Documento emitido con Resolución Anexo IV-RS-155-2017 / SUNAT
        </div>
        <div style="font-size: 9px; text-align: center; margin-top: 5px;">
            Todo cambio o devolución del producto será dentro de 15 dias útiles de realizada la compra, con sus accesorios y empaques completos sin señales de uso
        </div>
        <div style="font-size: 9px; text-align: right; margin-top: 5px;">
            <strong>{{ $document->seller ? $document->seller->name : $document->user->name }}</strong>
        </div>
        
        
    </div>

</body>

</html>
