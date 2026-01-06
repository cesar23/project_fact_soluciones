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

<table class="full-width m-10">
    <tr>
        <td class="text-center desc desc " style="text-transform:uppercase;">{{ $company->name }}</td>
    </tr>
    <tr>
        <td class="text-center desc">{{ 'RUC: '.$company->number }}</td>
     
    </tr>
    <tr>
        <td class="text-center desc" style="text-transform: uppercase;">
            {{ ($establishment->address !== '-')? $establishment->address : '' }} <br>
            {{ ($establishment->district_id !== '-')? ' '.$establishment->district->description : '' }}
            {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
            {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
        </td>
    </tr>

    @isset($establishment->trade_address)
    <tr>
        <td class="text-center desc ">{{  ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : ''  }}</td>
    </tr>
    @endisset
    <tr>
        <td class="text-center desc ">{{ ($establishment->telephone !== '-')? 'Cel: '.$establishment->telephone : '' }}</td>
    </tr>

    <tr>
        <td class="text-center desc pt-3">{{ $document->document_type->description }}</></td>
    </tr>
    <tr>
        <td class="text-center desc">{{ $document_number }}</td>
    </tr>
</table>
<table class="full-width m-10">
    <tr >
        <td width="" class="pt-3"><p class="desc">FECHA EMISION</p></td>
        <td width="" class="pt-3"><p class="desc">: {{ $document->date_of_issue->format('d/m/Y') }}</p></td>
    </tr>


    <tr>
        <td class="align-top"><p class="desc">DNI O RUC</p></td>
        <td><p class="desc">: {{ $customer->number }}</p></td>
    </tr>
    <tr>
        <td class="align-top"><p class="desc">CLIENTE</p></td>
        <td><p class="desc">: {{ $customer->name }}</p></td>
    </tr>

    @if ($customer->address !== '')
        <tr>
            <td class="align-top"><p class="desc">DIRECCION</p></td>
            <td>
                <p class="desc">
                    : {{ $customer->address }}
                    {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                    {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                    {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
                </p>
            </td>
        </tr>
    @endif

    <tr>
        <td class="align-top"><p class="desc">EMAIL</p></td>
        <td><p class="desc">: {{ $customer->email }}</p></td>
    </tr>

    <tr>
        <td class="align-top"><p class="desc">TELEFONO</p></td>
        <td><p class="desc">: {{ $customer->telephone }}</p></td>
    </tr>


    @if ($document->reference_data)
        <tr>
            <td class="align-top"><p class="desc">D. Referencia:</p></td>
            <td>
                <p class="desc">
                    {{ $document->reference_data }}
                </p>
            </td>
        </tr>
    @endif

    @if ($document->detraction)
        <tr>
            <td  class="align-top"><p class="desc">N. Cta detracciones:</p></td>
            <td><p class="desc">{{ $document->detraction->bank_account}}</p></td>
        </tr>
        <tr>
            <td  class="align-top"><p class="desc">B/S Sujeto a detracción:</p></td>
            @inject('detractionType', 'App\Services\DetractionTypeService')
            <td><p class="desc">{{$document->detraction->detraction_type_id}} - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id ) }}</p></td>
        </tr>
        <tr>
            <td  class="align-top"><p class="desc">Método de Pago:</p></td>
            <td><p class="desc">{{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id ) }}</p></td>
        </tr>
        <tr>
            <td  class="align-top"><p class="desc">Porcentaje detracción:</p></td>
            <td><p class="desc">{{ $document->detraction->percentage}}%</p></td>
        </tr>
        <tr>
            <td  class="align-top"><p class="desc">Monto detracción:</p></td>
            <td><p class="desc">S/ {{ $document->detraction->amount}}</p></td>
        </tr>
        @if($document->detraction->pay_constancy)
        <tr>
            <td  class="align-top"><p class="desc">Constancia de Pago:</p></td>
            <td><p class="desc">{{ $document->detraction->pay_constancy}}</p></td>
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

    @endif


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
<table class="full-width m-10">
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


<table class="full-width m-10">
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
<table class="m-10">>
    <tr>
        <td colspan="3">
            <strong >Guias de remisión</strong>

        </td>
    </tr>
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
<table class="full-width m-10">
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



<table class="full-width m-10" autosize="1">
    <thead class="">

        

    <tr>
        <th class="border-top-bottom desc text-left">CODIGO</th>
        <th colspan="3" class="border-top-bottom desc text-center desc">DESCRIPCION</th>
     
    </tr>
    <tr>
        <th class="border-top-bottom desc text-left"></th>
        <th class="border-top-bottom desc text-left">Cantidad</th>
        <th class="border-top-bottom desc text-right">PRECIO</th>
        <th class="border-top-bottom desc text-right">TOTAL</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        <tr>
            <td class="desc text-left align-top">
                {{ $row->relation_item->internal_id }}

            </td>

            <td colspan="3" style="" class="desc text-left align-top" 
            >
                @if($row->name_product_pdf)
                @php
                    $name_product_pdf = $row->name_product_pdf;
                    //remove <p></p> and <br/>
                    $name_product_pdf = str_replace(['<p>', '</p>', '<br/>'], '', $name_product_pdf);
                    //cortar  el texto en 15 caracteres
                    $name_product_pdf = substr($name_product_pdf, 0, 30);
                @endphp
                    {!!$name_product_pdf!!}
                @else
                @php

                    $description = $row->item->description;
                    //cortar  el texto en 15 caracteres
                    $description = substr($description, 0, 30);
                    @endphp
                    {!!$description!!}
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

         
        </tr>
        <tr>
            <td></td>
            <td style="" class="desc text-left align-top">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 3) }}
                @endif
            </td>

            <td style="" class="desc text-right align-top">{{ number_format($row->unit_price, 2) }}</td>
            <td style="" class="desc text-right align-top">{{ number_format($row->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="4" class=""></td>
        </tr>
    @endforeach



       




    </tbody>

    
</table>
<table class="full-width m-10">
    <tbody>
        <tr>
            <td  class="border-top text-right desc">NUMERO DE ItemS </td>
            <td colspan="2" class="border-top text-right desc"></td>
            <td class="border-top text-right desc">
                {{ $document->items->count() }}
            </td>
        </tr> 

        <tr>
            
            <td  class="text-right desc">SUBTOTAL</td>
            <td colspan="2" class="text-right desc">{{ $document->currency_type->symbol }}</td>
            <td class="text-right desc">{{ number_format($document->subtotal, 2) }}</td>
        </tr>


        <tr>
            <td class="text-right desc">DESCUENTOS </td>
            <td colspan="2" class="text-right desc">{{ $document->currency_type->symbol }}</td>
            <td class="text-right desc">{{ number_format($document->total_discount, 2) }}</td>
        </tr>
        <tr>
            <td  class="text-right desc">OP. GRAVADAS </td>
            <td colspan="2" class="text-right desc">{{ $document->currency_type->symbol }}</td>
            <td class="text-right desc">{{ number_format($document->total_taxed, 2) }}</td>
        </tr>

    
        <tr>
            <td class="text-right desc">OP. NO GRAVADAS </td>
            <td colspan="2" class="text-right desc">{{ $document->currency_type->symbol }}</td>
            <td class="text-right desc">{{ number_format($document->total_exonerated, 2) }}</td>
        </tr>





    @if($document->total_plastic_bag_taxes > 0)
        <tr>
            <td colspan="3" class="text-right desc">Icbper: {{ $document->currency_type->symbol }}</td>
            <td class="text-right desc">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
        </tr>
    @endif
    <tr>
        <td class="text-right desc">IGV</td>
        <td colspan="2" class="text-right desc">{{ $document->currency_type->symbol }}</td>
        <td class="text-right desc">{{ number_format($document->total_igv, 2) }}</td>
    </tr>

    @if($document->total_isc > 0)
    <tr>
        <td colspan="3" class="text-right desc">ISC: {{ $document->currency_type->symbol }}</td>
        <td class="text-right desc">{{ number_format($document->total_isc, 2) }}</td>
    </tr>
    @endif

    <tr>
        <td  class="text-right desc">IMPORTE TOTAL</td>
        <td colspan="2" class="text-right desc"> {{ $document->currency_type->symbol }}</td>
        <td class="text-right desc">{{ number_format($document->total, 2) }}</td>
    </tr>

    <tr>
        <td colspan="4" class="text-left desc">SON:
{{strtoupper($document->number_to_letter)}}</td>

        </td>
       
        
    </tr>

    </tbody>
</table>
<table class="full-width text-center desc">
    <tr>
        <td width="" ><p class="text-center desc desc">Hora de emisión: {{ $document->time_of_issue }}</p></td>
    </tr>


    <tr>
        <td class="text-center desc desc pt-1">
            Este documento es una representación <br> impresa de su {{ $document->document_type->description }}
            <br/>Consulte su comprobante en la siguiente
            <br/> dirección: {!! searchUrl() !!}
        </td>
    </tr>

</table>

</body>
</html>
