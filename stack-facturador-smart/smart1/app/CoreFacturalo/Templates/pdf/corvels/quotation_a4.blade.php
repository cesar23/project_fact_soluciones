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
    $accounts = \App\Models\Tenant\BankAccount::all();
    $tittle = $document->prefix . '-' . str_pad($document->number ?? $document->id, 8, '0', STR_PAD_LEFT);
    $count = 0;
    // dd($establishment);
@endphp
<html>

<head>
</head>

<body style="padding-top:1px;margin-top:1px;">
    <div style="border: 3px double #000; height: 95%;">
        <table class="full-width">
            <tr>
                @if ($company->logo)
                    <td width="50%">
                        <div class="company_logo_box">
                            <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                                alt="{{ $company->name }}" class="company_logo" style="max-width: 250px;">
                        </div>
                    </td>
                @else
                    <td width="50%">
                        {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px"> --}}
                    </td>
                @endif
                <td width="50%" class="pl-3">
                    <center>
                        <div class="text-center">
                            <h2 class="font-lg font-bold">{{ $company->name }}</h2>
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
                                <h6>{{ $establishment->web_address !== '-' ? 'Web: ' . $establishment->web_address : '' }}
                                </h6>
                            @endisset

                            @isset($establishment->aditional_information)
                                <h6>{{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
                                </h6>
                            @endisset
                            <h5 class="font-sm font-bold">{{ 'R.U.C. ' . $company->number }}</h5>
                        </div>
                    </center>
                </td>
            </tr>
        </table><br><br>
        <table class="full-width pt-5" style=" padding-top: 20px;">
            <tr>
                <td width="25%" class="amarillo p-3 font-sm font-bold">
                    Cotiazción Nº
                </td>
                <td width="20%" class="pl-3 font-sm font-bold">

                    <center>{{ $tittle }}</center>
                </td>
                <td width="55%" class=" font-sm  text-center amarillo">
                    <h5 class="text-center font-sm font-bold">Fecha: {{ $document->date_of_issue->format('Y-m-d') }}
                    </h5>
                    {{-- <h3 class="text-center">{{ $tittle }}</h3> --}}
                </td>
            </tr>
        </table>
        <table class="full-width mt-5">
            <tr>
                <td width="16%" class="amarillo p-1 font-bold">Empresa:</td>
                <td width="45%" class=" p-1">{{ $customer->name }}</td>
                <td class="amarillo p-1 font-bold">{{ $customer->identity_document_type->description }}:</td>
                <td class=" p-1">{{ $customer->number }}</td>
            </tr>
            <tr>
                <td class="align-top amarillo p-1 font-bold">Vendedor:</td>
                <td colspan="">
                    @if ($document->seller->name)
                        {{ $document->seller->name }}
                    @else
                        {{ $document->user->name }}
                    @endif
                </td>
                <td class="align-center amarillo p-1 font-bold">Condición de Pago:</td>
                @if ($document->payment_method_type && isset($document->payment_method_type->description))
                    {{ $document->payment_method_type->description }}
                @else
                    Contado
                @endif
                </td>
            </tr>
            <tr>
                <td class="align-top amarillo p-1 font-bold">Contacto:</td>
                <td colspan="">
                    @if ($document->contact)
                        {{ $document->contact }}
                    @endif
                </td>
                <td class="align-center amarillo p-1 font-bold">Tiempo de validez:</td>
                <td colspan="">{{ $document->date_of_due }}</td>
            </tr>
            <tr>
                <td class="align-top amarillo p-1 font-bold">Teléfono:</td>
                <td colspan="">
                    @if ($document->phone)
                        {{ $document->phone }}
                    @endif
                </td>
                <td class="align-center amarillo p-1 font-bold">Tiempo de entrega:</td>
                <td colspan="">{{ $document->delivery_date }}</td>
            </tr>
            <tr>
                <td class="align-top amarillo p-1 font-bold">Información:</td>
                <td colspan="3">
                    @if ($document->referential_information)
                        {{ $document->referential_information }}
                    @endif
                </td>
                {{-- <td class="align-center amarillo p-1 font-bold"></td>
        <td colspan="">{{ $document->delivery_date }}</td> --}}
            </tr>
            {{--     @if ($customer->address !== '')
    <tr>
         <td class="align-center amarillo p-1 font-bold">EMAIL:</td>
        <td colspan="">{{ $customer->email }}</td>
            <td width="" class="amarillo p-1 font-bold">Tiempo de entrega:</td>
            <td width="">{{ $document->delivery_date }}</td>
        <td class="align-top amarillo p-1 font-bold">Vendedor:</td>
        <td colspan="">
            @if ($document->seller->name)
                {{ $document->seller->name }}
            @else
                {{ $document->user->name }}
            @endif
        </td>
    </tr>
    @endif --}}

            <tr>
                {{-- <td class="align-top amarillo p-1 font-bold">Teléfono:</td>
        <td colspan="">{{ $customer->telephone }}</td> --}}
            </tr>

        </table>

        @if ($document->description)
            <table class="full-width mt-3">
                <tr>
                    <td width="15%" class="align-top">Observación: </td>
                    <td width="85%">{{ $document->description }}</td>
                </tr>
            </table>
        @endif


        <h4 class=" text-left font-md" style="line-height: 1.2; ">Por medio de la presente reciba usted nuestros más
            cordiales saludos, y a su vez hacerle llegar la Cotización solicitada de nuestros productos.</h4>
        <table class="full-width">
            <thead class="">
                <tr class="bg-greys amarillo">
                    <th class="border-box0 text-center py-2 font-md" width="8%">Item</th>
                    <th class="border-box0 text-center py-2">Descripción</th>
                    <th class="border-box0 text-center py-2">Imagen referencial</th>
                    <th class="border-box0 text-center py-2" width="8%">Cant.</th>
                    <th class="border-box0 text-center py-2" width="8%">Unid.</th>
                    <th class="border-box0 text-center py-2" width="12%">P.Unit</th>
                    <th class="border-box0 text-center py-2" width="12%">Sub. Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // dd($document->terms_condition);
                @endphp
                @foreach ($document->items as $row)
                    @php
                        // dd($row->attributes);
                        // dd(public_path("storage/uploads/items/{$row->item->attributes->image_small}"));
                        // dd($row->getPrintExtraData());
                        $extra_string = $row->getPrintExtraData();
                        // $imagen = $extra_string['image_url'];
                        // dd($extra_string['image_url']);
                        // dd($row_data->image);
                        $count++;
                    @endphp
                    <tr>
                        <td class="text-center amarillo border-box0">
                            {{ $count }}
                        </td>
                        <td class="text-center amarillo border-box0">
                            @if ($row->item->name_product_pdf ?? false)
                                {!! $row->item->name_product_pdf ?? '' !!}
                            @else
                                {!! $row->item->description !!}
                            @endif

                            @if ($row->attributes)
                                @foreach ($row->attributes as $attr)
                                    <br /><span style="font-size: 9px">{!! $attr->description !!} :
                                        {{ $attr->value }}</span>
                                @endforeach
                            @endif
                            @if ($row->discounts)
                                @foreach ($row->discounts as $dtos)
                                    <br /><span style="font-size: 9px">{{ $dtos->factor * 100 }}%
                                        {{ $dtos->description }}</span>
                                @endforeach
                            @endif

                            @if (isset($row->item->extra_attr_value) && $row->item->extra_attr_value != '')
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

                        </td>
                        <td class="text-center border-box0">
                            {{-- @foreach ($row->getPrintExtraData() as $row_data) --}}
                            <img src="data:{{ mime_content_type(public_path(parse_url($extra_string['image_url'], PHP_URL_PATH))) }};base64, {{ base64_encode(file_get_contents(public_path(parse_url($extra_string['image_url'], PHP_URL_PATH)))) }}"
                                alt="{{ $company->name }}" alt="" class="item_logo"
                                style="max-width: 150px;max-height: 150px; ">
                            {{-- alt="{{ $company->name }}" alt="" class="item_logo"
                                style="max-width: 150px;"> --}}

                            {{-- @endforeach --}}
                        </td>
                        <td class="text-center border-box0">
                            @if ((int) $row->quantity != $row->quantity)
                                {{ $row->quantity }}
                            @else
                                {{ number_format($row->quantity, 0) }}
                            @endif
                        </td>
                        <td class="text-center border-box0">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                        <td class="text-right font-bold border-box0">
                            {{ $row->item->has_igv != 'true' ? number_format($row->unit_value, 2) : number_format($row->unit_price, 2) }}
                        </td>

                        <td class="text-right font-bold border-box0">
                            {{ $row->item->has_igv == 'true' ? number_format($row->total, 2) : number_format($row->total_value, 2) }}
                        </td>
                    </tr>
                    {{--  <tr>
            <td colspan="7" class="border-bottom"></td>
        </tr> --}}
                @endforeach
                @if ($document->total_exportation > 0)
                    <tr>
                        <td colspan="6" class="text-right font-bold">Op. Exportación:
                            {{ $document->currency_type->symbol }}</td>
                        <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
                    </tr>
                @endif
                @if ($document->total_free > 0)
                    <tr>
                        <td colspan="6" class="text-right font-bold">Op. Gratuitas:
                            {{ $document->currency_type->symbol }}</td>
                        <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
                    </tr>
                @endif
                @if ($document->total_unaffected > 0)
                    <tr>
                        <td colspan="6" class="text-right font-bold">Op. Inafectas:
                            {{ $document->currency_type->symbol }}</td>
                        <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
                    </tr>
                @endif
                @if ($document->total_exonerated > 0)
                    <tr>
                        <td colspan="6" class="text-right font-bold">Op. Exoneradas:
                            {{ $document->currency_type->symbol }}</td>
                        <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
                    </tr>
                @endif
                @if ($document->total_taxed > 0)
                    <tr>
                        <td colspan="6" class="text-right font-bold">Op. Gravadas:
                            {{ $document->currency_type->symbol }}</td>
                        <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
                    </tr>
                @endif
                @if ($document->total_discount > 0)
                    <tr>
                        <td colspan="6" class="text-right font-bold">
                            {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento TOTAL' }}:
                            {{ $document->currency_type->symbol }}</td>
                        <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="6" class="text-right font-bold">IGV: {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="text-right font-bold">Total a pagar:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
                </tr>
            </tbody>
        </table>
        <table class="full-width" style="padding: 0; margin:0">
            <tr>
                <td width="100%" style="text-align: top; vertical-align: top;">
                    <br>
                    <table class="full-width" style="border:#000 solid 1px">
                        <thead class="">
                            <tr class=""
                                style="background-color: #464646; color: #ffffff!important; width: 100px;">
                                <th class=" text-center py-2" width="8%"
                                    style="color: #ffffff!important; font-size: 10px;">Banco</th>
                                <th class=" text-center py-2" width="50px"
                                    style="color: #ffffff!important; font-size: 10px;">Moneda</th>
                                <th class=" text-center py-2" width="8%"
                                    style="color: #ffffff!important; font-size: 10px;">Cta. Cte.</th>
                                <th class=" text-center py-2" width="8%"
                                    style="color: #ffffff!important; font-size: 10px;">Cuenta Interbancaria (CCI)</th>
                            </tr>
                        </thead>.
                        <tbody>

                            @foreach ($accounts as $account)
                                <tr>
                                    <td width="100%" class="text-center">{{ $account->bank->description }}</td>
                                    <td width="100%" class="text-center">{{ $account->currency_type->description }}
                                    </td>
                                    <td width="100%" class="text-center">{{ $account->number }}</td>
                                    <td width="100%" class="text-center">{{ $account->cci }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
        </table>
        <table class="full-width">
            <tr>
                <td class="">
                    {!! $document->terms_condition !!}
                </td>
            </tr>
            <br><br>
        </table>

        {{-- {!!$document->terms_condition!!} --}}
    </div>
</body>

</html>
