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
    if($establishment__->telephone && $establishment__->telephone != '-'){
        $info_additional[] = [
            'image' => 'phone.png',
            'value' => $establishment__->telephone,
        ];
    }
    if($establishment__->email){
        $info_additional[] = [
            'image' => 'email.png',
            'value' => $establishment__->email,
        ];
    }
    if($establishment__->web_address){
        $info_additional[] = [
            'image' => 'website.png',
            'value' => $establishment__->web_address,
        ];
    }
    

    foreach($information_additional_pdf as $additional_info){
        if($additional_info->description || $additional_info->image){
            $imagePath = null;
            $imageBase64 = null;
            
            // Solo procesar imagen si existe y es válida
            if($additional_info->image){
                $fullPath = storage_path('app/public/' . $additional_info->image);
                if(file_exists($fullPath) && filesize($fullPath) <= 500000) { // Máximo 500KB
                    try {
                        $imageContent = file_get_contents($fullPath);
                        if($imageContent !== false && strlen($imageContent) > 0){
                            $imageBase64 = base64_encode($imageContent);
                            $imagePath = $fullPath;
                        }else{
                        }
                    } catch (Exception $e) {
                        // Si hay error, continuar sin la imagen
                    }
                }else{
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
    
@endphp
<html>

<head>

</head>

<body>


    <div class="header">
        <div style="float:left;width:20%">
            @if ($company->logo)
                <div class="company_logo_box" style="width: 100%;text-align: center;">
                    <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                        alt="{{ $company->name }}" class="company_logo" style="max-width: 200px;">
                </div>
            @else
                <br>
            @endif
        </div>
        <div style="float:left;width:2%;">
            <br>
        </div>
        <div style="float:left;width:48%;text-align:left;">
            <h5 style="margin: 0px !important;color: red;">{{ $company_name }}</h5>
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
            <table style="margin: 0px; border-collapse: collapse;">
                @foreach($info_additional as $info)
                <tr>
                    <td colspan="2" style="height: 2px;"></td>
                </tr>
                    <tr>
                        <td style="vertical-align: middle; padding: 0px;">
                            @if(isset($info['is_dynamic']) && $info['is_dynamic'] && isset($info['image_base64']) && $info['image_base64'])
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
                <h7 style="margin: 0px;line-height:0px;">
                    {{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
                </h7>
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
    @isset($establishment__->aditional_information)
    <div
        style="border: 1px solid red;width: 100%;height: 40px;border-radius: 10px;padding:5px;text-align: center;margin-top: 10px;">
        <span style="font-size: 11px;">
            {{ $establishment__->aditional_information !== '-' ? $establishment__->aditional_information : '' }}
        </span>
    </div>
    @endisset
    @inject('detractionType', 'App\Services\DetractionTypeService')

    <table class="full-width mt-3">
        <tr>
            <td valign="top">SEÑOR(ES)</td>
            <td valign="top">:{{ $customer->name }}</td>
            <td valign="top">F.EMISIÓN</td>
            <td valign="top">:{{ $document->date_of_issue->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td valign="top">RUC</td>
            <td valign="top">:{{ $customer->number }}</td>
            <td valign="top">F.VENCIMIENTO</td>
            <td valign="top">:{{ $document->invoice->date_of_due->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td rowspan="2" valign="top">DIRECCIÓN</td>
            <td rowspan="2" valign="top">:{{ $customer->address }}
                {{ $customer->district_id !== '-' ? '' . $customer->district->description : '' }}
                {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                {{ $customer->department_id !== '-' ? ', ' . $customer->department->description : '' }}</td>
            <td valign="top">CONDICIÓN PAGO</td>
            <td valign="top" style="text-transform: uppercase;">:{{ $document->payment_condition->name }}</td>
        </tr>
        <tr>
            <td valign="top">METODO DE PAGO</td>
            <td valign="top" style="text-transform: uppercase;">
                @php
                $item = null;
                $payment_method = "EFECTIVO";
                if($document->payment_condition_id == '01'){
                    $item = $document->payments->first();
                }else{
                    $item = $document->fee->first();
                }
                if($item){
                    try{
                        $payment_method = $item->payment_method_type->description;
                    }catch(\Exception $e){
                        $payment_method = "EFECTIVO";
                    }
                }
                @endphp
                :{{ $payment_method }}
            </td>
        </tr>
        @if ($document->detraction)
            <tr>
                <td>CTA. DETRACCIÓN</td>
                <td>:{{ $document->detraction->bank_account }}</td>
                <td>B/S SUJETO A DETRACCIÓN</td>
                <td>:{{ $document->detraction->detraction_type_id }} -
                    {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id) }}</td>
            </tr>
            <tr>
                <td>MÉTODO DE PAGO</td>
                <td>:{{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id) }}
                </td>
                <td>P. DETRACCIÓN</td>
                <td>:{{ $document->detraction->percentage }}%</td>
            </tr>
            <tr>
                <td>MONTO DETRACCIÓN</td>
                <td>:{{ $document->detraction->amount }}</td>
                <td>CONSTANCIA DE PAGO</td>
                <td>:{{ $document->detraction->pay_constancy }}</td>
            </tr>
        @endif
        @if ($document->retention)
            <tr>
                <td>BASE IMP. RETENCIÓN</td>
                <td>:{{ $document->currency_type->symbol }} {{ $document->retention->base }}</td>
                <td>PORC. RETENCIÓN</td>
                <td>:{{ $document->retention->percentage * 100 }}%</td>
            </tr>
            <tr>
                <td>MONTO RETENCIÓN</td>
                <td>:{{ $document->currency_type->symbol }} {{ $document->retention->amount_pen }}</td>
                <td></td>
                <td></td>
            </tr>
        @endif

        @if ($document->related_document && isset($document->related_document->first()->related_document))
            <tr>
                <td valign="top">DOCUMENTO RELACIONADO</td>
                <td valign="top" style="text-transform: uppercase;">
                    :{{ $document->related_document->first()->related_document }}</td>
                <td></td>
                <td></td>
            </tr>
        @endif

        @if ($document->reference_data)
            <tr>
                <td valign="top">D. REFERENCIA</td>
                <td valign="top">:{{ $document->reference_data }}</td>
                <td></td>
                <td></td>
            </tr>
        @endif

        @if ($document->detraction && $invoice->operation_type_id == '1004')
            <tr>
                <td colspan="4" valign="top"><strong>DETALLE - SERVICIOS DE TRANSPORTE DE CARGA</strong></td>
            </tr>
            @if (isset($document->detraction->origin_location_id) &&
                    $document->detraction->origin_location_id &&
                    !$detraction_transport_not_require_fields)
                <tr>
                    <td valign="top">UBIGEO ORIGEN</td>
                    <td valign="top">:{{ $document->detraction->origin_location_id[2] }}</td>
                    @if (isset($document->detraction->origin_address) &&
                            $document->detraction->origin_address &&
                            !$detraction_transport_not_require_fields)
                        <td valign="top">DIRECCIÓN ORIGEN</td>
                        <td valign="top">:{{ $document->detraction->origin_address }}</td>
                    @else
                        <td></td>
                        <td></td>
                    @endif
                </tr>
            @elseif (isset($document->detraction->origin_address) &&
                    $document->detraction->origin_address &&
                    !$detraction_transport_not_require_fields)
                <tr>
                    <td valign="top">DIRECCIÓN ORIGEN</td>
                    <td valign="top">:{{ $document->detraction->origin_address }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endif

            @if (isset($document->detraction->delivery_location_id) &&
                    $document->detraction->delivery_location_id &&
                    !$detraction_transport_not_require_fields)
                <tr>
                    <td valign="top">UBIGEO DESTINO</td>
                    <td valign="top">:{{ $document->detraction->delivery_location_id[2] }}</td>
                    @if (isset($document->detraction->delivery_address) &&
                            $document->detraction->delivery_address &&
                            !$detraction_transport_not_require_fields)
                        <td valign="top">DIRECCIÓN DESTINO</td>
                        <td valign="top">:{{ $document->detraction->delivery_address }}</td>
                    @else
                        <td></td>
                        <td></td>
                    @endif
                </tr>
            @elseif (isset($document->detraction->delivery_address) &&
                    $document->detraction->delivery_address &&
                    !$detraction_transport_not_require_fields)
                <tr>
                    <td valign="top">DIRECCIÓN DESTINO</td>
                    <td valign="top">:{{ $document->detraction->delivery_address }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endif

            @if (isset($document->detraction->reference_value_service) && $document->detraction->reference_value_service)
                <tr>
                    <td valign="top">VALOR REF. SERVICIO TRANSPORTE</td>
                    <td valign="top">:{{ $document->detraction->reference_value_service }}</td>
                    @if (isset($document->detraction->reference_value_effective_load) &&
                            $document->detraction->reference_value_effective_load &&
                            !$detraction_transport_not_require_fields)
                        <td valign="top">VALOR REF. CARGA EFECTIVA</td>
                        <td valign="top">:{{ $document->detraction->reference_value_effective_load }}</td>
                    @else
                        <td></td>
                        <td></td>
                    @endif
                </tr>
            @elseif (isset($document->detraction->reference_value_effective_load) &&
                    $document->detraction->reference_value_effective_load &&
                    !$detraction_transport_not_require_fields)
                <tr>
                    <td valign="top">VALOR REF. CARGA EFECTIVA</td>
                    <td valign="top">:{{ $document->detraction->reference_value_effective_load }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endif

            @if (isset($document->detraction->reference_value_payload) &&
                    $document->detraction->reference_value_payload &&
                    !$detraction_transport_not_require_fields)
                <tr>
                    <td valign="top">VALOR REF. CARGA ÚTIL</td>
                    <td valign="top">:{{ $document->detraction->reference_value_payload }}</td>
                    @if (isset($document->detraction->trip_detail) &&
                            $document->detraction->trip_detail &&
                            !$detraction_transport_not_require_fields)
                        <td valign="top">DETALLE DEL VIAJE</td>
                        <td valign="top">:{{ $document->detraction->trip_detail }}</td>
                    @else
                        <td></td>
                        <td></td>
                    @endif
                </tr>
            @elseif (isset($document->detraction->trip_detail) &&
                    $document->detraction->trip_detail &&
                    !$detraction_transport_not_require_fields)
                <tr>
                    <td valign="top">DETALLE DEL VIAJE</td>
                    <td valign="top">:{{ $document->detraction->trip_detail }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endif
        @endif

        @if ($document->guides)
            @foreach ($document->guides as $guide)
                <tr>
                    @if (isset($guide->document_type_description))
                        <td valign="top">{{ $guide->document_type_description }}</td>
                    @else
                        <td valign="top">{{ $guide->document_type_id }}</td>
                    @endif
                    <td valign="top">:{{ $guide->number }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        @endif

        @if ($document->dispatch_valid)
            <tr>
                <td valign="top"><strong>GUÍAS DE REMISIÓN</strong></td>
                <td valign="top">:{{ $document->dispatch_valid->number_full }}</td>
                <td></td>
                <td></td>
            </tr>
        @elseif ($document->reference_guides_valid)
            @if (count($document->reference_guides_valid) > 0)
                @foreach ($document->reference_guides_valid as $guide)
                    <tr>
                        @if ($loop->first)
                            <td valign="top"><strong>GUÍAS DE REMISIÓN</strong></td>
                        @else
                            <td valign="top"></td>
                        @endif
                        <td valign="top">:{{ $guide->series }}-{{ $guide->number }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
            @endif
        @endif

        @if ($document->prepayments)
            @foreach ($document->prepayments as $p)
                <tr>
                    <td valign="top">ANTICIPO</td>
                    <td valign="top">:{{ $p->number }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        @endif

        @if ($document->purchase_order)
            <tr>
                <td valign="top">ORDEN DE COMPRA</td>
                <td valign="top">:{{ $document->purchase_order }}</td>
                <td></td>
                <td></td>
            </tr>
        @endif

        @if ($document->quotation_id)
            <tr>
                <td valign="top">COTIZACIÓN</td>
                <td valign="top">:{{ $document->quotation->identifier }}</td>
                @isset($document->quotation->delivery_date)
                    <td valign="top">F. ENTREGA</td>
                    <td valign="top">
                        :{{ $document->date_of_issue->addDays($document->quotation->delivery_date)->format('d-m-Y') }}</td>
                @else
                    <td></td>
                    <td></td>
                @endisset
            </tr>
        @endif

        @isset($document->quotation->sale_opportunity)
            <tr>
                <td valign="top">O. VENTA</td>
                <td valign="top">:{{ $document->quotation->sale_opportunity->number_full }}</td>
                <td></td>
                <td></td>
            </tr>
        @endisset

        @if (!is_null($document_base))
            <tr>
                <td valign="top">DOC. AFECTADO</td>
                <td valign="top">:{{ $affected_document_number }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td valign="top">TIPO DE NOTA</td>
                <td valign="top">
                    :{{ $document_base->note_type === 'credit' ? $document_base->note_credit_type->description : $document_base->note_debit_type->description }}
                </td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td valign="top">DESCRIPCIÓN</td>
                <td valign="top">:{{ $document_base->note_description }}</td>
                <td></td>
                <td></td>
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
        <table class="full-width items">
            <thead class="">
                <tr class="">
                    <th class="border-bottom border-left desc text-center py-2 px-2"
                        width="{{ $width_description }}%">
                        Descripción</th>
                    <th class="border-bottom  desc text-center py-2 rounded-t" width="8%">Cant.
                    </th>
                    <th class="border-bottom border-left desc text-center py-2" width="8%">Unidad
                    </th>

                    @if (!$configuration->document_columns)

                        <th class="border-bottom border-left desc text-right desc py-2 px-2"
                            width="{{ $width_column }}%">V.Unit</th>
                        <th class="border-bottom border-left desc text-right desc py-2 px-2" width="8%">Dto.</th>
                        <th class="border-bottom border-left desc text-right desc py-2 px-2" width="12%">Importe
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
                $cycle = 15;
                $count_items = count($document->items);
                if ($document->prepayments) {
                    try {
                        $prepayments = (array) $document->prepayments;
                        $count_items = $count_items + count($prepayments);
                    } catch (\Exception $e) {
                        $count_items = $count_items + 1;
                    }
                }

                $cycle = 15 - $count_items;

            @endphp
            <tbody>
                @foreach ($document->items as $row)
                    <tr>
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
                        </td>.
                        <td class="text-left desc" class="text-center desc align-top border-left" width="8%">
                            @if ((int) $row->quantity != $row->quantity)
                                {{ $row->quantity }}
                            @else
                                {{ number_format($row->quantity, 0) }}
                            @endif
                        </td>
                        <td class="text-left desc" class="text-center desc align-top border-left" width="8%">
                            {{ symbol_or_code($row->item->unit_type_id) }}</td>

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
        <tr>
            <td valign="top">
                <div class="text-left desc" style="text-align: top; vertical-align: top;">
                    @foreach (array_reverse((array) $document->legends) as $row)
                        @if ($row->code == '1000')
                            <p style="padding:0px;margin:0px;">Son: <span class="font-bold desc"
                                    style="text-transform: uppercase;">{{ $row->value }}
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

                </div>
            </td>
            <td width="50%" valign="top">
                <table class="full-width">


                    @if ($document->total_exportation > 0)
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">OP.
                                EXPORTACIÓN:
                                {{ $document->currency_type->symbol }}</td>
                            <td class="text-left desc" class="text-right desc font-bold desc">
                                {{ number_format($document->total_exportation, 2) }}</td>
                        </tr>
                    @endif
                    @if ($document->total_free > 0)
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">OP.
                                GRATUITAS:
                                {{ $document->currency_type->symbol }}</td>
                            <td class="text-left desc" class="text-right desc font-bold desc">
                                {{ number_format($document->total_free, 2) }}</td>
                        </tr>
                    @endif
                    @if ($document->total_unaffected > 0)
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">OP.
                                INAFECTAS:
                                {{ $document->currency_type->symbol }}</td>
                            <td class="text-left desc" class="text-right desc font-bold desc">
                                {{ number_format($document->total_unaffected, 2) }}</td>
                        </tr>
                    @endif
                    @if ($document->total_exonerated > 0)
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">OP.
                                EXONERADAS:
                                {{ $document->currency_type->symbol }}</td>
                            <td class="text-left desc" class="text-right desc font-bold desc">
                                {{ number_format($document->total_exonerated, 2) }}</td>
                        </tr>
                    @endif

                    @if ($document->document_type_id === '07')
                        @if ($document->total_taxed >= 0)
                            <tr>
                                <td class="text-left desc" colspan="6" class="text-right desc">OP.
                                    GRAVADAS:
                                    {{ $document->currency_type->symbol }}
                                </td>
                                <td class="text-left desc" class="text-right desc">
                                    {{ number_format($document->total_taxed, 2) }}</td>
                            </tr>
                        @endif
                    @elseif($document->total_taxed > 0)
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc">OP. GRAVADAS:
                                {{ $document->currency_type->symbol }}</td>
                            <td class="text-left desc" class="text-right desc">
                                {{ number_format($document->total_taxed, 2) }}</td>
                        </tr>
                    @endif

                    @if ($document->total_plastic_bag_taxes > 0)
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                                ICBPER:
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
                        <td class="text-left desc" class="text-right desc">
                            {{ number_format($document->total_igv, 2) }}</td>
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
                                SUBTOTAL:
                                {{ $document->currency_type->symbol }}
                            </td>
                            <td class="text-left desc" class="text-right desc font-bold desc">
                                {{ number_format($document->subtotal, 2) }}</td>
                        </tr>
                    @endif

                    @if ($document->total_discount > 0)
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                                {{ $document->total_prepayment > 0 ? 'ANTICIPO' : 'DESCUENTO TOTAL' }}
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
                                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                                    CARGOS
                                    ({{ $total_factor }}
                                    %): {{ $document->currency_type->symbol }}</td>
                                <td class="text-left desc" class="text-right desc font-bold desc">
                                    {{ number_format($document->total_charge, 2) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                                    CARGOS:
                                    {{ $document->currency_type->symbol }}</td>
                                <td class="text-left desc" class="text-right desc font-bold desc">
                                    {{ number_format($document->total_charge, 2) }}</td>
                            </tr>
                        @endif
                    @endif
                    @if ($document->perception && $document->invoice->operation_type_id == '2001')
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                                IMPORTE TOTAL:
                                {{ $document->currency_type->symbol }}</td>
                                <td class="text-left desc" class="text-right desc font-bold desc" width="18%">
                                {{ number_format($document->total, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                                PERCEPCIÓN:
                                {{ $document->currency_type->symbol }}</td>
                            <td class="text-left desc" class="text-right desc font-bold desc">
                                {{ number_format($document->perception->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                                TOTAL A PAGAR:
                                {{ $document->currency_type->symbol }}</td>
                            <td class="text-left desc" class="text-right desc font-bold desc">
                                {{ number_format($document->total + $document->perception->amount, 2) }}</td>
                        </tr>
                    @elseif($document->retention)
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">IMPORTE
                                TOTAL:
                                {{ $document->currency_type->symbol }}</td>
                            <td class="text-left desc" class="text-right desc font-bold desc" width="18%">
                                {{ number_format($document->total, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc">TOTAL
                                RETENCIÓN
                                ({{ $document->retention->percentage * 100 }}
                                %): {{ $document->currency_type->symbol }}</td>
                            <td class="text-left desc" class="text-right desc" width="18%">
                                {{ number_format($document->retention->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc">IMPORTE NETO:
                                {{ $document->currency_type->symbol }}</td>
                            <td class="text-left desc" class="text-right desc" width="18%">
                                {{ number_format($document->total - $document->retention->amount, 2) }}
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                                TOTAL A PAGAR:
                                {{ $document->currency_type->symbol }}</td>
                            <td class="text-left desc" class="text-right desc font-bold desc" width="18%">
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
                                PENDIENTE:
                                {{ $document->currency_type->symbol }}</td>
                            <td class="text-left desc" class="text-right desc font-bold desc">
                                {{ number_format($document->total_pending_payment, 2) }}</td>
                        </tr>
                    @endif

                    @if ($balance < 0)
                        <tr>
                            <td class="text-left desc" colspan="6" class="text-right desc font-bold desc">
                                VUELTO:
                                {{ $document->currency_type->symbol }}
                            </td>
                            <td class="text-left desc" class="text-right desc font-bold desc">
                                {{ number_format(abs($balance), 2, '.', '') }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>
    <table class="mt-2 full-width">
        @php
        $establishment_data = \App\Models\Tenant\Establishment::find($document->establishment_id);
        $yape_number = $establishment_data->yape_number;
        $plin_number = $establishment_data->plin_number;

    @endphp
        <tr>
            <td width="50%">
                <table class="full-width">
                    <tr>
                        <td style="width: 45px; height: auto;">
                            <img src="data:image/png;base64, {{ base64_encode(file_get_contents(app_path('CoreFacturalo/Templates/pdf/default_red/plin.png'))) }}" style="width: 35px; height: auto;">
                        </td>
                        <td class="text-left">
                            <p style="font-size: 12px;font-weight: bold;">
                                {{ $plin_number }}
                            </p>
                        </td>
                        <td rowspan="2">
                            <img src="data:image/jpeg;base64, {{ base64_encode(file_get_contents(app_path('CoreFacturalo/Templates/pdf/default_red/tarjetas.jpg'))) }}" style="width: 120px; height: auto;">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <img src="data:image/png;base64, {{ base64_encode(file_get_contents(app_path('CoreFacturalo/Templates/pdf/default_red/yape.png'))) }}" style="width: 40px; height: auto;">
                        </td>
                        <td class="text-left">
                            <p style="font-size: 12px;font-weight: bold;">
                                {{ $yape_number }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="text-end">
                <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px; width: 80px;" />
                <p style="font-size: 8px;margin:0px;">{{ $document->hash }}</p>
            </td>

        </tr>
    </table>
    <div class="full-width">
        @if (in_array($document->document_type->id, ['01', '03']) && count($accounts) > 0)
            <div class="full-width border-box p-1 text-center" style="border-radius: 10px;border:1px solid #000;">
                <table class="full-width table-banks">
                    <thead>
                        <tr>
                            <th class="text-center" width="25%">
                                BANCO
                            </th>
                            <th class="text-center" width="25%">CUENTA</th>
                            <th class="text-center" width="25%">CCI</th>
                            <th class="text-center" width="25%">NOMBRE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($accounts as $account)
                            <tr>
                                <td class="text-center">
                                    {{ $account->description }}
                                </td>
                                <td class="text-center">
                                    {{ $account->number }}
                                </td>
                                <td class="text-center">
                                    @if ($account->cci)
                                        {{ $account->cci }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $account->bank->description }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>








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
</body>

</html>
