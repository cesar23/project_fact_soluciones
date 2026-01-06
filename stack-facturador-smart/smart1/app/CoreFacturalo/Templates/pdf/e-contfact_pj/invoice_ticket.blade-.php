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
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
    $document_base = ($document->note) ? $document->note : null;
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
    if($document_base) {
        $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    } else {
        $affected_document_number = null;
    }
    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');

@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body class="ticket">

@if($company->logo)
    <div class="text-center pt-1">
        <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class=" contain">
    </div>
{{--@else--}}
    {{--<div class="text-center company_logo_box pt-5">--}}
        {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo_ticket contain">--}}
    {{--</div>--}}
@endif

@if($document->state_type->id == '11')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:500px">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
@endif
<table class="full-width">
    <tr>
        <td class="text-center"><h4><strong>{{ $company->name }}</h4></strong></td>
    </tr>
    {{--<tr>
        <td class="text-center"><h5><strong>{{ $company->trade_name }}</h5></strong></td>
    </tr>--}}
    <tr>
        <td class="text-center"><h5><strong>{{ 'R.U.C. '.$company->number }}</h5></strong></td>
    </tr>
    <tr>
        <td class="text-center" style="text-transform: uppercase;">
            {{ ($establishment->address !== '-')? $establishment->address : '' }}
            {{ ($establishment->district_id !== '-')? '- '.$establishment->district->description : '' }}
            {{ ($establishment->province_id !== '-')? '- '.$establishment->province->description : '' }}
            {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
        </td>
    </tr>

    @isset($establishment->trade_address)
    <tr>
        <td class="text-center ">{{  ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : ''  }}</td>
    </tr>
    @endisset
    <tr>
        <td class="text-center ">{{ ($establishment->telephone !== '-')? 'Contactos: '.$establishment->telephone : '' }}</td>
    </tr>
    <tr>
        <td class="text-center">{{ ($establishment->email !== '-')? 'E-mail: '.$establishment->email : '' }}</td>
    </tr>
    @isset($establishment->web_address)
        <tr>
            <td class="text-center">{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</td>
        </tr>
    @endisset

    @isset($establishment->aditional_information)
        <tr>
            <td class="text-center pb-3">{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</td>
        </tr>
    @endisset

    <tr>
        <td class="text-center pt-3 border-top3"><h4><strong>{{ $document->document_type->description }}</h4></strong></td>
    </tr>
    <tr>
        <td class="text-center pb-3 border-bottom3"><h3><strong>{{ $document_number }}</h3></strong></td>
    </tr>
</table>
<table class="full-width">
    <tr >
        <td width="" class=" font-xs"><p class="desc font-bold">F. EMISIÓN:</p></td>
        <td width="80" class=" font-xs"><p class="desc">{{ $document->date_of_issue->format('Y-m-d') }}</p></td>
        <td width="" ><p class="desc font-xs font-bold">H. EMISIÓN:</p></td>
        <td width="" ><p class="desc font-xs">{{ $document->time_of_issue }}</p></td>
    </tr>
    <tr>
    </tr>
    @isset($invoice->date_of_due)
    <tr>
        <td><p class="desc font-bold">F. DE VENC:</p></td>
        <td width="80"><p class="desc">{{ $invoice->date_of_due->format('Y-m-d') }}</p></td>
        <td width="" ><p class="desc font-xs font-bold">Moneda:</p></td>
        <td width="" ><p class="desc font-xs">{{ $document->currency_type->description }}</p></td>
    </tr>
    @endisset

    <tr>
        <td class="align-top font-bold"><p class="desc">Cliente:</p></td>
        <td colspan="3"><p class="desc"><strong>{{ $customer->name }}</p></td>
    </tr>
    <tr>
        <td><p class="desc font-bold">{{ $customer->identity_document_type->description }}:</p></td>
        <td colspan="3"><p class="desc"><strong>{{ $customer->number }}</p></td>
    </tr>
    @if ($customer->address !== '')
        <tr>
            <td class="align-top font-bold"><p class="desc">Dirección:</p></td>
            <td colspan="3">
                <p class="desc">
                    <strong>{{ strtoupper($customer->address) }}
                    <strong>{{ ($customer->district_id !== '-')? '- '.strtoupper($customer->district->description) : '' }}
                    <strong>{{ ($customer->province_id !== '-')? '- '.strtoupper($customer->province->description) : '' }}
                    <strong>{{ ($customer->department_id !== '-')? '- '.strtoupper($customer->department->description) : '' }}
                </p>
            </td>
        </tr>
    @endif
    @if ($document->detraction)
        <tr>
            <td width="" class="font-xs font-bold">Observación:</td>
            <td colspan="3" width="" class="font-xs">Operación Sujeta a Detracción 
                @inject('detractionType', 'App\Services\DetractionTypeService')
                {{$document->detraction->detraction_type_id}} - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id ) }} {{ $document->detraction->percentage}}% - <strong>Banco de la Nación Cta. Detracción N° {{ $document->detraction->bank_account}}</strong> -  Importe <strong> S/ {{ $document->detraction->amount}}</strong>
            </td>
        </tr>
    @endif
    @if ($document->reference_data)
        <tr>
            <td class="align-top"><p class="desc">D. Referencia:</p></td>
            <td colspan="3">
                <p class="desc">
                    {{ $document->reference_data }}
                </p>
            </td>
        </tr>
    @endif

   {{--  @if ($document->detraction)
        <tr>
            <td  class="align-top"><p class="desc">N. Cta detracciones:</p></td>
            <td colspan="3"><p class="desc">{{ $document->detraction->bank_account}}</p></td>
        </tr>
        <tr>
            <td  class="align-top"><p class="desc">B/S Sujeto a detracción:</p></td>
            @inject('detractionType', 'App\Services\DetractionTypeService')
            <td colspan="3"><p class="desc">{{$document->detraction->detraction_type_id}} - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id ) }}</p></td>
        </tr>
        <tr>
            <td  class="align-top"><p class="desc">Método de Pago:</p></td>
            <td colspan="3"><p class="desc">{{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id ) }}</p></td>
        </tr>
        <tr>
            <td  class="align-top"><p class="desc">Porcentaje detracción:</p></td>
            <td colspan="3"><p class="desc">{{ $document->detraction->percentage}}%</p></td>
        </tr>
        <tr>
            <td  class="align-top"><p class="desc">Monto detracción:</p></td>
            <td colspan="3"><p class="desc">S/ {{ $document->detraction->amount}}</p></td>
        </tr>
        @if($document->detraction->pay_constancy)
        <tr>
            <td  class="align-top"><p class="desc">Constancia de Pago:</p></td>
            <td colspan="3"><p class="desc">{{ $document->detraction->pay_constancy}}</p></td>
        </tr>
        @endif


        @if($invoice->operation_type_id == '1004')
        <tr class="mt-2">
            <td colspan="2"></td>
        </tr>
        <tr class="mt-2">
            <td colspan="2">Detalle - Servicios de transporte de carga</td>
        </tr>
        <tr>
            <td class="align-top"><p class="desc">Ubigeo origen:</p></td>
            <td><p class="desc">{{ $document->detraction->origin_location_id[2] }}</p></td>
        </tr>
        <tr>
            <td  class="align-top"><p class="desc">Dirección origen:</td>
            <td><p class="desc">{{ $document->detraction->origin_address }}</td>
        </tr>
        <tr>
            <td class="align-top"><p class="desc">Ubigeo destino:</p></td>
            <td><p class="desc">{{ $document->detraction->delivery_location_id[2] }}</p></td>
        </tr>
        <tr>

            <td  class="align-top"><p class="desc">Dirección destino:</p></td>
            <td><p class="desc">{{ $document->detraction->delivery_address }}</p></td>
        </tr>
        <tr>
            <td class="align-top"><p class="desc">Valor referencial servicio de transporte:</p></td>
            <td><p class="desc">{{ $document->detraction->reference_value_service }}</p></td>
        </tr>
        <tr>

            <td  class="align-top"><p class="desc">Valor referencia carga efectiva:</p></td>
            <td><p class="desc">{{ $document->detraction->reference_value_effective_load }}</p></td>
        </tr>
        <tr>
            <td class="align-top"><p class="desc">Valor referencial carga útil:</p></td>
            <td><p class="desc">{{ $document->detraction->reference_value_payload }}</p></td>
        </tr>
        <tr>
            <td  class="align-top"><p class="desc">Detalle del viaje:</p></td>
            <td><p class="desc">{{ $document->detraction->trip_detail }}</p></td>
        </tr>
        @endif

    @endif --}}


    @if ($document->retention)
        <br>
        <tr>
            <td colspan="2">
                <p class="desc"><strong>Información de la retención</strong></p>
            </td>
        </tr>
        <tr>
            <td><p class="desc">Base imponible: </p></td>
            <td><p class="desc">{{ $document->currency_type->symbol}} {{ $document->retention->base }} </p></td>
        </tr>
        <tr>
            <td><p class="desc">Porcentaje:</p></td>
            <td><p class="desc">{{ $document->retention->percentage * 100 }}%</p></td>
        </tr>
        <tr>
            <td><p class="desc">Monto:</p></td>
            <td><p class="desc">{{ $document->currency_type->symbol}} {{ $document->retention->amount }}</p></td>
        </tr>
    @endif


    @if ($document->prepayments)
        @foreach($document->prepayments as $p)
        <tr>
            <td><p class="desc">Anticipo :</p></td>
            <td><p class="desc">{{$p->number}}</p></td>
        </tr>
        @endforeach
    @endif
    @if ($document->purchase_order)
        <tr>
            <td><p class="desc">Orden de compra:</p></td>
            <td><p class="desc">{{ $document->purchase_order }}</p></td>
        </tr>
    @endif
    @if ($document->quotation_id)
        <tr>
            <td><p class="desc">Cotización:</p></td>
            <td><p class="desc">{{ $document->quotation->identifier }}</p></td>
        </tr>
    @endif
    @isset($document->quotation->delivery_date)
        <tr>
            <td><p class="desc">F. Entrega</p></td>
            <td><p class="desc">{{ $document->date_of_issue->addDays($document->quotation->delivery_date)->format('d-m-Y') }}</p></td>
        </tr>
    @endisset
    @isset($document->quotation->sale_opportunity)
        <tr>
            <td><p class="desc">O. Venta</p></td>
            <td><p class="desc">{{ $document->quotation->sale_opportunity->number_full}}</p></td>
        </tr>
    @endisset
</table>

@if ($document->guides)
{{--<strong>Guías:</strong>--}}
<table>
    @foreach($document->guides as $guide)
        <tr>
            @if(isset($guide->document_type_description))
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
    $origin_district_id = (array)$transport->origin_district_id;
    $destinatation_district_id = (array)$transport->destinatation_district_id;
    $origin_district = Modules\Order\Services\AddressFullService::getDescription($origin_district_id[2]);
    $destinatation_district = Modules\Order\Services\AddressFullService::getDescription($destinatation_district_id[2]);
@endphp


<table class="full-width mt-3">
    <tr>
        <td><p class="desc">{{ $transport->identity_document_type->description }}:</p></td>
        <td><p class="desc">{{ $transport->number_identity_document }}</p></td>
    </tr>
    <tr>
        <td><p class="desc">Nombre:</p></td>
        <td><p class="desc">{{ $transport->passenger_fullname }}</p></td>
    </tr>


    <tr>
        <td><p class="desc">N° Asiento:</p></td>
        <td><p class="desc">{{ $transport->seat_number }}</p></td>
    </tr>
    <tr>
        <td><p class="desc">M. Pasajero:</p></td>
        <td><p class="desc">{{ $transport->passenger_manifest }}</p></td>
    </tr>

    <tr>
        <td><p class="desc">F. Inicio:</p></td>
        <td><p class="desc">{{ $transport->start_date }}</p></td>
    </tr>
    <tr>
        <td><p class="desc">H. Inicio:</p></td>
        <td><p class="desc">{{ $transport->start_time }}</p></td>
    </tr>


    <tr>
        <td><p class="desc">U. Origen:</p></td>
        <td><p class="desc">{{ $origin_district }}</p></td>
    </tr>
    <tr>
        <td><p class="desc">D. Origen:</p></td>
        <td><p class="desc">{{ $transport->origin_address }}</p></td>
    </tr>

    <tr>
        <td><p class="desc">U. Destino:</p></td>
        <td><p class="desc">{{ $destinatation_district }}</p></td>
    </tr>
    <tr>
        <td><p class="desc">D. Destino:</p></td>
        <td><p class="desc">{{ $transport->destinatation_address }}</p></td>
    </tr>

</table>
@endif


@if (count($document->reference_guides) > 0)
<br/>
<strong>Guias de remisión</strong>
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

@if(!is_null($document_base))
<table>
    <tr>
        <td class="desc">Documento Afectado:</td>
        <td class="desc">{{ $affected_document_number }}</td>
    </tr>
    <tr>
        <td class="desc">Tipo de nota:</td>
        <td class="desc">{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>
    </tr>
    <tr>
        <td class="align-top desc">Descripción:</td>
        <td class="text-left desc">{{ $document_base->note_description }}</td>
    </tr>
</table>
@endif

<table class="full-width mt-10 mb-10 border-box">
    <thead class="">
    <tr>
        <th class="border-top-bottom desc-9 text-left">Cant.</th>
        <th class="border-top-bottom desc-9 text-left">U. M.</th>
        <th class="border-top-bottom desc-9 text-left">Descripción</th>
        <th class="border-top-bottom desc-9 text-left">P.U.</th>
        <th class="border-top-bottom desc-9 text-left">Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        <tr>
            <td class="text-center desc-9 align-top font-bold">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center desc-9 align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
            <td class="text-left desc-9 align-top font-bold">
                @if($row->name_product_pdf)
                    {!!$row->name_product_pdf!!}
                @else
                    {!!$row->item->description!!}
                @endif

                @if($row->total_isc > 0)
                    <br/>ISC : {{ $row->total_isc }} ({{ $row->percentage_isc }}%)
                @endif

                  

                @foreach($row->additional_information as $information)
                    @if ($information)
                        <br/>{{ $information }}
                    @endif
                @endforeach

                @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/>{!! $attr->description !!} : {{ $attr->value }}
                    @endforeach
                @endif
                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><small>{{ $dtos->factor * 100 }}% {{$dtos->description }}</small>
                    @endforeach
                @endif

                @if($row->charges)
                    @foreach($row->charges as $charge)
                        <br/><small>{{ $document->currency_type->symbol}} {{ $charge->amount}} ({{ $charge->factor * 100 }}%) {{$charge->description }}</small>
                    @endforeach
                @endif
                
                @if($row->item->is_set == 1)

                 <br>
                 @inject('itemSet', 'App\Services\ItemSetService')
                 @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                     {{$item}}<br>
                 @endforeach
                 {{-- {{join( "-", $itemSet->getItemsSet($row->item_id) )}} --}}
                @endif

                @if($document->has_prepayment)
                    <br>
                    *** Pago Anticipado ***
                @endif
            </td>
            <td class="text-right desc-9 align-top">{{ number_format($row->unit_price, 2) }}</td>
            <td class="text-right desc-9 align-top font-bold">{{ number_format($row->total, 2) }}</td>
        </tr>
    @endforeach
        <tr>
            <td colspan="5" class="border-bottom"></td>
        </tr>

    @if ($document->prepayments)
        @foreach($document->prepayments as $p)
        <tr>
            <td class="text-center desc-9 align-top">
                1
            </td>
            <td class="text-center desc-9 align-top">NIU</td>
            <td class="text-left desc-9 align-top">
                Anticipo: {{($p->document_type_id == '02')? 'Factura':'Boleta'}} Nro. {{$p->number}}
            </td>
            <td class="text-right  desc-9 align-top">-{{ number_format($p->total, 2) }}</td>
            <td class="text-right  desc-9 align-top">-{{ number_format($p->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="5" class="border-bottom"></td>
        </tr>
        @endforeach
    @endif


            @if($document->total_taxed >= 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc border-top-bottom_dashed">TOTAL OP. GRAVADA: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc border-top-bottom_dashed">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
            @endif

        @if($document->total_plastic_bag_taxes > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc border-top-bottom_dashed">Icbper: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc border-top-bottom_dashed">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="4" class="text-right font-bold desc border-top-bottom_dashed">I.G.V. (18%): {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc border-top-bottom_dashed">{{ number_format($document->total_igv, 2) }}</td>
        </tr>

        @if($document->total_isc > 0)
        <tr>
            <td colspan="4" class="text-right font-bold desc border-top-bottom_dashed">I.S.C.: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc border-top-bottom_dashed">{{ number_format($document->total_isc, 2) }}</td>
        </tr>
        @endif


            <tr>
                <td colspan="4" class="text-right font-bold desc border-top-bottom_dashed">Descuento: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc border-top-bottom_dashed">{{ number_format($document->total_discount, 2) }}</td>
            </tr>

        @if($document->total_charge > 0)
            @if($document->charges)
                @php
                    $total_factor = 0;
                    foreach($document->charges as $charge) {
                        $total_factor = ($total_factor + $charge->factor) * 100;
                    }
                @endphp
                <tr>
                    <td colspan="4" class="text-right font-bold desc border-top-bottom_dashed">CARGOS ({{$total_factor}}%): {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold desc border-top-bottom_dashed">{{ number_format($document->total_charge, 2) }}</td>
                </tr>
            @else
                <tr>
                    <td colspan="4" class="text-right font-bold desc border-top-bottom_dashed">CARGOS: {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold desc border-top-bottom_dashed">{{ number_format($document->total_charge, 2) }}</td>
                </tr>
            @endif
        @endif

        <tr>
            <td colspan="4" class="text-right font-bold desc border-top-bottom_dashed">Total a pagar: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc border-top-bottom_dashed">{{ number_format($document->total, 2) }}</td>
        </tr>
        
        @if(($document->retention || $document->detraction) && $document->total_pending_payment > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc border-top-bottom_dashed">M. Pendiente: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc border-top-bottom_dashed">{{ number_format($document->total_pending_payment, 2) }}</td>
            </tr>
        @endif
        
        @if($balance < 0)
           <tr>
               <td colspan="4" class="text-right font-bold desc border-top-bottom_dashed">Vuelto: {{ $document->currency_type->symbol }}</td>
               <td class="text-right font-bold desc border-top-bottom_dashed">{{ number_format(abs($balance),2, ".", "") }}</td>
           </tr>
        @endif
    </tbody>
</table>
<table class="full-width">
    <tr>

        @foreach(array_reverse((array) $document->legends) as $row)
            <tr>
                @if ($row->code == "1000")
                    <td class="desc pt-0"><strong>SÓN: </strong><span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></td>
                    @if (count((array) $document->legends)>1)
                    <tr><td class="desc pt-3"><span class="font-bold">Leyenda</span></td></tr>
                    @endif
                @else
                    <td class="desc pt-0">{{$row->code}}: {{ $row->value }}</td>
                @endif
            </tr>
        @endforeach
    </tr>


    {{-- @if ($document->detraction)
        <tr>
            <td class="desc pt-2 font-bold">
                Operación sujeta al Sistema de Pago de Obligaciones Tributarias
            </td>
        </tr>
    @endif --}}

   {{-- <tr>
        <td class="desc pt-3">
            @foreach($document->additional_information as $information)
                @if ($information)
                    @if ($loop->first)
                        <strong>Información adicional</strong>
                    @endif
                    <p class="desc">{{ $information }}</p>
                @endif
            @endforeach
            <br>
            @if(in_array($document->document_type->id,['01','03']))
                @foreach($accounts as $account)
                    <p class="desc">
                        <small>
                            <span class="font-bold desc">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                            <span class="font-bold desc">N°:</span> {{$account->number}}
                            @if($account->cci)
                            <span class="font-bold desc">CCI:</span> {{$account->cci}}
                            @endif
                        </small>
                    </p>
                @endforeach
            @endif
        </td>
    </tr> --}}
    <tr>
        <td class="text-center pt-3"><img class="qr_code" src="data:image/png;base64, {{ $document->qr }}" /></td>
    </tr>
    <tr>
        <td class="text-center desc"> <strong>Código Hash: {{ $document->hash }}</td> </strong>
    </tr>


    <tr>
        <td class="desc pt-2 border-bottom_dashed">
            <strong>FORMA DE Pago: @foreach($payments as $row) {{ $row->payment_method_type->description }} @endforeach </strong>
        </td>
    </tr>
   
    @php
        $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);

    @endphp

    {{-- Condicion de pago  Crédito / Contado --}}
    <tr>
        <td class="desc pt-1 border-bottom_dashed">
            <strong>Condición de Pago: {{ $paymentCondition }} </strong>
        </td>
    </tr>

    @if($document->payment_method_type_id)
        <tr>
            <td class="desc pt-5">
                <strong>Método de Pago: </strong>{{ $document->payment_method_type->description }}
            </td>
        </tr>
    @endif

    @if ($document->payment_condition_id === '01')

        @if($payments->count())
            <tr>
                <td class="desc pt-3 border-bottom_dashed">
                    <strong>Pagos efectuados:</strong>
                </td>
            </tr>
            @foreach($payments as $row)
                <tr>
                    <td class="desc border-bottom_dashed">&#8226; {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
                </tr>
            @endforeach
        @endif
    @else
        @foreach($document->fee as $key => $quote)
            <tr>
                <td class="desc">&#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota # '.( $key + 1) : $quote->getStringPaymentMethodType()) }} / Fecha Vcto: {{ $quote->date->format('d-m-Y') }} / Importe Cuota: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
            </tr>
        @endforeach
            <tr>
                <td class="desc border-bottom_dashed"> </td>
            </tr>

    @endif

    <tr>
        <td class="desc">
            <strong>Vendedor:</strong>
        </td>
    </tr>
    <tr>
        @if ($document->seller)
            <td class="desc">{{ $document->seller->name }}</td>
        @else
            <td class="desc">{{ $document->user->name }}</td>
        @endif
    </tr>

    @if ($document->terms_condition)
        <tr>
            <td class="desc">
                <br>
                <h6 style="font-size: 10px; font-weight: bold;">Términos y condiciones del servicio</h6>
                {!! $document->terms_condition !!}
            </td>
        </tr>
    @endif

    </tr>

    <tr>
        <td class="text-center desc pt-2">Representación Impresa del <strong>COMPROBANTE ELECTRONICO</strong>, Generado desde el Sistema del Contribuyente, para visualizar el documento visita: <strong> {!! url('/buscar') !!} </strong></td>
    </tr>
</table>

</body>
</html>
