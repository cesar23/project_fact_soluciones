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

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();

    if($document_base) {

        $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

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
    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();
    $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);

    //calculate items
    $allowed_items = 24;
    $quantity_items = $document->items()->count();
    $cycle_items = $allowed_items - ($quantity_items);
    $total_weight = 0;

@endphp
<html>
<head>
</head>
<body>
@if($document->state_type->id == '11')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img
            src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}"
            alt="anulado" class="" style="opacity: 0.6;">
    </div>
@endif


<div class="header">
    <div class="float-left header-logo_rec text-center">

        @if ($establishment->logo ?? false)
            <div class=" text-center">
                <img src="{{ public_path($establishment->logo) }}" alt="{{$company->name}}" class="" style="width:100%; max-height: 100px;">
            </div>
        @else
            @if($company->logo)
                <div class=" text-center">
                    <img src="{{ public_path("storage/uploads/logos/{$company->logo}") }}" alt="{{$company->name}}" class="" style="width:100%; max-height: 100px">
                </div>
            @else
                <img src="{{ asset('logo/logo.jpg') }}" class="company_logo_rec" style="width: 100%; max-height: 100px">
            @endif
        @endif

            {{-- <div class="font-xlg text-uppercase font-bold">{{ $company->name }}</div> --}}
            <div class="text-uppercase mayus">
                {{ ($establishment->address !== '-')? $establishment->address.'' : '' }}
            </div>
            <div class="text-uppercase mayus">
                {{ ($establishment->district_id !== '-')? ' '.$establishment->district->description : '' }}
                {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
            </div>
            <div>
                {{ ($establishment->telephone !== '-')? ''.$establishment->telephone : '' }}
            </div>
            <div class="text-center">
                {{ ($establishment->web_address !== '-')? ''.$establishment->web_address : '' }}
            </div>
            @isset($establishment->aditional_information)
                <div>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</div>
            @endisset


    </div>
    <div  class="text-center float-left header-number_rec py-3 font-boldg font-lg">
        <div style="margin-top: 8px">RUC {{$company->number }}</div>

        <div style="margin-top: 10px">{{ $document->document_type->description }}</div>

        <div style="margin-top: 10px">Nº {{ $document_number }}</div>
    </div>
</div>



<div style="height: 5px;"></div>
<div class="information mt-10 no_pad_mar">
    <div class="div-table no_pad_mar">
         <div class="div-table-row no_pad_mar">
            <div class="border_redondo">
                <div class="div-table-col w-11 font-bold font-xs margin_b_8 mayus">SEÑOR(ES):</div>
                <div  class="div-table-col w-58 font-xs margin_b_8 mayus">{{ $customer->name }}</div>

                <div class="div-table-col w-10 font-bold font-xs margin_b_8 mayus">{{ $customer->identity_document_type->description }}:</div>
                <div  class="div-table-col w-20 font-xs margin_b_8 mayus">{{ $customer->number }}</div>

                <div class="div-table-col w-11 font-bold font-xs  mayus">DIRECCIÓN:</div>
                <div  class="div-table-col w-58 font-xs  mayus"> {{ $customer->address }} &nbsp;</div>

                <div class="div-table-col w-10 font-bold font-xs margin_b_8 mayus">{{$document->quotations_optional}}</div>
                <div  class="div-table-col w-20 font-xs margin_b_8 mayus"> {{$document->quotations_optional_value}}</div>

                
            </div>
         </div>
    </div>
</div>



<div style="height: 5px;"></div>
<div class="information mt-10 no_pad_mar">
    <div class="div-table no_pad_mar">
         <div class="div-table-row no_pad_mar">
            <div class="div-table-col w-100 border_redondo2 text-center no_pad_mar">

                <div class="div-table-col w-16 no_pad_mar border-box_right">
                    <div class="mayus font-xxs font-bold padding_t_3 border-bottom">FECHA EMISIÓN</div>
                    <div class="mayus font-xxs padding_t_b_3">{{$document->date_of_issue->format('Y-m-d')}}</div>
                </div>
                <div class="div-table-col w-17 no_pad_mar border-box_right">
                    <div class="mayus font-xxs font-bold padding_t_3 border-bottom">CONDICIÓN DE PAGO</div>
                    <div class="mayus font-xxs padding_t_b_3">{{ $paymentCondition }} &nbsp;</div>
                </div>
                <div class="div-table-col w-17 no_pad_mar border-box_right">
                    <div class="mayus font-xxs font-bold padding_t_3 border-bottom">FECHA VENCIMIENTO</div>
                    <div class="mayus font-xxs padding_t_b_3">@if($invoice) {{$invoice->date_of_due->format('Y-m-d')}} @endif</div>
                </div>
                <div class="div-table-col w-16 no_pad_mar border-box_right">
                    <div class="mayus font-xxs font-bold padding_t_3 border-bottom">Nº DE ORDEN</div>
                    <div class="mayus font-xxs padding_t_b_3">@if ($document->purchase_order) {{ $document->purchase_order }} @endif &nbsp;</div>
                </div>
                <div class="div-table-col w-16 no_pad_mar border-box_right">
                    <div class="mayus font-xxs font-bold padding_t_3 border-bottom">Nº DE PEDIDO</div>
                    <div class="mayus font-xxs padding_t_b_3">&nbsp;
                        @if ($document->reference_order_form_id)
                            @if($document->order_form)
                                {{ ($document->order_form) ? $document->order_form->number_full : "" }}
                            @endif
                        @elseif ($document->order_form_external)
                            {{ $document->order_form_external }}
                        @endif
                    </div>
                </div>
                <div class="div-table-col w-17 no_pad_mar">
                    <div class="mayus font-xxs font-bold padding_t_3 border-bottom">Nº GUIA DE REMISIÓN</div>
                    <div class="mayus font-xxs padding_t_b_3">
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
                    </div>
                </div>

            </div>
            </div>
         </div>
    </div>
</div>

<div style="height: 5px;"></div>
<div class="information mt-10 no_pad_mar">
    <div class="div-table no_pad_mar">
         <div class="div-table-row border_redondo2 text-center no_pad_mar">

            <div class="div-table-col w-10 no_pad_mar border-box_right">
                <div class="mayus font-xxs font-bold padding_t_3">CÓDIGO DE <br> ARTÍCULO</div>
            </div>
            <div class="div-table-col w-10 no_pad_mar border-box_right">
                <div class="mayus font-xxs font-bold padding_t_3" style="padding-top: 8.5px; padding-bottom: 8.5px">Cantidad</div>
            </div>
            <div class="div-table-col w-50 no_pad_mar border-box_right">
                <div class="mayus font-xxs font-bold padding_t_3" style="padding-top: 8.5px; padding-bottom: 8.5px">DESCRIPCIÓN</div>
            </div>
            <div class="div-table-col w-9 no_pad_mar border-box_right">
                <div class="mayus font-xxs font-bold padding_t_3">UNIDAD <br> DE VENTA</div>
            </div>
            <div class="div-table-col w-10 no_pad_mar border-box_right">
                <div class="mayus font-xxs font-bold padding_t_3">VALOR <br> UNITARIO</div>
            </div>
            <div class="div-table-col w-10 no_pad_mar">
                <div class="mayus font-xxs font-bold padding_t_3">VALOR DE<br> VENTA</div>
            </div>

         </div>
    </div>
</div>

<div class="information mt-10 no_pad_mar" style="padding-top: -10px">
    <div class="div-table no_pad_mar">
         <div class="div-table-row border_redondo4 text-center no_pad_mar">
            @foreach($document->items as $row)
                <div class="div-table-col w-10 no_pad_mar border-box_right">
                    <div class="mayus font-xxs padding_t_3 text-center">{{ $row->item->internal_id }} &nbsp;</div>
                </div>
                <div class="div-table-col w-10 no_pad_mar border-box_right">
                    <div class="mayus font-xxs padding_t_3 text-center">
                        @if(((int)$row->quantity != $row->quantity))
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </div>
                </div>
                <div class="div-table-col w-50 no_pad_mar border-box_right">
                    <div class="mayus font-xxs padding_t_3 text-left">
                        @if($row->name_product_pdf)
                            {!!strip_tags($row->name_product_pdf)!!}
                        @else
                            {!!strip_tags($row->item->description)!!}
                        @endif

                        @if($row->total_isc > 0)
                            <br/><span style="font-size: 9px">ISC : {{ $row->total_isc }} ({{ $row->percentage_isc }}%)</span>
                        @endif

                        @if (!empty($row->item->presentation)) {!!$row->item->presentation->description!!} @endif

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
                            <span
                                style="font-size: 9px">*** Canjeado por {{$row->item->used_points_for_exchange}}  puntos ***</span>
                        @endif

                        @if($document->has_prepayment)
                            <br>
                            *** Pago Anticipado ***
                        @endif
                    </div>
                </div>
                <div class="div-table-col w-9 no_pad_mar border-box_right">
                    <div class="mayus font-xxs padding_t_3 text-center">{{ symbol_or_code($row->item->unit_type_id) }}</div>
                </div>
                <div class="div-table-col w-10 no_pad_mar border-box_right">
                    <div class="mayus font-xxs padding_t_3 text-right">{{ number_format($row->unit_price, 2) }}</div>
                </div>
                <div class="div-table-col w-10 no_pad_mar">
                    <div class="mayus font-xxs font-bold padding_t_3 text-right">{{ number_format($row->total, 2) }}</div>
                </div>
            @endforeach

            @for($i = 0; $i < $cycle_items; $i++)
                <div class="div-table-col w-10 no_pad_mar border-box_right">
                    <div class="mayus font-xxs padding_t_3 text-center">&nbsp;</div>
                </div>
                <div class="div-table-col w-10 no_pad_mar border-box_right">
                    <div class="mayus font-xxs padding_t_3 text-center">&nbsp;</div>
                </div>
                <div class="div-table-col w-50 no_pad_mar border-box_right">
                    <div class="mayus font-xxs padding_t_3">&nbsp;</div>
                </div>
                <div class="div-table-col w-9 no_pad_mar border-box_right">
                    <div class="mayus font-xxs padding_t_3 text-center">&nbsp;</div>
                </div>
                <div class="div-table-col w-10 no_pad_mar border-box_right">
                    <div class="mayus font-xxs padding_t_3 text-right">&nbsp;</div>
                </div>
                <div class="div-table-col w-10 no_pad_mar">
                    <div class="mayus font-xxs padding_t_3 text-right">&nbsp;</div>
                </div>
            @endfor
         </div>
    </div>
</div>

<div style="margin-top: 5px"></div>

<div class="border-box-number text-LEFT">
    @foreach(array_reverse( (array) $document->legends) as $row)
        @if ($row->code == "1000")
            <span style="text-transform: uppercase;" class=" font-xxs">SON: <span
                    class="">{{ $row->value }} {{ $document->currency_type->description }}</span></span><br>
        @endif

    @endforeach

</div>


<div style="height: 5px;"></div>
<div class="information mt-10 no_pad_mar">
    <div class="div-table no_pad_mar">
         <div class="div-table-row no_pad_mar">
            <div class="div-table-col w-59  text-left no_pad_mar border_redondo2 font-xxs" style="padding: 5px"> 

                
                @foreach($document->additional_information as $information)
                
                    @if ($information)
                    <strong>OBSERVACIONES:</strong>
                        @if ($loop->first)
                        @endif
                        <div>@if(\App\CoreFacturalo\Helpers\Template\TemplateHelper::canShowNewLineOnObservation())
                                {!! \App\CoreFacturalo\Helpers\Template\TemplateHelper::SetHtmlTag($information) !!}
                            @else
                                {{$information}}
                            @endif</div>
                    @endif
                @endforeach
                @foreach(array_reverse( (array) $document->legends) as $row)
                    @if ($row->code == "1000")
                        <span style="text-transform: uppercase;" class=" font-xxs"><span
                                class=""> </span></span><br>
                        @if (count((array) $document->legends)>1)
                            <span><span class="font-bold">Leyenda</span></span>
                        @endif
                    @else
                        <span> {{$row->code}}: {{ $row->value }} 
                            @inject('detractionType', 'App\Services\DetractionTypeService')
                            <br>Bien o servicio: {{ $document->detraction->detraction_type_id }} - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id) }}
                            <br>Medio de pago: {{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id) }}
                            <br>Porcentaje de detracción: {{ $document->detraction->percentage }}%
                            <br>Monto detracción: 
                            @php
                                        $amount_detraction = $document->detraction->amount;
                                        // if($document->currency_type_id != "PEN"){
                                        //     $exchange_rate_sale = $document->exchange_rate_sale;
                                        //     $amount_detraction = $amount_detraction * $exchange_rate_sale;
                                        //     $amount_detraction = number_format($amount_detraction, 2, ".", "");
                                        // }
                                    @endphp

                                    S/ {{ $amount_detraction }}
                            <br>Nro. Cta Banco de la Nación: {{ $document->detraction->bank_account }}</span>
                    @endif

                @endforeach

            </div>
            <div class="div-table-col w-37 no_pad_mar  border_redondo2" style="padding: 5px">

                <div class="div-table-col w-55 no_pad_mar  font-md font-bold font-xs text-left">Total Ope. Gravadas</div>
                <div class="div-table-col w-4 no_pad_mar  font-md font-bold font-xs text-center">{{ $document->currency_type->symbol }} </div>
                <div class="div-table-col w-39 no_pad_mar  font-md font-bold font-xs  text-right">{{ number_format($document->total_taxed, 2) }} </div>

                <div class="div-table-col w-55 no_pad_mar  font-md font-bold font-xs text-left">Total Ope. Inafectas</div>
                <div class="div-table-col w-4 no_pad_mar  font-md font-bold font-xs text-center">{{ $document->currency_type->symbol }} </div>
                <div class="div-table-col w-39 no_pad_mar  font-md font-bold font-xs  text-right">{{ number_format($document->total_unaffected, 2) }} </div>

                <div class="div-table-col w-55 no_pad_mar  font-md font-bold font-xs text-left">Total Ope. Exoneradas</div>
                <div class="div-table-col w-4 no_pad_mar  font-md font-bold font-xs text-center">{{ $document->currency_type->symbol }} </div>
                <div class="div-table-col w-39 no_pad_mar  font-md font-bold font-xs  text-right">{{ number_format($document->total_exonerated, 2) }} </div>

                <div class="div-table-col w-55 no_pad_mar  font-md font-bold font-xs text-left">Total Descuentos</div>
                <div class="div-table-col w-4 no_pad_mar  font-md font-bold font-xs text-center">{{ $document->currency_type->symbol }} </div>
                <div class="div-table-col w-39 no_pad_mar  font-md font-bold font-xs  text-right">{{ number_format($document->total_discount, 2) }} </div>


                <div class="div-table-col w-55 no_pad_mar  font-md font-bold font-xs text-left">Total IGV 18%</div>
                <div class="div-table-col w-4 no_pad_mar  font-md font-bold font-xs text-center">{{ $document->currency_type->symbol }} </div>
                <div class="div-table-col w-39 no_pad_mar  font-md font-bold font-xs  text-right">{{ number_format($document->total_igv, 2) }} </div>

                <div class="div-table-col w-55 no_pad_mar  font-md font-bold font-xs text-left">Total ISC</div>
                <div class="div-table-col w-4 no_pad_mar  font-md font-bold font-xs text-center">{{ $document->currency_type->symbol }} </div>
                <div class="div-table-col w-39 no_pad_mar  font-md font-bold font-xs  text-right"> &nbsp; @if($document->total_isc > 0) {{ number_format($document->total_isc, 2) }} @else 0.00 @endif</div>

                <div class="div-table-col w-55 no_pad_mar  font-md font-bold font-xs text-left">TOTAL A PAGAR</div>
                <div class="div-table-col w-4 no_pad_mar  font-md font-bold font-xs text-center">{{ $document->currency_type->symbol }} </div>
                <div class="div-table-col w-39 no_pad_mar  font-md font-bold font-xs  text-right">@if ($document->retention) {{ number_format($document->total-$document->retention->amount, 2) }} @else {{ number_format($document->total, 2) }} @endif </div>

            </div>
         </div>
    </div>
</div>

<div style="height: 5px;"></div>
<div class="information mt-10 no_pad_mar">
    <div class="div-table no_pad_mar">
         <div class="div-table-row no_pad_mar">
            <div class="div-table-col w-25  text-left no_pad_mar border_redondo2 font-xxs" style="padding: 5px"> 
                @if(in_array($document->document_type->id,['01','03']))
                    @if(count($accounts)>0)
                        @foreach($accounts as $account)
                            <div class="font-bold no_pad_mar">{{$account->bank->description}}</div>
                            <div>Cuenta: {{$account->currency_type->description}}</div>
                            <div>Cuenta Nº: {{$account->number}}</div>
                            @if($account->cci)
                                <div>CCI Nº: {{$account->cci}}</div>
                            @endif
                        @endforeach
                    @endif
                @endif
            </div>
            <div class="div-table-col w-3  text-center no_pad_mar font-xxs" style="">&nbsp;</div>
            <div class="div-table-col w-45  text-center no_pad_mar font-xxs" style="">
               {{ $document->hash }} <br>
                Representación impresa de la {{ $document->document_type->description }}.
                Esta puede ser consultada en:
                 <b>{!! searchUrl() !!}</b> 
                 <br>
    <hr>
@if($document->payment_method_type_id)
    <table class="full-width">
        <tr>
            <td class="font-xxs">
                <strong class="font-xxs">MÉTODO DE PAGO: </strong>{{ $document->payment_method_type->description }}
            </td>
        </tr>
    </table>
@endif
@if ($document->payment_condition_id === '01')
    @if($payments->count())
        <table class="full-width">
            <tr>
                <td>
                    <strong class="font-xxs">PAGOS:</strong></td>
            </tr>
            @php
                $payment = 0;
            @endphp
            @foreach($payments as $row)
                <tr>
                    <td class="font-xxs">&#8226; {{ $row->payment_method_type->description }}
                        - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
                </tr>
            @endforeach
        </table>
    @endif
@else
    <table class="full-width">
        @foreach($document->fee as $key => $quote)
            <tr>
                <td class="font-xxs">
                    &#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }}
                    / Fecha: {{ $quote->date->format('d-m-Y') }} /
                    Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
            </tr>
            @endforeach
    </table>
@endif
            </div>
            <div class="div-table-col w-25  text-center no_pad_mar font-xxs" style="">
               <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px; width: 120px"/>
            </div>
         </div>
    </div>
</div>

</body>
</html>
