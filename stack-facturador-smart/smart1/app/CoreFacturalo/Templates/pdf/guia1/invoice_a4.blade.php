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
    $document_base = $document->note ? $document->note : null;
    
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series. '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
    
    if ($document_base) {
        $affected_document_number = $document_base->affected_document ? $document_base->affected_document->series. '-' . str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series. '-' . str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);
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
    $balance = $document->total - $total_payment - $document->payments->sum('change');
    $bg = "storage/uploads/header_images/{$configurations->background_image}";
    $total_discount_items = 0;

    $establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $logo = $establishment__->logo ?? $company->logo;

    
    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }
    
    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }
    
    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();
    $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
@endphp
<html>

<head>
    {{-- <title>{{ $document_number }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>
    @if($document->state_type->id == '11'  || $document->state_type->id == '09')
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
    @if ($configurations->background_image)
    <div class="centered">
        <img src="data:{{ mime_content_type(public_path("{$bg}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$bg}"))) }}"
            alt="anulado" class="order-1">
    </div>
    @endif

<div class="header">
    <div class="text-left float-left header-logo">
        @if ($establishment->logo ?? false)
            <div class=" text-left">
                <img src="{{ public_path($establishment->logo) }}" alt="{{$company->name}}" class="company_logo_rec" style="width:100%; ">
            </div>
        @else
            @if($company->logo)
                <div class=" text-left">
                    <img src="{{ public_path("storage/uploads/logos/{$company->logo}") }}" alt="{{$company->name}}" class="company_logo_rec" style="width:100%; ">
                </div>
            @else
                <img src="{{ asset('logo/logo.jpg') }}" class="company_logo_rec" style="width: 100%; max-height: 70px">
            @endif
        @endif
    </div>
    <div class="text-left float-left header-company">
        @php
        $header_img = $configurations->header_image; 
        @endphp
        @if($header_img)
        {{-- <img src="{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$establishment__->template_pdf.''.DIRECTORY_SEPARATOR.'datos.png') }}" style="max-height: 180px" /> --}}
        
        <img src="{{ public_path("storage/uploads/header_images/{$header_img}") }}" alt="{{$company->name}}" class="company_logo_rec" style="max-height: 180px" />
        @else
        <div class="text-uppercase font-bold" style="font-size: 17px; color: #fff width:250px;">.</div>
        @endif
        {{-- <div class="text-uppercase font-bold" style="font-size: 17px; color: #3a628c">{{ $company->name }}</div>
        <div class="text-uppercase mayus">
            Oficina Principal: {{ ($establishment->address !== '-')? $establishment->address.'' : '' }}
        </div>
        <div class="text-uppercase mayus">
            {{ ($establishment->district_id !== '-')? ' '.$establishment->district->description : '' }}
            {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
            {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
        </div>
        @isset($establishment->trade_address)
        <div class="text-uppercase mayus">
        {{ $establishment->trade_address !== '-' ? 'Sucursal: ' . $establishment->trade_address : '' }}
        </div>
        @endisset
        <div>
            {{ ($establishment->telephone !== '-')? ''.$establishment->telephone : '' }}
            {{ $establishment->email !== '-' ? ' ' . $establishment->email : '' }}
        </div>
        <div class="text-left">
            {{ ($establishment->web_address !== '-')? ''.$establishment->web_address : '' }}
        </div>
        @isset($establishment->aditional_information)
            <div>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</div>
        @endisset --}}
    </div>
    <div  class="text-center float-left header-number py-3 font-bold ">
        <div style="margin-top: 5px" class="font-lg">RUC {{$company->number }}</div>
        <div style="margin-top: 2px" class="font-lg">{{ $document->document_type->description }}</div>

        <div style="margin-top: 5px">Nro {{ $document_number }}</div>
    </div>
</div>

<div class="div-table-row border_redondo">
    <div class="w-100">
        <table class="full-width font-bold">
            <tr>
                <td class="pt-1 pb-1 font-xs">SEÑORES</td>
                <td class="pt-1 pb-1 font-xs">:</td>
                <td class="pt-1 pb-1 font-xs" colspan="4">{{ $customer->name }}</td>
            </tr>
            <tr>
                <td class="pt-1 pb-1 font-xs" width="13%">N.COMERCIAL</td>
                <td class="pt-1 pb-1 font-xs" width="1%">:</td>
                <td class="pt-1 pb-1 font-xs" width="50%">{{ $customer->trade_name }}</td>
                <td class="pt-1 pb-1 font-xs" width="13%">TIPO PAGO</td>
                <td class="pt-1 pb-1 font-xs" width="1%">:</td>
                <td class="pt-1 pb-1 font-xs" width="22%">{{ $paymentCondition }} </td>
            </tr>
            <tr>
                <td class="pt-1 pb-1 font-xs">DIRECCION</td>
                <td class="pt-1 pb-1 font-xs">:</td>
                <td class="pt-1 pb-1 font-xs" colspan="4">
                    {{ $customer->address }}
                    {{ $customer->district_id !== '-' ? ', ' . $customer->district->description : '' }}
                    {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                    {{ $customer->department_id !== '-' ? '- ' . $customer->department->description : '' }}
                </td>
            </tr>
            <tr>
                <td class="pt-1 pb-1 font-xs">{{ $customer->identity_document_type->description }}</td>
                <td class="pt-1 pb-1 font-xs">:</td>
                <td class="pt-1 pb-1 font-xs">{{ $customer->number }}</td>
                <td class="pt-1 pb-1 font-xs">VENDEDOR</td>
                <td class="pt-1 pb-1 font-xs">:</td>
                <td class="pt-1 pb-1 font-xs">@if ($document->seller) {{ $document->seller->name }} @else {{ $document->user->name }} @endif</td>
            </tr>
            <tr>
                <td class="pt-1 pb-1 font-xs">FEC.EMISION</td>
                <td class="pt-1 pb-1 font-xs">:</td>
                <td class="pt-1 pb-1 font-xs">{{ $document->date_of_issue->format('d/m/Y') }}</td>
                <td class="pt-1 pb-1 font-xs">Moneda</td>
                <td class="pt-1 pb-1 font-xs">:</td>
                <td class="pt-1 pb-1 font-xs">{{ $document->currency_type->description }}</td>
            </tr>
        </table>
    </div>
</div>

<table class="full-width mt-2 font-bold">
    <tr>
        <td class="font-xs border-box pl-2 font-xs pt-2 pb-2" width="50%">GUIA REMISION: 
            @if ($document->guides)
                @foreach ($document->guides as $guide)
                    {{ $guide->number }}
                @endforeach
            @endif
            
            @if ($document->dispatch)
                {{ $document->dispatch->number_full }}
            @elseif ($document->reference_guides)
                @if (count($document->reference_guides) > 0)
                    @foreach ($document->reference_guides as $guide)
                        {{ $guide->series }}-{{ $guide->number }}
                    @endforeach
                @endif
            @endif
        </td>
        <td class="font-xs border-box pl-2 font-xs pt-2 pb-2" width="50%">NRO.ORDEN DE COMPRA: @if ($document->purchase_order) {{ $document->purchase_order }} @endif</td>
    </tr>
</table>

<table class="full-width mt-10 mb-10">
    <thead class="">
        <tr class="bg-grey border-box">
            <th class="text-white font-xs pl-2 pb-2 text-center py-2" width="10%">CÓDIGO</th>
            <th class="text-white font-xs pl-2 pb-2 text-center py-2" width="7%">CANT.</th>
            <th class="text-white font-xs pl-2 pb-2 text-left py-2">DESCRIPCION</th>
            <th class="text-white font-xs pl-2 pb-2 text-center py-2" width="6%">U.M.</th>
            <th class="text-white font-xs pl-2 pb-2 text-right py-2" width="10%">P.UNIT.</th>
            <th class="text-white font-xs pl-2 pb-2 text-right py-2" width="10%">IMPORTE</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($document->items as $row)
        @php
            $data_item = \App\Models\Tenant\Item::find($row->item_id);
            $digemid_codes = \App\Models\System\Digemid::select('cod_prod', 'num_regsan', 'nom_prod', 'nom_form_farm_simplif', 'concent', 'nom_titular', 'fracciones')->where('cod_prod',$data_item->cod_digemid)->first();
            // dd($data_item->cod_digemid);
        @endphp
            <tr>
                <td class="font-xs text-center align-top">{{ $row->item->internal_id }}</td>
                <td class="font-xs text-center align-top">
                    @if ((int) $row->quantity != $row->quantity)
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                <td class="text-left align-top">
                    @if ($row->name_product_pdf)
                        {!! $row->name_product_pdf !!}
                    @else
                        {!! $row->item->description !!}
                    @endif

                    @if ($row->total_isc > 0)
                        <br /><span style="font-size: 9px">ISC : {{ $row->total_isc }}
                            ({{ $row->percentage_isc }}%)</span>
                    @endif

                    @if (!empty($row->item->presentation))
                        {!! $row->item->presentation->description !!}
                    @endif

                    @if ($row->total_plastic_bag_taxes > 0)
                        <br /><span style="font-size: 9px">ICBPER : {{ $row->total_plastic_bag_taxes }}</span>
                    @endif

                    @if ($row->attributes)
                        @foreach ($row->attributes as $attr)
                            <br /><span style="font-size: 9px">{!! $attr->description !!} :
                                {{ $attr->value }}</span>
                        @endforeach
                    @endif
                    @if ($row->discounts)
                        @foreach ($row->discounts as $dtos)
                                @if($dtos->is_amount == false)
                                <br /><span style="font-size: 9px">{{ $dtos->factor * 100 }}%
                                    {{ $dtos->description }}</span>
                               
                                @endif
                        @endforeach
                    @endif
                    @isset($row->item->sizes_selected)
                        @if (count($row->item->sizes_selected) > 0)
                            @foreach ($row->item->sizes_selected as $size)
                                <small> Característica {{ $size->size }} | {{ $size->qty }} und.</small> <br>
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

                    @if ($row->item->is_set == 1)
                        <br>
                        @inject('itemSet', 'App\Services\ItemSetService')
                        @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                            {{ $item }}<br>
                        @endforeach
                    @endif

                    @if ($row->item->used_points_for_exchange ?? false)
                        <br>
                        <span style="font-size: 9px">*** Canjeado por {{ $row->item->used_points_for_exchange }}
                            puntos ***</span>
                    @endif

                    @if ($document->has_prepayment)
                        <br>
                        *** Pago Anticipado ***
                    @endif
                    <div class="font-xs">
                        @isset($digemid_codes)
                            LAB: {{$digemid_codes->nom_titular}} 
                        @endisset
                        @if ($row->item->IdLoteSelected ?? false)
                            @foreach ($row->item->IdLoteSelected as $lot)
                                LOTE: {{ $lot->code }} - FEC.VCTO.: {{ $lot->date_of_due }}
                            @endforeach
                        @endif
                        @isset($data_item->sanitary)
                            RS: {{$data_item->sanitary}} 
                        @endisset
                    </div>
                </td>
                <td class="font-xs text-center align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                @if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf)
                    <td class="text-right align-top">
                        {{ $row->generalApplyNumberFormat($row->unit_price, $configuration_decimal_quantity->decimal_quantity_unit_price_pdf) }}
                    </td>
                @else
                    <td class="font-xs text-right align-top">{{ number_format($row->unit_price, 2) }}</td>
                @endif
                <td class="font-xs text-right align-top">{{ number_format($row->total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="full-width border-box mt-2">
    <tr class="">
        <td class="font-xs pb-2 pt-2 pl-1" width="65%" style="text-align: top; vertical-align: top;">
            @foreach (array_reverse((array) $document->legends) as $row)
                @if ($row->code == '1000')
                    <p style="text-transform: uppercase;" class="font-xs">Son: <span class="font-bold font-xs">{{ $row->value }}
                            {{ $document->currency_type->description }}</span></p>
                    @if (count((array) $document->legends) > 1)
                        <p class="font-xs"><span class="font-bold ">Leyendas</span></p>
                    @endif
                @else
                    <p class="font-xs"> {{ $row->code }}: {{ $row->value }} </p>
                @endif
            @endforeach
        </td>
    </tr>
</table>

<table class="full-width mt-2">
    <tr class="">
        <td class="text-left border-right" width="65%" style="text-align: top; vertical-align: top;">
            <table class="full-width">
                <tr class="align-top">
                    <td width="30%" class="align-top"><img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px;" width="135px" /></td>
                    <td width="70%" class="align-top">

    @if ($document->payment_condition_id === '01')
        @if($payments->count())
        <table class="full-width">
            <tr>
                <td>
                    <strong>Pagos:</strong>
                </td>
            </tr>
            @php
            $payment = 0;
            @endphp
            @foreach($payments as $row)
            <tr>
                <td>&#8226; {{ $row->payment_method_type->description }}
                    - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
            </tr>
            @endforeach
            </tr>
        </table>
        @endif
    @else
    <table class="full-width">
        @foreach($document->fee as $key => $quote)
        <tr>
            <td>
                &#8226; {{ (empty($quote->getStringPaymentMethodType()) ? 'Cuota #'.( $key + 1) : $quote->getStringPaymentMethodType()) }}
                / Fecha: {{ $quote->date->format('d-m-Y') }} /
                Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}</td>
        </tr>
        @endforeach
        </tr>
    </table>
    @endif
                    </td>
                </tr>
            </table>
            
        </td>
        <td class="" width="35%" style="text-align: top; vertical-align: top;">

            <table class="full-width">
                <tr>
                    <td class="text-left" width="140px">Sub total </td>
                    <td class="text-left" width="10px"> {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold" width="">{{ number_format($document->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-left" width="130px">Otros Cargos </td>
                    <td class="text-left" width="10px"> {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold" width="">{{ number_format($document->total_charge, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-left" width="130px">Descuento Total </td>
                    <td class="text-left" width="10px"> {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold" width="">
                        @php 
                            $total_discount = $document->total_discount;
                            $discounts =  $document->discounts;
                            if($discounts){
                            $discounts = get_object_vars($document->discounts);
                            isset($discounts[0]) ? $discounts[0] : null;
                            $is_split = $discount->is_split;
                            if($is_split){
                                $total_discount = $total_discount * 1.18;
                            }
                            }else{
                                $total_discount = $total_discount_items;
                            }
                        @endphp
                        {{ number_format($total_discount, 2) }}
                    </td>
                </tr>
                <tr>
                    <td class="text-left">Operaciones Gravada </td>
                    <td class="text-left"> {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-left">Operaciones Exonerada </td>
                    <td class="text-left"> {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-left">I.G.V. 18.00% </td>
                    <td class="text-left"> {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-left font-bold">IMPORTE TOTAL </td>
                    <td class="text-left"> {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
                </tr>

            @if($document->retention)
                <tr>
                    <td class="text-left">TOTAL RETENCIÓN ({{ $document->retention->percentage * 100 }} %)</td>
                    <td class="text-left"> {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->retention->amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-left font-bold">IMPORTE NETO:</td>
                    <td class="text-left"> {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total - $document->retention->amount, 2) }}
                    </td>
                </tr>
            @endif
            </table>

            
        </td>
    </tr>
</table>

@if ($document->retention)
<br>
<table class="full-width">
    <tr>
        <td>
            <strong>Información de la retención:</strong>
        </td>
    </tr>
    <tr>
        <td>Base imponible de la retención:
            S/ {{ round($document->retention->amount_pen / $document->retention->percentage, 2) }}</td>
    </tr>
    <tr>
        <td>Porcentaje de la retención {{ $document->retention->percentage * 100 }}%</td>
    </tr>
    <tr>
        <td>Monto de la retención S/ {{ $document->retention->amount_pen }}</td>
    </tr>
</table>
@endif







@php
    $path_style = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'style.css');
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
@endphp

<head>
    <link href="{{ $path_style }}" rel="stylesheet" />
</head>

@if($document)
    <table class="full-width">
        <tr>
            <td class="font-bold">CUENTAS:</td>
        </tr>

        @if (in_array($document->document_type->id, ['01', '03']))
            @foreach ($accounts as $account)
            <tr>
                <td class="font-xs">{{ $account->bank->description }}:{{ $account->number }}  @if ($account->cci) CCI: {{ $account->cci }} @endif - {{ $account->currency_type->description }}</td>
            </tr>
            @endforeach
        @endif
        <tr>
            <td class="font-xs">Representacion impresa de la {{ $document->document_type->description }}, esta puede ser consultada en <a href="{!! route('search.index', ['external_id' => $document->external_id]) !!}" style="text-decoration: none; font-weight: bold;color:black;">{!! searchUrl() !!}</a></td>
        </tr>
    </table>
@endif

</body>
</html>
