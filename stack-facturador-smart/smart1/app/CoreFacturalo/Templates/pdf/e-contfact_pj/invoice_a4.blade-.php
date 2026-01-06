@php
$establishment = $document->establishment;
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

    $payments = $document->payments;
    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');

    //calculate items
    $allowed_items = 20;
    $quantity_items = $document->items()->count();
    $cycle_items = $allowed_items - ($quantity_items * 3);
    $total_weight = 0;

$item_=0;

    $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
    $template_data = \App\Models\Tenant\Establishment::where('code', '=', $establishment->code)->first();

@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
@if($document->state_type->id == '11')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
@endif
    <div class="company_logo_box" style="position: absolute; text-align: center; top:94%; right: 5%;">
        <img src="{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'logo.png') }}" alt="{{$company->name}}" style="width: 50px;">
    </div>
    <div class="header">
        <div class="text-center float-left header-logo">
            @if($company->logo)
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("storage".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."logos".DIRECTORY_SEPARATOR."{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."logos".DIRECTORY_SEPARATOR."{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="width: 80%;">
                </div>
            @endif
        {{-- <div class="text-center float-left header-company"> --}}
            <div class="text-left font-xs">
                
                <span class="font-xlg text-uppercase font-bold font-xs">{{ $company->name }}</span><br>
                <span class="font-bold"></span>{{ ($establishment->address !== '-')? $establishment->address.'' : '' }}
                {{ ($establishment->district_id !== '-')? '- '.$establishment->district->description : '' }}
            {{ ($establishment->province_id !== '-')? '- '.$establishment->province->description : '' }}
            {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }} <br>
                <span class="font-bold">Teléf: </span>{{ ($establishment->telephone !== '-')? $establishment->telephone : '' }} 
                @isset($establishment->web_address)
                    web{{ ($establishment->web_address !== '-')? ': '.$establishment->web_address : '' }}
                @endisset
                E-mail: {{ ($establishment->email !== '-')? $establishment->email : '' }}
                @isset($establishment->aditional_information)
                <br>
                    <span>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</span>
                @endisset

                
            </div>
        {{-- </div> --}}
        </div>
        <div  class="text-center float-left header-number py-3 font-bold borde_celeste1 font-xlg courier">
            <span class="courier" style="font-family: 'Courier New';">R.U.C. {{$company->number }}</span>
            {{-- <br> --}}
            <div class="fondo_gris alto_">{{ $document->document_type->description }}</div>
            {{-- <br> --}}
            Nº {{ $document_number }}
        </div>
    </div>

    <div class="information mt-3 borde_celeste1">
        <div class="div-table">
            <div class="div-table-row">
                <div class="div-table-col w-15 font-xs"> <strong>Cliente</div>
                <div class="div-table-col w-85 font-xs">: {{ $customer->name }}</div>
            </div>
            <div class="div-table-row">
                <div class="div-table-col w-15 font-xs"> <strong>Dirección</div>
                <div class="div-table-col w-85 font-xs">: 
                    {{ strtoupper($customer->address) }}
                    {{ ($customer->district_id !== '-')? '- '.strtoupper($customer->district->description) : '' }}
                    {{ ($customer->province_id !== '-')? '- '.strtoupper($customer->province->description) : '' }}
                    {{ ($customer->department_id !== '-')? '- '.strtoupper($customer->department->description) : '' }}
                </div>
            </div>
            <div class="div-table-row">
                <div class="div-table-col w-15 font-xs"> <strong> {{ $customer->identity_document_type->description }}</div>
                <div class="div-table-col w-85 font-xs">: {{$customer->number}}</div>
            </div>
             <div class="div-table-row">
                <div class="div-table-col w-15 font-xs"> <strong>FORMA DE PAGO</div>
                <div  class="div-table-col w-85 font-xs">: @foreach($payments as $row) {{ $row->payment_method_type->description }} @endforeach</div>
             </div>
             <div class="div-table-row">
                <div class="div-table-col w-15 font-xs"> <strong>Fecha de emisión</div>
                <div  class="div-table-col w-85 font-xs">: {{$document->date_of_issue->format('Y-m-d')}}</div>
             </div>
             <div class="div-table-row">
                <div class="div-table-col w-15 font-xs"> <strong>FECHA DE VENC.</div>
                <div  class="div-table-col w-85 font-xs">: @if($invoice) {{$invoice->date_of_due->format('Y-m-d')}} @endif</div>
             </div>

        </div>
    </div>
    <div class="information mt-3 borde_celeste1">
        <div class="div-table">
            <div class="div-table-row">
                <div class="div-table-col w-10 font-xs"> <strong> G. R. R.</div>
                <div class="div-table-col w-20 font-xs">: @if ($document->guides)
                @foreach($document->guides as $guide)
                    {{ $guide->number }}
                @endforeach
            @endif

            @if ($document->reference_guides)
                @foreach($document->reference_guides as $guide)
                    {{ $guide->number }}
                @endforeach
            @endif
        </div>
                <div class="div-table-col w-15 font-xs"> <strong> G. R. T.</div>
                <div class="div-table-col w-25 font-xs">:  </div>
                <div class="div-table-col w-10 font-xs"> <strong>MONEDA</div>
                <div class="div-table-col w-19 font-xs">: {{ $document->currency_type->description }} </div>
            </div>

            <div class="div-table-row">
                <div class="div-table-col w-10 font-xs"> <strong>Vendedor</div>
                <div class="div-table-col w-20 font-xs">: @if ($document->seller) {{ $document->seller->name }} @else {{ $document->user->name }} @endif</div>
                <div class="div-table-col w-15 font-xs"> <strong>Orden de compra</div>
                <div class="div-table-col w-25 font-xs">:  @if ($document->purchase_order) {{ $document->purchase_order }} @endif</div>
                <div class="div-table-col w-10 font-xs"> <strong>OTROS</div>
                <div class="div-table-col w-19 font-xs">: @if($document->additional_information)
                @foreach($document->additional_information as $information)
                    @if ($information)
                        {{ $information }}
                    @endif
                @endforeach
            @endif </div>
            </div>
        </div>
    </div>
    <div class="mt-2">
        <div class="div-table">
            <div class="div-table-row">
                <div class="div-table-col w-100 font-xs"><strong>OBSERVACIÓN: </strong> 
    @if ($document->detraction)
        <span>Operación Sujeta a Detracción</span>
        @inject('detractionType', 'App\Services\DetractionTypeService')
        <span>{{$document->detraction->detraction_type_id}} - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id ) }} {{ $document->detraction->percentage}}% - - <strong>BANCO DE LA NACIÓN CTA. DETRACCIÓN N° {{ $document->detraction->bank_account}}</strong> -  Importe <strong> S/ {{ $document->detraction->amount}}</span></strong>

    @endif
                </div>
            </div>
        </div>
    </div>
{{-- <br> --}}


<table class="full-width mt-0 mb-0 borde_celeste1">
    <thead >
        <tr class="">
            <th class="border-c_todo_dashed text-center py-1 desc" width="5%">ITEM</th>
            <th class="border-c_todo_dashed text-center py-1 desc" width="10%">Código</th>
            <th class="border-c_todo_dashed text-center py-1 desc" width="8%">Cant.</th>
            <th class="border-c_todo_dashed text-center py-1 desc" width="8%">U.M.</th>
            <th class="border-c_todo_dashed text-center py-1 desc" width="40%">Descripción</th>
            <th class="border-c_todo_dashed text-center py-1 desc" width="8%">P.U</th>
            <th class="border-c_todo_dashed text-center py-1 desc" width="8%">Importe</th>
        </tr>
    </thead>
    <tbody class="">
        @foreach($document->items as $row)
        @php $item_++; @endphp
            <tr>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales">{{ $item_ }}</td>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales">{{ $row->item->internal_id }}</td>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales">
                    @if(((int)$row->quantity != $row->quantity))
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales">{{ $row->item->unit_type_id }}</td>
                <td class="p-1 border-c_todo_dashed text-left align-top desc text-upp border_c_laterales">
                    @if($row->name_product_pdf)
                        {!!$row->name_product_pdf!!}
                    @else
                        {!!$row->item->description!!}
                    @endif

                      

                    @if($row->attributes)
                        @foreach($row->attributes as $attr)
                            <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                        @endforeach
                    @endif
                    {{-- @if($row->discounts)
                        @foreach($row->discounts as $dtos)
                            <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                        @endforeach
                    @endif --}}

                    @if($row->item->is_set == 1)
                     <br>
                     @inject('itemSet', 'App\Services\ItemSetService')
                        {{join( "-", $itemSet->getItemsSet($row->item_id) )}}
                    @endif
                </td>
                <td class="p-1 border-c_todo_dashed text-right align-top desc border_c_laterales">{{ number_format($row->unit_price, 2) }}</td>
                <td class="p-1 border-c_todo_dashed text-right align-top desc border_c_laterales">{{ number_format($row->total, 2) }}</td>
            </tr>

        @endforeach
            @for($i = 0; $i < $cycle_items; $i++)
            <tr>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales">&nbsp;</td>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales"></td>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales"></td>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales"></td>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales"></td>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales"></td>
                <td class="p-1 border-c_todo_dashed text-center align-top desc border_c_laterales"></td>
            </tr>
            @endfor
    </tbody>
</table>

    <div class="div-table">
        <div class="div-table-row">
            <div class="div-table-col w-74">

                <div class="div-table-col w-100">
                {{-- <br> --}}
                @foreach(array_reverse( (array) $document->legends) as $row)
                    @if ($row->code == "1000")
                        <span class="font-bold"> SÓN: {{ strtoupper($row->value) }} {{ strtoupper($document->currency_type->description) }} </span>
                    @endif
                @endforeach
                {{-- <span style="font-size: 9px">Código Hash: {{ $document->hash }}</span> --}}
                {{-- <br>
                @if(in_array($document->document_type->id,['01','03']))
                    @foreach($accounts as $account)
                        <p>
                        <span class="font-bold">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                        <span class="font-bold">N°:</span> {{$account->number}}
                        @if($account->cci)
                        <span class="font-bold">CCI:</span> {{$account->cci}}
                        @endif
                        </p>
                    @endforeach
                @endif --}}
                </div>
                <div class="div-table-col w-40">
                {{-- <br> --}}<center>
                <img src="data:image/png;base64, {{ $document->qr }}"  style="width:90px" />
                <br>
                <span style="font-size: 9px">Código Hash:<br> {{ $document->hash }}</span></center>
                {{-- <br>
                @if(in_array($document->document_type->id,['01','03']))
                    @foreach($accounts as $account)
                        <p>
                        <span class="font-bold">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                        <span class="font-bold">N°:</span> {{$account->number}}
                        @if($account->cci)
                        <span class="font-bold">CCI:</span> {{$account->cci}}
                        @endif
                        </p>
                    @endforeach
                @endif --}}
                </div>
                <div class="div-table-col w-60">
                      

<table class="mt-10 mb-10 font-xxs">
    <tr>
        <td colspan="8" class="text-center font-bold font-xxs border-c_todo_solid"><strong>Condición de Pago: {{ $paymentCondition }} </strong></td>
    </tr>
    <tr>
        <td colspan="8" class="text-center font-bold font-xxs border-c_todo_solid">


@if ($document->payment_condition_id === '01')
    @if($payments->count())
        <table class="full-width">
            <tr>
                <td class="font-xxs"><strong>PAGOS EFECTUADOS:</strong></td>
            </tr>
                @php $payment = 0; @endphp
                @foreach($payments as $row)
                    <tr>
                        <td class="font-xxs">&#8226; {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
                    </tr>
                @endforeach
            </tr>
        </table>
    @endif
@else
    <table class="full-width">
            @foreach($document->fee as $key => $quote)
                <tr>
                    <td class="font-xxs">&#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota # '.( $key + 1) : $quote->getStringPaymentMethodType()) }} / Fecha Vcto: {{ $quote->date->format('d-m-Y') }} / Importe Cuota: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
                </tr>
            @endforeach
        </tr>
    </table>
@endif



        </td>
    </tr>
</table>

                </div>
            </div>
            <div class="div-table-col w-25 font-xxs prueba">

<table class="full-width mt-10 mb-10 font-xxs">

            <tr>
                <td colspan="8" class="text-center font-bold font-xxs border-c_todo_solid">TOTAL OP. GRAVADA </td>
                <td class="text-right font-bold font-xxs border-c_todo_solid padding_right">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>


            <tr>
                <td colspan="8" class="text-center font-bold font-xxs border-c_todo_solid">{{(($document->total_prepayment > 0) ? 'Anticipo':'Descuento')}} </td>
                <td class="text-right font-bold font-xxs border-c_todo_solid padding_right">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        <tr>
            <td colspan="8" class="text-center font-bold font-xxs border-c_todo_solid">I.G.V. (18%) </td>
            <td class="text-right font-bold font-xxs border-c_todo_solid padding_right">{{ number_format($document->total_igv, 2) }}</td>
        </tr>


        <tr>
            <td colspan="8" class="text-center font-bold font-xxs border-c_todo_solid">IMPORTE TOTAL  </td>
            <td class="text-right font-bold font-xxs border-c_todo_solid padding_right">{{ number_format($document->total, 2) }}</td>
        </tr>


</table>

            </div>
        </div>
    </div>
<div class="font-xs">
<strong>"SIRVASE DEPOSITAR A NUESTRAS CUENTAS" - CUENTA:</strong> 
 @if(in_array($document->document_type->id,['01','03']))
                @foreach($accounts as $account)
                    <span class="font-bold font-xs">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                    <span class="font-bold font-xs">N°:</span> {{$account->number}}
                    @if($account->cci)
                    <span class="font-bold font-xs">CCI:</span> {{$account->cci}}
                    @endif
                @endforeach
            @endif
<br><br>
Representación Impresa del <strong>COMPROBANTE ELECTRONICO</strong>, Generado desde el Sistema del Contribuyente, para visualizar el documento visita: <strong> {!! url('/buscar') !!}</strong> , comprobante emitido desde <strong>www.econtfact.com </strong>

</div>

</body>
</html>
