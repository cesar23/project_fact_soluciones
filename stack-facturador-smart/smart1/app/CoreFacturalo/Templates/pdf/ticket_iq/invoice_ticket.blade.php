@php
    $establishment = $document->establishment;
    $customer = $document->customer;
    $invoice = $document->invoice;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
    $document_base = ($document->note) ? $document->note : null;
    $payments = $document->payments;

    if($document_base) {
        $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    } else {
        $affected_document_number = null;
    }
    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');


    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body class="ticket">


@if($document->state_type->id == '11')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:500px">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
@endif
<div style="height:0.5px"></div>
<table class="full-width border-box mt-10">
    <tr style="background-color: #000;" class="mt-3">
        <td class="text-center" style="background-color: #000; color: #fff!important; font-weight: bold; "><span style="color:white;">{{ $document->document_type->description }}</span></td>
    </tr>
    <tr class="mt-3">
        <td class="text-center">{{ 'RUC '.$company->number }}</td>
    </tr>
    <tr class="mt-3">
        <td class="text-center">{{ $document_number }}</td>
    </tr>
</table>

<table class="full-width border-box mt-2">
    <tr>
        <td class="text-center" width="50px" style="font-weight: bold; padding: 5px;">
            @if($company->logo)
                <div class="text-center ">
                    <img src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" class="" style="width: 150px; ">
                </div>
            @endif
        </td>
    </tr>
    <tr>
        <td class="text-center" width="300px" style="font-size: 10px;">
            <div style="color:black;font-weight: bold; ">{{ $company->name }}</div>
            {{-- <div style="color:black;font-weight: bold; ">{{  ($establishment->trade_address !== ' ')? 'D. Comercial: '.$establishment->trade_address : ''  }}</div> --}}
            <div style="color:black;">
                {{ ($establishment->address !== '-')? $establishment->address : '' }}
            </div>
            <div style="color:black;">
                {{ ($establishment->district_id !== '-')? ''.$establishment->district->description : '' }}
                {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
            </div>
            <div style="color:black;">{{ ($establishment->telephone !== '-')? 'Teléfonos: '.$establishment->telephone : '' }}</div>
            <div style="color:black;">{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</div>
            <div style="color:black;">{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</div>
            <div style="color:black;">
                @isset($establishment->aditional_information)
                    {{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}
                @endisset
            </div>
        </td>
    </tr>
</table>

<table class="full-width border-box mt-2">
    <tr style="background-color: #f0f0f0;">
        <td class="text-center" style="background-color: #f0f0f0; color: #000!important; font-weight: bold; "><span style="color:black;">DATOS DEL CLIENTE</span></td>
    </tr>
</table>

<table class="full-width" style="font-size: 10px;">
    <tr style="background-color: #ffffff;">
        <td class="text-left" style="background-color: #ffffff;font-size: 10px; color: #000!important; "><span style="color:black;font-weight: bold; ">RAZÓN:</span> {{ $customer->name }}</td>
    </tr>
    <tr style="background-color: #ffffff;">
        <td class="text-left" style="background-color: #ffffff;font-size: 10px; color: #000!important; "><span style="color:black; font-weight: bold;">{{ $customer->identity_document_type->description }}:</span> {{ $customer->number }}</td>
    </tr>
    <tr style="background-color: #ffffff;">
        <td class="text-left" style="background-color: #ffffff;font-size: 10px; color: #000!important; "><span style="color:black; font-weight: bold; ">DIRECCIÓN: </span>
                            {{ $customer->address }}
                    {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                    {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                    {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
        </td>
    </tr>
    <tr style="background-color: #ffffff;">
        <td class="text-left" style="background-color: #ffffff;font-size: 10px; color: #000!important; "><span style="color:black; font-weight: bold; ">&nbsp;</td>
    </tr>
    <tr style="background-color: #ffffff;">
        <td class="text-center" style="background-color: #ffffff;font-size: 10px; color: #000!important; "><span style="color:black; font-weight: bold; ">FECHA DE EMISIÓN: {{ $document->date_of_issue->format('Y-m-d') }}  {{ $document->time_of_issue }}</span>
        </td>
    </tr>
</table>



<table class="full-width mt-10 mb-10">
    <thead class="">
    <tr>
        <th class="border-top-bottom desc-9 text-left">PRODUCTO</th>
        <th class="border-top-bottom desc-9 text-center">UNIDAD</th>
        <th class="border-top-bottom desc-9 text-center">CANT.</th>
        <th class="border-top-bottom desc-9 text-center">PRECIO</th>
        <th class="border-top-bottom desc-9 text-right">V.GENERAL</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
    @php
// dd($document->data_json->items);
    @endphp
        <tr>
            <td class="text-left desc-9 align-top font-bold">
                @if($row->name_product_pdf)
                    {!!$row->name_product_pdf!!}
                @else
                    {!!$row->item->description!!}
                @endif

                @if($row->total_isc > 0)
                    <br/>ISC : {{ $row->total_isc }} ({{ $row->percentage_isc }}%)
                @endif

                @if (!empty($row->item->presentation)) {!!$row->item->presentation->description!!} @endif

                @if($row->total_plastic_bag_taxes > 0)
                    <br/>ICBPER : {{ $row->total_plastic_bag_taxes }}
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
            <td class="text-center desc-9 align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
            <td class="text-center desc-9 align-top">

                    @if ((int) $row->quantity != $row->quantity)
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif

            </td>
            <td class="text-right desc-9 align-top">{{ number_format($row->unit_price, 2) }}</td>
            <td class="text-right desc-9 align-top font-bold">{{ number_format($row->total, 2) }}</td>
        </tr>
    @endforeach
            <tr>
                <td class="text-center desc" colspan="5">...................................................................................................</td>
            </tr>
            @if ($document->total_exportation > 0)
            <tr>
                <td class="text-right font-bold desc"></td>
                <td class="text-right font-bold desc"></td>
                <td class="text-right desc">Op. Exportación:</td>
                <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_exportation, 2) }}</td>
            </tr>
            @endif

            @if ($document->total_free > 0)
            <tr>
                <td class="text-right font-bold desc"></td>
                <td class="text-right font-bold desc"></td>
                <td class="text-right desc">Op. Gratuitas:</td>
                <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_free, 2) }}</td>
            </tr>
            @endif

            @if ($document->total_unaffected > 0)
            <tr>
                <td class="text-right font-bold desc"></td>
                <td class="text-right font-bold desc"></td>
                <td class="text-right desc">Op. Inafectas:</td>
                <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_unaffected, 2) }}</td>
            </tr>
            @endif

            @if ($document->total_exonerated > 0)
            <tr>
                <td class="text-right font-bold desc"></td>
                <td class="text-right font-bold desc"></td>
                <td class="text-right desc">Op. Exoneradas:</td>
                <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_exonerated, 2) }}</td>
            </tr>
            @endif

            @if ($document->document_type_id === '07')
                @if ($document->total_taxed >= 0)
                <tr>
                    <td class="text-right font-bold desc"></td>
                    <td class="text-right font-bold desc"></td>
                    <td class="text-right desc">Op. Gravadas:</td>
                    <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold desc">{{ number_format($document->total_taxed, 2) }}</td>
                </tr>
                @endif
            @elseif($document->total_taxed > 0)
            <tr>
                <td class="text-right font-bold desc"></td>
                <td class="text-right font-bold desc"></td>
                <td class="text-right desc">Op. Gravadas:</td>
                <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
            @endif

            @if ($document->total_plastic_bag_taxes > 0)
            <tr>
                <td class="text-right font-bold desc"></td>
                <td class="text-right font-bold desc"></td>
                <td class="text-right desc">Icbper:</td>
                <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
            </tr>
            @endif

            <tr>
                <td class="text-right font-bold desc"></td>
                <td class="text-right font-bold desc"></td>
                <td class="text-right desc">IGV:</td>
                <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_igv, 2) }}</td>
            </tr>

            @if ($document->total_isc > 0)
            <tr>
                <td class="text-right font-bold desc"></td>
                <td class="text-right font-bold desc"></td>
                <td class="text-right desc">ISC:</td>
                <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_isc, 2) }}</td>
            </tr>
            @endif

            @if ($document->total_discount > 0 && $document->subtotal > 0)
            <tr>
                <td class="text-right font-bold desc"></td>
                <td class="text-right font-bold desc"></td>
                <td class="text-right desc">Subtotal:</td>
                <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->subtotal, 2) }}</td>
            </tr>
            @endif

            @if ($document->total_discount > 0)
            <tr>
                <td class="text-right font-bold desc"></td>
                <td class="text-right font-bold desc"></td>
                <td class="text-right desc">Descuento total:</td>
                <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($total_discount, 2) }}</td>
            </tr>
            @endif

            @if ($document->total_charge > 0)
            <tr>
                <td class="text-right font-bold desc"></td>
                <td class="text-right font-bold desc"></td>
                <td class="text-right desc">Cargos:</td>
                <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_charge, 2) }}</td>
            </tr>
            @endif

            <tr>
                <td class="text-right font-bold desc"></td>
                <td class="text-right font-bold desc"></td>
                <td class="text-right desc">Total a pagar:</td>
                <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total, 2) }}</td>
            </tr>

            @if (($document->retention || $document->detraction) && $document->total_pending_payment > 0)
            <tr>
                <td class="text-right font-bold desc"></td>
                <td class="text-right font-bold desc"></td>
                <td class="text-right desc">M. Pendiente:</td>
                <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_pending_payment, 2) }}</td>
            </tr>
            @endif

            @if ($balance < 0)
            <tr>
                <td class="text-right font-bold desc"></td>
                <td class="text-right font-bold desc"></td>
                <td class="text-right desc">Vuelto:</td>
                <td class="text-right font-bold desc">{{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format(abs($balance), 2, '.', '') }}</td>
            </tr>
            @endif

            <tr>
                <td class="text-center desc" colspan="5">...................................................................................................</td>
            </tr>

            <tr>
                <td class="text-left " colspan="5" style="font-size:10px">
        @foreach(array_reverse((array) $document->legends) as $row)
                @if ($row->code == "1000")
                    <div class="desc">Son: <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></div>
                    @if (count((array) $document->legends)>1)
                    <div><span class="font-bold">Leyendas</span></div>
                    @endif
                @else
                    <div class="desc">{{$row->code}}: {{ $row->value }}</div>
                @endif
        @endforeach
                </td>
            </tr>
            <tr>
                <td class="text-center desc" colspan="5">...................................................................................................</td>
            </tr>
        {{-- @if($balance < 0)
           <tr>
               <td colspan="3" class="text-right font-bold desc">VUELTO: {{ $document->currency_type->symbol }}</td>
               <td class="text-right font-bold desc">{{ number_format(abs($balance),2, ".", "") }}</td>
           </tr>
        @endif --}}
    </tbody>
</table>
<table class="full-width">
    <tr>
        <td class="text-center" width="90px"><img class="qr_code" src="data:image/png;base64, {{ $document->qr }}" width="90px" /></td>
        <td class=" align-center" width="">
<table class="full-width">
    <tr>
        <td style="font-size:8px" colspan="4">MONEDA: {{ $document->currency_type->description }}</td>
    </tr>
    <tr>
        <td style="font-size:8px">Pagó:  
        </td>
        <td style="font-size:8px ">   @if ($document->payment_condition_id === '01')

        @if($payments->count())
            @foreach($payments as $row)
            {{ $row->payment + $row->change }}
            @endforeach
        @endif
    @else
        {{-- @foreach($document->fee as $key => $quote)
            <tr>
                <td class="desc">&#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }} / Fecha: {{ $quote->date->format('d-m-Y') }} / Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
            </tr>
        @endforeach --}}
    @endif
        </td>
        <td style="font-size:8px">Vuelto: </td>
        <td style="font-size:8px">{{ number_format(abs($balance),2, ".", "") }}</td>
    </tr>
    <tr>
        <td style="font-size:8px" colspan="4">VENDEDOR: @if ($document->seller) {{ $document->seller->name }} @else {{ $document->user->name }} @endif</td>
    </tr>
    <tr>
        <td style="font-size:8px" colspan="4" class="text-center">
            @if ($customer->department_id == 16) "Bienes transferidos en la Amazonía para ser consumidos en la misma"
            @endif
        </td>
    </tr>
</table>

        </td>
    </tr>
    <tr>
        <td style="font-size:11px" colspan="2" class="font-bold text-center"> NO SE ACEPTAN CAMBIOS NI DEVOLUCIONES</td>
    </tr>
    <tr>
        <td style="font-size:10px" colspan="2" class="text-center"> Para consultar el comprobante ingresar a: <br><b>{!! searchUrl() !!}</b></td>
    </tr>

    {{-- <tr>
        <td class="desc pt-3">
            @foreach($document->additional_information as $information)
                @if ($information)
                    @if ($loop->first)
                        <strong>Información adicional</strong>
                    @endif
                    <p class="desc">@if(\App\CoreFacturalo\Helpers\Template\TemplateHelper::canShowNewLineOnObservation())
                            {!! \App\CoreFacturalo\Helpers\Template\TemplateHelper::SetHtmlTag($information) !!}
                        @else
                            {{$information}}
                        @endif</p>
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
    {{-- <tr>
        <td class="text-center desc">Código Hash: {{ $document->hash }}</td>
    </tr> --}}

   {{--  @if ($customer->department_id == 16)
        <tr>
            <td class="text-center desc pt-5">
                Representación impresa del Comprobante de Pago Electrónico.
                <br/>Esta puede ser consultada en:
                <br/> <b>{!! searchUrl() !!}</b>
                <br/> "Bienes transferidos en la Amazonía
                <br/>para ser consumidos en la misma
            </td>
        </tr>
    @endif
    @php
        $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);

    @endphp
    <tr>
        <td class="desc pt-5">
            <strong>CONDICIÓN DE PAGO: {{ $paymentCondition }} </strong>
        </td>
    </tr>

    @if($document->payment_method_type_id)
        <tr>
            <td class="desc pt-5">
                <strong>MÉTODO DE PAGO: </strong>{{ $document->payment_method_type->description }}
            </td>
        </tr>
    @endif

    @if ($document->payment_condition_id === '01')

        @if($payments->count())
            <tr>
                <td class="desc pt-5">
                    <strong>PAGOS:</strong>
                </td>
            </tr>
            @foreach($payments as $row)
                <tr>
                    <td class="desc">&#8226; {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
                </tr>
            @endforeach
        @endif
    @else
        @foreach($document->fee as $key => $quote)
            <tr>
                <td class="desc">&#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }} / Fecha: {{ $quote->date->format('d-m-Y') }} / Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
            </tr>
        @endforeach
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
        <td class="text-center desc pt-5">Para consultar el comprobante ingresar a {!! searchUrl() !!}</td>
    </tr> --}}
</table>

</body>
</html>
