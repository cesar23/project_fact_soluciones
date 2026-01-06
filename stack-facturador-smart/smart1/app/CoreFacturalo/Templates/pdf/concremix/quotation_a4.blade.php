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
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
    $tittle = str_pad($document->id, 8, '0', STR_PAD_LEFT);

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

    $template_data = \App\Models\Tenant\Establishment::where('code', '=', $establishment->code)->first();

    $miimage = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'membrete.png');
    $footer = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'footer.png');

    //calculate items
    $allowed_items = 8;
    // $quantity_items = $document->items()->count();
    $cycle_items = $allowed_items - $document->items()->count();
    // $total_weight = 0;
    // $count_=0;

@endphp
<html>
<head>
</head>
<body>

<div class="full-width" style="padding-top: -20px; padding-left: 28px">
    <div style="width:50%; float: left; ">
        @if($company->logo)
            <div class="">
                <img src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" style="max-width: 240px;">
            </div>
        @endif
        <div class="font-bold font-lg" style="padding-top: 12px; padding-left: 28px">CONCRE MIX PERU S.A.C.</div>
    </div>
    <div style="width:40%; float: right; ">
        <div style="padding-top: -17px; ">
            <h5 class="text-center font-xxlg font-bold naranja" style=" margin-bottom: 0px!important">Cotización</h5>
            <h3 class="text-center font-xlg font-bold" style="color: #ffffff; margin-top: 0px!important; margin-bottom: 0px!important">Nº {{ $tittle }}</h3>
            <div style="height:22px"></div>
            <div class="text-center font-lg " style="color: #000;; margin-top: 10px!important;">Fecha: {{ $document->date_of_issue->format('Y-m-d') }}</div>
        </div>
    </div>
</div>
<div style="clear: both;"></div>

<table class="full-width mt-6 mb-20">
    <tr>
        <td colspan="4" class="bg-grey font-bold" style="padding: 5px;">DATOS DEL CLIENTE</td>
    </tr>
    <tr>
        <td width="120px" style="padding: 5px;">Razón Social:</td>
        <td  style="padding: 5px;">{{ $customer->name }}</td>
        <td width="100px" style="padding: 5px;">{{ $customer->identity_document_type->description }}:</td>
        <td width="150px" style="padding: 5px;">{{ $customer->number }}</td>
    </tr>
    <tr>
        <td style="padding: 5px;">Dirección Fiscal:</td>
        <td style="padding: 5px;">{{ $customer->address }} {{-- {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }} {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }} {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }} --}}</td>
        <td style="padding: 5px;">Teléfono:</td>
        <td style="padding: 5px;">@if ($customer->telephone) {{ $customer->telephone }} @endif</td>
    </tr>
    <tr>
        <td style="padding: 5px;">Dirección de Obra:</td>
        <td style="padding: 5px;">@if ($document->shipping_address) {{ $document->shipping_address }} @endif </td>
        <td style="padding: 5px;"> Proyecto: </td>
        <td style="padding: 5px;">{{ $document->description }}</td>
    </tr>
    <tr>
        <td style="padding: 5px;">Asesor Comercial:</td>
        <td style="padding: 5px;">@if ($document->contact) {{ $document->contact }} @endif</td>
        <td style="padding: 5px;"> Email: </td>
        <td style="padding: 5px;">@if($document->date_of_due) {{ $document->date_of_due }} @endif</td>
    </tr>
    <tr>
        <td style="padding: 5px;">Teléfono:</td>
        <td style="padding: 5px;"> @if ($document->phone) {{ $document->phone }} @endif</td>
    </tr>

</table>

<table class="full-width mb-20" style="margin-top: 50px;">
    <tr>
        <td colspan="4" class="bg-grey font-bold" style="padding: 5px;">ESPECIFICACIONES DEL CONCRETO PREMEZCLADO</td>
    </tr>
</table>

<table class="full-width mt-10">
    <thead class="">
    <tr class="bg-black text-white">
        <th class="bg-black text-white text-center py-2 mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px;" width="15%">CANTIDAD  <br>(m3)</th>
        <th class="bg-black text-white text-center py-2">Descripción</th>
        <th class="bg-black text-white text-center py-2 mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px;" width="15%">PRECIO <br> (m3)</th>
        <th class="bg-black text-white text-center py-2 mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px;" width="15%">TOTAL <br> (S/)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        @php
            // dd($allowed_items);
        @endphp
        <tr>
            <td class="text-center align-top border-grey mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px;">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-left bg-grey2 border-grey mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px; padding-left: 5px;">
                  @if($row->item->name_product_pdf ?? false) {!!$row->item->name_product_pdf ?? ''!!} @else {!!$row->item->description!!} @endif
                  
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

                  @if($row->item !== null && property_exists($row->item,'extra_attr_value') && $row->item->extra_attr_value != '')
                    <br/><span style="font-size: 9px">{{$row->item->extra_attr_name}}: {{ $row->item->extra_attr_value }}</span>
                @endif

                @if($row->item->is_set == 1)
                 <br>
                    @inject('itemSet', 'App\Services\ItemSetService')
                    @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                        {{$item}}<br>
                    @endforeach
                @endif

            </td>
            <td class="text-right align-top border-grey mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px; padding-right: 5px">{{ number_format($row->unit_price, 2) }}</td>
            <td class="text-right align-top bg-grey2 border-grey mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px; padding-right: 5px">{{ number_format($row->total, 2) }}</td>
        </tr>
    @endforeach

    @for($i = 0; $i < $cycle_items; $i++)
        <tr>
            <td class="text-center align-top border-grey mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px;">&nbsp;</td>
            <td class="text-left bg-grey2 border-grey mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px; padding-left: 5px;"></td>
            <td class="text-right align-top border-grey mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px; padding-right: 5px"></td>
            <td class="text-right align-top bg-grey2 border-grey mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px; padding-right: 5px"></td>
        </tr>
    @endfor

    </tbody>
</table>
<table class="full-width">
    <tbody>
        <tr class="">
            <td class="text-center py-2" colspan="2" rowspan="3" style="padding-right: 20px;">
            
    <table class="full-width border-box2 my-2 ">
        <thead>
                <tr class="bg-grey2">
                    <th class="p-1 border-grey font-xs font-bold font-xs" style="padding-top: 5px; padding-bottom: 5px;">ENTIDAD <br> FINANCIERA</th>
                    <th class="p-1 border-grey font-xs font-bold font-xs" style="padding-top: 5px; padding-bottom: 5px;">NUMERO DE CUENTA CORRIENTE <br> CONCRE MIX PERU SAC</th>
                    <th class="p-1 border-grey font-xs font-bold font-xs" style="padding-top: 5px; padding-bottom: 5px;">CCI</th>
                    <th class="p-1 border-grey font-xs font-bold font-xs" style="padding-top: 5px; padding-bottom: 5px;">Moneda</th>
                </tr>
        </thead>
        <tfoot>
            @foreach($accounts as $account)
                <tr>
                    <td class="text-center border-grey border-box3 font-xs" style="padding-top: 5px; padding-bottom: 5px;">
                        <img src="{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'') }}{{$account->bank->id}}.jpg" alt="Footer" class="" style="width: 100px;"></td>
                    <td class="text-center border-grey border-box3 font-xs" style="padding-top: 5px; padding-bottom: 5px;">{{$account->number}}</td>
                    <td class="text-center border-grey border-box3 font-xs" style="padding-top: 5px; padding-bottom: 5px;">
                        @if($account->cci)
                            {{$account->cci}}
                        @endif
                    </td>
                    <td class="text-center border-grey text-upp border-box3 font-xs" style="padding-top: 5px; padding-bottom: 5px;"><span  @if($account->currency_type->id=='USD') style='color:#00a800' @endif>{{$account->currency_type->description}}
                    </span></td>
                </tr>
            @endforeach
        </tfoot>
    </table>

            </td>
            <td class="bg-black text-white text-center py-2 mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px;" width="15%">SUB TOTAL {{ $document->currency_type->symbol }}</td>
            <td class="bg-grey2 border-grey text-black text-right py-2 mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px; padding-right: 5px;" width="15%">{{ number_format($document->total_taxed, 2) }}</td>
        </tr>

        <tr class="">
            <td class="bg-black text-white text-center py-2 mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px;" width="15%">IGV 18% {{ $document->currency_type->symbol }}</td>
            <td class="bg-grey2 border-grey text-black text-right py-2 mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px; padding-right: 5px;" width="15%">{{ number_format($document->total_igv, 2) }}</td>
        </tr>
        <tr class="">
            <td class="bg-black text-white text-center py-2 mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px;" width="15%">TOTAL {{ $document->currency_type->symbol }}</td>
            <td class="bg-grey2 border-grey text-black text-right py-2 mt-5 mb-5" style="padding-top: 10px; padding-bottom: 10px; padding-right: 5px;" width="15%">{{ number_format($document->total, 2) }}</td>
        </tr>
    </tbody>
</table>
<br>
<img src="data:{{mime_content_type($footer)}};base64, {{base64_encode(file_get_contents($footer))}}" alt="footer" class="">
{{-- 
<br>
<table class="full-width">
<tr>
    <td>
    <strong>Pagos:</strong> </td></tr>
        @php
            $payment = 0;
        @endphp
        @foreach($document->payments as $row)
            <tr><td>- {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment }}</td></tr>
            @php
                $payment += (float) $row->payment;
            @endphp
        @endforeach
        <tr><td><strong>Saldo:</strong> {{ $document->currency_type->symbol }} {{ number_format($document->total - $payment, 2) }}</td>
    </tr>

</table> --}}

</body>


<div class="page-break"></div>

<div class="" style="position: absolute; text-align: center; top:0px; left: 0px; z-index: -9999999999999;">
        <img src="data:{{mime_content_type(app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'membrete.png'))}};base64, {{base64_encode(file_get_contents(app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'pag2.png')))}}" alt="" class="" style="">
    </div>


</html>

