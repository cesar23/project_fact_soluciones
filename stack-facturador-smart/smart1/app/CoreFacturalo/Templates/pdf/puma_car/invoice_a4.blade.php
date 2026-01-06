@php
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

    $establishment = $document->establishment;
    $configuration = \App\Models\Tenant\Configuration::first();

    $company_name = $company->name;
    $company_owner = null;
    if ($configurations->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }
    $establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $logo = $establishment__->logo ?? $company->logo;
    // $documment_columns = \App\Models\Tenant\DocumentColumn::where('is_visible', true)->where('type','DOC')
    $documment_columns = \App\Models\Tenant\DocumentColumn::where('is_visible', true)->where('type','DOC')
        ->orderBy('column_order', 'asc')
        ->get();

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }
    $configuration = \App\Models\Tenant\Configuration::first();
    $configurations = \App\Models\Tenant\Configuration::first();
    $company_name = $company->name;
    $company_owner = null;
    if ($configurations->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }

    $customer = $document->customer;
    $invoice = $document->invoice;
    $document_base = $document->note ? $document->note : null;

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();

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

    $configuration = \App\Models\Tenant\Configuration::first();
    $configurations = \App\Models\Tenant\Configuration::first();
    $company_name = $company->name;
    $company_owner = null;
    if ($configurations->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }

    $payments = $document->payments;

    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = $document->total - $total_payment - $document->payments->sum('change');
    $bg = "storage/uploads/header_images/{$configurations->background_image}";
    $total_discount_items = 0;

    $logo = $establishment__->logo ?? $company->logo;

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();
    $company_name = $company->name;
    $company_owner = null;
    if ($configurations->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }
    $plate_number_info = $document->plate_number_info;
@endphp
<html>

<head>
    {{-- <title>{{ $document_number }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>
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
    @if ($configurations->background_image)
        <div class="centered">
            <img src="data:{{ mime_content_type(public_path("{$bg}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$bg}"))) }}"
                alt="anulado" class="order-1">
        </div>
    @endif
    <div class="header">
        <div style="width: 100%; overflow: hidden;">
            <div style="float: left; width: 50%;">
                @if ($company->logo)
                    <div class="company_logo_box" style="width: 100%; text-align: left;">
                        <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                            alt="{{ $company->name }}" class="company_logo" style="max-width: 250px;">
                    </div>
                @endif
                <div style="width: 100%; ">
                    <div style="text-align: left; width: 100%;">
                        Domicilio fiscal: {{ $establishment->address !== '-' ? $establishment->address : '' }}
                        {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                        {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                        {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                    </div>
                    <div style="text-align: center; width: 100%; ">
                        Telf.: {{ $establishment->telephone !== '-' ? $establishment->telephone : '' }}
                    </div>
                    <div style="text-align: center; width: 100%;font-size: 11px;">
                        Películas de Seguridad - Accesorios para Automóviles - Aire Acondicionado
                    </div>
                </div>
            </div>

            <div style="float: left; width: 10%;">
                <!-- Espacio en medio -->
            </div>

            <div style="float: right; width: 35%;">
                <div style="border: 2px solid black; text-align: center;height: 120px;">
                    <div style="font-size: 16px; font-weight:bold; margin-top: 15px;">{{ 'RUC ' . $company->number }}
                    </div>
                    <div style="font-size: 16px; font-weight:bold; margin-top: 3px; text-align: center;">
                        {{ $document->document_type->description }}
                    </div>
                    <div style="font-size: 16px; font-weight:bold; margin-top: 3px; text-align: center;">
                        {{ $document_number }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <table class="full-width mt-2">
        <tr>
            <td width="20%" style="font-family: 'Arial'; font-size: 12px; font-weight: bold;">CLIENTE</td>
            <td width="5%" style="font-family: 'Arial'; font-size: 12px;">:</td>
            <td colspan="7" width="75%" style="font-family: 'Arial'; font-size: 12px;">{{ $customer->name }}</td>
        </tr>
        <tr>
            <td style="font-family: 'Arial'; font-size: 12px; font-weight: bold;">R.U.C.</td>
            <td style="font-family: 'Arial'; font-size: 12px;">:</td>
            <td colspan="7" style="font-family: 'Arial'; font-size: 12px;">{{ $customer->number }}</td>
        </tr>
        <tr>
            <td style="font-family: 'Arial'; font-size: 12px; font-weight: bold;">DIRECCIÓN</td>
            <td style="font-family: 'Arial'; font-size: 12px;">:</td>
            <td colspan="7" style="font-family: 'Arial'; font-size: 12px;">{{ $customer->address }}</td>
        </tr>
        <tr>
            <td style="font-family: 'Arial'; font-size: 12px; font-weight: bold;">FECHA EMISIÓN</td>
            <td style="font-family: 'Arial'; font-size: 12px;">:</td>
            <td style="font-family: 'Arial'; font-size: 12px;">{{ $document->date_of_issue->format('d/m/Y') }}</td>
            <td style="font-family: 'Arial'; font-size: 12px; font-weight: bold;" width="15%">FECHA VENC.</td>
            <td style="font-family: 'Arial'; font-size: 12px;" width="5%">:</td>
            <td style="font-family: 'Arial'; font-size: 12px;">{{ $document->invoice->date_of_due->format('d/m/Y') }}
            </td>
            <td style="font-family: 'Arial'; font-size: 12px; font-weight: bold;">MONEDA</td>
            <td style="font-family: 'Arial'; font-size: 12px;" width="5%">:</td>
            <td style="font-family: 'Arial'; font-size: 12px;">{{ $document->currency_type_id }}</td>
        </tr>
        <tr>
            <td style="font-family: 'Arial'; font-size: 12px; font-weight: bold;">FORMA PAGO</td>
            <td style="font-family: 'Arial'; font-size: 12px;">:</td>
            <td style="font-family: 'Arial'; font-size: 12px;">
                {{ $document->payment_condition_id == '01' ? 'CONTADO' : 'CRÉDITO' }}

            </td>
            <td style="font-family: 'Arial'; font-size: 12px; font-weight: bold;">OC</td>
            <td style="font-family: 'Arial'; font-size: 12px;">:</td>
            <td style="font-family: 'Arial'; font-size: 12px;">{{ $document->purchase_order }}</td>
            <td style="font-family: 'Arial'; font-size: 12px; font-weight: bold;">GUIA REMISIÓN</td>
            <td style="font-family: 'Arial'; font-size: 12px;">:</td>
            <td style="font-family: 'Arial'; font-size: 12px;">
                @foreach ($document->reference_guides_valid as $guide)
                    {{ $guide->series }} - {{ $guide->number }}
                @endforeach
            </td>

        </tr>
    </table>







    @if ($document->guides)
        <br />
        <table>
            @foreach ($document->guides as $guide)
                <tr>
                    @if (isset($guide->document_type_description))
                        <td class="text-left desc">{{ $guide->document_type_description }}</td>
                    @else
                        <td class="text-left desc">{{ $guide->document_type_id }}</td>
                    @endif
                    <td class="text-left desc">:</td>
                    <td class="text-left desc">{{ $guide->number }}</td>
                </tr>
            @endforeach
        </table>
    @endif


    @if ($document->transport)
        <br>
        <strong>Transporte de pasajeros</strong>
        @php
            $transport = $document->transport;
            $agency_origin = '-';
            $agency_destination = '-';
            if ($transport->agency_origin_id) {
                $agency_origin = $transport->agency_origin->description;
            }
            if ($transport->agency_destination_id) {
                $agency_destination = $transport->agency_destination->description;
            }
            $origin_district_id = (array) $transport->origin_district_id;
            $destinatation_district_id = (array) $transport->destinatation_district_id;
            $origin_district = Modules\Order\Services\AddressFullService::getDescription(
                isset($origin_district_id[2]) ? $origin_district_id[2] : null,
            );
            $destinatation_district = Modules\Order\Services\AddressFullService::getDescription(
                isset($destinatation_district_id[2]) ? $destinatation_district_id[2] : null,
            );
        @endphp

        <table class="full-width mt-3">
            <tr>
                <td class="text-left desc" width="120px">{{ $transport->identity_document_type->description }}</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->number_identity_document }}</td>
                <td class="text-left desc" width="120px">Nombre</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->passenger_fullname }}</td>
            </tr>
            <tr>
                <td class="text-left desc" width="120px">N° asiento</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->seat_number }}</td>
                <td class="text-left desc" width="120px">M. pasajero</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->passenger_manifest }}</td>
            </tr>
            <tr>
                <td class="text-left desc" width="120px">F. inicio</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->start_date }}</td>
                <td class="text-left desc" width="120px">H. inicio</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->start_time }}</td>
            </tr>
            <tr>
                <td class="text-left desc" width="120px">Agencia origen</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $agency_origin }}</td>
                <td class="text-left desc" colspan="3"></td>
            </tr>
            <tr>
                <td class="text-left desc" width="120px">U. origen</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $origin_district }}</td>
                <td class="text-left desc" width="120px">D. origen</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->origin_address }}</td>
            </tr>
            <tr>
                <td class="text-left desc" width="120px">Agencia destino</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $agency_destination }}</td>
                <td class="text-left desc" colspan="3"></td>
            </tr>
            <tr>
                <td class="text-left desc" width="120px">U. destino</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $destinatation_district }}</td>
                <td class="text-left desc" width="120px">D. destino</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->destinatation_address }}</td>
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
            $agency_origin_dispatch = '-';
            $agency_destination_dispatch = '-';
            if ($transport_dispatch->agency_origin_id) {
                $agency_origin_dispatch = $transport_dispatch->agency_origin->description;
            }
            if ($transport_dispatch->agency_destination_id) {
                $agency_destination_dispatch = $transport_dispatch->agency_destination->description;
            }
            $origin_district_dispatch = null;
            $destination_district_dispatch = null;
            if ($transport_dispatch->origin_district_id && $transport_dispatch->destinatation_district_id) {
                $origin_district_id = (array) $transport_dispatch->origin_district_id;
                $destinatation_district_id = (array) $transport_dispatch->destinatation_district_id;
                $origin_district_dispatch = Modules\Order\Services\AddressFullService::getDescription(
                    $origin_district_id[2],
                );
                $destination_district_dispatch = Modules\Order\Services\AddressFullService::getDescription(
                    $destinatation_district_id[2],
                );
            }
        @endphp

        <table class="full-width mt-3">
            <thead>
                <tr>
                    <th colspan="6" class="text-left">
                        <strong>Remitente</strong>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left desc" width="120px">{{ $sender_identity_document_type }}</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $transport_dispatch->sender_number_identity_document }}</td>
                    <td class="text-left desc" width="120px">Nombre</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $transport_dispatch->sender_passenger_fullname }}</td>
                </tr>
                <tr>

                </tr>
                @if ($transport_dispatch->sender_telephone)
                    <tr>
                        <td class="text-left desc" width="120px">Teléfono</td>
                        <td class="text-left desc" width="8px">:</td>
                        <td class="text-left desc">{{ $transport_dispatch->sender_telephone }}</td>
                        <td class="text-left desc" colspan="3"></td>
                    </tr>
                @endif
                <tr>
                    <td class="text-left desc" width="120px">U. origen</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $origin_district_dispatch }}</td>
                    <td class="text-left desc" width="120px">D. origen</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $transport_dispatch->origin_address }}</td>

                </tr>
                @if ($agency_origin_dispatch != '-')
                    <tr>
                        <td class="text-left desc" width="120px">Agencia origen</td>
                        <td class="text-left desc" width="8px">:</td>
                        <td class="text-left desc">{{ $agency_origin_dispatch }}</td>
                        <td class="text-left desc" colspan="3"></td>
                    </tr>
                @endif
            </tbody>
            <thead>
                <tr>
                    <th colspan="6" class="text-left">
                        <strong>Destinatario</strong>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left desc" width="120px">{{ $recipient_identity_document_type }}</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $transport_dispatch->recipient_number_identity_document }}</td>
                    <td class="text-left desc" width="120px">Nombre</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $transport_dispatch->recipient_passenger_fullname }}</td>
                </tr>
                @if ($transport_dispatch->recipient_telephone)
                    <tr>
                        <td class="text-left desc" width="120px">Teléfono</td>
                        <td class="text-left desc" width="8px">:</td>
                        <td class="text-left desc">{{ $transport_dispatch->recipient_telephone }}</td>
                        <td class="text-left desc" colspan="3"></td>
                    </tr>
                @endif
                <tr>
                    <td class="text-left desc" width="120px">U. destino</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $destination_district_dispatch }}</td>
                    <td class="text-left desc" width="120px">D. destino</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $transport_dispatch->destinatation_address }}</td>

                </tr>
                @if ($agency_destination_dispatch != '-')
                    <tr>
                        <td class="text-left desc" width="120px">Agencia destino</td>
                        <td class="text-left desc" width="8px">:</td>
                        <td class="text-left desc">{{ $agency_destination_dispatch }}</td>
                        <td class="text-left desc" colspan="3"></td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif
    


    <table class="full-width mt-3">
        @if ($document->prepayments)
            @foreach ($document->prepayments as $p)
                <tr>
                    <td class="text-left desc" width="120px">Anticipo</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $p->number }}</td>
                </tr>
            @endforeach
        @endif
        @if ($document->purchase_order)
            <tr>
                <td class="text-left desc" width="120px">Orden de compra</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->purchase_order }}</td>
            </tr>
        @endif
        @if ($document->quotation_id)
            <tr>
                <td class="text-left desc" width="120px">Cotización</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->quotation->identifier }}</td>

                @isset($document->quotation->delivery_date)
                    <td class="text-left desc" width="120px">F. entrega</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">
                        {{ $document->date_of_issue->addDays($document->quotation->delivery_date)->format('d-m-Y') }}</td>
                @endisset
            </tr>
        @endif
        @isset($document->quotation->sale_opportunity)
            <tr>
                <td class="text-left desc" width="120px">O. Venta</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->quotation->sale_opportunity->number_full }}</td>
            </tr>
        @endisset
        @if (!is_null($document_base))
            <tr>
                <td class="text-left desc" width="120px">Doc. Afectado</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $affected_document_number }}</td>
            </tr>
            <tr>
                <td class="text-left desc">Tipo de nota</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc">
                    {{ $document_base->note_type === 'credit' ? $document_base->note_credit_type->description : $document_base->note_debit_type->description }}
                </td>
            </tr>
            <tr>
                <td class="text-left desc">Descripción</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc">{{ $document_base->note_description }}</td>
            </tr>
        @endif
        @if ($document->folio)
            <tr>
                <td class="text-left desc">Folio</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc">{{ $document->folio }}</td>
            </tr>
        @endif

    </table>

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
                    <th class=" desc text-center py-1 rounded-t bg-grey-dark" width="8%">
                        Cantidad
                    </th>

                    <th class="border-left desc text-left py-1 px-2 bg-grey-dark" width="{{ $width_description }}%">
                        Descripción
                    </th>


                    <th class="border-left desc text-right desc py-1 px-2 bg-grey-dark" width="8%">Precio
                        unitario
                    </th>
                    <th class="border-left desc text-right desc py-1 px-2 bg-grey-dark" width="12%">Importe
                    </th>

                </tr>
            </thead>
            @php
                $init = 33;
                $cycle = $init;
                $count_items = count($document->items);
                if ($document->prepayments) {
                    try {
                        $prepayments = (array) $document->prepayments;
                        $count_items = $count_items + count($prepayments);
                    } catch (\Exception $e) {
                        $count_items = $count_items + 1;
                    }
                }
                if ($count_items > 7) {
                    $cycle = 0;
                } else {
                    $cycle = $init - $count_items;
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

                            <span style="margin-top: 0px;padding-top: 0px;">
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
                                <br>
                                *** Pago Anticipado ***
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
                    <tr>


                    </tr>
                @endforeach
                @if ($document->prepayments)
                    @foreach ($document->prepayments as $p)
                        <tr>
                            <td class="text-left desc" class="text-center desc align-top">1</td>
                            <td class="text-left desc" class="text-center desc align-top border-left">NIU</td>
                            <td class="text-left desc" class="text-left desc align-top border-left px-2">
                                Anticipo: {{ $p->document_type_id == '02' ? 'Factura' : 'Boleta' }} Nro.
                                {{ $p->number }}
                            </td>
                            @if (!$configurations->document_columns)
                                <td class="text-left desc" class="text-right desc desc align-top border-left  px-2">
                                    -{{ number_format($p->total, 2) }}</td>
                                <td class="text-left desc" class="text-right desc desc align-top border-left  px-2">
                                    0.00</td>
                                <td class="text-left desc" class="text-right desc desc align-top border-left  px-2">
                                    -{{ number_format($p->total, 2) }}</td>
                            @else
                                @foreach ($documment_columns as $column)
                                    @if ($column->value == 'total_price')
                                        <td class="text-left desc" class="text-right desc desc align-top border-left">
                                            -{{ number_format($p->total, 2) }}</td>
                                    @else
                                        <td class="text-left desc" class="text-right desc desc align-top border-left">
                                            0.00</td>
                                    @endif
                                @endforeach
                            @endif
                        </tr>
                    @endforeach
                @endif
                @for ($i = 0; $i < $cycle; $i++)
                    <tr>
                        <td class="text-left desc" class="text-center desc align-top">
                            <br>
                        </td>
                        <td class="text-left desc" class="text-center desc align-top border-left"></td>
                        <td class="text-left desc" class="text-left desc align-top border-left"></td>
                        <td class="text-left desc" class="text-left desc align-top border-left"></td>

                    </tr>
                @endfor



            </tbody>
        </table>
    </div>
    <div>
        @foreach (array_reverse((array) $document->legends) as $row)
            @if ($row->code == '1000')
                <p style="padding:0px;margin:0px;">Son: <span class="font-bold desc"
                        style="text-transform: uppercase;">{{ $row->value }}
                        {{ $document->currency_type->description }}</span></p>
                {{-- @if (count((array) $document->legends) > 1)
                <p><span class="font-bold desc desc">Leyendas</span></p>
            @endif --}}
            @else
                <p style="padding:0px;margin:0px;"> {{ $row->code }}: {{ $row->value }} </p>
            @endif
        @endforeach
    </div>
    <div class="full-width">
        <div class="float-left" style="width: 50%;">
            @php
                $plate_number_document = $document->plateNumberDocument;
                $plate_number = null;
                if ($plate_number_document) {
                    $plate_number = $plate_number_document->plateNumber;
                }
            @endphp
            <div class="full-width">
                <div class="float-left" style="width: 50%;margin-right: 0px;padding:0px;">
                    <img src="data:image/png;base64, {{ $document->qr }}" style="width: 200px;" />
                </div>
                <div class="float-left" style="width: 50%;">
                    <table>
                        <tr>
                            <td>
                                <strong>PLACA:</strong>
                                {{ $plate_number ? $plate_number->description : '' }}
                            </td>

                        </tr>
                        <tr>
                            <td>
                                <strong>MARCA:</strong>
                                {{ $plate_number ? optional($plate_number->brand)->description ?? '' : '' }}
                            </td>
                        </tr>
                
                        <tr>
                            <td>
                                <strong>MODELO:</strong>
                                {{ $plate_number ? optional($plate_number->model)->description ?? '' : '' }}
                            </td>
                        </tr>
                        <tr></tr>
                    </table>
                    <div class="full-width mt-3">
                        @foreach ($accounts as $account)
                            <div>
                                <strong>CTA CTE {{ $account->description }} {{ $account->currency_type->symbol }}
                                    {{ $account->number }}
                                </strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div style="font-size: 10px;">
                Representación impresa de la Factura Electrónica
            </div>
            <div style="font-size: 10px;">
                Vea una copia en : http://www.sunat.gob.pe usando la clave sol
            </div>
            <div style="font-size: 10px;">
                Documento emitido con Resolución Anexo IV-RS-155-2017 / SUNAT
            </div>
        </div>

        <div class="float-left" style="width: 5%;">
            <br>
        </div>
        <div class="float-left" style="width: 45%;">
            <div class="full-width">
                <table style="border: 1px solid #000; padding: 10px;width: 100%;">
                    <tr>
                        <td style="padding: 3px 10px;">Operación Gravada</td>
                        <td style="padding: 3px 10px;">
                            {{ $document->currency_type_id }}
                        </td>
                        <td style="padding: 3px 10px; text-align: right;">
                            {{ number_format($document->total_taxed, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 10px;">Operación Exonerada</td>
                        <td style="padding: 3px 10px;">
                            {{ $document->currency_type_id }}
                        </td>
                        <td style="padding: 3px 10px; text-align: right;">
                            {{ number_format($document->total_exonerated, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 10px;">Operación Inafecta</td>
                        <td style="padding: 3px 10px;">
                            {{ $document->currency_type_id }}
                        </td>
                        <td style="padding: 3px 10px; text-align: right;">
                            {{ number_format($document->total_unaffected, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 10px;">Operación Gratuita</td>
                        <td style="padding: 3px 10px;">
                            {{ $document->currency_type_id }}
                        </td>
                        <td style="padding: 3px 10px; text-align: right;">
                            {{ number_format($document->total_free, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 10px;">Total Descuento</td>
                        <td style="padding: 3px 10px;">
                            {{ $document->currency_type_id }}
                        </td>
                        <td style="padding: 3px 10px; text-align: right;">
                            {{ number_format($document->total_discount, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 10px;">ISC</td>
                        <td style="padding: 3px 10px;">
                            {{ $document->currency_type_id }}
                        </td>
                        <td style="padding: 3px 10px; text-align: right;">
                            {{ number_format($document->total_isc, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 10px;">IGV (18%)</td>
                        <td style="padding: 3px 10px;">
                            {{ $document->currency_type_id }}
                        </td>
                        <td style="padding: 3px 10px; text-align: right;">
                            {{ number_format($document->total_igv, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 10px;">Importe Total</td>
                        <td style="padding: 3px 10px;">
                            {{ $document->currency_type_id }}
                        </td>
                        <td style="padding: 3px 10px; text-align: right;">
                            {{ number_format($document->total, 2) }}
                        </td>
                    </tr>
                </table>
            </div>
            <div style="font-size: 10px;text-align: right; text-transform: uppercase;margin-top: 10px;">
                {{ $document->seller ? $document->seller->name : $document->user->name }}
            </div>
        </div>
    </div>


    {{-- <table class="full-width desc">
        <tr>
            <td class="text-left desc">
                <strong>Condición de Pago: {{ $paymentCondition }} </strong>
            </td>
        </tr>
    </table> --}}

    @if ($document->payment_method_type_id)
        <table class="full-width desc">
            <tr>
                <td class="text-left desc">
                    <strong>Método de Pago: </strong>{{ $document->payment_method_type->description }}
                </td>
            </tr>
        </table>
    @endif

    <div style="border: 1px solid #000;width: 50%;">
        <table class="full-width border-collapse border-1">
            <thead>
                <tr>
                    <th class="border-box">
                        Fecha Venc.
                    </th>
                    <th class="border-box">
                        Importe Neto
                    </th>
                    <th class="border-box">
                        Moneda
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border-box text-center">
                        {{ $document->invoice->date_of_due->format('d/m/Y') }}
                    </td>
                    <td class="border-box text-center">
                        {{ $document->total }}
                    </td>
                    <td class="border-box text-center">
                        {{ $document->currency_type_id }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>





</body>

</html>
