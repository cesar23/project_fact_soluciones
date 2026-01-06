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
$bg = "storage/uploads/header_images/{$configurations->background_image}";
if ($logo === null && !file_exists(public_path("$logo}"))) {
    $logo = "{$company->logo}";
}

if ($logo) {
    $logo = "storage/uploads/logos/{$logo}";
    $logo = str_replace("storage/uploads/logos/storage/uploads/logos/", "storage/uploads/logos/", $logo);
}


    $customer = $document->customer;

    $document_base = $document->note;
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $document_type_description_array = [
        '01' => 'Factura',
        '03' => 'Boleta DE VENTA',
        '07' => 'NOTA DE CREDITO',
        '08' => 'NOTA DE DEBITO',
    ];
    $identity_document_type_description_array = [
        '-' => 'S/D',
        '0' => 'S/D',
        '1' => 'DNI',
        '6' => 'RUC',
    ];
    $affected_document_ = null;

    if($document_base->affected_document || $document_base->affected_sale_note){
        $affected_document_ = $document_base->affected_document ?? $document_base->affected_sale_note;
    }

    $affected_document_number = $affected_document_ ? $affected_document_->series.'-'.str_pad($affected_document_->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    //$affected_document_number = $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT);
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
    if ($configurations->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }


@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>

{{-- <table class="full-width">
    <tr>
        @if($company->logo)
            <td width="20%">
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                </div>
            </td>
        @else
            <td width="20%">
                <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px">
            </td>
        @endif
        <td width="50%" class="pl-3">
            <div class="text-left">
                <h4 class="">{{ $company_name }}</h4>
                <h5>{{ 'RUC '.$company->number }}</h5>
                @if ($company_owner)
                    <h6 class="">De: {{ $company_owner }}</h6>
                @endif
                <h6 style="text-transform: uppercase;">
                    {{ ($establishment->address !== '-')? $establishment->address : '' }}
                    {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </h6>
                <h6>{{ ($establishment->email !== '-')? $establishment->email : '' }}</h6>
                <h6>{{ ($establishment->telephone !== '-')? $establishment->telephone : '' }}</h6>
            </div>
        </td>
        <td width="30%" class="border-box py-4 px-2 text-center">
            <h5 class="text-center">{{ $document->document_type->description }}</h5>
            <h3 class="text-center">{{ $document_number }}</h3>
        </td>
    </tr>
</table> --}}
@if ($document->state_type->id == '11' || $document->state_type->id == '09' || $document->state_type->id == '55')
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

        <div style="float:left;width:20%">
            @if ($company->logo && file_exists(public_path("{$logo}")))
                <div class="company_logo_box" style="width: 100%;text-align: center;">
                    <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                        alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                </div>
            @else
                <br>
            @endif
        </div>
        <div style="float:left;width:1%;">
            <br>
        </div>
        <div style="float:left;width:48%;text-align:left;">
            <h4 style="margin: 0px !important;">{{ $company_name }}</h4>
            @if ($company_owner)
                De: {{ $company_owner }}
            @endif
            @if ($configuration->show_company_address)
                <h6 style="text-transform: uppercase;margin: 0px !important;line-height:0px;">
                    {{ $establishment->address !== '-' ? $establishment->address : '' }}
                    {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                    {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                    {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                </h6>
            @endif

            @isset($establishment->trade_address)
                <h6 style="margin: 0px !important;line-height:0px;">
                    {{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                </h6>
            @endisset

            <h6 style="margin: 0px !important;line-height:0px;">
                {{ $establishment->telephone !== '-' ? '' . $establishment->telephone : '' }}
            </h6>
            @if ($configuration->show_email)
                <h6 style="margin: 0px !important;line-height:0px;">
                    {{ $establishment->email !== '-' ? '' . $establishment->email : '' }}</h6>
            @endif
            @isset($establishment__->web_address)
                <h6 style="margin: 0px;line-height:0px;">
                    {{ $establishment__->web_address !== '-' ? '' . $establishment__->web_address : '' }}
                </h6>
            @endisset

            @isset($establishment->aditional_information)
                <h6 style="margin: 0px;line-height:0px;">
                    {{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
                </h6>
            @endisset
        </div>
        <div style="float:left;width:30%;">
            <div style="border-radius:10px;border:1px solid black;text-align:center;width: 100%;height: 80px;">
                <div style="margin-top:12px;">{{ 'RUC ' . $company->number }}</div>
                <div class="text-center" style="margin-top:3px;">{{ $document->document_type->description }}</div>
                <div class="text-center" style="margin-top:3px;">{{ $document_number }}</div>
            </div>
        </div>
    </div>

    <table class="full-width mt-3">
    <tr>
        <td class="text-left desc" width="120px">Fecha de emisión</td>
        <td class="text-left desc" width="8px">:</td>
        <td class="text-left desc">{{ $document->date_of_issue->format('Y-m-d') }}</td>
    </tr>
    <tr>
        <td class="text-left desc">Cliente</td>
        <td class="text-left desc">:</td>
        <td class="text-left desc">{{ $customer->name }}</td>
    </tr>
    <tr>
        <td class="text-left desc">{{ $customer->identity_document_type->description }}</td>
        <td class="text-left desc">:</td>
        <td class="text-left desc">{{ $customer->number }}</td>
        {{--@isset($document->date_of_due)--}}
            {{--<td>Fecha de vencimiento:</td>--}}
            {{--<td>{{ $document->date_of_due->format('Y-m-d') }}</td>--}}
        {{--@endisset--}}
    </tr>
    @if ($customer->address !== '')
    <tr>
        <td class="text-left desc">Dirección</td>
        <td class="text-left desc">:</td>
        <td class="text-left desc">
            {{ $customer->address }}
            {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
            {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
            {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
        </td>
    </tr>
    @endif
</table>

@if ($document->guides)
<table class="full-width mt-3">
@foreach($document->guides as $guide)
    <tr>
        <td class="text-left desc">{{ $guide->document_type_id }}</td>
        <td class="text-left desc">{{ $guide->number }}</td>
    </tr>
@endforeach
</table>
@endif

<table class="full-width mt-3">
    @if ($document->purchase_order)
    <tr>
        <td class="text-left desc">Orden de compra</td>
        <td class="text-left desc">:</td>
        <td class="text-left desc">{{ $document->purchase_order }}</td>
    </tr>
    @endif
    <tr>
        <td width="120px" class="text-left desc">Doc. Afectado</td>
        <td width="5px" class="text-left desc">:</td>
        <td class="text-left desc">{{ $affected_document_number }}</td>
    </tr>
    <tr>
        <td class="text-left desc">Tipo de nota</td>
        <td class="text-left desc">:</td>
        <td class="text-left desc">{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>
    </tr>
    <tr>
        <td class="text-left desc">Descripción</td>
        <td class="text-left desc">:</td>
        <td class="text-left desc">{{ $document_base->note_description }}</td>
    </tr>
    @if($document->additional_information)
    <tr>
        <td class="text-left desc">Observación</td>
        <td class="text-left desc">:</td>
        <td class="text-left desc">
            @foreach($document->additional_information as $information)
                <span>{{ $information }}</span>
                @if(!$loop->last)
                    -
                @endif
            @endforeach
        </td>
    </tr>
    @endif
</table>
<div class="border-box mb 10 mt-2">
    <table class="full-width">
        <thead class="">
        <tr class="">
            <th class="border-bottom  desc text-center py-2 rounded-t" width="">Cod.</th>
            <th class="border-bottom border-left desc text-center py-2" width="8%">Cant.</th>
            <th class="border-bottom border-left desc text-left py-2 px-2" width="8%">Unidad</th>
            <th class="border-bottom border-left desc text-left py-2 px-2">Descripción</th>
            <th class="border-bottom border-left desc text-left py-2 px-2" width="12%">P.Unit</th>
            <th class="border-bottom border-left desc text-left py-2 px-2" width="8%">Dto.</th>
            <th class="border-bottom border-left desc text-left py-2 px-2" width="12%">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($document->items as $row)
            <tr>
                <td class="text-left desc">{{ $row->relation_item->internal_id }}</td>
                <td class="text-center desc align-top border-left">
                    @if(((int)$row->quantity != $row->quantity))
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                <td class="text-center desc align-top border-left">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                <td class="text-center desc align-top border-left">
                    {!! $row->item->description !!}
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
                </td>
                <td class="text-center desc align-top border-left">{{ number_format($row->unit_price, 2) }}</td>
                <td class="text-center desc align-top border-left">
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
                <td class="text-center desc align-top border-left">{{ number_format($row->total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="7" class="text-center desc align-top border-left"></td>
            </tr>
        @endforeach
        
        </tbody>
        <tfoot style="border-top: 1px solid #333;">
    
    
    
        @if(isset($document->optional->observations))
            <tr>
                <td colspan="3" class="desc"><b>Obsevaciones</b></td>
                <td colspan="2" class="desc"></td>
            </tr>
            <tr>
                <td colspan="3" class="desc">{{ $document->optional->observations }}</td>
                <td colspan="2" class="desc"></td>
            </tr>
        @endif
        </tfoot>
    </table>
</div>
    
    <table class="full-width">
        
        @if($document->total_exportation > 0)
        <tr>
            <td colspan="6" class="text-right desc font-bold">Op. Exportación: {{ $document->currency_type->symbol }}</td>
            <td class="text-right desc font-bold">{{ number_format($document->total_exportation, 2) }}</td>
        </tr>
    @endif
    @if($document->total_free > 0)
        <tr>
            <td colspan="6" class="text-right desc font-bold">Op. Gratuitas: {{ $document->currency_type->symbol }}</td>
            <td class="text-right desc font-bold">{{ number_format($document->total_free, 2) }}</td>
        </tr>
    @endif
    @if($document->total_unaffected > 0)
        <tr>
            <td colspan="6" class="text-right desc font-bold">Op. Inafectas: {{ $document->currency_type->symbol }}</td>
            <td class="text-right desc font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
        </tr>
    @endif
    @if($document->total_exonerated > 0)
        <tr>
            <td colspan="6" class="text-right desc font-bold">Op. Exoneradas: {{ $document->currency_type->symbol }}</td>
            <td class="text-right desc font-bold" style="width: 12%;">{{ number_format($document->total_exonerated, 2) }}</td>
        </tr>
    @endif
    @if($document->total_taxed > 0)
        <tr>
            <td colspan="6" class="text-right desc font-bold">Op. Gravadas: {{ $document->currency_type->symbol }}</td>
            <td class="text-right desc font-bold">{{ number_format($document->total_taxed, 2) }}</td>
        </tr>
    @endif
    @if($document->total_discount > 0)
        <tr>
            <td colspan="6" class="text-right desc font-bold">{{(($document->total_prepayment > 0) ? 'Anticipo':'Descuento TOTAL')}}: {{ $document->currency_type->symbol }}</td>
            <td class="text-right desc font-bold">{{ number_format($document->total_discount, 2) }}</td>
        </tr>
    @endif
    <tr>
        <td colspan="6" class="text-right desc font-bold">IGV: {{ $document->currency_type->symbol }}</td>
        <td class="text-right desc font-bold">{{ number_format($document->total_igv, 2) }}</td>
    </tr>
    <tr>
        <td colspan="6" class="text-right desc font-bold">Total a pagar: {{ $document->currency_type->symbol }}</td>
        <td class="text-right desc font-bold">{{ number_format($document->total, 2) }}</td>
    </tr>
    </table>

    <div class="full-width desc mb-10">
        <div class="float-left w-65">
            Son: <span class="font-bold">{{ $document->number_to_letter }} {{ $document->currency_type->description }}</span>
            <table class="full-width desc">
                
                @if ($document->payment_condition_id === '02' && $document->isCreditNoteAndType13())
                    <tr>
                        <td>
                        </br>
                        </td>
                    </tr>
                    @foreach($document->fee as $key => $quote)
                        <tr>
                            <td class="text-left desc" >
                                @if (!$configurations->show_the_first_cuota_document)
                                    &#8226;
                                    {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                                    / Fecha: {{ $quote->date->format('d-m-Y') }} /
                                    Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}
                                @else
                                    @if ($key == 0)
                                        &#8226;
                                        {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                                        / Fecha: {{ $quote->date->format('d-m-Y') }} /
                                        Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
            
            </table>
        </div>
        <div class="float-left w-35">
            <div class="text-left desc" class="text-right desc">
                <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px; width: 100px;" />
                <p style="font-size: 8px;margin:0px;">{{ $document->hash }}</p>
            </div>
        </div>
    </div>

</body>
</html>
