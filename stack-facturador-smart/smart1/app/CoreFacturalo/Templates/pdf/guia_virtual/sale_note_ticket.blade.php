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

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

    $customer = $document->customer;
    $invoice = $document->invoice;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $tittle = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
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
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();

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
    $documment_columns = \App\Models\Tenant\DocumentColumn::where('is_visible', true)
        ->orderByRaw('CASE WHEN `order` = 0 THEN 0 ELSE 1 END, `order` ASC')
        ->get();
    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();
    $total_discount_items = 0;
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
                <h4>{{ $company_name }}</h4>
            </td>
        </tr>
        @if ($company_owner)
            <tr>
                <td class="text-center">
                    <h5>De: {{ $company_owner }}</h5>
                </td>
            </tr>
        @endif
        <tr>
            <td class="text-center">
                <h5>{{ 'RUC ' . $company->number }}</h5>
            </td>
        </tr>
        <tr>
            <td class="text-center" style="text-transform: uppercase;">
                {{ $establishment->address !== '-' ? $establishment->address : '' }}
                {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
            </td>
        </tr>
        <tr>
            <td class="text-center">
                {{ $establishment->email !== '-' ? $establishment->email : '' }}
            </td>
        </tr>
        <tr>
            <td class="text-center pb-3">
                {{ $establishment->telephone !== '-' ? $establishment->telephone : '' }}
            </td>
        </tr>
        <tr>
            <td class="text-center pt-3 border-top">
                <h4>{{ get_document_name('sale_note', 'Nota de venta') }}</h4>
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
                <p class="desc">{{ $document->date_of_issue->format('Y-m-d') }}
                    {{ $document->time_of_issue }}
                </p>
            </td>
        </tr>
        @if ($document->due_date)
            <tr>
                <td width="" class="pt-3">
                    <p class="desc">F. Vencimiento:</p>
                </td>
                <td width="" class="pt-3">
                    <p class="desc">{{ $document->getFormatDueDate() }}</p>
                </td>
            </tr>
        @endif

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
                <p class="desc">
                    {{ $customer->identity_document_type->description }}:</p>
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
                        {{ strtoupper($customer->address) }}
                        {{ $customer->district_id !== '-' ? ', ' . strtoupper($customer->district->description) : '' }}
                        {{ $customer->province_id !== '-' ? ', ' . strtoupper($customer->province->description) : '' }}
                        {{ $customer->department_id !== '-' ? '- ' . strtoupper($customer->department->description) : '' }}
                    </p>
                </td>
            </tr>
        @endif
        @if ($document->shipping_address)
            <tr>
                <td class="align-top">
                    <p class="desc">Dir. de envío:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->shipping_address }}</p>
                </td>
            </tr>
        @endif
        @if ($quotation && $quotation->shipping_address)
            <tr>
                <td class="align-top">
                    <p class="desc">Dir. de envío:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $quotation->shipping_address }}
                    </p>
                </td>
            </tr>
        @endif
        @if (isset($customer->location) && $customer->location !== '')
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
        @if ($customer->telephone && $customer->telephone !== '')
            <tr>
                <td class="align-top">
                    <p class="desc">Teléfono:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ strtoupper($customer->telephone) }}
                    </p>
                </td>
            </tr>
        @endif
        @if (is_integrate_system())
            <tr>
                <td class="align-top">
                    <p class="desc">Estado:</p>
                </td>
                <td>
                    <p class="desc">
                        @if ($document->total_canceled)
                            PAGADO
                        @else
                            PENDIENTE DE PAGO
                        @endif
                    </p>
                </td>
            </tr>
            @if ($document->total_canceled)
                <tr>
                    <td class="align-top">
                        <p class="desc">Condición de pago:</p>
                    </td>
                    <td>
                        <p class="desc">
                            @if ($document->payment_condition)
                                {{ $document->payment_condition->name }}
                            @else
                                CONTADO
                            @endif
                        </p>
                    </td>
                </tr>
                <tr>
                    <td class="align-top">
                        <p class="desc">Método de pago:</p>
                    </td>
                    <td>
                        <p class="desc">
                            @isset($document->payments)
                                @php
                                    $first_payment = $document->payments->first();
                                @endphp
                                @if ($first_payment)
                                    {{ $first_payment->payment_method_type->description }}
                                @endif
                            @endisset
                        </p>
                    </td>
                </tr>
            @endif
        @endif
        <tr>
            <td class="align-top">
                <p class="desc">Vendedor:</p>
            </td>
            <td class="desc">
                @if ($document->seller_id != 0)
                    {{ $document->seller->name }}
                @else
                    {{ $document->user->name }}
                @endif
            </td>
        </tr>

        @if ($document->hotelRent)
            <tr>
                <td>Destino:</td>
                <td>{{ $document->hotelRent->destiny }}</td>
            </tr>
        @endif
        @if ($document->plate_number !== null)
            <tr>
                <td class="align-top">
                    <p class="desc">N° Placa:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->plate_number }}</p>
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
        @if ($document->observation && !is_integrate_system())
            <tr>
                <td>
                    <p class="desc">Observación:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->observation }}</p>
                </td>
            </tr>
        @endif
        @if ($document->reference_data)
            <tr>
                <td class="align-top">
                    <p class="desc">D. Referencia:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $document->reference_data }}
                    </p>
                </td>
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
                        <td class="align-top">
                            <p class="desc">

                                Dirección de envío:
                            </p>
                        </td>
                        <td class="desc" style="text-transform: capitalize;">
                            {{ $ship_address }}
                        </td>
                    </tr>
                @endif
                @if ($observation)
                    <tr>
                        <td class="align-top">
                            <p class="desc">
                                Observación Pd:
                            </p>
                        </td>
                        <td class="desc" style="text-transform: capitalize;">
                            {{ $observation }}
                        </td>
                    </tr>
                @endif
            @endisset
        @endif
        @if ($document->isPointSystem())
            <tr>
                <td>
                    <p class="desc">P. Acumulados:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->person->accumulated_points }}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Puntos por la compra:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->getPointsBySale() }}</p>
                </td>
            </tr>
        @endif

    </table>
    @if ($document->transport)

        <p class="desc"><strong>Transporte de pasajeros</strong></p>

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
                <td>
                    <p class="desc">
                        {{ $transport->identity_document_type->description }}:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->number_identity_document }}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Nombre:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->passenger_fullname }}</p>
                </td>
            </tr>
            @if ($transport->passenger_age)
                <tr>
                    <td>
                        <p class="desc">Edad
                        </p>
                    </td>
                    <td>
                        <p class="desc">{{ $transport->passenger_age }}</p>
                    </td>
                </tr>
            @endif


            <tr>
                <td>
                    <p class="desc">N° Asiento:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->seat_number }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">M. Pasajero:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->passenger_manifest }}</p>
                </td>
            </tr>

            <tr>
                <td>
                    <p class="desc">F. Inicio:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->start_date }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">H. Inicio:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->start_time }}</p>
                </td>
            </tr>


            <tr>
                <td>
                    <p class="desc">U. Origen:</p>
                </td>
                <td>
                    <p class="desc">{{ $origin_district }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">D. Origen:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->origin_address }}</p>
                </td>
            </tr>

            <tr>
                <td>
                    <p class="desc">U. Destino:</p>
                </td>
                <td>
                    <p class="desc">{{ $destinatation_district }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">D. Destino:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->destinatation_address }}</p>
                </td>
            </tr>

        </table>
    @endif
    @if ($document->transport_dispatch)
        @php
            $transport_dispatch = $document->transport_dispatch;
            $sender_identity_document_type = $transport_dispatch->sender_identity_document_type->description;
            $recipient_identity_document_type = $transport_dispatch->recipient_identity_document_type->description;
        @endphp
        <p class="desc"><strong>Información de encomienda</strong></p>
        <table class="full-width mt-3">
            <tr>
                <td class="desc" colspan="2">
                    <strong>REMITENTE</strong>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">{{ $sender_identity_document_type }}:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $transport_dispatch->sender_number_identity_document }}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Nombre:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $transport_dispatch->sender_passenger_fullname }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Teléfono:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport_dispatch->sender_telephone }}
                    </p>
                </td>
            </tr>
            <tr>
                <td class="desc" colspan="2">
                    <strong>DESTINATARIO</strong>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">{{ $recipient_identity_document_type }}:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $transport_dispatch->recipient_number_identity_document }}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Nombre:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $transport_dispatch->recipient_passenger_fullname }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Teléfono:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $transport_dispatch->recipient_telephone }}</p>
                </td>
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
@if ($configuration->taxed_igv_visible_nv)
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
@php
    $change_payment = $document->getChangePayment();
@endphp
@if ($change_payment < 0)
    <tr>
        <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">
            Vuelto:
            {{ $document->currency_type->symbol }}</td>
        <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
            {{ number_format(abs($change_payment), 2, '.', '') }}</td>
    </tr>
@endif
</table>
    @php
        $quotation = \App\Models\Tenant\Quotation::select(['number', 'prefix', 'shipping_address'])
            ->where('id', $document->quotation_id)
            ->first();

    @endphp

    @if (is_integrate_system())
        <table class="full-width">
            @php
                $cot = \App\Models\Tenant\Quotation::where('id', $document->quotation_id)->first();
            @endphp
            @if ($cot)
                <tr>


                    <td>
                        <p class="desc">Cotización:</p>
                    </td>
                    <td>
                        <p class="desc">{{ $cot->prefix }}-{{ $cot->number }}
                        </p>
                    </td>

                </tr>
                <tr>


                    <td>
                        <p class="desc">Observación cot.:</p>
                    </td>
                    <td>
                        <p class="desc">{{ $cot->description }}</p>
                    </td>

                </tr>
            @endif
            {{-- <tr>
                                                    <td>
                                                        <p class="desc">Observación adm.:</p>
                                                    </td>
                                                    <td>
                                                        <p class="desc">{{ $document->additional_information }}</p>
                                                    </td>
                                                </tr> --}}
            {{-- @php
                                                    $prod = \App\Models\Tenant\ProductionOrder::where(
                                                        'sale_note_id',
                                                        $document->id,
                                                    )->first();
                                                @endphp
                                                @if ($prod)
                                                    <tr>
                                                        <td>
                                                            <p class="desc">Observación prod.:</p>
                                                        </td>
                                                        <td>
                                                            <p class="desc">

                                                                {{ $prod->observation }}
                                                            </p>
                                                        </td>

                                                    </tr>
                                                @endif --}}
        </table>
    @endif
    <table class="full-width">
        <tr>

            @foreach (array_reverse((array) $document->legends) as $row)
        <tr>
            @if ($row->code == '1000')
                <td class="desc pt-3" style="text-transform: uppercase;">Son:
                    <span class="font-bold">{{ $row->value }}
                        {{ $document->currency_type->description }}</span>
                </td>
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

        @if (!$is_integrate_system)
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
        @endif

    </table>

    @if ($document->payment_method_type_id && $payments->count() == 0)
        <table class="full-width">
            <tr>
                <td class="desc pt-5">
                    <strong>Pago:
                    </strong>{{ $document->payment_method_type->description }}
                </td>
            </tr>
        </table>
    @endif
    @if ($document->payment_condition_id !== '02')
        @if ($payments->count())
            <table class="full-width">
                <tr>
                    <td><strong>Pagos:</strong> </td>
                </tr>
                @php
                    $payment = 0;
                @endphp
                @foreach ($payments as $row)
                    <tr>
                        <td>- {{ $row->date_of_payment->format('d/m/Y') }} -
                            {{ $row->payment_method_type->description }} -
                            {{ $row->reference ? $row->reference . ' - ' : '' }}
                            {{ $document->currency_type->symbol }}
                            {{ $row->payment + $row->change }}</td>
                    </tr>
                    @php
                        $payment += (float) $row->payment;
                    @endphp
                @endforeach
                <tr>
                    <td class="pb-10"><strong>Saldo:</strong>
                        {{ $document->currency_type->symbol }}
                        {{ number_format($document->total - $payment, 2) }}</td>
                </tr>
            </table>
        @endif
    @endif
    @if ($document->fee && count($document->fee) > 0)
        <div class="desc">
            <strong>Cuotas:</strong>
        </div>
        @foreach ($document->fee as $key => $quote)
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
                                            Nombre:
                                            {{ $establishment_data->yape_owner }}
                                        </strong>
                                    @endif
                                    @if ($establishment_data->yape_number)
                                        <br>
                                        <strong>
                                            Número:
                                            {{ $establishment_data->yape_number }}
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
                                            Nombre:
                                            {{ $establishment_data->plin_owner }}
                                        </strong>
                                    @endif
                                    @if ($establishment_data->plin_number)
                                        <br>
                                        <strong>
                                            Número:
                                            {{ $establishment_data->plin_number }}
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
                    <h6 style="font-size: 10px; font-weight: bold;">Términos y
                        condiciones del servicio</h6>
                    {!! $document->terms_condition !!}
                </td>
            </tr>
        </table>
    @endif

    <!-- Coupon Details Section -->
    @if ($document->items->where('item.id_cupones', '!=', null)->count())
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>

        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <div
            style="border: 1px solid #ccc; padding: 15px; margin-top: 20px; background-color: #f9f9f9; border-radius: 8px;">
            <h3 style="margin-bottom: 15px; color: #2c3e50; font-size: 16px;">Cupon
            </h3>
            @foreach ($document->items as $row)
                @if ($row->item->id_cupones)
                    @php
                        $coupon = \App\Models\Tenant\Coupon::find($row->item->id_cupones);
                    @endphp
                    @if ($coupon)
                        <div
                            style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ddd;text-align: center;">
                            @if ($coupon->imagen)
                                @php
                                    $coupon_image = 'storage/' . $coupon->imagen;
                                @endphp
                                <img src="data:{{ mime_content_type(public_path("{$coupon_image}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$coupon_image}"))) }}"
                                    alt="Coupon Image" style="max-width: 120px; display: block; margin-bottom: 10px;">
                            @endif
                            @if ($coupon->titulo)
                                <p style="margin: 5px 0; font-size: 14px; font-weight: bold; color: #34495e;">
                                    {{ $coupon->titulo }}</p>
                            @endif
                            @if ($coupon->descripcion)
                                <p style="margin: 5px 0; font-size: 13px; color: #7f8c8d;">
                                    {{ $coupon->descripcion }}</p>
                            @endif
                            @if ($coupon->descuento)
                                <p style="margin: 5px 0; font-size: 13px; color: #27ae60;">
                                    <strong>Descuento:</strong>
                                    {{ $coupon->descuento }}%
                                </p>
                            @endif
                            @if ($coupon->fecha_caducidad)
                                <p style="margin: 5px 0; font-size: 13px; color: #c0392b;">
                                    <strong>Fecha de caducidad:</strong>
                                    {{ \Carbon\Carbon::parse($coupon->fecha_caducidad)->format('Y-m-d') }}
                                </p>
                            @endif
                            @if ($coupon->barcode)
                                <p style="margin: 5px 0; font-size: 13px; color: #34495e; text-align: center;">
                                    <strong>Código de barras:</strong>
                                </p>
                                <div style="text-align: center;">
                                    <img src="{{ $coupon->barcode }}" alt="Barcode Image"
                                        style="max-width: 120px; display: inline-block; margin-bottom: 10px;">
                                </div>
                            @endif
                        </div>
                    @else
                        <p style="margin: 5px 0; font-size: 13px; color: #e74c3c;">
                            Coupon not found</p>
                    @endif
                @endif
            @endforeach
        </div>
    @endif

</body>

</html>
