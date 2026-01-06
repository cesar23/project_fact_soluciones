@php
    $establishment__ = \App\Models\Tenant\Establishment::where('id', $document->establishment_id)->first();
    $establishment = $document->establishment;
$configuration = \App\Models\Tenant\Configuration::first();
$configurations = \App\Models\Tenant\Configuration::first();
$company_name = $company->name;
$company_owner = null;
if ($configurations->trade_name_pdf) {
    $company_name = $company->trade_name;
    $company_owner = $company->name;
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
    }    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
    $is_integrate_system = Modules\BusinessTurn\Models\BusinessTurn::isIntegrateSystem();
    $quotation = null;
    if ($is_integrate_system) {
        $quotation = \App\Models\Tenant\Quotation::select(['number', 'prefix', 'shipping_address'])
            ->where('id', $document->quotation_id)
            ->first();
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
    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();
    $documment_columns = \App\Models\Tenant\DocumentColumn::where('is_visible', true)
        ->orderByRaw('CASE WHEN `order` = 0 THEN 0 ELSE 1 END, `order` ASC')
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
        <h6 style="text-transform: uppercase;margin: 0px !important;line-height:0px;">
            {{ $establishment->address !== '-' ? $establishment->address : '' }}
            {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
            {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
            {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
        </h6>

        @isset($establishment->trade_address)
            <h6 style="margin: 0px !important;line-height:0px;">
                {{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
            </h6>
        @endisset

        <h6 style="margin: 0px !important;line-height:0px;">
            {{ $establishment->telephone !== '-' ? '' . $establishment->telephone : '' }}
        </h6>

        <h6 style="margin: 0px !important;line-height:0px;">
            {{ $establishment->email !== '-' ? '' . $establishment->email : '' }}</h6>

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
        <div style="border-radius:10px;border:1px solid black;text-align:center;width: 100%;height: 80px;">
            <div style="margin-top:12px;">{{ 'RUC ' . $company->number }}</div>
            <div class="text-center" style="margin-top:3px;">{{ get_document_name('sale_note', 'Nota de venta') }}</div>
            <div class="text-center" style="margin-top:3px;">{{ $tittle }}</div>
        </div>
    </div>
    </div>
    <table class="full-width mt-5">
        <tr>
            <td width="15%">Cliente:</td>
            <td width="45%">{{ $customer->name }}</td>
            <td width="25%">Fecha de emisión:</td>
            <td width="15%">{{ $document->date_of_issue->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td>{{ $customer->identity_document_type->description }}:</td>
            <td>{{ $customer->number }}</td>

            @if ($document->due_date)
                <td class="align-top">Fecha Vencimiento:</td>
                <td>{{ $document->getFormatDueDate() }}</td>
            @endif

        </tr>
        @if (isset($customer->location) && $customer->location != '')
            <tr>
                <td class="align-top">Ubicación:</td>
                <td colspan="3">{{ $customer->location }}</td>
            </tr>
        @endif
        @if ($customer->address !== '')
            <tr>
                <td class="align-top">Dirección:</td>
                <td colspan="3">
                    {{ strtoupper($customer->address) }}
                    {{ $customer->district_id !== '-' ? ', ' . strtoupper($customer->district->description) : '' }}
                    {{ $customer->province_id !== '-' ? ', ' . strtoupper($customer->province->description) : '' }}
                    {{ $customer->department_id !== '-' ? '- ' . strtoupper($customer->department->description) : '' }}
                </td>
            </tr>
        @endif
        @if ($document->shipping_address)
            <tr>
                <td class="align-top">Dir. de envío:</td>
                <td colspan="3">{{ $document->shipping_address }}</td>
            </tr>
        @endif
        @if ($quotation && $quotation->shipping_address)
            <tr>
                <td class="align-top">Dir. de envío:</td>
                <td colspan="3">{{ $quotation->shipping_address }}</td>
            </tr>
        @endif

        <tr>
            <td>Teléfono:</td>
            <td>{{ $customer->telephone }}</td>
            <td>Vendedor:</td>
            <td>
                @if ($document->seller_id != 0)
                    {{ $document->seller->name }}
                @else
                    {{ $document->user->name }}
                @endif
            </td>
        </tr>
        @if ($document->plate_number !== null)
            <tr>
                <td width="15%">N° Placa:</td>
                <td width="85%">{{ $document->plate_number }}</td>
            </tr>
        @endif
        @if ($is_integrate_system)
            <tr>
                <td class="align-top">Estado:</td>
                <td colspan="3">
                    @if ($document->total_canceled)
                        PAGADO
                    @else
                        PENDIENTE DE PAGO
                    @endif
                </td>
            </tr>
            @if ($document->total_canceled)
                <tr>
                    <td class="align-top">Condición de pago:</td>
                    <td class="align-top">
                        @if ($document->payment_condition)
                            {{ $document->payment_condition->name }}
                        @else
                            CONTADO
                        @endif
                    </td>
                    <td class="align-top">Método de pago:</td>
                    <td class="align-top">
                        @isset($document->payments)
                            @php
                                $first_payment = $document->payments->first();
                            @endphp
                            @if ($first_payment)
                                {{ $first_payment->payment_method_type->description }}
                            @endif
                        @endisset


                    </td>
                </tr>
            @else
                @if (is_integrate_system() && $document->payment_condition_id == '02')
                <tr>

                    <td class="align-top">Condición de pago:</td>
                    <td class="align-top">Crédito</td>
                    <td></td>
                    <td></td>
                </tr>
                @endif
            @endif
        @else
            @if ($document->total_canceled)
                <tr>
                    <td class="align-top">Estado:</td>
                    <td colspan="3">CANCELADO</td>
                </tr>
            @else
                <tr>
                    <td class="align-top">Estado:</td>
                    <td colspan="3">PENDIENTE DE PAGO</td>
                </tr>
            @endif
        @endif
        @if ($document->hotelRent)
            <tr>
                <td class="align-top">Destino:</td>
                <td colspan="3">{{ $document->hotelRent->destiny }}</td>
            </tr>
        @endif
        @if ($configuration->show_dispatcher_documents_sale_notes_order_note)
            @isset($document->order_note)
                @php
                    $order_note = $document->order_note;
                    $ship_address = $order_note->shipping_address;
                    $observation = $order_note->observation;
                @endphp
                @if ($ship_address)
                    <tr>
                        <td class="align-top">Dirección de envio:</td>
                        <td class="align-top">
                            {{ $ship_address }}
                        </td>
                    </tr>
                @endif
                @if ($observation)
                    <tr>
                        <td class="align-top">
                            Observación Pd:</td>
                        <td class="align-top">
                            {{ $observation }}
                        </td>
                    </tr>
                @endif
            @endisset
        @endif
        @if ($document->observation && !is_integrate_system())
            <tr>
                <td class="align-top">Observación:</td>
                <td colspan="3">{{ $document->observation }}</td>
            </tr>
        @endif
        @if ($document->reference_data)
            <tr>
                <td class="align-top">D. Referencia:</td>
                <td colspan="3">{{ $document->reference_data }}</td>
            </tr>
        @endif
        @if ($document->purchase_order)
            <tr>
                <td class="align-top">Orden de compra:</td>
                <td colspan="3">{{ $document->purchase_order }}</td>
            </tr>
        @endif
    </table>

    @if ($document->isPointSystem())
        <table class="full-width mt-3">
            <tr>
                <td width="15%">P. ACUMULADOS</td>
                <td width="8px">:</td>
                <td>{{ $document->person->accumulated_points }}</td>

                <td width="140px">PUNTOS POR LA COMPRA</td>
                <td width="8px">:</td>
                <td>{{ $document->getPointsBySale() }}</td>
            </tr>
        </table>
    @endif


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
    @if ($document->transport)
        <br>
        <strong>Transporte de pasajeros</strong>
        @php
            $transport = $document->transport;
            $origin_district_id = (array) $transport->origin_district_id;
            $destinatation_district_id = (array) $transport->destinatation_district_id;
            $origin_district = Modules\Order\Services\AddressFullService::getDescription($origin_district_id[2]);
            $destinatation_district = Modules\Order\Services\AddressFullService::getDescription(
                $destinatation_district_id[2],
            );
        @endphp

        <table class="full-width mt-3">
            <tr>
                <td width="120px">{{ $transport->identity_document_type->description }}</td>
                <td width="8px">:</td>
                <td>{{ $transport->number_identity_document }}</td>
                <td width="120px">NOMBRE</td>
                <td width="8px">:</td>
                <td>{{ $transport->passenger_fullname }}</td>
            </tr>
            <tr>
                <td width="120px">N° ASIENTO</td>
                <td width="8px">:</td>
                <td>{{ $transport->seat_number }}</td>
                <td width="120px">M. PASAJERO</td>
                <td width="8px">:</td>
                <td>{{ $transport->passenger_manifest }}</td>
            </tr>
            <tr>
                <td width="120px">F. INICIO</td>
                <td width="8px">:</td>
                <td>{{ $transport->start_date }}</td>
                <td width="120px">H. INICIO</td>
                <td width="8px">:</td>
                <td>{{ $transport->start_time }}</td>
            </tr>
            <tr>
                <td width="120px">U. ORIGEN</td>
                <td width="8px">:</td>
                <td>{{ $origin_district }}</td>
                <td width="120px">D. ORIGEN</td>
                <td width="8px">:</td>
                <td>{{ $transport->origin_address }}</td>
            </tr>
            <tr>
                <td width="120px">U. DESTINO</td>
                <td width="8px">:</td>
                <td>{{ $destinatation_district }}</td>
                <td width="120px">D. DESTINO</td>
                <td width="8px">:</td>
                <td>{{ $transport->destinatation_address }}</td>
            </tr>
        </table>
    @endif
    @if ($document->transport_dispatch)
        <br>
        <strong>Información de encomienda</strong>
        @php
            $transport_dispatch = $document->transport_dispatch;
            $sender_identity_document_type = $transport_dispatch->sender_identity_document_type->description;
            $recipient_identity_document_type = $transport_dispatch->recipient_identity_document_type->description;
            // $origin_district_id = (array) $transport_dispatch->origin_district_id;
            // $destinatation_district_id = (array) $transport_dispatch->destinatation_district_id;
            // $origin_district = Modules\Order\Services\AddressFullService::getDescription($origin_district_id[2]);
            // $destinatation_district = Modules\Order\Services\AddressFullService::getDescription($destinatation_district_id[2]);
        @endphp

        <table class="full-width mt-3">
            <thead>
                <tr>
                    <th colspan="6" class="text-left">
                        <strong>REMITENTE</strong>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="120px">{{ $sender_identity_document_type }}</td>
                    <td width="8px">:</td>
                    <td>{{ $transport_dispatch->sender_number_identity_document }}</td>
                    <td width="120px">NOMBRE</td>
                    <td width="8px">:</td>
                    <td>{{ $transport_dispatch->sender_passenger_fullname }}</td>
                </tr>
                <tr>

                </tr>
                <tr>
                    <td width="120px">TELÉFONO</td>
                    <td width="8px">:</td>
                    <td>{{ $transport_dispatch->sender_telephone }}</td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
            <thead>
                <tr>
                    <th colspan="6" class="text-left">
                        <strong>DESTINATARIO</strong>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="120px">{{ $recipient_identity_document_type }}</td>
                    <td width="8px">:</td>
                    <td>{{ $transport_dispatch->recipient_number_identity_document }}</td>
                    <td width="120px">NOMBRE</td>
                    <td width="8px">:</td>
                    <td>{{ $transport_dispatch->recipient_passenger_fullname }}</td>
                </tr>

                <tr>
                    <td width="120px">TELÉFONO</td>
                    <td width="8px">:</td>
                    <td>{{ $transport_dispatch->recipient_telephone }}</td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
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
        <table class="full-width">
            <thead class="">
                <tr class="">
                    <th class="border-bottom  desc text-center py-2 rounded-t" width="8%">Cant.
                    </th>
                    <th class="border-bottom border-left desc text-center py-2" width="8%">Unidad
                    </th>
                    <th class="border-bottom border-left desc text-left py-2 px-2"
                        width="{{ $width_description }}%">Descripción</th>
                    @if (!$configurations->document_columns)

                        <th class="border-bottom border-left desc text-right desc py-2 px-2"
                            width="{{ $width_column }}%">P.Unit</th>
                        <th class="border-bottom border-left desc text-right desc py-2 px-2"
                            width="8%">Dto.</th>
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
            @php
                $cycle = 24;
                $count_items = count($document->items);
                if ($count_items > 7) {
                    $cycle = 0;
                } else {
                    $cycle = 24 - $count_items;
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
                                <br>
                                *** Pago Anticipado ***
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
                                    width="{{ $column->width }}%">
                                    @php
                                        $value = $column->getValudDocumentItem($row, $column->value);
                                    @endphp
                            @if($column->value == 'image')
                            <img src="data:{{ mime_content_type(public_path(parse_url($value, PHP_URL_PATH))) }};base64, {{ base64_encode(file_get_contents(public_path(parse_url($value, PHP_URL_PATH)))) }}" alt="Imagen" style="width: 150px; height: auto;">
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
    @if (is_integrate_system())
        <table class="full-width">
            @php
                $cot = \App\Models\Tenant\Quotation::where('id', $document->quotation_id)->first();
            @endphp
            @if ($cot)
                <tr>
                    <td width="23%" style="font-weight: bold;text-transform:uppercase;" class="align-top">
                        Cotizacion :</td>
                    <td style="font-weight: bold;text-transform:uppercase;text-align:left;" colspan="3">
                        {{ $cot->prefix }}- {{ $cot->number }}</td>

                </tr>
            @endif
            @if ($cot)
                <tr>
                    <td width="23%" style="font-weight: bold;text-transform:uppercase;" class="align-top">
                        Observación com.:</td>
                    <td style="font-weight: bold;text-transform:uppercase;text-align:left;" colspan="3">
                        {{ $cot->description }}</td>

                </tr>
            @endif
            <tr>
                <td width="23%" style="font-weight: bold;text-transform:uppercase;" class="align-top">Observación
                    adm.:
                </td>
                <td style="font-weight: bold;text-transform:uppercase;text-align:left;" colspan="3">
                    {{ $document->additional_information }}</td>
            </tr>

            @php
                $prod = \App\Models\Tenant\ProductionOrder::where('sale_note_id', $document->id)->first();
            @endphp
            @if ($prod)
                <tr>
                    <td width="23%" style="font-weight: bold;text-transform:uppercase;" class="align-top">
                        Observación prod.:</td>
                    <td style="font-weight: bold;text-transform:uppercase;text-align:left;" colspan="3">
                        {{ $prod->observation }}</td>

                </tr>
            @endif
        </table>
    @endif
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
                <td>
                    <strong>Pagos:</strong>
                </td>
            </tr>
            @php
                $payment = 0;
            @endphp
            @foreach ($payments as $row)
                <tr>
                    <td>- {{ $row->date_of_payment->format('d/m/Y') }} -
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
                <td><strong>Saldo:</strong> {{ $document->currency_type->symbol }}
                    {{ number_format($document->total - $payment, 2) }}</td>
            </tr>

        </table>
    @endif
    @if ($document->fee->count())
        <table class="full-width">
            @foreach ($document->fee as $key => $quote)
                <tr>
                    <td>
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
        @php
            $establishment_data = \App\Models\Tenant\Establishment::find($document->establishment_id);
        @endphp
        <tbody>
            <tr>
                @if ($configuration->yape_qr_sale_notes && $establishment_data->yape_logo)
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
                @if ($configuration->plin_qr_sale_notes && $establishment_data->plin_logo)
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


</body>

</html>
