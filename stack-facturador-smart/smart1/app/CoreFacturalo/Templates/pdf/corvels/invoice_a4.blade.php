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
    $customer = $document->customer;
    $invoice = $document->invoice;
    $document_base = ($document->note) ? $document->note : null;

    $path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
    $template_data = \App\Models\Tenant\Establishment::where('code', '=', $establishment->code)->first();
// dd($template_data->template_pdf); 
    if($document_base) {

        $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    } else {

        $affected_document_number = null;
    }

    $payments = $document->payments;

    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');
// dd(app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'bgbox3.png'));
@endphp
<html>
<head>
</head>
<body>
@if($document->state_type->id == '11'  || $document->state_type->id == '09')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img src="{{ public_path("status_images".DIRECTORY_SEPARATOR."anulado.png") }}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
@endif
<table class="full-width">
    <tr>
        <td width="70%" class="pl-3">
        @if ($establishment->logo ?? false)
            <div class="company_logo_box">
                <img src="{{ public_path($establishment->logo) }}" alt="{{$company->name}}" class="company_logo" style="max-width: 440px;"> 
            </div>
        @else
            @if($company->logo)
                    <div class="company_logo_box">
                        <img src="{{ public_path("storage/uploads/logos/{$company->logo}") }}" alt="{{$company->name}}" class="company_logo" style="max-width: 440px;">
                    </div>
            @else
                    <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 440px">
            @endif
        @endif


                

                @isset($establishment->aditional_information)
                    <h6>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</h6>
                @endisset
            </div>
        </td>
        <td width="30%" class="border-box py-4 px-2 text-center" style="background: url('{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'bgbox.png') }}'); background-repeat: no-repeat;">
            <h5 class="text-center font-xxlg font-bold">{{ 'RUC '.$company->number }}</h5>
            <h5 class="text-center font-xxlg font-bold">{{ $document->document_type->description }}</h5>
            <h3 class="text-center font-xxlg font-bold">{{ $document_number }}</h3>
        </td>
    </tr>
</table>

<table class="full-widths mt-3">
    <tr>
        <td width="70%" class="pl-2 pr-4 border-box2" style="background: url('{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'bgbox2.png') }}'); background-repeat: no-repeat;">
<div class="border-boxs font-bold" style="background: url('{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'bgbox2.png') }}'); background-repeat: no-repeat;">
    Datos del cliente
</div>

<table class="full-width py-4s px-s2 ext-center">
{{--     <tr>
        <td colspan="3" width="80px" class=" font-bold" style="vertical-align: top;">Datos del cliente</td>
    </tr> --}}
    <tr>
        <td style="vertical-align: top;" class="font-bold font-md font-xs">{{ $customer->identity_document_type->description }}</td>
        <td class="font-bold font-md font-xs" style="vertical-align: top;">:</td>
        <td style="vertical-align: top;" class=" font-xs">{{$customer->number}}</td>


    </tr>
    <tr>
        <td style="vertical-align: top;" class="font-bold font-xs">Cliente</td>
        <td style="vertical-align: top;" class="font-bold font-xs">:</td>
        <td style="vertical-align: top;" class=" font-xs">
            {{ $customer->name }}
            @if ($customer->internal_code ?? false)
             
            <small> {{ $customer->internal_code ?? '' }}</small>
            @endif







        </td>


    </tr>

    @if ($customer->address !== '')
    <tr>
        <td class="align-top font-bold font-xs">Dirección</td>
        <td class="font-bold font-xs">:</td>
        <td class=" font-xs" style="text-transform: uppercase;">
            {{ $customer->address }}
            {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
            {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
            {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
        </td>


    </tr>
    @endif
    

    @if ($document->detraction)
        @if($document->detraction->pay_constancy)
        <tr>
            <td colspan="3">
            </td>
            <td width="120px" class="font-bold">Constancia de pago</td>
            <td width="8px" class="font-bold">:</td>
            <td>{{ $document->detraction->pay_constancy}}</td>
        </tr>
        @endif
    @endif

    @if($document->detraction && $invoice->operation_type_id == '1004')
    <tr>
        <td colspan="4"><strong>Detalle - Servicios de transporte de carga</strong></td>
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

        </td>
        <td width="30%" class="border-box1 py-4 px-2 ext-center" style="background: url('{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'bgbox1.png') }}'); background-repeat: no-repeat;">



<table class="full-width ">

    <tr>
        <td width="120px" class="font-bold font-xs">Fecha de emisión</td>
        <td width="8px" class="font-bold font-xs">:</td>
        <td class=" font-xs">{{$document->date_of_issue->format('Y-m-d')}}</td>


    </tr>
    @if($invoice)
        <tr>
            <td class="font-bold font-xs">Fecha de vencim.</td>
            <td class="font-bold font-xs" width="8px">:</td>
            <td class=" font-xs">{{$invoice->date_of_due->format('Y-m-d')}}</td>
        </tr> 
    @endif




    @if ($document->reference_data)
        <tr>
            <td width="120px" class="font-bold">D. REFERENCIA</td>
            <td width="8px" class="font-bold">:</td>
            <td>{{ $document->reference_data}}</td>
        </tr>
    @endif

    @if ($document->detraction)
        @if($document->detraction->pay_constancy)
        <tr>
            <td colspan="3">
            </td>
            <td width="120px" class="font-bold">Constancia de pago</td>
            <td width="8px" class="font-bold">:</td>
            <td>{{ $document->detraction->pay_constancy}}</td>
        </tr>
        @endif
    @endif

    @if($document->detraction && $invoice->operation_type_id == '1004')
    <tr>
        <td colspan="4"><strong>Detalle - Servicios de transporte de carga</strong></td>
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

        </td>
    </tr>
</table>



@if ($document->guides)
<br/>
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

@if (sizeof($document->reference_guides_valid))
<br/>
<strong>Guias de remisión: 
    @foreach($document->reference_guides_valid as $guide)
        @if($guide === end($document->reference_guides_valid))
            |  {{ $guide->series.'-'. $guide->number }}
        @else
            {{ $guide->series.'-'. $guide->number }} 
        @endif
    @endforeach
</strong>

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
            <td width="120px">Orden de compra o servicio</td>
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
                    <td width="120px">F. ENTREGA</td>
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
</table>


<table class="full-width mt-10 mb-10 ">
    <thead class="border-tables">
        <tr class="bg-grey cabeceratabless">
            <th class="border-top-bottom2 text-center py-2" width="8%">Cant.</th>
            <th class="border-top-bottom2 text-center py-2" width="5%">Um</th>
            <th class="border-top-bottom2 text-center py-2" width="80">Cod</th>
            <th class="border-top-bottom2 text-center py-2" colspan="2">Descripción</th>
            <th class="border-top-bottom2 text-right py-2">V/U</th>
            {{-- <th class="border-top-bottom text-center py-2" width="8%">Lote</th> --}}
            {{-- <th class="border-top-bottom text-center py-2" width="8%">Serie</th> --}}
            <th class="border-top-bottom2 text-right py-2" width="12%">P.Unit</th>
            <th class="border-top-bottom2 text-right py-2" width="8%">Dto.</th>
            <th class="border-top-bottom2 text-right py-2" width="12%">Total</th>
        </tr>
    </thead>
    <tbody class="colores">
    @foreach($document->items as $row)
        <tr class="color-border">
            <td class="text-center align-top color-border">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center align-top color-border">{{ symbol_or_code($row->item->unit_type_id) }}</td>
            <td class="text-center align-top color-border">{{ $row->item->internal_id }}</td>
            <td class="text-left align-top color-border" colspan="2">
                @if($row->name_product_pdf)
                    {!!$row->item->description!!} {!!$row->name_product_pdf!!} 
                @else
                    {!!$row->item->description!!} {!!$row->name_product_pdf!!} 
                @endif

                  





                @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                    @endforeach
                @endif
                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                    @endforeach
                @endif

                @if($row->item->is_set == 1)
                <br>
                @inject('itemSet', 'App\Services\ItemSetService')
                    @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                        {{$item}}<br>
                    @endforeach
                @endif

                @if($document->has_prepayment)
                    <br>
                    *** Pago Anticipado ***
                @endif
            </td>
            <td class="text-right align-top color-border">{{ number_format($row->unit_value, 2) }}</td>
            {{-- <td class="text-center align-top color-border">
                @inject('itemLotGroup', 'App\Services\ItemLotsGroupService')
                {{ $itemLotGroup->getLote($row->item->IdLoteSelected) }}
            </td>
            <td class="text-center align-top color-border">

                @isset($row->item->lots)
                    @foreach($row->item->lots as $lot)
                        @if( isset($lot->has_sale) && $lot->has_sale)
                            <span style="font-size: 9px">{{ $lot->series }}</span><br>
                        @endif
                    @endforeach
                @endisset

            </td> --}}
            <td class="text-right align-top color-border">{{ number_format($row->unit_price, 2) }}</td>
            <td class="text-right align-top color-border">
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
            <td class="text-right align-top color-border">{{ number_format($row->total, 2) }}</td>
        </tr>
{{--         <tr>
            <td colspan="9" class="border-bottom"></td>
        </tr> --}}
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
            <td colspan="9" class="border-bottom"></td>
        </tr>
        @endforeach
    @endif


    </tbody>
</table>
<table class="full-width">
        @if($document->total_exportation > 0)
            <tr>
                <td colspan="8" class="text-right font-bold">Op. Exportación: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
            </tr>
        @endif
        @if($document->total_free > 0)
            <tr>
                <td colspan="8" class="text-right font-bold">Op. Gratuitas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
            </tr>
        @endif
        @if($document->total_unaffected > 0)
            <tr>
                <td colspan="8" class="text-right font-bold">Op. Inafectas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
            </tr>
        @endif
        @if($document->total_exonerated > 0)
            <tr class="bg-white">
                <td colspan="8" class="text-right font-bold bg-white">Op. Exoneradas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold bg-white">{{ number_format($document->total_exonerated, 2) }}</td>
            </tr>
        @endif
        @if($document->total_taxed > 0)
            <tr>
                <td colspan="8" class="text-right font-bold">Op. Gravadas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif
         @if($document->total_discount > 0)
            <tr>
                <td colspan="8" class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'Anticipo':'Descuento TOTAL')}}: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        @endif
        @if($document->total_plastic_bag_taxes > 0)
            <tr>
                <td colspan="8" class="text-right font-bold">Icbper: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="8" class="text-right font-bold">IGV 18%: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
        </tr>

        @if($document->perception)
            <tr>
                <td colspan="8" class="text-right font-bold"> Importe total: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="8" class="text-right font-bold">Percepción: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->perception->amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="8" class="text-right font-bold">Total a pagar: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format(($document->total + $document->perception->amount), 2) }}</td>
            </tr>
        @else
            <tr>
                <td colspan="8" class="text-right font-bold">Total a pagar: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
            </tr>
        @endif

        @if($balance < 0)

            <tr>
                <td colspan="8" class="text-right font-bold">Vuelto: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format(abs($balance),2, ".", "") }}</td>
            </tr>

        @endif
</table>

<div class="border-box-number">
            @foreach(array_reverse( (array) $document->legends) as $row)
                @if ($row->code == "1000")
                    <span style="text-transform: uppercase;">Son: <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></span>
                    @if (count((array) $document->legends)>1)
                        <span><span class="font-bold">Leyendas</span></span>
                    @endif
                @else
                    <span> {{ $row->value }} </span>
                @endif


            @endforeach

            @if ($document->detraction)
            @inject('detractionType', 'App\Services\DetractionTypeService')
                <span style=""><br><span class="font-bold">{{ $document->detraction->detraction_type_id }} - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id) }}</span></span>               </span></span>
                <span style="">Porcentaje <span class="font-bold">{{ $document->detraction->percentage}}%</span></span>
                <span style="">Nro. de cuenta <span class="font-bold">{{ $document->detraction->bank_account}}</span></span>
                <span style="">Monto <span class="font-bold">S/ {{ $document->detraction->amount}}</span></span>
            @endif
</div>

<div style="margin-top: 10px"></div>
<div class="border-box-number-1">

{{-- datos de pagos --}}
{{-- @if($document->payment_method_type_id)
    <strong>Pago: </strong>{{ $document->payment_method_type->description }}
@endif --}}
@php
    if($document->payment_condition_id === '01') {
        $paymentCondition = "Contado";
    }else{
        $paymentCondition = "Crédito";
    }
@endphp

<strong>Condición de Pago: {{ $paymentCondition }} </strong>

| <span class="font-xs"><strong>Vendedor:</strong> 
        @if ($document->seller)
            {{ $document->seller->name }}
        @else
            {{ $document->user->name }}
        @endif
    </span>
    <br>
@if ($document->payment_condition_id === '01')
    @if($payments->count())
    {{-- <span class="font-xs"><strong>Pagos: </strong></span> --}}
               @php $payment = 0; @endphp
                @foreach($payments as $row)
                 <span class="font-xs">  &#8226; {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }} </span>
                @endforeach
            <br>
    @endif
@else
    @php
        $paymentMethod = \App\Models\Tenant\PaymentMethodType::where('id', '09')->first();
    @endphp
    {{-- <span class="font-xs"><strong>Pagos: {{ $paymentMethod->description }}</strong></span> --}}
            @foreach($document->fee as $key => $quote)
               <span class="font-xs"> &#8226;  Cuota #{{ $key + 1 }} / Fecha: {{ $quote->date->format('d-m-Y') }} / Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }} </span>
            @endforeach
            <br>
        {{-- </tr>
    </table> --}}
@endif



<span style="font-size: 9px"><strong>Código Hash: </strong>{{ $document->hash }} <br></span>
            {{-- <br/> --}}
            @if ($document->detraction)


            @endif
            @foreach($document->additional_information as $information)
                @if ($information)
                    @if ($loop->first)
                        <strong>Información adicional</strong>
                    @endif
                    <span>{{ $information }}</span>
                @endif
            @endforeach
            <br>
            @if(in_array($document->document_type->id,['01','03']))
                @foreach($accounts as $account)
                    {{-- <p> --}}<br>
                    <span class="font-bold">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                    <span class="font-bold"> | N°:</span> {{$account->number}}
                    @if($account->cci)
                    <span class="font-bold"> | CCI:</span> {{$account->cci}}
                    @endif
                    {{-- </p> --}}
                @endforeach
            @endif

@if ($document->terms_condition)
    {{-- <br> --}}
    <h6 class="font-md font-bold">Términos y condiciones del servicio</h6>
    <span class="font-xs<">{!! $document->terms_condition !!}</span>
@endif

                <br/><br/><br/>
                <div class="font-xs text-center">
                    <center>
                        Representación impresa del Comprobante de Pago Electrónico. <br/>Esta puede ser consultada en: <a href="{!! route('search.index', ['external_id' => $document->external_id]) !!}" style="text-decoration: none; font-weight: bold;color:black;">{!! searchUrl() !!}</a>
            @if ($customer->department_id == 16)
                        <br/> "Bienes transferidos en la Amazonía para ser consumidos en la misma".
            @endif
                    </center>
                </div>
                <br/>

{{-- <span class="text-center">Representación impresa del Comprobante de Pago Electrónico. <br/>Esta puede ser consultada en {!! searchUrl() !!}</span> --}}
</div>
<div class="border-box-number-2">

    <img src="data:image/png;base64, {{ $document->qr }}" />

</div>
<div style="clear: both;"></div>


</body>
</html>
