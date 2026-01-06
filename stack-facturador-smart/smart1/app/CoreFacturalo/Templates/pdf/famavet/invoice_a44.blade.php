@php
    $establishment = $document->establishment;
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

    $payments = $document->payments;

    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();
    $cont_ = 0;

    //calculate items
    $allowed_items = 45 - (\App\Models\Tenant\BankAccount::all()->count())*3;
    $quantity_items = $document->items()->count();
    $cycle_items = $allowed_items - ($quantity_items * 3);
    $total_weight = 0;

@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
@if($document->state_type->id == '11'  || $document->state_type->id == '09')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img src="{{ public_path("status_images".DIRECTORY_SEPARATOR."anulado.png") }}" alt="anulado" class=""
             style="opacity: 0.6;">
    </div>
@endif


<div class="header">
    <div class="text-left float-left header-logo">

        @if ($establishment->logo ?? false)
            <div class=" text-left">
                <img src="{{ public_path($establishment->logo) }}" alt="{{$company->name}}" class="company_logo_rec" style="width:100%; max-height: 150px;">
            </div>
        @else
            @if($company->logo)
                <div class=" text-left">
                    <img src="{{ public_path("storage/uploads/logos/{$company->logo}") }}" alt="{{$company->name}}" class="company_logo_rec" style="width:100%; max-height: 150px">
                </div>
            @else
                <img src="{{ asset('logo/logo.jpg') }}" class="company_logo_rec" style="width: 100%; max-height: 150px">
            @endif
        @endif
    </div>
    <div class="text-center float-left header-company">

        <div class="font-xlg text-uppercase font-bold">{{ $company->name }}</div>
        @isset($establishment->trade_address)
            <h6>{{ ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : '' }}</h6>
        @endisset
        <div class="text-uppercase mayus">
        
        </div>
        <div class="text-center">
            <div style="text-transform: capitalize;">

                {{ ($establishment->address !== '-')? $establishment->address.',' : '' }}
                {{ ($establishment->district_id !== '-')? $establishment->district->description : '' }}
                {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}<br> 
            </div>           
                {{ ($establishment->telephone !== '-')? 'Telf. '.$establishment->telephone : '' }}<br>
                {{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}<br>
                {{ ($establishment->web_address !== '-')? ''.$establishment->web_address : '' }}
        </div>
        @isset($establishment->aditional_information)
            <div><strong> E-mail:</strong>  {{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</div>
        @endisset


    </div>
    <div  class="text-center float-left header-number_rec py-3 font-bold font-xlg">
        <div style="margin-top: 8px">RUC {{$company->number }}</div>

        <div style="margin-top: 10px">{{ $document->document_type->description }}</div>

        <div style="margin-top: 10px">Nº {{ $document_number }}</div>
    </div>
</div>


<div style="height: 5px;"></div>
<div class="information mt-10 no_pad_mar">
    <div class="div-table no_pad_mar">
            <div class="div-table-col w-100 div-table" style="margin: 0px; padding: 0px">
                 <div class=" border_redondo align-center" style="height:80px">

                    <div class="div-table-col w-14 font-bold font-xs margin_b_8 desc">FECHA DE EMISIÓN</div>
                    <div  class="div-table-col w-54 font-xs margin_b_8 align-center desc">: {{$document->date_of_issue->format('Y-m-d')}}</div>
                    <div class="div-table-col w-15 font-bold font-xs margin_b_8 desc">FECHA DE VCTO.</div>
                    <div  class="div-table-col w-15 font-xs margin_b_8 align-center desc">: @if($invoice) {{$invoice->date_of_due->format('Y-m-d')}} @endif</div>

                    <div class="div-table-col w-14 font-bold font-xs margin_b_8 desc">CLIENTE</div>
                    <div  class="div-table-col w-54 font-xs margin_b_8 align-center desc">: {{ $customer->name }}</div>
                    <div class="div-table-col w-15 font-bold font-xs margin_b_8 desc">GUÍA DE REMISIÓN</div>
                    <div  class="div-table-col w-15 font-xs margin_b_8 align-center desc">: 
                        @if ($document->guides)
                            @foreach($document->guides as $guide)
                                <span>{{ $guide->number }}</span>
                            @endforeach
                        @endif

                        @if (sizeof($document->reference_guides))
                            @foreach($document->reference_guides as $guide)
                                {{ $guide->series.'-'. $guide->number }}
                            @endforeach
                         @endif
                          &nbsp;
                    </div>
                    
                    <div class="div-table-col w-14 font-bold font-xs margin_b_8 desc">DIRECCIÓN</div>
                    <div  class="div-table-col w-54 font-xs margin_b_8 align-center desc">: 
                        @if ($customer->address !== '')
                            {{ $customer->address }}
                        @endif
                    </div>
                    <div class="div-table-col w-15 font-bold font-xs margin_b_8 desc">ORDEN DE COMPRA</div>
                    <div  class="div-table-col w-15 font-xs margin_b_8 align-center desc">: @if ($document->purchase_order) {{ $document->purchase_order }} @endif &nbsp;</div>

                    <div class="div-table-col w-14 font-bold font-xs margin_b_8 desc">&nbsp;</div>
                    <div class="div-table-col w-54 font-xs margin_b_8 desc">
                        @if ($customer->address !== '')
                            {{ ($customer->district_id !== '-')? ''.$customer->district->description : '' }}
                            {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                            {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
                        @endif
                    </div>
                    <div class="div-table-col w-15 font-bold font-xs margin_b_8 desc">MONEDA</div>
                    <div  class="div-table-col w-15 font-xs margin_b_8 align-center desc">: {{ $document->currency_type->description }}</div>
                    
                    <div class="div-table-col w-14 font-bold font-xs margin_b_8 desc">{{ $customer->identity_document_type->description }}</div>
                    <div  class="div-table-col w-20 font-xs margin_b_8 align-center desc">: {{ $customer->number }}</div>
                    <div class="div-table-col w-11 font-bold font-xs margin_b_8 desc">VENDEDOR</div>
                    <div  class="div-table-col w-23 font-xs margin_b_8 align-center desc">: @if ($document->seller) {{ $document->seller->name }} @else {{ $document->user->name }} @endif</div>
                    <div class="div-table-col w-15 font-bold font-xs margin_b_8 desc">COND. PAGO</div>
                    <div  class="div-table-col w-15 font-xs margin_b_8 align-center desc">: 
                        @php
                            if($document->payment_condition_id === '01') {
                                $paymentCondition = \App\Models\Tenant\PaymentMethodType::where('id', '10')->first();
                            }else{
                                $paymentCondition = \App\Models\Tenant\PaymentMethodType::where('id', '09')->first();
                            }
                        @endphp
                       {{ $paymentCondition->description }} 
                    </div>

                 </div>
            </div>
    </div>
</div>
<div class="information mt-10 no_pad_mar">

    <table class="full-width mt-10 mb-10" >
        <thead class="">
        <tr class="">
            <th class="border-box desc text-center py-2" width="6%">ITEM</th>
            <th class="border-box desc text-center py-2" width="6%">CANT.</th>
            <th class="border-box desc text-center py-2" width="6%">U.M</th>
            <th class="border-box desc text-center py-2"width="44%">DESCRIPCIÓN</th>
            <th class="border-box desc text-center py-2" width="10%">PRECIO <br> UNITARIO</th>
            <th class="border-box desc text-center py-2" width="10%">VALOR <br> UNITARIO</th>
            <th class="border-box desc text-center py-2" width="8%">DSCTO</th>
            <th class="border-box desc text-center py-2" width="10%">VALOR <br> VENTA</th>
        </tr>
        </thead>
        <tbody>
        @foreach($document->items as $row)
            @php
                $cont_++;
                // dd($row);
            @endphp
            <tr>
                <td class="desc border-left-right text-center align-top">{{$cont_}}</td>
                <td class="desc border-left-right text-center align-top">
                    @if(((int)$row->quantity != $row->quantity))
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                <td class="desc border-left-right text-center align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                <td class="desc border-left-right text-left align-top">
                    @if($row->name_product_pdf)
                        {!!$row->name_product_pdf!!}
                    @else
                        {!!$row->item->description!!}
                    @endif

                    @if($row->total_isc > 0)
                        <br/><span style="font-size: 9px">ISC : {{ $row->total_isc }} ({{ $row->percentage_isc }}%)</span>
                    @endif

                      

                    @if($row->total_plastic_bag_taxes > 0)
                        <br/><span style="font-size: 9px">ICBPER : {{ $row->total_plastic_bag_taxes }}</span>
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

                    @if($row->charges)
                        @foreach($row->charges as $charge)
                            <br/><span style="font-size: 9px">{{ $document->currency_type->symbol}} {{ $charge->amount}} ({{ $charge->factor * 100 }}%) {{$charge->description }}</span>
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
                        <span style="font-size: 9px">*** Canjeado por {{$row->item->used_points_for_exchange}}  puntos ***</span>
                    @endif

                    @if($document->has_prepayment)
                        <br>
                        *** Pago Anticipado ***
                    @endif
                </td>
                <td class="desc border-left-right text-center align-top">{{ number_format($row->unit_price, 2) }}</td>
                <td class="desc border-left-right text-center align-top">{{ number_format($row->unit_value, 2) }}</td>
                <td class="desc border-left-right text-center align-top">
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
                <td class="desc border-left-right text-center align-top">{{ number_format($row->total, 2) }}</td>
            </tr>
        @endforeach

        @for($i = 0; $i < $cycle_items; $i++)
            <tr>
                <td class="desc border-left-right text-right align-top">&nbsp;</td>
                <td class="desc border-left-right text-right align-top">&nbsp;</td>
                <td class="desc border-left-right text-right align-top">&nbsp;</td>
                <td class="desc border-left-right text-right align-top">&nbsp;</td>
                <td class="desc border-left-right text-right align-top">&nbsp;</td>
                <td class="desc border-left-right text-right align-top">&nbsp;</td>
                <td class="desc border-left-right text-right align-top">&nbsp;</td>
                <td class="desc border-left-right text-right align-top">&nbsp;</td>
            </tr>
        @endfor
            <tr>
                <td class="desc border-bottom border-left-right text-right align-top"></td>
                <td class="desc border-bottom border-left-right text-right align-top"></td>
                <td class="desc border-bottom border-left-right text-right align-top"></td>
                <td class="desc border-bottom border-left-right text-right align-top"></td>
                <td class="desc border-bottom border-left-right text-right align-top"></td>
                <td class="desc border-bottom border-left-right text-right align-top"></td>
                <td class="desc border-bottom border-left-right text-right align-top"></td>
                <td class="desc border-bottom border-left-right text-right align-top"></td>
            </tr>

            <tr>
                <td class="desc text-left align-top" colspan="9" style="padding-top: 3px;">
                @foreach(array_reverse( (array) $document->legends) as $row)
                    @if ($row->code == "1000")
                        <p style="text-transform: uppercase;">Son: <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></p>
                        @if (count((array) $document->legends)>1)
                            <p><span class="font-bold">Leyendas</span></p>
                        @endif
                    @else
                        <p> {{$row->code}}: {{ $row->value }} </p>
                    @endif

                @endforeach
                </td>
            </tr>


        </tbody>
    </table>
</div>
<div style="height: 5px;"></div>
<div class="information mt-10 no_pad_mar">
    <div class="div-table no_pad_mar">
         <div class="div-table-row no_pad_mar">
            <div class="div-table-col w-60  text-center no_pad_mar">

                <div class="div-table-col w-100 text-left no_pad_mar desc font-bold no_pad_mar"> {{ $document->hash }} </div>
                <div class="div-table-col w-30 text-left no_pad_mar desc font-bold no_pad_mar"> <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px; width: 130px;" /> </div>
                <div class="div-table-col w-49 text-left no_pad_mar desc no_pad_mar"> 

                    @if(in_array($document->document_type->id,['01','03']))

                                        <div class="font-bold">Cuentas Bancarias:</div>
                        @foreach($accounts as $account)
                            <div style="width: 40%; float: left" class="font-bold">{{$account->bank->description}} {{$account->currency_type->description}}</div>
                            <div style="width: 60%; float: left">{{$account->number}}
                                @if($account->cci)
                                    <br><strong>CCI:</strong> {{$account->cci}}
                                @endif
                            </div>
                        @endforeach
                    @endif

                @php
                    $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
                @endphp
                {{-- Condicion de pago  Crédito / Contado --}}

            
                @if ($document->payment_method_type_id)
                    <table class="full-width">
                        <tr>
                            <td>
                                <strong>Método de pago: </strong>{{ $document->payment_method_type->description }}
                            </td>
                        </tr>
                    </table>
                @endif
            
                @if ($document->payment_condition_id === '01')
                    @if ($payments->count())
                        <table class="full-width">
                            <tr>
                                <td><strong>Pagos:</strong></td>
                            </tr>
                            @php $payment = 0; @endphp
                            @foreach ($payments as $row)
                                <tr >
                                    <td>&#8226; {{ $row->payment_method_type->description }}
                                        - {{ $row->reference ? $row->reference . ' - ' : '' }}
                                        {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
                                </tr>
                            @endforeach
                            </tr>
                        </table>
                    @endif
                @else
                    <table class="full-width">
                        @foreach ($document->fee as $key => $quote)
                            <tr>
                                <td class="desc">
                                    &#8226;
                                    {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                                    / Fecha: {{ $quote->date->format('d-m-Y') }} /
                                    Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
                            </tr>
                        @endforeach
                        </tr>
                    </table>
                @endif



                </div>

            </div>
            <div class="div-table-col w-39-2 text-center no_pad_mar border_redondo">

                <div class="div-table-col w-45 text-left no_pad_mar border-box_right desc font-bold" style="padding-left: 4px; padding-top: 2px; padding-bottom: 2px;">Total Ope. Gravadas </div>
                <div class="div-table-col w-10 text-center no_pad_mar border-box_right desc font-bold" style=" padding-top: 2px; padding-bottom: 2px;"> {{ $document->currency_type->symbol }} </div>
                <div class="div-table-col w-40 text-right no_pad_mar border-box_bottom desc font-bold" style="padding-right: 6px;  padding-top: 2px; padding-bottom: 2px;">{{ number_format($document->total_taxed, 2) }} </div>

                <div class="div-table-col w-45 text-left no_pad_mar border-box_right desc font-bold" style="padding-left: 4px; padding-top: 2px; padding-bottom: 2px;">Total Ope. Inafectas </div>
                <div class="div-table-col w-10 text-center no_pad_mar border-box_right desc font-bold" style=" padding-top: 2px; padding-bottom: 2px;"> {{ $document->currency_type->symbol }} </div>
                <div class="div-table-col w-40 text-right no_pad_mar border-box_bottom desc font-bold" style="padding-right: 6px; padding-top: 2px; padding-bottom: 2px;">{{ number_format($document->total_unaffected, 2) }} </div>

                <div class="div-table-col w-45 text-left no_pad_mar border-box_right desc font-bold" style="padding-left: 4px; padding-top: 2px; padding-bottom: 2px;">Total Ope. Exoneradas </div>
                <div class="div-table-col w-10 text-center no_pad_mar border-box_right desc font-bold" style=" padding-top: 2px; padding-bottom: 2px;"> {{ $document->currency_type->symbol }} </div>
                <div class="div-table-col w-40 text-right no_pad_mar border-box_bottom desc font-bold" style="padding-right: 6px; padding-top: 2px; padding-bottom: 2px;">{{ number_format($document->total_exonerated, 2) }} </div>

                <div class="div-table-col w-45 text-left no_pad_mar border-box_right desc font-bold" style="padding-left: 4px; padding-top: 2px; padding-bottom: 2px;">Total Descuentos </div>
                <div class="div-table-col w-10 text-center no_pad_mar border-box_right desc font-bold" style=" padding-top: 2px; padding-bottom: 2px;"> {{ $document->currency_type->symbol }} </div>
                <div class="div-table-col w-40 text-right no_pad_mar border-box_bottom desc font-bold" style="padding-right: 6px; padding-top: 2px; padding-bottom: 2px;">
                    @if($document->total_discount > 0)
                        {{ number_format($document->total_discount, 2) }}
                    @else
                        0.00
                    @endif
                </div>

                <div class="div-table-col w-45 text-left no_pad_mar border-box_right desc font-bold" style="padding-left: 4px; padding-top: 2px; padding-bottom: 2px;">Total Ope. Gratuitas </div>
                <div class="div-table-col w-10 text-center no_pad_mar border-box_right desc font-bold" style=" padding-top: 2px; padding-bottom: 2px;"> {{ $document->currency_type->symbol }} </div>
                <div class="div-table-col w-40 text-right no_pad_mar border-box_bottom desc font-bold" style="padding-right: 6px; padding-top: 2px; padding-bottom: 2px;">{{ number_format($document->total_free, 2) }} </div>

                <div class="div-table-col w-45 text-left no_pad_mar border-box_right desc font-bold" style="padding-left: 4px; padding-top: 2px; padding-bottom: 2px;">Total IGV 18% </div>
                <div class="div-table-col w-10 text-center no_pad_mar border-box_right desc font-bold" style=" padding-top: 2px; padding-bottom: 2px;"> {{ $document->currency_type->symbol }} </div>
                <div class="div-table-col w-40 text-right no_pad_mar border-box_bottom desc font-bold" style="padding-right: 6px; padding-top: 2px; padding-bottom: 2px;">{{ number_format($document->total_igv, 2) }} </div>

                <div class="div-table-col w-45 text-left no_pad_mar border-box_right2 desc font-bold" style="padding-left: 4px; padding-bottom: 5px; padding-top: 5px;">IMPORTE TOTAL</div>
                <div class="div-table-col w-10 text-center no_pad_mar border-box_right2 desc font-bold" style="padding-bottom: 5px; padding-top: 5px;"> {{ $document->currency_type->symbol }} </div>
                <div class="div-table-col w-40 text-right no_pad_mar border-box_bottoms desc font-bold" style="padding-right: 6px; padding-bottom: 5px; padding-top: 5px;">@if ($document->retention) {{ number_format($document->total-$document->retention->amount, 2) }} @else {{ number_format($document->total, 2) }} @endif</div>


            </div>

            <div class="div-table-col w-100 text-center mt-2 mb-2">
                Representación impresa del Comprobante de Pago Electrónico. Esta puede ser consultada en: {!! url('/buscar') !!}
            </div>

         </div>
    </div>
</div>

</body>
</html>
