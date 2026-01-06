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
    $logo = str_replace("storage/uploads/logos/storage/uploads/logos/", "storage/uploads/logos/", $logo);
}


$customer = $document->customer;
$invoice = $document->invoice;
$document_base = ($document->note) ? $document->note : null;

//$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
$document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
$accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();

if($document_base) {

$affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

} else {

$affected_document_number = null;
}

$configurations = \App\Models\Tenant\Configuration::first();

$payments = $document->payments;

$company_name = $company->name;
$company_owner = null;
if ($configurations->trade_name_pdf) {
    $company_name = $company->trade_name;
    $company_owner = $company->name;
}

$document->load('reference_guides');

$total_payment = $document->payments->sum('payment');
$balance = ($document->total - $total_payment) - $document->payments->sum('change');

$establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
$logo = $establishment__->logo ?? $company->logo;

if ($logo === null && !file_exists(public_path("$logo}"))) {
    $logo = "{$company->logo}";
}

if ($logo) {
    $logo = "storage/uploads/logos/{$logo}";
    $logo = str_replace("storage/uploads/logos/storage/uploads/logos/", "storage/uploads/logos/", $logo);
}



$configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();

@endphp
<html>

<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>

<body>
    @if($document->state_type->id == '11'  || $document->state_type->id == '09')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
    @endif
    @if($document->soap_type_id == '01')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."demo.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."demo.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
    @endif
    <table class="full-width">
        <tr>
            @if($company->logo)
            <td width="20%">
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 150px;">
                </div>
            </td>
            @else
            <td width="20%">
                {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px">--}}
            </td>
            @endif
            <td width="50%" class="pl-3">
                <div class="text-left">
                    <h4 class="">{{ $company_name }}</h4>
                    @if ($company_owner)
                        De: {{ $company_owner }}
                    @endif
                    <h6 style="text-transform: uppercase;">
                        {{ ($establishment->address !== '-')? $establishment->address : '' }}
                        {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                        {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                        {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                    </h6>

                    @isset($establishment->trade_address)
                    <h6>{{ ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : '' }}</h6>
                    @endisset

                    <h6>{{ ($establishment->telephone !== '-')? 'Central telefónica: '.$establishment->telephone : '' }}</h6>

                    <h6>{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</h6>

                    @isset($establishment->web_address)
                    <h6>{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</h6>
                    @endisset

                    @isset($establishment->aditional_information)
                    <h6>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</h6>
                    @endisset
                </div>
            </td>
            <td width="30%" class="border-box py-3 px-2 text-center">
                <h5>{{ 'RUC '.$company->number }}</h5>
                <h5 style="vertical-align: top; text-transform: capitalize;" class="text-center">{{ $document->document_type->description }}</h5>
                <h5 class="text-center">{{ $document_number }}</h5>
            </td>
        </tr>
    </table>
    <table class="full-width mt-5">
        <tr>
            <td width="120px">Fecha de emisión</td>
            <td width="8px">:</td>
            <td>{{$document->date_of_issue->format('Y-m-d')}}</td>

            @if ($document->detraction)

            <td width="120px">N. Cta detracciones</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->bank_account}}</td>
            @endif
        </tr>
        @if($invoice)
        <tr>
            <td>F. de vencimiento</td>
            <td width="8px">:</td>
            <td>{{$invoice->date_of_due->format('Y-m-d')}}</td>
        </tr>
        @endif

        @if ($document->detraction)
        <td width="140px">B/S Sujeto a detracción</td>
        <td width="8px">:</td>
        @inject('detractionType', 'App\Services\DetractionTypeService')
        <td width="220px">{{$document->detraction->detraction_type_id}}
            - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id ) }}</td>

        @endif
        <tr>
            <td style="vertical-align: top;">Cliente:</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">
                {{ $customer->name }}
                @if ($customer->internal_code ?? false)
                <br>
                <small>{{ $customer->internal_code ?? '' }}</small>
                @endif
            </td>

            @if ($document->detraction)
            <td width="120px">Método de pago</td>
            <td width="8px">:</td>
            <td width="220px">{{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id ) }}</td>
            @endif

        </tr>
        <tr>
            <td>{{ $customer->identity_document_type->description }}</td>
            <td>:</td>
            <td>{{$customer->number}}</td>

            @if ($document->detraction)

            <td width="120px">P. Detracción</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->percentage}}%</td>
            @endif
        </tr>
        @if ($customer->address !== '')
        <tr>
            <td class="align-top">Dirección:</td>
            <td>:</td>
            <td style="text-transform: capitalize;">
                {{ $customer->address }}
                {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                {{ ($customer->department_id !== '-')? ', '.$customer->department->description : '' }}
            </td>

            @if ($document->detraction)
            <td width="120px">Monto detracción</td>
            <td width="8px">:</td>
            <td>S/ {{ $document->detraction->amount}}</td>
            @endif
        </tr>
        @endif
        @if ($document->quotations_optional !== '' && $document->quotations_optional_value !== '')
        <tr>
            <td class="align-top">{{$document->quotations_optional}}:</td>
            <td>:</td>
            <td style="text-transform: uppercase;">
                {{ $document->quotations_optional_value }}
            </td>
        </tr>
        @endif

        @if ($document->hotelRent)
        <tr>
            <td class="align-top">Destino:</td>
            <td>:</td>
            <td style="text-transform: uppercase;">
                {{ $document->hotelRent->destiny }}
            </td>
        </tr>
    @endif

        @if ($document->reference_data)
        <tr>
            <td width="120px">D. referencia</td>
            <td width="8px">:</td>
            <td>{{ $document->reference_data}}</td>
        </tr>
        @endif

        @if ($document->detraction)
        @if($document->detraction->pay_constancy)
        <tr>
            <td colspan="3">
            </td>
            <td width="120px">Constancia de pago</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->pay_constancy}}</td>
        </tr>
        @endif
        @endif

        @if($document->detraction && $invoice->operation_type_id == '1004')
        <tr>
            <td colspan="4"><strong>Detalle - servicio de transporte de carga</strong></td>
        </tr>
        <tr>
            <td class="align-top">Ubigeo origen</td>
            <td>:</td>
            <td>{{ $document->detraction->origin_location_id[2] }}</td>

            <td width="120px">Dirección origen</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->origin_address }}</td>
        </tr>
        <tr>
            <td class="align-top">Ubigeo destino</td>
            <td>:</td>
            <td>{{ $document->detraction->delivery_location_id[2] }}</td>

            <td width="120px">Dirección destino</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->delivery_address }}</td>
        </tr>
        <tr>
            <td class="align-top" width="170px">Valor referencial servicio de transporte</td>
            <td>:</td>
            <td>{{ $document->detraction->reference_value_service }}</td>

            <td width="170px">Valor referencia carga efectiva</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->reference_value_effective_load }}</td>
        </tr>
        <tr>
            <td class="align-top">Valor referencial carga útil</td>
            <td>:</td>
            <td>{{ $document->detraction->reference_value_payload }}</td>

            <td width="120px">Detalle del viaje</td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->trip_detail }}</td>
        </tr>
        @endif

    </table>


    {{--@if ($document->retention)--}}
    {{-- <table class="full-width mt-3">--}}
    {{-- <tr>--}}
    {{-- <td colspan="3">--}}
    {{-- <strong>Información de la retención</strong>--}}
    {{-- </td>--}}
    {{-- </tr>--}}
    {{-- <tr>--}}
    {{-- <td width="120px">Base imponible</td>--}}
    {{-- <td width="8px">:</td>--}}
    {{-- <td>{{ $document->currency_type->symbol}} {{ $document->retention->base }}</td>--}}

    {{-- <td width="80px">Porcentaje</td>--}}
    {{-- <td width="8px">:</td>--}}
    {{-- <td>{{ $document->retention->percentage * 100 }}%</td>--}}
    {{-- </tr>--}}
    {{-- <tr>--}}
    {{-- <td width="120px">Monto</td>--}}
    {{-- <td width="8px">:</td>--}}
    {{-- <td>{{ $document->currency_type->symbol}} {{ $document->retention->amount }}</td>--}}
    {{-- </tr>--}}
    {{-- </table>--}}
    {{--@endif--}}


    @if ($document->isPointSystem())
    <table class="full-width mt-3">
        <tr>
            <td width="120px">P. acumulados</td>
            <td width="8px">:</td>
            <td>{{ $document->person->accumulated_points }}</td>

            <td width="140px">Puntos por la compra</td>
            <td width="8px">:</td>
            <td>{{ $document->getPointsBySale() }}</td>
        </tr>
    </table>
    @endif


    @if ($document->guides)
    <br />
    <table>
        @foreach($document->guides as $guide)
        <tr>
            @if(isset($guide->document_type_description))
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
    $origin_district_id = (array)$transport->origin_district_id;
    $destinatation_district_id = (array)$transport->destinatation_district_id;
    $origin_district = Modules\Order\Services\AddressFullService::getDescription($origin_district_id[2]);
    $destinatation_district = Modules\Order\Services\AddressFullService::getDescription($destinatation_district_id[2]);
    @endphp

    <table class="full-width mt-3">
        <tr>
            <td width="120px">{{ $transport->identity_document_type->description }}</td>
            <td width="8px">:</td>
            <td>{{ $transport->number_identity_document }}</td>
            <td width="120px">Nombre</td>
            <td width="8px">:</td>
            <td>{{ $transport->passenger_fullname }}</td>
        </tr>
        <tr>
            <td width="120px">N° asiento</td>
            <td width="8px">:</td>
            <td>{{ $transport->seat_number }}</td>
            <td width="120px">M. pasajero</td>
            <td width="8px">:</td>
            <td>{{ $transport->passenger_manifest }}</td>
        </tr>
        <tr>
            <td width="120px">F. inicio</td>
            <td width="8px">:</td>
            <td>{{ $transport->start_date }}</td>
            <td width="120px">H. inicio</td>
            <td width="8px">:</td>
            <td>{{ $transport->start_time }}</td>
        </tr>
        <tr>
            <td width="120px">U. origen</td>
            <td width="8px">:</td>
            <td>{{ $origin_district }}</td>
            <td width="120px">D. origne</td>
            <td width="8px">:</td>
            <td>{{ $transport->origin_address }}</td>
        </tr>
        <tr>
            <td width="120px">U. destino</td>
            <td width="8px">:</td>
            <td>{{ $destinatation_district }}</td>
            <td width="120px">D. destino</td>
            <td width="8px">:</td>
            <td>{{ $transport->destinatation_address }}</td>
        </tr>
    </table>
    @endif

    @if ($document->dispatch)
    <br />
    <strong>Guías de remisión</strong>
    <table>
        <tr>
            <td>{{ $document->dispatch->number_full }}</td>
        </tr>
    </table>

    @elseif ($document->reference_guides)
    @if (count($document->reference_guides) > 0)
    <br />
    <strong>Guías de remisión</strong>
    <table>
        @foreach($document->reference_guides as $guide)
        <tr>
            <td>{{ $guide->series }}</td>
            <td>-</td>
            <td>{{ $guide->number }}</td>
        </tr>
        @endforeach
    </table>
    @endif
    @endif


    <table class="full-width mt-3">
        @if ($document->prepayments)
        @foreach($document->prepayments as $p)
        <tr>
            <td width="120px">Anticipo</td>
            <td width="8px">:</td>
            <td>{{$p->number}}</td>
        </tr>
        @endforeach
        @endif
        @if ($document->purchase_order)
        <tr>
            <td width="120px">Orden de compra</td>
            <td width="8px">:</td>
            <td>{{ $document->purchase_order }}</td>
        </tr>
        @endif
        @if ($document->quotation_id)
        <tr>
            <td width="120px">Cotización</td>
            <td width="8px">:</td>
            <td>{{ $document->quotation->identifier }}</td>

            @isset($document->quotation->delivery_date)
            <td width="120px">F. entrega</td>
            <td width="8px">:</td>
            <td>{{ $document->date_of_issue->addDays($document->quotation->delivery_date)->format('d-m-Y') }}</td>
            @endisset
        </tr>

        @endif
        @isset($document->quotation->sale_opportunity)
        <tr>
            <td width="120px">O. Venta</td>
            <td width="8px">:</td>
            <td>{{ $document->quotation->sale_opportunity->number_full}}</td>
        </tr>
        @endisset
        @if(!is_null($document_base))
        <tr>
            <td width="120px">Doc. Afectado</td>
            <td width="8px">:</td>
            <td>{{ $affected_document_number }}</td>
        </tr>
        <tr>
            <td>Tipo de nota</td>
            <td>:</td>
            <td>{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>
        </tr>
        <tr>
            <td>Descripción</td>
            <td>:</td>
            <td>{{ $document_base->note_description }}</td>
        </tr>
        @endif
        @if($document->folio)
        <tr>
            <td>Folio</td>
            <td>:</td>
            <td>{{ $document->folio }}</td>
        </tr>
        @endif
    </table>

    {{--<table class="full-width mt-3">--}}
    {{--<tr>--}}
    {{--<td width="25%">Documento Afectado:</td>--}}
    {{--<td width="20%">{{ $document_base->affected_document->series }}-{{ $document_base->affected_document->number }}</td>--}}
    {{--<td width="15%">Tipo de nota:</td>--}}
    {{--<td width="40%">{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>--}}
    {{--</tr>--}}
    {{--<tr>--}}
    {{--<td class="align-top">Descripción:</td>--}}
    {{--<td class="text-left" colspan="3">{{ $document_base->note_description }}</td>--}}
    {{--</tr>--}}
    {{--</table>--}}

    <table class="full-width mt-10 mb-10">
        <thead class="">
            <tr class="bg-grey">
                <th class="border-top-bottom text-center py-2" width="8%">Cant.</th>
                <th class="border-top-bottom text-center py-2" width="8%">Unidad</th>
                <th class="border-top-bottom text-left py-2">Descripción</th>
                <th class="border-top-bottom text-right py-2" width="12%">V.Unit</th>
                <th class="border-top-bottom text-right py-2" width="8%">Dto.</th>
                <th class="border-top-bottom text-right py-2" width="12%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->items as $row)
            <tr>
                <td class="text-center align-top">
                    @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                    @else
                    {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                <td class="text-center align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                <td class="text-left align-top">
                    @if($row->name_product_pdf)
                    {!!$row->name_product_pdf!!}
                    @else
                    {!!$row->item->description!!}
                    @endif

                    @if($row->total_isc > 0)
                    <br /><span style="font-size: 9px">ISC : {{ $row->total_isc }} ({{ $row->percentage_isc }}%)</span>
                    @endif

                      

                    @if($row->total_plastic_bag_taxes > 0)
                    <br /><span style="font-size: 9px">ICBPER : {{ $row->total_plastic_bag_taxes }}</span>
                    @endif

                    @if($row->attributes)
                    @foreach($row->attributes as $attr)
                    <br /><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                    @endforeach
                    @endif
                    @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                    <br /><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                    @endforeach
                    @endif
                    @isset($row->item->sizes_selected)
                    @if (count($row->item->sizes_selected)>0)
                        @foreach ($row->item->sizes_selected as $size)
                        <small> Talla {{$size->size}} | {{$size->qty}} und</small> <br>
                        @endforeach
                    @endif
                    @endisset
                    @if($row->charges)
                    @foreach($row->charges as $charge)
                    <br /><span style="font-size: 9px">{{ $document->currency_type->symbol}} {{ $charge->amount}} ({{ $charge->factor * 100 }}%) {{$charge->description }}</span>
                    @endforeach
                    @endif

                    @if($row->item->is_set == 1)
                    <br>
                    @inject('itemSet', 'App\Services\ItemSetService')
                    @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                    {{$item}}<br>
                    @endforeach
                    @endif

                    @if($row->item->used_points_for_exchange ?? false)
                    <br>
                    <span style="font-size: 9px">*** Canjeado por {{$row->item->used_points_for_exchange}} puntos ***</span>
                    @endif

                    @if($document->has_prepayment)
                    <br>
                    *** Pago Anticipado ***
                    @endif
                </td>

                @if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf)
                <td class="text-right align-top">{{ $row->generalApplyNumberFormat($row->unit_price, $configuration_decimal_quantity->decimal_quantity_unit_price_pdf) }}</td>
                @else
                <td class="text-right align-top">{{ number_format($row->unit_value, 2) }}</td>
                @endif

                <td class="text-right align-top">
                    @if($row->discounts)
                    @php
                    $total_discount_line = 0;
                    foreach ($row->discounts as $disto) {
                    $total_discount_line = $total_discount_line + $disto->amount;
                    }
                    @endphp
                    {{ number_format($total_discount_line, 2) }}
                    @else
                    0
                    @endif
                </td>
                <td class="text-right align-top">{{ number_format($row->total_value, 2) }}</td>
            </tr>
            <tr>
                <td colspan="6" class="border-bottom"></td>
            </tr>
            @endforeach



            @if ($document->prepayments)
            @foreach($document->prepayments as $p)
            <tr>
                <td class="text-center align-top">1</td>
                <td class="text-center align-top">NIU</td>
                <td class="text-left align-top">
                    Anticipo: {{($p->document_type_id == '02')? 'Factura':'Boleta'}} Nro. {{$p->number}}
                </td>
                <td class="text-center align-top"></td>
                <td class="text-center align-top"></td>
                <td class="text-center align-top"></td>
                <td class="text-right align-top">-{{ number_format($p->total, 2) }}</td>
                <td class="text-right align-top">0</td>
                <td class="text-right align-top">-{{ number_format($p->total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="6" class="border-bottom"></td>
            </tr>
            @endforeach
            @endif

            @if($document->total_exportation > 0)
            <tr>
                <td colspan="5" class="text-right">Op. Exportación: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_exportation, 2) }}</td>
            </tr>
            @endif
            @if($document->total_free > 0)
            <tr>
                <td colspan="5" class="text-right">Op. gratuita: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_free, 2) }}</td>
            </tr>
            @endif
            @if($document->total_unaffected > 0)
            <tr>
                <td colspan="5" class="text-right">Op. Inafectas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_unaffected, 2) }}</td>
            </tr>
            @endif
            @if($document->total_exonerated > 0)
            <tr>
                <td colspan="5" class="text-right">Op. Exoneradas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_exonerated, 2) }}</td>
            </tr>
            @endif

            @if ($document->document_type_id === '07')
            @if($document->total_taxed >= 0)
            <tr>
                <td colspan="5" class="text-right">Op. Gravadas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
            @endif
            @elseif($document->total_taxed > 0)
            <tr>
                <td colspan="5" class="text-right">Op. Gravadas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
            @endif

            @if($document->total_plastic_bag_taxes > 0)
            <tr>
                <td colspan="5" class="text-right">Icbper: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="5" class="text-right">IGV: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_igv, 2) }}</td>
            </tr>

            @if($document->total_isc > 0)
            <tr>
                <td colspan="5" class="text-right">ISC: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_isc, 2) }}</td>
            </tr>
            @endif

            @if($document->total_discount > 0 && $document->subtotal > 0)
            <tr>
                <td colspan="5" class="text-right">Subtotal: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->subtotal, 2) }}</td>
            </tr>
            @endif

            @if($document->total_discount > 0)
            <tr>
                <td colspan="5" class="text-right">{{(($document->total_prepayment > 0) ? 'Anticipo':'Descuento TOTAL')}}
                    : {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
            @endif

            @if($document->total_charge > 0)
            @if($document->charges)
            @php
            $total_factor = 0;
            foreach($document->charges as $charge) {
            $total_factor = ($total_factor + $charge->factor) * 100;
            }
            @endphp
            <tr>
                <td colspan="5" class="text-right">Cargos ({{$total_factor}}
                    %): {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_charge, 2) }}</td>
            </tr>
            @else
            <tr>
                <td colspan="5" class="text-right">Cargos: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_charge, 2) }}</td>
            </tr>
            @endif
            @endif

            @if($document->perception)
            <tr>
                <td colspan="5" class="text-right">Importe total: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right">Percepción: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->perception->amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right">Total a pagar: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format(($document->total + $document->perception->amount), 2) }}</td>
            </tr>
            @elseif($document->retention)
            <tr>
                <td colspan="5" class="text-right font-bold" style="font-size: 16px;">Importe totalL: {{ $document->currency_type->symbol }}</td>
                <td class="text-right" style="font-size: 16px;">{{ number_format($document->total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right">Total retención ({{ $document->retention->percentage * 100 }}
                    %): {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->retention->amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right">Importe neto: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format(($document->total - $document->retention->amount), 2) }}</td>
            </tr>
            @else
            <tr>
                <td colspan="5" class="text-right font-bold">Total a pagar: {{ $document->currency_type->symbol }}</td>
                <td class="text-right  font-bold">{{ number_format($document->total, 2) }}</td>
            </tr>
            @endif

            @if(($document->retention || $document->detraction) && $document->total_pending_payment > 0)
            <tr>
                <td colspan="5" class="text-right">M. Pendiente: {{ $document->currency_type->symbol }}</td>
                <td class="text-right">{{ number_format($document->total_pending_payment, 2) }}</td>
            </tr>
            @endif

            @if($balance < 0) <tr>
                <td colspan="5" class="text-right">Vuelto: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format(abs($balance),2, ".", "") }}</td>
                </tr>
                @endif

        </tbody>
    </table>

    
<table class="full-width">
    @foreach(array_reverse((array) $document->legends) as $row)
        <tr>
            @if ($row->code == "1000")
                <td class="text-left pt-3" colspan="2">Son: <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></td>
                @if (count((array) $document->legends)>1)
                <tr><td class="text-left pt-3"><span class="font-bold">Leyendas</span></td></tr>
                @endif
            @else
                <td class="text-left pt-3" colspan="2">{{$row->code}}: {{ $row->value }}</td>
            @endif
            <br>
            <br>
        </tr>
    @endforeach
    <tr>
        <td class="text-center pt-1">
            <img class="" style="max-width: 100px" src="data:image/png;base64, {{ $document->qr }}" />          
        </td>

        <td>
            @if ($document->detraction)
                <p>Operación sujeta al Sistema de Pago de Obligaciones Tributarias</p>
            @endif
            @foreach($document->additional_information as $information)
                @if ($information)
                    @if ($loop->first)
                        <strong>Información adicional</strong>
                    @endif
                    <p class="text-left">{{ $information }}</p>
                @endif
            @endforeach
            @if(in_array($document->document_type->id,['01','03']))
                @foreach($accounts as $account)
                    <p class="text-left">

                            <span class="font-bold text-left">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                            <span class="font-bold text-left">N°:</span> {{$account->number}}
                            @if($account->cci)
                            <span class="font-bold text-left">CCI:</span> {{$account->cci}}
                            @endif

                    </p>
                @endforeach
            @endif


            
            <p class="text-left"><strong>Código hash:</strong> {{ $document->hash }}</p>

            @php
                $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
            @endphp
            {{-- Condicion de pago  Crédito / Contado --}}
            <p class="text-left">
                <strong>Condición de Pago: </strong>{{ $paymentCondition }} 
            </p>

            @if($document->payment_method_type_id)
                <p class="text-left">
                    <strong>Método de Pago: </strong>{{ $document->payment_method_type->description }}
                </p>
            @endif

            @if ($document->payment_condition_id === '01')

                @if($payments->count())
                        <p class="text-left">
                            <strong>Pagos:</strong>
                        </p>
                    @foreach($payments as $row)
                        <p>
                            <span class="text-left">&#8226; {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</span>
                        </p>
                    @endforeach
                @endif
            @else
                @foreach($document->fee as $key => $quote)
                    <p class="text-left">
                        <span class="text-left">&#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }} / Fecha: {{ $quote->date->format('d-m-Y') }} / Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</span>
                    </p>
                @endforeach
            @endif

            <p class="text-left"><strong>Vendedor:</strong> {{ $document->seller ? $document->seller->name : $document->user->name}}</p>

        </td>
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
        $establishment_data = \App\Models\Tenant\Establishment::find($document->establishment_id);
        @endphp

        <td class="text-center pt-1">
            @if ($configuration->yape_qr_documents && $establishment_data->yape_logo)
                @php
                    $yape_logo = $establishment_data->yape_logo;
                @endphp
                <td class="text-center">
                    <table>
                        <tr>
                            <td>
                                <strong>
                                    Yape
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
                                @if($establishment_data->yape_owner)
                                         {{ $establishment_data->yape_owner }}
                                @endif
                                @if($establishment_data->yape_number)
                                    <br>

                                         {{ $establishment_data->yape_number }}

                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            @endif         
        </td>

        <td class="text-center pt-1">

            @if ($configuration->plin_qr_documents && $establishment_data->plin_logo)
                @php
                    $plin_logo = $establishment_data->plin_logo;
                @endphp
                <td class="text-center">
                    <table>
                        <tr>
                            <td>
                                <strong>
                                    Plin
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
                                @if($establishment_data->plin_owner)
                                         {{ $establishment_data->plin_owner }}
                                @endif
                                @if($establishment_data->plin_number)
                                    <br>
                                         {{ $establishment_data->plin_number }}
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            @endif


        </td>
        
    </tr>
</table>
<table class="full-width">
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
        $establishment_data = \App\Models\Tenant\Establishment::find($document->establishment_id);
    @endphp
    
</table>
<table class="full-width">
    @if ($customer->department_id == 16)
        <tr>
            <td class="text-center text-left">
                Representación impresa del Comprobante de Pago Electrónico.
                <br/>Esta puede ser consultada en:
                <br/> <a href="{!! route('search.index', ['external_id' => $document->external_id]) !!}" style="text-decoration: none; font-weight: bold;color:black;">{!! searchUrl() !!}</a>
                <br/> "Bienes transferidos en la Amazonía
                <br/>para ser consumidos en la misma
            </td>
        </tr>
    @endif
    @if ($document->terms_condition)
        <tr>
            <td class="text-left">
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
    <tr>
        <td class="text-center pt-0">
            <img  style="max-height: 350px;" src="data:{{mime_content_type(public_path("{$footer_logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$footer_logo}")))}}" alt="{{$company->name}}">
        </td>
    </tr>
    @endif
    @if($company->footer_text_template)
    <tr>
        <td class="text-center desc pt-2">

            
            {!!func_str_find_url($company->footer_text_template)!!}
        </td>
    </tr>
    @endif

</table>



</body>
</html>
