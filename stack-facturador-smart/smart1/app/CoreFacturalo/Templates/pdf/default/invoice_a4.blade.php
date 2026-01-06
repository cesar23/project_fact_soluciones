@php
    $connection = \Illuminate\Support\Facades\DB::connection('tenant');
    $configuration = \App\Models\Tenant\Configuration::getConfig();
    $show_no_stock = $configuration->show_no_stock;
    $company_name = $company->name;
    $company_owner = null;
    if ($configuration->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }
    $company_name = $company->name;
    $company_owner = null;
    if ($configuration->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }

    $establishment = $document->establishment;

    $company_name = $company->name;
    $company_owner = null;
    if ($configuration->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }
    // $establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $establishment__ = $connection
        ->table('establishments')
        ->select('logo', 'web_address', 'aditional_information', 'telephone', 'email')
        ->where('id', $document->establishment_id)
        ->first();
    $logo = $establishment__->logo ?? $company->logo;
    // $documment_columns = \App\Models\Tenant\DocumentColumn::where('is_visible', true)->where('type','DOC')
    $documment_columns = \App\Models\Tenant\DocumentColumn::where('is_visible', true)
        ->where('type', 'DOC')
        ->orderBy('column_order', 'asc')
        ->get();

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

    $company_name = $company->name;
    $company_owner = null;
    if ($configuration->trade_name_pdf) {
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

    $detraction_transport_not_require_fields = $configuration->detraction_transport_not_require_fields;

    $payments = $document->payments;

    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = $document->total - $total_payment - $document->payments->sum('change');
    $bg = "storage/uploads/header_images/{$configuration->background_image}";
    $total_discount_items = 0;

    $logo = $establishment__->logo ?? $company->logo;

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

    $company_name = $company->name;
    $company_owner = null;
    if ($configuration->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }
    $plate_number_info = $document->plate_number_info;

    $km = null;
    $plate_number = null;
    $technical_service_car = null;
    $technical_service = $document->technical_service;
    if ($technical_service) {
        $technical_service_car = $technical_service->technical_service_car;
        if ($technical_service_car) {
            $km = $technical_service_car->km;
            $plate_number = $technical_service_car->plate_number;
        }
    }
    $information_additional_pdf = \App\Models\Tenant\InformationAdditionalPdf::where('is_active', true)->get();
    $info_additional = [];
    if ($establishment__->telephone && $establishment__->telephone != '-') {
        $info_additional[] = [
            'image' => 'phone.png',
            'value' => $establishment__->telephone,
        ];
    }
    if ($establishment__->email && $configuration->show_email) {
        $info_additional[] = [
            'image' => 'email.png',
            'value' => $establishment__->email,
        ];
    }
    if ($establishment__->web_address) {
        $info_additional[] = [
            'image' => 'website.png',
            'value' => $establishment__->web_address,
        ];
    }

    foreach ($information_additional_pdf as $additional_info) {
        if ($additional_info->description || $additional_info->image) {
            $imagePath = null;
            $imageBase64 = null;

            // Solo procesar imagen si existe y es válida
            if ($additional_info->image) {
                $fullPath = storage_path('app/public/' . $additional_info->image);
                if (file_exists($fullPath) && filesize($fullPath) <= 500000) {
                    // Máximo 500KB
                    try {
                        $imageContent = file_get_contents($fullPath);
                        if ($imageContent !== false && strlen($imageContent) > 0) {
                            $imageBase64 = base64_encode($imageContent);
                            $imagePath = $fullPath;
                        } else {
                        }
                    } catch (Exception $e) {
                        // Si hay error, continuar sin la imagen
                    }
                } else {
                }
            }

            $info_additional[] = [
                'image_path' => $imagePath,
                'image_base64' => $imageBase64,
                'value' => $additional_info->description ?: '',
                'is_dynamic' => true,
            ];
        }
    }
    $supply_document = $document->supply_document;
    $address_supply = null;
    if ($supply_document) {
        $supply = $supply_document->SupplyPlanRegistered->supply;
        $sector = $supply->sector;
        $supply_via = $supply->supplyVia;
        $mz = $supply->mz;
        $lte = $supply->lte;
        $address_supply =
            $sector->name . ' - ' . $supply_via->name . ' ' . ($mz ? 'Mz ' . $mz : '') . ($lte ? ' Lte ' . $lte : '');
    }

@endphp
<html>

<head>

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
    @if ($configuration->background_image && file_exists(public_path("{$bg}")))
        <div class="centered">
            <img src="data:{{ mime_content_type(public_path("{$bg}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$bg}"))) }}"
                alt="anulado" class="order-1">
        </div>
    @endif
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
            {{-- 
            <h6 style="margin: 0px !important;line-height:0px;">
                {{ $establishment->telephone !== '-' ? '' . $establishment->telephone : '' }}
            </h6>
            @if ($configuration->show_email)
                <h6 style="margin: 0px !important;line-height:0px;">
                    {{ $establishment->email !== '-' ? '' . $establishment->email : '' }}</h6>
            @endif
            @isset($establishment__->web_address)
                <h6 style="margin: 0px;line-height:0px;">
                    {{ $establishment__->web_address !== '-' ? '' . $establishment__->web_address : '' }}
                </h6>
            @endisset --}}
            <table style="margin: 0px; border-collapse: collapse;">
                @foreach ($info_additional as $info)
                    <tr>
                        <td colspan="2" style="height: 2px;"></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: middle; padding: 0px;">
                            @if (isset($info['is_dynamic']) && $info['is_dynamic'] && isset($info['image_base64']) && $info['image_base64'])
                                {{-- Imagen dinámica optimizada --}}
                                <img src="data:image/{{ pathinfo($info['image_path'], PATHINFO_EXTENSION) }};base64,{{ $info['image_base64'] }}"
                                    style="width: 15px; height: 15px;">
                            @elseif(isset($info['image']) && $info['image'] && !isset($info['is_dynamic']))
                                {{-- Imagen estática desde el template --}}
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(app_path('CoreFacturalo/Templates/pdf/default_red/' . $info['image']))) }}"
                                    style="width: 15px; height: 15px;">
                            @endif
                        </td>
                        <td style="vertical-align: middle; padding: 0px; padding-left: 5px;">
                            {{ $info['value'] !== '-' ? $info['value'] : '' }}
                        </td>
                    </tr>
                @endforeach
            </table>

            @isset($establishment->aditional_information)
                <h6 style="margin: 0px;line-height:0px;">
                    {{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
                </h6>
            @endisset
        </div>
        <div style="float:left;width:30%;">
            <div style="border-radius:10px;border:1px solid black;text-align:center;width: 100%;height: 80px;">
                <div style="margin-top:12px;">{{ 'RUC ' . $company->number }}</div>
                <div class="text-center" style="margin-top:3px;">{{ $document->document_type->description }}</div>
                <div class="text-center" style="margin-top:3px;">{{ $document_number }}</div>
            </div>
        </div>
    </div>

    <table class="full-width mt-3">
        <tr>
            <td class="text-left desc" width="120px">Fecha de emisión</td>
            <td class="text-left desc" width="8px">:</td>
            <td class="text-left desc">{{ $document->date_of_issue->format('Y-m-d') }}</td>

            @if ($document->detraction)
                <td class="text-left desc" width="140px">N. Cta detracciones</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->detraction->bank_account }}</td>
            @endif
        </tr>
        @if ($invoice)
            @if ($configuration->date_of_due_pdf)
                <tr>
                    <td class="text-left desc">Fecha de vencimiento</td>
                    <td class="text-left desc">:</td>
                    <td class="text-left desc">{{ $invoice->date_of_due->format('Y-m-d') }}</td>
                </tr>
            @endif
        @endif

        @if ($document->detraction)
            <td class="text-left desc" width="140px">B/S Sujeto a detracción</td>
            <td class="text-left desc" width="8px">:</td>
            @inject('detractionType', 'App\Services\DetractionTypeService')
            <td class="text-left desc" width="220px">{{ $document->detraction->detraction_type_id }}
                - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id) }}</td>
        @endif
        <tr>
            @if ($configuration->info_customer_pdf)
                <td class="text-left desc" style="vertical-align: top;">Cliente</td>
                <td class="text-left desc" style="vertical-align: top;">:</td>
                <td class="text-left desc" style="vertical-align: top;">
                    {{ $customer->name }}
                    @if ($configuration->show_internal_code_person)
                        @if (isset($customer->internal_code) && $customer->internal_code !== '')
                            <br>
                            <small>{{ $customer->internal_code ?? '' }}</small>
                        @elseif (isset($customer->internal_id) && $customer->internal_id !== '')
                            <br>
                            <small>{{ $customer->internal_id ?? '' }}</small>
                        @endif
                    @endif
            @endif
            </td>

            @if ($document->detraction)
                <td class="text-left desc" width="140px">Método de pago</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">
                    {{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id) }}
                </td>
            @endif

        </tr>
        <tr>
            @if ($configuration->info_customer_pdf)
                <td class="text-left desc">{{ $customer->identity_document_type->description }}</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc">{{ $customer->number }}</td>
            @endif

            @if ($document->detraction)
                <td class="text-left desc" width="140px">P. Detracción</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->detraction->percentage }}%</td>
            @endif
        </tr>
        @isset($customer->search_telephone)
            @if ($customer->search_telephone != null)
                @if ($configuration->info_customer_pdf)
                    <tr>
                        <td class="text-left desc">Teléfono</td>
                        <td class="text-left desc">:</td>
                        <td class="text-left desc">{{ $customer->search_telephone }}</td>
                    </tr>
                @endif
            @endif
        @endisset


        @if ($customer->address !== '')
            @if ($configuration->info_customer_pdf)
                <tr>
                    <td class="text-left desc" class="align-top desc">Dirección</td>
                    <td class="text-left desc" style="vertical-align: top;">:</td>
                    <td class="text-left desc" style="desc text-transform: uppercase;">
                        @if ($address_supply)
                            {{ $address_supply }}
                        @else
                            {{ $customer->address }}
                            {{ $customer->district_id !== '-' ? '' . $customer->district->description : '' }}
                            {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                            {{ $customer->department_id !== '-' ? ', ' . $customer->department->description : '' }}
                        @endif
                    </td>
            @endif

            @if ($document->detraction)
                <td class="text-left desc" width="140px">Monto detracción</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">
                    @php
                        $amount_detraction = $document->detraction->amount;
                        // if($document->currency_type_id != "PEN"){
                        //     $exchange_rate_sale = $document->exchange_rate_sale;
                        //     $amount_detraction = $amount_detraction * $exchange_rate_sale;
                        //     $amount_detraction = number_format($amount_detraction, 2, ".", "");
                        // }
                    @endphp

                    S/ {{ $amount_detraction }}</td>
            @endif
            </tr>
        @endif
        @if (
            $document->quotations_optional !== '' &&
                $document->quotations_optional != null &&
                $document->quotations_optional_value !== null &&
                $document->quotations_optional_value != '')
            <tr>
                <td class="text-left desc" class="align-top">{{ $document->quotations_optional }}:</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc" style="text-transform: uppercase;">
                    {{ $document->quotations_optional_value }}
                </td>
            </tr>
        @endif

        @if ($document->related_document && isset($document->related_document->first()->related_document))
            <tr>
                <td class="text-left desc" class="align-top desc">Documento relacionado:</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc" style="text-transform: uppercase;">
                    {{ $document->related_document->first()->related_document }}
                </td>
            </tr>
        @endif

        @if ($document->hotelRent)
            <tr>
                <td class="text-left desc" class="align-top">Destino:</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc" style="text-transform: uppercase;">
                    {{ $document->hotelRent->destiny }}
                </td>
            </tr>
        @endif

        @if ($document->reference_data)
            <tr>
                <td class="text-left desc" width="120px">D. referencia</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->reference_data }}</td>
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
                        <td class="text-left desc" class="align-top">Dirección de envío</td>
                        <td class="text-left desc">:</td>
                        <td class="text-left desc" style="text-transform: uppercase;">
                            {{ $ship_address }}
                        </td>
                    </tr>
                @endif
                @if ($observation)
                    <tr>
                        <td class="text-left desc" class="align-top">Observación Pd</td>
                        <td class="text-left desc">:</td>
                        <td class="text-left desc" style="text-transform: uppercase;">
                            {{ $observation }}
                        </td>
                    </tr>
                @endif
            @endisset
        @endif
        @if ($document->detraction)
            @if ($document->detraction->pay_constancy)
                <tr>
                    <td class="text-left desc" colspan="3">
                    </td>
                    <td class="text-left desc" width="140px">Constancia de pago</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $document->detraction->pay_constancy }}</td>
                </tr>
            @endif
        @endif

        @if ($document->detraction && $invoice->operation_type_id == '1004')
            <tr>
                <td class="text-left desc" colspan="4"><strong>Detalle - Servicios de transporte de carga</strong>
                </td>
            </tr>
            <tr>
                @if (isset($document->detraction->origin_location_id) &&
                        $document->detraction->origin_location_id &&
                        !$detraction_transport_not_require_fields)
                    <td class="text-left desc" class="align-top">Ubigeo origen</td>
                    <td class="text-left desc">:</td>
                    <td class="text-left desc">{{ $document->detraction->origin_location_id[2] }}</td>
                @endif
                @if (isset($document->detraction->origin_address) &&
                        $document->detraction->origin_address &&
                        !$detraction_transport_not_require_fields)
                    <td class="text-left desc" width="140px">Dirección origen</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $document->detraction->origin_address }}</td>
                @endif
            </tr>
            <tr>
                @if (isset($document->detraction->delivery_location_id) &&
                        $document->detraction->delivery_location_id &&
                        !$detraction_transport_not_require_fields)
                    <td class="text-left desc" class="align-top">Ubigeo destino</td>
                    <td class="text-left desc">:</td>
                    <td class="text-left desc">{{ $document->detraction->delivery_location_id[2] }}</td>
                @endif
                @if (isset($document->detraction->delivery_address) &&
                        $document->detraction->delivery_address &&
                        !$detraction_transport_not_require_fields)
                    <td class="text-left desc" width="140px">Dirección destino</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $document->detraction->delivery_address }}</td>
                @endif
            </tr>
            <tr>
                @if (isset($document->detraction->reference_value_service) && $document->detraction->reference_value_service)
                    <td class="text-left desc" class="align-top" width="140px">Valor referencial servicio de
                        transporte
                    </td>
                    <td class="text-left desc">:</td>
                    <td class="text-left desc">
                        {{$document->currency_type->symbol}}
                        {{ $document->detraction->reference_value_service }}</td>
                @endif
                @if (isset($document->detraction->reference_value_effective_load) &&
                        $document->detraction->reference_value_effective_load &&
                        !$detraction_transport_not_require_fields)
                    <td class="text-left desc" width="140px">Valor referencia carga efectiva</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $document->currency_type->symbol}} {{ $document->detraction->reference_value_effective_load }}</td>
                @endif
            </tr>
            <tr>
                @if (isset($document->detraction->reference_value_payload) &&
                        $document->detraction->reference_value_payload &&
                        !$detraction_transport_not_require_fields)
                    <td class="text-left desc" class="align-top">Valor referencial carga útil</td>
                    <td class="text-left desc">:</td>
                    <td class="text-left desc">{{ $document->currency_type->symbol}} {{ $document->detraction->reference_value_payload }}</td>
                @endif
                @if (isset($document->detraction->trip_detail) &&
                        $document->detraction->trip_detail &&
                        !$detraction_transport_not_require_fields)
                    <td class="text-left desc" width="140px">Detalle del viaje</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $document->detraction->trip_detail }}</td>
                @endif
            </tr>
        @endif
        @if ($document->isPointSystem())
            <tr>
                <td class="text-left desc align-top" width="120px">P. acumulados</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->person->accumulated_points }}</td>

                <td class="text-left desc" width="140px">Puntos por la compra</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->getPointsBySale() }}</td>
            </tr>
        @endif

        @if ($plate_number_info)
            <tr>
                <td class="align-top">N° Placa</td>
                <td class="text-left desc" width="8px">:</td>
                <td>{{ $plate_number_info['description'] }}</td>
                <td>Año</td>
                <td class="text-left desc" width="8px">:</td>
                <td>{{ $plate_number_info['year'] }}</td>
            </tr>
            <tr>
                <td>Marca</td>
                <td class="text-left desc" width="8px">:</td>
                <td>{{ $plate_number_info['brand'] }}</td>
                <td>Modelo</td>
                <td class="text-left desc" width="8px">:</td>
                <td>{{ $plate_number_info['model'] }}</td>
            </tr>
            <tr>
                <td>Color</td>
                <td class="text-left desc" width="8px">:</td>
                <td>{{ $plate_number_info['color'] }}</td>
                <td>Tipo</td>
                <td class="text-left desc" width="8px">:</td>
                <td>{{ $plate_number_info['type'] }}</td>
            </tr>
        @endif
        @if ($technical_service_car)
            <tr>
                <td class="text-left desc" width="140px">N° Placa</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $plate_number }}</td>
                <td class="text-left desc align-top" width="120px">Kilometraje</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $km }}</td>


            </tr>
        @endif

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
    @if ($document->dispatch_valid)
        <br />
        <strong>Guías de remisión</strong>
        <table>
            <tr>
                <td class="text-left desc">{{ $document->dispatch_valid->number_full }}</td>
            </tr>
        </table>
    @elseif ($document->reference_guides_valid)
        @if (count($document->reference_guides_valid) > 0)
            <br />
            <strong>Guías de remisión</strong>
            <table>
                @foreach ($document->reference_guides_valid as $guide)
                    <tr>
                        <td class="text-left desc">{{ $guide->series }}</td>
                        <td class="text-left desc">-</td>
                        <td class="text-left desc">{{ $guide->number }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
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
        if ($configuration->change_decimal_quantity_unit_price_pdf) {
            if (
                $configuration->decimal_quantity_unit_price_pdf > 6 &&
                $configuration->decimal_quantity_unit_price_pdf <= 8
            ) {
                $width_column = 13;
            } elseif ($configuration->decimal_quantity_unit_price_pdf > 8) {
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
                    @if (!$configuration->document_columns)

                        <th class="border-bottom border-left desc text-right desc py-2 px-2"
                            width="{{ $width_column }}%">P.Unit</th>
                        <th class="border-bottom border-left desc text-right desc py-2 px-2" width="8%">Dto.</th>
                        <th class="border-bottom border-left desc text-right desc py-2 px-2" width="12%">Total
                        </th>
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
                $cycle = 1;
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
                    $cycle = 1 - $count_items;
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

                            <span style="margin-top: 0px;padding-top: 0px;">
                                {!! $description !!}
                            </span>

                            @isset($row->item->type_discount)
                                <br>
                                <span style="font-size: 9px">Dscto: {{ $row->item->type_discount }}</span>
                            @endisset
                            @if ($configuration->name_pdf)
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
                            @if ($configuration->presentation_pdf && isset($row->item->presentation) && isset($row->item->presentation->description))
                                <div>
                                    <span style="font-size: 9px">{{ $row->item->presentation->description }}</span>
                                </div>
                            @endif
                            @if ($row->total_isc > 0)
                                <br /><span style="font-size: 9px">ISC : {{ $row->total_isc }}
                                    ({{ $row->percentage_isc }}%)</span>
                            @endif



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
                            @if ($configuration->show_item_attributes == true)
                                @if (isset($row->item->idAttributeSelect))
                                    @foreach ($row->item->idAttributeSelect as $row_data)
                                        Marca: {{ $row_data->attribute }}<br>
                                        Año de Modelo {{ $row_data->attribute5 }}<br>
                                        Color: {{ $row_data->attribute3 }}<br>
                                        Motor: {{ $row_data->attribute4 }}<br>
                                        Modelo: {{ $row_data->attribute2 }}<br>
                                        Serie / Chasis {{ $row_data->chassis }}<br>
                                    @endforeach
                                @endif
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
                                @if ($configuration->change_decimal_quantity_unit_price_pdf)
                                    {{ $row->generalApplyNumberFormat($row->unit_price, $configuration->decimal_quantity_unit_price_pdf) }}
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
                                    @if ($column->value == 'image')
                                        <img src="data:{{ mime_content_type(public_path(parse_url($value, PHP_URL_PATH))) }};base64, {{ base64_encode(file_get_contents(public_path(parse_url($value, PHP_URL_PATH)))) }}"
                                            alt="Imagen" style="width: 150px; height: auto;">
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
                            @if (!$configuration->document_columns)
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
                        @if (!$configuration->document_columns)
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
    @if ($show_no_stock)
        <div class="full-width desc">
            @if ($document->no_stock)
                <div class="text-left desc">
                    <span class="font-bold desc">ENTREGA PARCIAL</span>
                </div>
            @else
                <div class="text-left desc">
                    <span class="font-bold desc">ENTREGA TOTAL</span>
                </div>
            @endif
        </div>
    @endif
    <table class="full-width">


        @if ($configuration->taxed_igv_visible_doc && !$company->is_rus)
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
            @if ($document->total_igv > 0)
                <tr>
                    <td class="text-left desc" colspan="6" class="text-right desc">IGV:
                        {{ $document->currency_type->symbol }}
                    </td>
                    <td class="text-left desc" class="text-right desc">{{ number_format($document->total_igv, 2) }}
                    </td>
                </tr>
            @endif
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
                                $discount = isset($discounts[0]) ? $discounts[0] : null;
                                $is_split = isset($discount->is_split) ? $discount->is_split : false;
                                $has_unaffected = collect($document->items)->some(function ($i) {
                                    return $i->affectation_igv_type_id !== '10';
                                });
                                if ($is_split && !$has_unaffected) {
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
        @if ($document->perception && $document->invoice->operation_type_id == '2001')
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
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">Importe
                    total:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-left desc" class="text-right desc font-bold desc" width="12%">
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

        @if ($balance < 0 && !is_integrate_system())
            <tr>
                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                    Vuelto:
                    {{ $document->currency_type->symbol }}
                </td>
                <td class="text-left desc" class="text-right desc font-bold desc">
                    {{ number_format(abs($balance), 2, '.', '') }}</td>
            </tr>
        @endif
    </table>
    <div class="full-width desc mb-10">
        <div class="float-left w-65">
            <div class="text-left desc" style="text-align: top; vertical-align: top;">
                @foreach (array_reverse((array) $document->legends) as $row)
                    @if ($row->code == '1000')
                        <p style="padding:0px;margin:0px;">Son: <span class="font-bold desc">{{ $row->value }}
                                {{ $document->currency_type->description }}</span></p>
                    @else
                        <p style="padding:0px;margin:0px;"> {{ $row->code }}: {{ $row->value }} </p>
                    @endif
                @endforeach
                <p style="padding:0px;margin:0px;">
                    <span class="font-bold desc desc">
                    </span>
                </p>
                <br />

                @foreach ($document->additional_information as $information)
                    @if ($information)
                        @if ($loop->first)
                            <strong>Información adicional</strong>
                        @endif
                        <p>
                            @if (\App\CoreFacturalo\Helpers\Template\TemplateHelper::canShowNewLineOnObservation())
                                {!! \App\CoreFacturalo\Helpers\Template\TemplateHelper::SetHtmlTag($information) !!}
                            @else
                                @if ($document->hotelRent)
                                    {!! $information !!}
                                @else
                                    {{ $information }}
                                @endif
                            @endif
                        </p>
                    @endif
                @endforeach
                @if (in_array($document->document_type->id, ['01', '03']) && count($accounts) > 0)
                    <div class="float-left w-80 border-box p-1" style="border-radius: 10px;">
                        @foreach ($accounts as $account)
                            <div class="desc ">{{ $account->bank->description }} Cuenta:
                                {{ $account->currency_type->description }} N°: {{ $account->number }}</div>

                            <div>
                                <span class="font-bold"></span>

                            </div>

                            <div class=" desc ">
                                <span class="font-bold">
                                </span>
                            </div>
                            @if ($account->cci)
                                <div class="desc ">
                                    <span class="">
                                        CCI:
                                    </span>
                                    {{ $account->cci }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="float-left w-35">
            <div class="text-left desc" class="text-right desc">
                <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px; width: 100px;" />
                <p style="font-size: 8px;margin:0px;">{{ $document->hash }}</p>
            </div>
        </div>
    </div>
    @php
        $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
    @endphp
    <table class="full-width desc">
        <tr>
            <td class="text-left desc">
                <strong>Condición de Pago: {{ $paymentCondition }} </strong>
            </td>
        </tr>
    </table>

    @if ($document->payment_method_type_id)
        <table class="full-width desc">
            <tr>
                <td class="text-left desc">
                    <strong>Método de Pago: </strong>{{ $document->payment_method_type->description }}
                </td>
            </tr>
        </table>
    @endif

    @if ($document->payment_condition_id === '01')
        @if ($payments->count())
            <table class="full-width desc">
                <tr>
                    <td class="text-left desc"><strong>Pagos:</strong></td>
                </tr>
                @php $payment = 0; @endphp
                @foreach ($payments as $row)
                    @isset($row->payment_method_type)
                        <tr>
                            <td class="text-left desc">&#8226; {{ $row->payment_method_type->description }}
                                - {{ $row->reference ? $row->reference . ' - ' : '' }}
                                {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
                        </tr>
                    @endisset
                    @php
                        $payment += (float) $row->payment;
                    @endphp
                @endforeach
                </tr>
                @if ($document->total - $payment > 0)
                    <tr>
                        <td class="text-left desc">&#8226; Saldo: {{ $document->currency_type->symbol }}
                            {{ number_format($document->total - $payment, 2) }}</td>
                    </tr>
                @endif
            </table>
        @endif
    @else
        <table class="full-width desc">
            @foreach ($document->fee as $key => $quote)
                <tr>
                    <td class="text-left desc">
                        @if (!$configuration->show_the_first_cuota_document)
                            &#8226;
                            {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                            / Fecha: {{ $quote->date->format('d-m-Y') }} /
                            Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}
                        @else
                            @if ($key == 0)
                                &#8226;
                                {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                                / Fecha: {{ $quote->date->format('d-m-Y') }} /
                                Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}
                            @endif
                        @endif

                    </td>
                </tr>
            @endforeach
            </tr>
        </table>
    @endif


    @if ($document->retention)
        <br>
        <table class="full-width desc">
            <tr>
                <td class="text-left desc">
                    <strong>Información de la retención:</strong>
                </td>
            </tr>
            <tr>
                <td class="text-left desc">Base imponible de la retención:
                    {{ $document->currency_type->symbol }} {{ $document->retention->base }}</td>
            </tr>
            <tr>
                <td class="text-left desc">Porcentaje de la retención {{ $document->retention->percentage * 100 }}%
                </td>
            </tr>
            <tr>
                <td class="text-left desc">Monto de la retención S/ {{ $document->retention->amount_pen }}</td>
            </tr>
        </table>
    @endif

    <br>
    <table class="full-width desc">
        <tr>
            <td class="text-left desc">
                <strong>Vendedor:</strong>
            </td>
        </tr>
        <tr>
            @if ($document->seller)
                <td class="text-left desc">{{ $document->seller->name }}</td>
            @else
                <td class="text-left desc">{{ $document->user->name }}</td>
            @endif
        </tr>

        @if (isset($document->channel))
            <tr>
                <td colspan="2">
                    <br>
                </td>
            </tr>
            <tr>
                <td class="text-left desc"><strong>Canal:</strong>
                </td>
            </tr>
            <tr>
                <td class="text-left desc">{{ $document->channel->channel->channel_name }}</td>
            </tr>
        @endif
    </table>

    <table class="full-width desc">
        @php
            $establishment_data = \App\Models\Tenant\Establishment::find($document->establishment_id);
        @endphp
        <tbody>
            <tr>
                @if ($configuration->yape_qr_documents && $establishment_data->yape_logo)
                    @php
                        $yape_logo = $establishment_data->yape_logo;
                    @endphp
                    @if ($yape_logo != null && file_exists(public_path("{$yape_logo}")))
                        <td class="text-left desc" class="text-center">
                            <table>
                                <tr>
                                    <td class="text-left desc">
                                        <strong>
                                            Qr Yape
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left desc">
                                        <img src="data:{{ mime_content_type(public_path("{$yape_logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$yape_logo}"))) }}"
                                            alt="{{ $company->name }}" class="company_logo"
                                            style="max-width: 150px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left desc">
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
                @endif
                @if ($configuration->plin_qr_documents && $establishment_data->plin_logo)
                    @php
                        $plin_logo = $establishment_data->plin_logo;
                    @endphp
                    @if ($plin_logo != null && file_exists(public_path("{$plin_logo}")))
                        <td class="text-left desc" class="text-center">
                            <table>
                                <tr>
                                    <td class="text-left desc">
                                        <strong>
                                            Qr Plin
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left desc">
                                        <img src="data:{{ mime_content_type(public_path("{$plin_logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$plin_logo}"))) }}"
                                            alt="{{ $company->name }}" class="company_logo"
                                            style="max-width: 150px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left desc">
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

                @endif
            </tr>
        </tbody>
    </table>
    @if ($document->terms_condition)
        <br>
        <table class="full-width desc">
            <tr>
                <td class="text-left desc">
                    <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                    {!! $document->terms_condition !!}
                </td>
            </tr>
        </table>
    @endif

    @if ($configuration->print_vouchers)
    <div style="page-break-before: always;">
        <table class="full-width desc" >
            @foreach ($payments as $payment)
                @if ($payment->payment_file)
                    @php
                        $imageSrc = null;
                        $filename = $payment->payment_file->filename;
                        $is_image = false;
                        $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'ico', 'webp'];
                        $extension = pathinfo($filename, PATHINFO_EXTENSION);
                        if (in_array($extension, $image_extensions)) {
                            $is_image = true;
                        }
                        if ($is_image) {
                            $filePath =
                                'payment_files' . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . $filename;

                            // Verificar si el archivo existe en el disco tenant
                            if (\Illuminate\Support\Facades\Storage::disk('tenant')->exists($filePath)) {
                                // Obtener el contenido y convertir a base64
                                $imageData = \Illuminate\Support\Facades\Storage::disk('tenant')->get($filePath);
                                $base64 = base64_encode($imageData);

                                // Obtener la ruta completa solo para el MIME type
                                $fullPath = \Illuminate\Support\Facades\Storage::disk('tenant')->path($filePath);
                                $mimeType = mime_content_type($fullPath);

                                $imageSrc = "data:{$mimeType};base64,{$base64}";
                            }
                        } else {
                            $imageSrc = null;
                        }
                    @endphp

                    @if ($imageSrc)
                        <tr>
                            <td class="text-left desc">
                                <h6 style="font-size: 12px; font-weight: bold; margin-bottom: 5px;">
                                    Voucher de Pago
                                </h6>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center desc">
                                <img src="{{ $imageSrc }}" alt="Voucher de Pago"
                                    style="max-width: 250px; margin: 10px auto; display: block; border: 1px solid #ddd; padding: 5px;">
                            </td>
                        </tr>
                    @endif
                @endif
            @endforeach
        </table>
    </div>
    @endif
</body>

</html>
