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
    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
    
    $configuration = \App\Models\Tenant\Configuration::first();
    $configuration = \App\Models\Tenant\Configuration::first();
    $configurations = \App\Models\Tenant\Configuration::first();
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
    $establishment_data = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $customer = $document->customer;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    // $accounts = \App\Models\Tenant\BankAccount::all();
    $tittle = $document->prefix . '-' . str_pad($document->number??$document->id, 8, '0', STR_PAD_LEFT);
    
    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
    
@endphp
<html>

<head>
    {{-- <title>{{ $tittle }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>
    <table class="full-width">
        <tr>
            @if ($company->logo)
                <td width="20%">
                    <div class="company_logo_box">
                        <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                            alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                    </div>
                </td>
            @else
                <td width="20%">
                    {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px"> --}}
                </td>
            @endif
            <td width="50%" class="pl-3">
                <div class="text-left">
                    <h4 class="">{{ $company_name }}</h4>
                    @if ($company_owner)
                        De: {{ $company_owner }}
                    @endif
                    <h5>{{ 'RUC ' . $company->number }}</h5>
                    <h6 style="text-transform: uppercase;">
                        {{ $establishment->address !== '-' ? $establishment->address : '' }}
                        {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                        {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                        {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                    </h6>

                    @isset($establishment->trade_address)
                        <h6>{{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                        </h6>
                    @endisset
                    <h6>{{ $establishment->telephone !== '-' ? 'Central telefónica: ' . $establishment->telephone : '' }}
                    </h6>

                    <h6>{{ $establishment->email !== '-' ? 'Email: ' . $establishment->email : '' }}</h6>

                    @isset($establishment->web_address)
                        <h6>{{ $establishment->web_address !== '-' ? 'Web: ' . $establishment->web_address : '' }}</h6>
                    @endisset

                    @isset($establishment->aditional_information)
                        <h6>{{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
                        </h6>
                    @endisset
                </div>
            </td>
            <td width="30%" class="border-box py-4 px-2 text-center">
                <h5 class="text-center">{{ get_document_name('quotation', 'Cotización') }}</h5>
                <h3 class="text-center">{{ $tittle }}</h3>
            </td>
        </tr>
    </table>

    <table class="full-width mt-5" style="border-collapse: collapse">
        <tr>
            <td class="border-top border-left-right"  width="15%">Empresa</td>
            <td class="border-top border-left-right"  width="50%">{{ $customer->name }}</td>
            <td class="border-top border-left-right"  width="10%">{{ $customer->identity_document_type->description }}:</td>
            <td class="border-top border-left-right" width="25%">{{ $customer->number }}</td>
        </tr>
    </table>
    <table class="full-width" style="border-collapse: collapse">
        @if ($customer->address !== '')
            <tr>
                <td class="border-top border-left-right"  width="15%">Dirección fiscal</td>
                <td class="border-top border-left-right"  width="85%" colspan="">
                    {{ $customer->address }}
                    {{ $customer->district_id !== '-' ? ', ' . $customer->district->description : '' }}
                    {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                    {{ $customer->department_id !== '-' ? '- ' . $customer->department->description : '' }}
                </td>

            </tr>
        @endif
    </table>
    <table class="full-width" style="border-collapse: collapse">

            <tr>
                <td class="border-top border-left-right"  width="15%">Teléfono Cliente</td>
                <td class="border-top border-left-right"  width="20%">{{ $document->phone }}</td>
                <td class="border-top border-left-right"  width="12%">Email Cliente</td>
                <td class="border-top border-left-right"  width="18%">{!! str_replace("\n", '<br/>', $document->description) !!}</td>
                <td class="border-top border-left-right"  width="10%">{{ $document->quotations_optional }}</td>
                <td class="border-top border-left-right"  width="25%">{{ $document->quotations_optional_value }}</td>
            </tr>
    </table>

    <table class="full-width" style="border-collapse: collapse">
        <tr>
            <td class="border-top border-left-right"  width="15%">Atención Cliente</td>
            <td class="border-top border-left-right"  width="50%">{{ $document->contact }}</td>
            <td class="border-top border-left-right"  width="10%">Vendedor</td>
            <td class="border-top border-left-right"  width="25%">                
                @if ($document->seller->name)
                    {{ $document->seller->name }}
                @else
                    {{ $document->user->name }}
                @endif</td>
        </tr>
    </table>

    <table class="full-width" style="border-collapse: collapse">
        <tr>
            <td class="border-box" width="15%">Lugar de entrega</td>
            <td class="border-box" width="50%">{{ $document->shipping_address }}</td>
            <td class="border-box" width="10%">Teléfono</td>
            <td class="border-box" width="25%">{{ $document->phone }}</td>
        </tr>
    </table>

    <table class="full-width my-2">
        <tr>
            <th class="bg-t p-1 border-box" width="25%">Tiempo de entrega </th>
            <th class="bg-t p-1 border-box" width="25%">Validez de oferta</th>
            <th class="bg-t p-1 border-box" width="25%">Condición de pago </th>
            <th class="bg-t p-1 border-box" width="25%">Moneda</th>

        </tr>
            <tr>
                <td class="text-center border-box">{{ $document->delivery_date }}</td>
                <td class="text-center border-box">{{ $document->date_of_due }}</td>
                <td class="text-center border-box">{{ $document->payment_method_type->description }}</td>
                <td class="text-center border-box">{{ $document->currency_type->description}}</td>              
            </tr>

    </table>


    <table width="30%" class="full-width mt-10 mb-10">
        <thead class="">
            <tr class="bg-grey">
                <th class="bg-t border-top-bottom text-center py-2" width="8%">Cant.</th>
                <th class="bg-t border-top-bottom text-center py-2" width="8%">Unidad</th>
                <th class="bg-t border-top-bottom text-left py-2">Descripción</th>
                <th class="bg-t border-top-bottom text-left py-2">MARCA</th>
                <th class="bg-t border-top-bottom text-left py-2">Modelo</th>
                <th class="bg-t border-top-bottom text-center py-2" width="8%">Lote</th>
                <th class="bg-t border-top-bottom text-right py-2" width="12%">P.Unit</th>
                <th class="bg-t border-top-bottom text-right py-2" width="8%">Dto.</th>
                <th class="bg-t border-top-bottom text-right py-2" width="12%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
                @php
                    $brand = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getBrandFormItem($row);
                    
                @endphp
                <tr>
                    <td class="text-center align-top">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                    <td class="text-center align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="text-left">
                        @if ($row->item->name_product_pdf ?? false)
                            {!! $row->item->name_product_pdf ?? '' !!}
                        @else
                            {!! $row->item->description !!}
                        @endif
   
                        @if ($row->attributes)
                            @foreach ($row->attributes as $attr)
                                <br /><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                            @endforeach
                        @endif
                        @if ($row->discounts)
                            @foreach ($row->discounts as $dtos)
                                <br /><span style="font-size: 9px">{{ $dtos->factor * 100 }}%
                                    {{ $dtos->description }}</span>
                            @endforeach
                        @endif

                        @if ($row->item !== null && property_exists($row->item, 'extra_attr_value') && $row->item->extra_attr_value != '')
                            <br /><span style="font-size: 9px">{{ $row->item->extra_attr_name }}:
                                {{ $row->item->extra_attr_value }}</span>
                        @endif

                        @if ($row->item->is_set == 1)
                            <br>
                            @inject('itemSet', 'App\Services\ItemSetService')
                            @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                                {{ $item }}<br>
                            @endforeach
                        @endif
                        @isset($row->item->info_link)
                            <a href="{{ $row->item->info_link }}">Más información..</a>
                        @endisset


                    </td>
                    <td class="text-left">{{ $brand }}</td>
                    <td class="text-left">{{ $row->item->model ?? '' }}</td>
                    <td class="text-center align-top">
                        @inject('itemLotGroup', 'App\Services\ItemLotsGroupService')
                        @if (isset($row->item->lots_group))
                            {{ $itemLotGroup->getLote($row->item->lots_group) }}
                        @endif
                    </td>
                    <td class="text-right align-top">{{ number_format($row->unit_price, 2) }}</td>
                    <td class="text-right align-top">
                        @if ($row->discounts)
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
                    <td class="text-right align-top">{{ number_format($row->total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="9" class="border-bottom"></td>
                </tr>
            @endforeach
            @if ($document->total_exportation > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">Op. Exportación:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_free > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">Op. Gratuitas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_unaffected > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">Op. Inafectas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_exonerated > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">Op. Exoneradas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_taxed > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">Op. Gravadas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_discount > 0)
                <tr>
                    <td colspan="8" class="text-right font-bold">
                        {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento TOTAL' }}:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="8" class="text-right font-bold">IGV: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
            </tr>
            <tr>
                <td colspan="8" class="text-right font-bold">Total a pagar: {{ $document->currency_type->symbol }}
                </td>
                <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
            </tr>
        </tbody>
    </table>




    @php
        $accounts = \App\Models\Tenant\BankAccount::all();
        //to array
        $accounts = $accounts->transform(function ($row) {
            return [
                'bank_id' => $row->bank_id,
                'bank' => $row->bank->description,
                'number' => $row->number,
                'cci' => $row->cci,
                'currency_type_id' => $row->currency_type_id,
                'currency_type' => $row->currency_type->description == "Soles" ? "SOLES":"DOLARES",
            ];
        });
        $all_accounts = $accounts->groupBy('bank_id')->toArray();
    @endphp

    <table width="70%">
        <thead>
            <tr>
                <th class="desc border-box p-1" width="25%">Banco</th>
                <th class="desc border-box p-1" width="25%">NRO CUENTA</th>
                <th class="desc border-box p-1" width="30%">CCI</th>
                <th class="desc border-box p-1">Moneda</th>
    
            </tr>
        </thead>
        <tbody>
            @foreach ($all_accounts as $accounts)
                @foreach($accounts as $key=>$account)
                    <tr>
                        @if($key==0)
                        @php
                            $rowspan = count($accounts);
                        @endphp
                        <td rowspan="{{$rowspan}}" class="text-center border-box">
                        <h5>{{$account['bank']}}</h5>
                        </td>
                        @endif

                        <td class="desc text-center border-box">{{$account['number']}}</td>
                        <td class="desc text-center border-box">
                            @if($account['cci'])
                                {{$account['cci']}}
                            @endif
                        </td>
                        <td class="desc text-center text-upp border-box">{{$account['currency_type']}}</td>
                        
                    </tr>
                @endforeach
            @endforeach
    </table>
{{-- 
    <table class="full-width my-2">
        <tr>
            <th class="p-1" width="25%">Banco</th>
            <th class="p-1" width="25%">Nro Cuenta</th>
            <th class="p-1" width="30%">CCI</th>
            <th class="p-1">Moneda</th>

        </tr>
        
        @foreach($accounts as $account)
            <tr>
                <td class="text-center">{{$account->bank->description}}</td>
                <td class="text-center">{{$account->number}}</td>
                <td class="text-center">
                    @if($account->cci)
                        {{$account->cci}}
                    @endif
                </td>
                <td class="text-center text-upp">{{$account->currency_type->description}}</td>
                
            </tr>
        @endforeach
    </table> --}}




















    <table class="full-width">

        <tr>
            {{-- <td width="65%">
            @foreach ($document->legends as $row)
                <p>Son: <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></p>
            @endforeach
            <br/>
            <strong>Información adicional</strong>
            @foreach ($document->additional_information as $information)
                <p>{{ $information }}</p>
            @endforeach
        </td> --}}
        </tr>
    </table>
    <br>
    <table class="full-width">
        <tr>
            <td>
                <strong>Pagos:</strong>
            </td>
        </tr>
        @php
            $payment = 0;
        @endphp
        @foreach ($document->payments as $row)
            <tr>
                <td>- {{ $row->payment_method_type->description }} -
                    {{ $row->reference ? $row->reference . ' - ' : '' }}
                    {{ $document->currency_type->symbol }} {{ $row->payment }}</td>
            </tr>
            @php
                $payment += (float) $row->payment;
            @endphp
        @endforeach
        <tr>
            <td><strong>Saldo:</strong> {{ $document->currency_type->symbol }}
                {{ number_format($document->total - $payment, 2) }}</td>
        </tr>

    </table>

    <table class="full-width">

        <tbody>
            <tr>
                @if ($configuration->yape_qr_quotations && $establishment_data->yape_logo )
                    @php
                        $yape_logo = $establishment_data->yape_logo;
                    @endphp
                    <td class="text-center">
                        <table>
                            <tr>
                                <td>
                                    <strong>
                                        Qr yape
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="data:{{ mime_content_type(public_path("{$yape_logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$yape_logo}"))) }}"
                                        alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    @if ($establishment_data->yape_owner)
                                        <strong>
                                            Nombre: {{ $establishment_data->yape_owner }}
                                        </strong>
                                    @endif
                                    @if ($establishment_data->yape_number)
                                        <br>
                                        <strong>
                                            Número: {{ $establishment_data->yape_number }}
                                        </strong>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                @endif
                @if ($configuration->plin_qr_quotations && $establishment_data->plin_logo)
                    @php
                        $plin_logo = $establishment_data->plin_logo;
                    @endphp
                    <td class="text-center">
                        <table>
                            <tr>
                                <td>
                                    <strong>
                                        Qr plin
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="data:{{ mime_content_type(public_path("{$plin_logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$plin_logo}"))) }}"
                                        alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    @if ($establishment_data->plin_owner)
                                        <strong>
                                            Nombre: {{ $establishment_data->plin_owner }}
                                        </strong>
                                    @endif
                                    @if ($establishment_data->plin_number)
                                        <br>
                                        <strong>
                                            Número: {{ $establishment_data->plin_number }}
                                        </strong>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                @endif
            </tr>
        </tbody>
    </table>
</body>

</html>
