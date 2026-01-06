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

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');

    $document_number = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
    // $document_type_driver = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->driver->identity_document_type_id);

    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

@endphp
<html>

<head>
    {{-- <title>{{ $document_number }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>
    @if (
        $document->state_type->id == '11' ||
            $document->state_type->id == '09' ||
            $document->state_type->id == '55' ||
            $document->state_type->id == '01' ||
            $document->state_type->id == '03')
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
            <td class="text-center">{{ $establishment->email !== '-' ? $establishment->email : '' }}</td>
        </tr>
        <tr>
            <td class="text-center pb-3">{{ $establishment->telephone !== '-' ? $establishment->telephone : '' }}</td>
        </tr>
        <tr>
            <td class="text-center pt-3 border-top">
                <h4>GUÍA DE REMISIÓN TRANSPORTISTA</h4>
            </td>
        </tr>
        <tr>
            <td class="text-center pb-3 border-bottom">
                <h3>{{ $document_number }}</h3>
            </td>
        </tr>
    </table>
    <table class="full-width border-box mt-10 mb-10">
        <thead>
            <tr>
                <th class="border-bottom text-left" colspan="2">ENVIO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Fecha Emisión: {{ $document->date_of_issue->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td>Fecha Inicio de Traslado: {{ $document->date_of_shipping->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td>Peso Bruto Total({{ $document->unit_type_id }}): {{ $document->total_weight }}


                </td>
            </tr>
            @if ($document->packages_number)
                <tr>
                    <td>Número de Bultos: {{ $document->packages_number }}</td>
                </tr>
            @endif
            <tr>
                <td>P.Partida:
                    {{ optional($document->sender_address_data)['address'] }}

                    @if (isset($document->sender_address_data) && isset($document->sender_address_data['location_id']))
                        {{ func_get_location($document->sender_address_data['location_id']) }}
                    @endif
                </td>
            </tr>
            <tr>
                <td>P.Llegada:
                    {{ optional($document->receiver_address_data)['address'] }}

                    @if (isset($document->receiver_address_data) && isset($document->receiver_address_data['location_id']))
                        {{ func_get_location($document->receiver_address_data['location_id']) }}
                    @endif


                </td>
            </tr>
            <tr>
                <td>Datos del Remitente: {{ $document->sender_data['name'] }}
                    - {{ $document->sender_data['number'] }}</td>
            </tr>
            <tr>
                <td>Datos del Destinatario: {{ $document->receiver_data['name'] }}
                    - {{ $document->receiver_data['number'] }}</td>
            </tr>

            

            @if ($document->flete_payer)
                <tr>
                    <td>Pagador del flete: {{ $document->flete_payer->name ?? '' }}</td>
                </tr>
                <tr>
                    <td>RUC/Doc. del pagador: {{ $document->flete_payer->number ?? '' }}</td>
                </tr>
            @endif

            {{--@if ($document->subcontract_company)
                <tr>
                    <td>Subcontratista: {{ $document->subcontract_company->name ?? '' }}</td>
                </tr>
                <tr>
                    <td>RUC/Doc. del subcontratista: {{ $document->subcontract_company->number ?? '' }}</td>
                </tr>
            @endif--}}
            <tr>
                <td colspan="2" class=" ">
                    Documentos relacionados:<br>
                    @foreach ($document->dispatches_related as $related)
                        Guía remitente {{ $related->serie_number }} RUC: {{ $related->company_number }}<br>
                    @endforeach
                </td>
            </tr>
        </tbody>
    </table>
    <table class="full-width border-box mt-10 mb-10">
        <thead>
            <tr>
                <th class="border-bottom text-left" colspan="2">TRANSPORTE</th>
            </tr>
        </thead>
        <tbody>
            @if ($company->mtc_auth)
                <tr>
                    <td>Número de autorización MTC: {{ $company->mtc_auth }}</td>
                </tr>
            @endif
            @if ($document->transport_data)
                <tr>
                    <td>Número de placa del vehículo: {{ $document->transport_data['plate_number'] }}</td>
                </tr>
                <tr>
                    <td>Modelo del vehículo: {{ $document->transport_data['model'] }}</td>
                </tr>
                @if ($document->transport_data['configuration'])
                    <tr>
                        <td>Configuración vehícular: {{ $document->transport_data['configuration'] }}</td>
                    </tr>
                @endif
            @endif
            @if (isset($document->transport_data['auth_plate_primary']))
                <tr>
                    <td>Autorización de placa principal: {{ $document->transport_data['auth_plate_primary'] }}</td>
                </tr>
            @endif
            @if (isset($document->transport_data['tuc']))
                <tr>
                    <td>Tarjeta Única de Circulación Principal: 
                        @php
                            $tuc = '';
                            if (isset($document->transport_data['tuc'])) {
                                $tuc = $document->transport_data['tuc'];
                            }
                        @endphp
                        {{ $tuc }}
                    </td>
                </tr>
            @endif
            @if ($document->tracto_carreta)
                <tr>
                    <td>Marca de tracto carreta: {{ $document->tracto_carreta }}</td>
                </tr>
            @endif
            @if (isset($document->secondary_transport_data['secondary_plate_number']))
                <tr>
                    <td>Número de placa secundaria del vehículo:
                        {{ $document->secondary_transport_data['secondary_plate_number'] }}</td>
                </tr>
            @else
                @if (isset($document->transport_data['secondary_plate_number']))
                    <tr>
                        <td>Número de placa secundaria del vehículo:
                            {{ $document->transport_data['secondary_plate_number'] }}</td>
                    </tr>
                @endif
            @endif

            @if (isset($document->secondary_transport_data['auth_plate_secondary']))
                <tr>
                    <td>Autorización de placa secundaria:
                        {{ $document->secondary_transport_data['auth_plate_secondary'] }}</td>
                </tr>
            @else
                @if (isset($document->transport_data['auth_plate_secondary']))
                    <tr>
                        <td>Autorización de placa secundaria: {{ $document->transport_data['auth_plate_secondary'] }}
                        </td>
                    </tr>
                @endif
            @endif
            @if (isset($document->transport_data['auth_plate_secondary']))
                <tr>
                    <td>Tarjeta Única de Circulación secundaria: 
                        @php
                            $tuc = '';
                            if (isset($document->transport_data['tuc_secondary'])) {
                                $tuc = $document->transport_data['tuc_secondary'];
                            }
                        @endphp
                        {{ $tuc }}
                    </td>
                </tr>
            @endif
            @if ($document->driver->name)
                <tr>
                    <td>Nombre Conductor: {{ $document->driver->name }}</td>
                </tr>
            @endif
            @if ($document->driver->number)
                <tr>
                    <td>Documento Conductor: {{ $document->driver->number }}</td>
                </tr>
            @endif
            @if ($document->driver->license)
                <tr>
                    <td>Licencia del conductor: {{ $document->driver->license }}</td>
                </tr>
            @endif
            @isset($document->secondary_driver->name)
                <tr>
                    <td>Nombre Conductor Secundario: {{ $document->secondary_driver->name }}</td>
                </tr>
                <tr>
                    <td>Documento Conductor Secundario: {{ $document->secondary_driver->number }}</td>
                </tr>
            @endisset




            {{--@if ($document->observations)
                <tr>
                    <td>Observaciones: {{ $document->observations }}</td>
                </tr>
            @endif--}}
        </tbody>
    </table>
    <table class="full-width border-box mt-10 mb-10">
        <thead class="">
            <tr>
                <th class="border-top-bottom text-center">Item</th>
                <th class="border-top-bottom text-center">Código</th>
                <th class="border-top-bottom text-left">Descripción</th>
                <th class="border-top-bottom text-left">Modelo</th>
                <th class="border-top-bottom text-center">Unidad</th>
                <th class="border-top-bottom text-right">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $row->item->internal_id }}</td>
                    <td class="text-left">
                        @if ($row->name_product_pdf)
                            {!! $row->name_product_pdf !!}
                        @else
                            {!! $row->item->description !!}
                        @endif



                        @if ($row->attributes)
                            @foreach ($row->attributes as $attr)
                                <br /><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                            @endforeach
                        @endif
                        @if ($row->discounts)
                            @foreach ($row->discounts as $dtos)
                                <br /><span style="font-size: 9px">{{ $dtos->factor * 100 }}%
                                    {{ $dtos->description }}</span>
                            @endforeach
                        @endif
                        @if ($row->relation_item->is_set == 1)
                            <br>
                            @inject('itemSet', 'App\Services\ItemSetService')
                            @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                                {{ $item }}<br>
                            @endforeach
                        @endif

                        @if ($document->has_prepayment)
                            <br>
                            *** Pago Anticipado ***
                        @endif
                    </td>
                    <td class="text-left">{{ $row->item->model ?? '' }}</td>
                    <td class="text-center">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="text-right">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($document->observations)
        <table class="full-width border-box mt-10 mb-10">
            <tr>
                <td class="text-bold border-bottom font-bold">OBSERVACIONES</td>
            </tr>
            <tr>
                <td>{{ $document->observations }}</td>
            </tr>
        </table>
    @endif
    @if ($document->qr)
        <table class="full-width">
            <tr>
                <td class="text-center">
                    <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px;" />
                </td>
            </tr>
        </table>
    @endif

    <br>
    <table class="full-width">
        @php
            $document_description = null;
            if (is_object($document)) {
                if ($document && $document->prefix == 'NV') {
                    $document_description = 'NOTA DE VENTA ELECTRÓNICA';
                }
                if ($document && $document->document_type_id && $document->document_type) {
                    $document_description = $document->document_type->description;
                }
            }
        @endphp
        <tr>
            @if ($document_description)
                <td class="text-center desc">Representación impresa de la {{ $document_description }} <br />Esta puede
                    ser consultada en {!! searchUrl() !!}</td>
            @else
                <td class="text-center desc">Representación impresa del Comprobante de Pago Electrónico. <br />Esta
                    puede ser consultada en {!! searchUrl() !!}</td>
            @endif
        </tr>
    </table>
    @if ($configurations->terms_condition_dispatches)
        <br>
        <table class="full-width">
            <tr>
                <td>
                    <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                    {!! $configurations->terms_condition_dispatches !!}
                </td>
            </tr>
        </table>
    @endif
</body>

</html>
