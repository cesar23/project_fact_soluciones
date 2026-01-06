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
    $documment_columns = \App\Models\Tenant\DocumentColumn::where('is_visible', true)
        ->orderBy('column_order', 'asc')
        ->get();
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
        <tr>
            <td class="text-center" style="text-transform:uppercase;">{{ $company_name }} </td>
        </tr>
        <tr>
            @if ($company_owner)
                <td class="text-center" style="text-transform:uppercase;">De: {{ $company_owner }}</td>
            @endif
        </tr>
        <tr>
            <td class="text-center">{{ 'RUC ' . $company->number }}</td>
        </tr>
        @if ($configuration->show_company_address)
            <tr>
                <td class="text-center" style="text-transform: capitalize;">
                    {{ $establishment->address !== '-' ? $establishment->address : '' }}
                    {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                    {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                    {{ $establishment->department_id !== '-' ? ', ' . $establishment->department->description : '' }}
                </td>
            </tr>

            @isset($establishment->trade_address)
                <tr>
                    <td class="text-center ">
                        {{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                    </td>
                </tr>
            @endisset
        @endif
        <tr>
            <td class="text-center ">
                {{ $establishment->telephone !== '-' ? 'Central telefónica: ' . $establishment->telephone : '' }}</td>
        </tr>
        @if ($configuration->show_email)
            <tr>
                <td class="text-center">{{ $establishment->email !== '-' ? 'Email: ' . $establishment->email : '' }}
                </td>
            </tr>
        @endif
        @isset($establishment->web_address)
            <tr>
                <td class="text-center">
                    {{ $establishment->web_address !== '-' ? 'Web: ' . $establishment->web_address : '' }}</td>
            </tr>
        @endisset

        @isset($establishment->aditional_information)
            <tr>
                <td class="text-center pb-3">
                    {{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}</td>
            </tr>
        @endisset

        <br>
        <br>

        <tr>
            <td style="vertical-align: top; text-transform: capitalize;" class="font-bold text-center">
                <h5>{{ $document->document_type->description }}</h5>
            </td>
        </tr>
        <tr>
            <td class="font-bold text-center">
                <h5>{{ $document_number }}</h5>
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
        <tr>
            <td width="">
                <p class="desc">H. Emisión:</p>
            </td>
            <td width="">
                <p class="desc">{{ $document->time_of_issue }}</p>
            </td>
        </tr>
        @isset($invoice->date_of_due)
            @if ($document->document_type_id !== '03' || ($document->document_type_id === '03' && $configuration->date_of_due))
                <tr>
                    <td>
                        <p class="desc">F. Vencimiento:</p>
                    </td>
                    <td>
                        <p class="desc">{{ $invoice->date_of_due->format('Y-m-d') }}</p>
                    </td>
                </tr>
            @endif
        @endisset
        @if ($document->document_type_id !== '03' || ($document->document_type_id === '03' && $configuration->info_customer_pdf))

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
            @isset($customer->search_telephone)
                @if ($customer->search_telephone != null)
                    <tr>
                        <td>
                            <p class="desc">Telefono:</p>
                        </td>
                        <td>
                            <p class="desc">{{ $customer->search_telephone }}</p>
                        </td>
                    </tr>
                @endif
            @endisset
            @if ($customer->address !== '')
                <tr>
                    <td class="align-top">
                        <p class="desc">Dirección:</p>
                    </td>
                    <td class="desc" style="text-transform: capitalize;">

                        {{ $customer->address }}
                        {{ $customer->district_id !== '-' ? ', ' . $customer->district->description : '' }}
                        {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                        {{ $customer->department_id !== '-' ? ', ' . $customer->department->description : '' }}
                    </td>
                </tr>
            @endif
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

        @if ($document->detraction)
            {{-- <strong>Operación sujeta a detracción</strong> --}}
            <tr>
                <td class="align-top">
                    <p class="desc">N. Cta detracciones:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->detraction->bank_account }}</p>
                </td>
            </tr>
            <tr>
                <td class="align-top">
                    <p class="desc">B/S Sujeto a detracción:</p>
                </td>
                @inject('detractionType', 'App\Services\DetractionTypeService')
                <td>
                    <p class="desc">{{ $document->detraction->detraction_type_id }} -
                        {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id) }}
                    </p>
                </td>
            </tr>
            <tr>
                <td class="align-top">
                    <p class="desc">Método de Pago:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id) }}
                    </p>
                </td>
            </tr>
            <tr>
                <td class="align-top">
                    <p class="desc">Porcentaje detracción:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->detraction->percentage }}%</p>
                </td>
            </tr>
            <tr>
                <td class="align-top">
                    <p class="desc">Monto detracción:</p>
                </td>
                <td>
                    <p class="desc">S/ {{ $document->detraction->amount }}</p>
                </td>
            </tr>
            @if ($document->detraction->pay_constancy)
                <tr>
                    <td class="align-top">
                        <p class="desc">Constancia de Pago:</p>
                    </td>
                    <td>
                        <p class="desc">{{ $document->detraction->pay_constancy }}</p>
                    </td>
                </tr>
            @endif


            @if ($invoice->operation_type_id == '1004')
                <tr class="mt-2">
                    <td colspan="2"></td>
                </tr>
                <tr class="mt-2">
                    <td colspan="2">Detalle - Servicios de transporte de carga</td>
                </tr>
                <tr>
                    <td class="align-top">
                        <p class="desc">Ubigeo origen:</p>
                    </td>
                    <td>
                        <p class="desc">{{ $document->detraction->origin_location_id[2] }}</p>
                    </td>
                </tr>
                <tr>
                    <td class="align-top">
                        <p class="desc">Dirección origen:
                    </td>
                    <td>
                        <p class="desc">{{ $document->detraction->origin_address }}
                    </td>
                </tr>
                <tr>
                    <td class="align-top">
                        <p class="desc">Ubigeo destino:</p>
                    </td>
                    <td>
                        <p class="desc">{{ $document->detraction->delivery_location_id[2] }}</p>
                    </td>
                </tr>
                <tr>

                    <td class="align-top">
                        <p class="desc">Dirección destino:</p>
                    </td>
                    <td>
                        <p class="desc">{{ $document->detraction->delivery_address }}</p>
                    </td>
                </tr>
                <tr>
                    <td class="align-top">
                        <p class="desc">Valor referencial servicio de transporte:</p>
                    </td>
                    <td>
                        <p class="desc">{{ $document->detraction->reference_value_service }}</p>
                    </td>
                </tr>
                <tr>

                    <td class="align-top">
                        <p class="desc">Valor referencia carga efectiva:</p>
                    </td>
                    <td>
                        <p class="desc">{{ $document->detraction->reference_value_effective_load }}</p>
                    </td>
                </tr>
                <tr>
                    <td class="align-top">
                        <p class="desc">Valor referencial carga útil:</p>
                    </td>
                    <td>
                        <p class="desc">{{ $document->detraction->reference_value_payload }}</p>
                    </td>
                </tr>
                <tr>
                    <td class="align-top">
                        <p class="desc">Detalle del viaje:</p>
                    </td>
                    <td>
                        <p class="desc">{{ $document->detraction->trip_detail }}</p>
                    </td>
                </tr>
            @endif

        @endif


        @if ($document->retention)
            <br>
            <tr>
                <td colspan="2">
                    <p class="desc"><strong>Información de la retención</strong></p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Base imponible: </p>
                </td>
                <td>
                    <p class="desc">{{ $document->currency_type->symbol }} {{ $document->retention->base }} </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Porcentaje:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->retention->percentage * 100 }}%</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Monto:</p>
                </td>
                <td>
                    <p class="desc">S/ {{ $document->retention->amount_pen }}</p>
                </td>
            </tr>
        @endif


        @if ($document->prepayments)
            @foreach ($document->prepayments as $p)
                <tr>
                    <td>
                        <p class="desc">Anticipo :</p>
                    </td>
                    <td>
                        <p class="desc">{{ $p->number }}</p>
                    </td>
                </tr>
            @endforeach
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
        @isset($document->quotation->delivery_date)
            <tr>
                <td>
                    <p class="desc">F. Entrega</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $document->date_of_issue->addDays($document->quotation->delivery_date)->format('d-m-Y') }}</p>
                </td>
            </tr>
        @endisset
        @isset($document->quotation->sale_opportunity)
            <tr>
                <td>
                    <p class="desc">O. Venta</p>
                </td>
                <td>
                    <p class="desc">{{ $document->quotation->sale_opportunity->number_full }}</p>
                </td>
            </tr>
        @endisset
    </table>

    @if ($document->guides)
        {{-- <strong>Guías:</strong> --}}
        <table>
            @foreach ($document->guides as $guide)
                <tr>
                    @if (isset($guide->document_type_description))
                        <td class="desc">{{ $guide->document_type_description }}</td>
                    @else
                        <td class="desc">{{ $guide->document_type_id }}</td>
                    @endif
                    <td class="desc">:</td>
                    <td class="desc">{{ $guide->number }}</td>
                </tr>
            @endforeach
        </table>
    @endif


    @if ($document->transport)
        <p class="desc"><strong>Transporte de pasajeros</strong></p>

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
                <td>
                    <p class="desc">{{ $transport->identity_document_type->description }}:</p>
                </td>
                <td>
                    <p class="desc-xl">
                        <strong>
                            {{ $transport->number_identity_document }}
                    </p>
                    </strong>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">NOMBRE:</p>
                </td>
                <td>
                    <p class="desc-xl">
                        <strong>
                            {{ $transport->passenger_fullname }}
                        </strong>
                    </p>
                </td>
            </tr>
            @if ($transport->passenger_age)
                <tr>
                    <td>
                        <p class="desc">EDAD
                        </p>
                    </td>
                    <td>
                        <p class="desc-xl">
                            <strong>
                                {{ $transport->passenger_age }}
                            </strong>
                        </p>
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
                    <p class="desc">N° Bus:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->bus_number }}</p>
                </td>
            </tr>
            @if ($transport->passenger_manifest)
                <tr>
                    <td>
                        <p class="desc">M. Pasajero:</p>
                    </td>
                    <td>
                        <p class="desc">{{ $transport->passenger_manifest }}</p>
                    </td>
                </tr>
            @endif

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
            @if ($agency_origin && $agency_origin !== '-')
                <tr>
                    <td>
                        <p class="desc">Agencia Origen:</p>
                    </td>
                    <td>
                        <p class="desc-l">
                            <strong>
                                {{ $agency_origin }}
                            </strong>
                        </p>
                    </td>
                </tr>
            @endif

            @if ($origin_district && $configuration->show_ubigeo)
                <tr>
                    <td>
                        <p class="desc">U. Origen:</p>
                    </td>
                    <td>
                        <p class="desc-l">
                            {{ $origin_district }}
                        </p>
                    </td>
                </tr>
            @endif
            @if ($transport->origin_address)
                <tr>
                    <td>
                        <p class="desc">D. Origen:</p>
                    </td>
                    <td>
                        <p class="desc-l">
                            {{ $transport->origin_address }}
                        </p>
                    </td>
                </tr>
            @endif
            @if ($agency_destination && $agency_destination != '-')
                <tr>
                    <td>
                        <p class="desc">AGENCIA DESTINO:</p>
                    </td>
                    <td>
                        <p class="desc-xl">
                            <strong>
                                {{ $agency_destination }}
                            </strong>
                        </p>
                    </td>
                </tr>
            @endif
            @if ($destinatation_district && $configuration->show_ubigeo)
                <tr>
                    <td>
                        <p class="desc">U. Destino:</p>
                    </td>
                    <td>
                        <p class="desc-l">
                            {{ $destinatation_district }}
                        </p>
                    </td>
                </tr>
            @endif
            @if ($transport->destinatation_address)
                <tr>
                    <td>
                        <p class="desc">D. Destino:</p>
                    </td>
                    <td>
                        <p class="desc-l">
                            {{ $transport->destinatation_address }}
                        </p>
                    </td>
                </tr>
            @endif

        </table>
    @endif

    @if ($document->transport_dispatch)
        @php
            $has_transport_dispatch = true;
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
            $destinatation_district_dispatch = null;
            if ($transport_dispatch->origin_district_id && $transport_dispatch->destinatation_district_id) {
                $origin_district_id = (array) $transport_dispatch->origin_district_id;
                $destinatation_district_id = (array) $transport_dispatch->destinatation_district_id;
                $origin_district_dispatch = Modules\Order\Services\AddressFullService::getDescription(
                    $origin_district_id[2],
                );
                $destinatation_district_dispatch = Modules\Order\Services\AddressFullService::getDescription(
                    $destinatation_district_id[2],
                );
            }
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
                    <p class="desc">
                        {{ $sender_identity_document_type }}:
                    </p>
                </td>
                <td>
                    <p class="desc">
                        <strong>
                            {{ $transport_dispatch->sender_number_identity_document }}
                        </strong>
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Nombre:</p>
                </td>
                <td>
                    <p class="desc">
                        <strong>
                            {{ $transport_dispatch->sender_passenger_fullname }}
                        </strong>
                    </p>
                </td>
            </tr>
            @if ($transport_dispatch->sender_telephone)
                <tr>
                    <td>
                        <p class="desc">Teléfono:</p>
                    </td>
                    <td>
                        <p class="desc">
                            <strong>
                                {{ $transport_dispatch->sender_telephone }}
                            </strong>
                        </p>
                    </td>
                </tr>
            @endif
            @if ($agency_origin_dispatch && $agency_origin_dispatch !== '-')
                <tr>
                    <td>
                        <p class="desc">Agencia Origen:</p>
                    </td>
                    <td>
                        <p class="desc-l">
                            <strong>
                                {{ $agency_origin_dispatch }}
                            </strong>
                        </p>
                    </td>
                </tr>
            @endif

            @if ($origin_district_dispatch && $origin_district_dispatch !== '-' && $configuration->show_ubigeo)
                <tr>
                    <td>
                        <p class="desc">U. Origen:</p>
                    </td>
                    <td>
                        <p class="desc-l">
                            {{ $origin_district_dispatch }}
                        </p>
                    </td>
                </tr>
            @endif
            @if ($transport_dispatch->origin_address)
                <tr>
                    <td>
                        <p class="desc">D. Origen:</p>
                    </td>
                    <td>
                        <p class="desc-l">
                            {{ $transport_dispatch->origin_address }}
                        </p>
                    </td>
                </tr>
            @endif
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
                        <strong>
                            {{ $transport_dispatch->recipient_number_identity_document }}
                        </strong>
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">NOMBRE:</p>
                </td>
                <td>
                    <p class="desc-xl">

                        <strong>
                            {{ $transport_dispatch->recipient_passenger_fullname }}
                        </strong>
                    </p>
                </td>
            </tr>
            @if ($transport_dispatch->recipient_telephone)
                <tr>
                    <td>
                        <p class="desc">Teléfono:</p>
                    </td>
                    <td>
                        <p class="desc">
                            <strong>
                                {{ $transport_dispatch->recipient_telephone }}
                            </strong>

                        </p>
                    </td>
                </tr>
            @endif
            @if ($agency_destination_dispatch && $agency_destination_dispatch !== '-')
                <tr>
                    <td>
                        <p class="desc">AGENCIA DESTINO:</p>
                    </td>
                    <td>
                        <p class="desc-xl">
                            <strong>
                                {{ $agency_destination_dispatch }}
                            </strong>
                        </p>
                    </td>
                </tr>
            @endif
            @if ($destinatation_district_dispatch && $destinatation_district_dispatch !== '-' && $configuration->show_ubigeo)
                <tr>
                    <td>
                        <p class="desc">U. Destino:</p>
                    </td>
                    <td>
                        <p class="desc-l">
                            {{ $destinatation_district_dispatch }}
                        </p>
                    </td>
                </tr>
            @endif
            @if (!is_null($transport_dispatch->destinatation_address))
                <tr>
                    <td>
                        <p class="desc">D. Destino:</p>
                    </td>
                    <td>
                        <p class="desc-l">
                            {{ $transport_dispatch->destinatation_address }}
                        </p>
                    </td>
                </tr>
            @endif

        </table>
    @endif

    @if (count($document->reference_guides_valid) > 0)
        <br />
        <strong>Guias de remisión</strong>
        <table>
            @foreach ($document->reference_guides_valid as $guide)
                <tr>
                    <td>{{ $guide->series }}</td>
                    <td>-</td>
                    <td>{{ $guide->number }}</td>
                </tr>
            @endforeach
        </table>
    @endif

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
                                    style="text-align: {{ $column->column_align }};"
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

        @if ($balance < 0)
            <tr>
                <td class="text-left desc" colspan="5" class="text-right desc font-bold desc">
                    Vuelto:
                    {{ $document->currency_type->symbol }}
                </td>
                <td class="text-left desc" colspan="2" class="text-right desc font-bold desc">
                    {{ number_format(abs($balance), 2, '.', '') }}</td>
            </tr>
        @endif
    </table>

    <table class="full-width">
        @foreach (array_reverse((array) $document->legends) as $row)
            <tr>
                @if ($row->code == '1000')
                    <td class="desc pt-3" colspan="2">Son: <span class="font-bold">{{ $row->value }}
                            {{ $document->currency_type->description }}</span></td>
                    @if (count((array) $document->legends) > 1)
            <tr>
                <td class="desc pt-3"><span class="font-bold">Leyendas</span></td>
            </tr>
        @endif
    @else
        <td class="desc pt-3" colspan="2">{{ $row->code }}: {{ $row->value }}</td>
        @endif
        </tr>
        @endforeach
        @if ($configuration->qr_payments_pdf)
            <tr>
                <td class="text-center pt-1">
                    <img class="" style="max-width: 100px"
                        src="data:image/png;base64, {{ $document->qr }}" />
                </td>
                <td>
                    @if ($document->detraction)
                        <p>Operación sujeta al Sistema de Pago de Obligaciones Tributarias</p>
                    @endif
                    @foreach ($document->additional_information as $information)
                        @if ($information)
                            @if ($loop->first)
                                <strong>Información adicional</strong>
                            @endif
                            <p class="desc">{{ $information }}</p>
                        @endif
                    @endforeach
                    @if (in_array($document->document_type->id, ['01', '03']))
                        @foreach ($accounts as $account)
                            <p class="desc">
                                <small>
                                    <span class="font-bold desc">{{ $account->bank->description }}</span>
                                    {{ $account->currency_type->description }}
                                    <span class="font-bold desc">N°:</span> {{ $account->number }}
                                    @if ($account->cci)
                                        <span class="font-bold desc">CCI:</span> {{ $account->cci }}
                                    @endif
                                </small>
                            </p>
                        @endforeach
                    @endif

                    <p class="desc"><strong>Código hash:</strong> {{ $document->hash }}</p>

                    @php
                        $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition(
                            $document,
                        );
                    @endphp
                    {{-- Condicion de pago Crédito / Contado --}}
                    <p class="desc pt-5">
                        <strong>Condición de Pago:</strong> {{ $paymentCondition }}
                    </p>

                    @if ($document->payment_method_type_id)
                        <p class="desc pt-5">
                            <strong>Método de Pago: </strong>{{ $document->payment_method_type->description }}
                        </p>
                    @endif

                    @if ($document->payment_condition_id === '01')

                        @if ($payments->count())
                            <p class="desc pt-5">
                                <strong>Pagos:</strong>
                            </p>
                            @php
                            $payment = 0;
                        @endphp
                            @foreach ($payments as $row)
                                <p>
                                    <span class="desc">&#8226; {{ $row->payment_method_type->description }} -
                                        {{ $row->reference ? $row->reference . ' - ' : '' }}
                                        {{ $document->currency_type->symbol }}
                                        {{ $row->payment + $row->change }}</span>
                                </p>
                                @php
                                $payment += (float) $row->payment;
                            @endphp
                            @endforeach
                            @if ($document->total - $payment > 0)
                                <p class="desc">&#8226; Saldo - {{ $document->currency_type->symbol }}
                                    {{ number_format($document->total - $payment, 2) }}</p>
                            @endif

                        @endif
                    @else
                        @foreach ($document->fee as $key => $quote)
                            @if (!$configuration->show_the_first_cuota_document)
                                <p class="desc pt-5">
                                    <span class="desc">&#8226;
                                        {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                                        / Fecha: {{ $quote->date->format('d-m-Y') }} / Monto:
                                        {{ $quote->currency_type->symbol }}{{ $quote->amount }}</span>
                                </p>
                            @else
                                @if ($key == 0)
                                    <p class="desc pt-5">
                                        <span class="desc">&#8226;
                                            {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                                            / Fecha: {{ $quote->date->format('d-m-Y') }} / Monto:
                                            {{ $quote->currency_type->symbol }}{{ $quote->amount }}</span>
                                    </p>
                                @endif
                            @endif
                        @endforeach
                    @endif

                    <p class="desc pt-5"><strong>Vendedor:</strong>
                        {{ $document->seller ? $document->seller->name : $document->user->name }}</p>

                </td>
            </tr>
        @endif
    </table>
    <table class="full-width">
        @php
            $establishment_data = \App\Models\Tenant\Establishment::find($document->establishment_id);
        @endphp
        <tbody>
            <tr>
                @if ($configuration->yape_qr_documents && $establishment_data->yape_logo)
                    @php
                        $yape_logo = $establishment_data->yape_logo;
                    @endphp
                    @if (file_exists(public_path("{$yape_logo}")))
                    <td class="text-center">
                        <table>
                            <tr>
                                <td>

                                    Yape

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
                                        {{ $establishment_data->yape_owner }}
                                    @endif
                                    @if ($establishment_data->yape_number)
                                        <br>

                                        {{ $establishment_data->yape_number }}
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                    @endif
                @endif
                @if ($configuration->plin_qr_documents && $establishment_data->plin_logo)
                    @php
                        $plin_logo = $establishment_data->plin_logo;
                    @endphp
                    @if (file_exists(public_path("{$plin_logo}")))
                    <td class="text-center">
                        <table>
                            <tr>
                                <td>

                                    Plin

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
                                        {{ $establishment_data->plin_owner }}
                                    @endif
                                    @if ($establishment_data->plin_number)
                                        <br>

                                        {{ $establishment_data->plin_number }}
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                    @endif
                @endif
            </tr>
        </tbody>
    </table>
    <table class="full-width">
        @if ($customer->department_id == 16)
            <tr>
                <td class="text-center desc pt-5">
                    Representación impresa del Comprobante de Pago Electrónico.
                    <br />Esta puede ser consultada en:
                    <br /> <a href="{!! route('search.index', ['external_id' => $document->external_id]) !!}"
                        style="text-decoration: none; font-weight: bold;color:black;">{!! url('/buscar') !!}</a>
                    <br /> "Bienes transferidos en la Amazonía
                    <br />para ser consumidos en la misma
                </td>
            </tr>
        @endif
        @if ($document->terms_condition)
            <tr>
                <td class="desc">
                    <br>
                    <h6 style="font-size: 10px; font-weight: bold;">Términos y condiciones del servicio</h6>
                    {!! $document->terms_condition !!}
                </td>
            </tr>
        @endif
        @if ($company->footer_logo)
            @php
                $footer_logo = "storage/uploads/logos/{$company->footer_logo}";
            @endphp
            @if (file_exists($footer_logo))
                <tr>
                    <td class="text-center pt-0">
                        <img style="max-height: 350px;"
                            src="data:{{ mime_content_type(public_path("{$footer_logo}")) }};base64,
                    {{ base64_encode(file_get_contents(public_path("{$footer_logo}"))) }}"
                            alt="{{ $company->name }}">
                    </td>
                </tr>
            @endif

        @endif
        @if ($company->footer_text_template)
            <tr>
                <td class="text-center desc pt-1">


                    {!! func_str_find_url($company->footer_text_template) !!}
                </td>
            </tr>
        @endif
        <tr>
            @php
                $description = $document->document_type->description;
            @endphp
            <td class="text-center desc pt-2">Representación impresa de la {{-- $description --}}
                {!! $description !!} Esta puede ser
                consultada en <a href="{!! route('search.index', ['external_id' => $document->external_id]) !!}"
                    style="text-decoration: none; font-weight: bold;color:black;">{!! url('/buscar') !!}</a></td>
        </tr>
    </table>

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
        <div
            style="border: 1px solid #ccc; padding: 15px; margin-top: 20px; background-color: #f9f9f9; border-radius: 8px;">
            <h3 style="margin-bottom: 15px; color: #2c3e50; font-size: 16px;">Cupon</h3>
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
                                <p style="margin: 5px 0; font-size: 13px; color: #7f8c8d;">{{ $coupon->descripcion }}
                                </p>
                            @endif
                            @if ($coupon->descuento)
                                <p style="margin: 5px 0; font-size: 13px; color: #27ae60;"><strong>Descuento:</strong>
                                    {{ $coupon->descuento }}%</p>
                            @endif
                            @if ($coupon->fecha_caducidad)
                                <p style="margin: 5px 0; font-size: 13px; color: #c0392b;"><strong>Fecha de
                                        caducidad:</strong>
                                    {{ \Carbon\Carbon::parse($coupon->fecha_caducidad)->format('Y-m-d') }}</p>
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
                        <p style="margin: 5px 0; font-size: 13px; color: #e74c3c;">Coupon not found</p>
                    @endif
                @endif
            @endforeach
        </div>
    @endif



</body>

</html>
