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
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $accounts = \App\Models\Tenant\BankAccount::all();
    $tittle = $document->prefix . '-' . str_pad($document->number ?? $document->id, 8, '0', STR_PAD_LEFT);

    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
    $cont_ = 0;
    $rubrica = "storage/uploads/firms/{$company->img_firm}";
    //calculate items
    $allowed_items = 35 - \App\Models\Tenant\BankAccount::all()->count() * 3;
    $quantity_items = $document->items()->count();
    $cycle_items = $allowed_items - $quantity_items * 3;
    $total_weight = 0;

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

@endphp
<html>

<head>
    {{-- <title>{{ $tittle }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>

    <div class="header">
        <div class="text-left float-left header-logo">

            @if ($establishment->logo ?? false)
                <div class=" text-left">
                    <img src="{{ public_path($establishment->logo) }}" alt="{{ $company->name }}" class="company_logo_rec"
                        style="width:100%; max-height: 150px;">
                </div>
            @else
                @if ($company->logo)
                    <div class=" text-left">
                        <img src="{{ public_path("storage/uploads/logos/{$company->logo}") }}" alt="{{ $company->name }}"
                            class="company_logo_rec" style="width:100%; max-height: 150px">
                    </div>
                @else
                    <img src="{{ asset('logo/logo.jpg') }}" class="company_logo_rec"
                        style="width: 100%; max-height: 150px">
                @endif
            @endif
        </div>
        <div class="text-center float-left header-company">

            <h4 style="margin: 0;padding:0;">{{ $company_name }}</h4>
            @if ($company_owner)
                De: {{ $company_owner }}<br>
            @endif
            @isset($establishment->aditional_information)
                <div><strong></strong>
                    {{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}</div>
                <br>
            @endisset
            @isset($establishment->trade_address)
                <h6>{{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}</h6>
            @endisset
            <div class="text-uppercase mayus">

            </div>
            <div class="text-center">
                <div style="text-transform: capitalize;">

                    {{ $establishment->address !== '-' ? $establishment->address . ',' : '' }}
                    {{ $establishment->district_id !== '-' ? $establishment->district->description : '' }}
                    {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                    {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}<br>
                </div>
                {{ $establishment->telephone !== '-' ? 'Telf. ' . $establishment->telephone : '' }}<br>
                {{ $establishment->email !== '-' ? 'Email: ' . $establishment->email : '' }}<br>
                {{ $establishment->web_address !== '-' ? 'Web: ' . $establishment->web_address : '' }}
            </div>



        </div>
        <div class="text-center float-left header-number_rec py-3 font-bold font-xlg">
            <div style="margin-top: 8px">RUC {{ $company->number }}</div>

            <div style="margin-top: 10px">Cotización</div>

            <div style="margin-top: 10px">Nº {{ $tittle }}</div>
        </div>
    </div>


    <div style="height: 5px;"></div>
    <div class="information mt-10 no_pad_mar">
        <div class="div-table no_pad_mar">
            <div class="div-table-col w-100 div-table" style="margin: 0px; padding: 0px">
                <div class=" border_redondo align-center" style="height:80px">

                    <div class="div-table-col w-14 font-bold font-xs margin_b_8 desc">Cliente</div>
                    <div class="div-table-col w-54 font-xs margin_b_8 align-center desc">: {{ $customer->name }}</div>
                    <div class="div-table-col w-15 font-bold font-xs margin_b_8 desc">Fecha de emisión</div>
                    <div class="div-table-col w-15 font-xs margin_b_8 align-center desc">:
                        {{ $document->date_of_issue->format('Y-m-d') }}</div>

                    <div class="div-table-col w-14 font-bold font-xs margin_b_8 desc">
                        {{ $customer->identity_document_type->description }}</div>
                    <div class="div-table-col w-54 font-xs margin_b_8 align-center desc">: {{ $customer->number }}
                    </div>
                    <div class="div-table-col w-15 font-bold font-xs margin_b_8 desc">Tiempo de Validez</div>
                    <div class="div-table-col w-15 font-xs margin_b_8 align-center desc">: @if ($document->date_of_due)
                            {{ $document->date_of_due }}
                        @endif &nbsp;</div>

                    <div class="div-table-col w-14 font-bold font-xs margin_b_8 desc">Dirección</div>
                    <div class="div-table-col w-54 font-xs margin_b_8 align-center desc">:
                        @if ($customer->address !== '')
                            {{ $customer->address }}
                        @endif
                    </div>

                    <div class="div-table-col w-15 font-bold font-xs margin_b_8 desc">Tiempo de Entrega</div>
                    <div class="div-table-col w-15 font-xs margin_b_8 align-center desc">: @if ($document->delivery_date)
                            {{ $document->delivery_date }}
                        @endif &nbsp;</div>


                    <div class="div-table-col w-14 font-bold font-xs margin_b_8 desc">&nbsp;</div>
                    <div class="div-table-col w-54 font-xs margin_b_8 desc">
                        @if ($customer->address !== '')
                            {{ $customer->district_id !== '-' ? '' . $customer->district->description : '' }}
                            {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                            {{ $customer->department_id !== '-' ? '- ' . $customer->department->description : '' }}
                        @endif
                        &nbsp;
                    </div>
                    <div class="div-table-col w-15 font-bold font-xs margin_b_8 desc">Moneda</div>
                    <div class="div-table-col w-15 font-xs margin_b_8 align-center desc">:
                        {{ $document->currency_type->description }}</div>


                    <div class="div-table-col w-14 font-bold font-xs margin_b_8 desc">Dir. Envío</div>
                    <div class="div-table-col w-54 font-xs margin_b_8 align-center desc">: @if ($document->shipping_address)
                            {{ $document->shipping_address }}
                        @endif &nbsp;</div>
                    <div class="div-table-col w-15 font-bold font-xs margin_b_8 desc">COND. PAGO</div>
                    <div class="div-table-col w-15 font-xs margin_b_8 align-center desc">:
                        {{ $document->payment_method_type->description }}
                    </div>

                    <div class="div-table-col w-14 font-bold font-xs margin_b_8 desc">Teléfono</div>
                    <div class="div-table-col w-20 font-xs margin_b_8 align-center desc">: @if ($customer->telephone)
                            {{ $customer->telephone }}
                        @endif &nbsp;</div>
                    <div class="div-table-col w-11 font-bold font-xs margin_b_8 desc">Contacto</div>
                    <div class="div-table-col w-23 font-xs margin_b_8 align-center desc">: @if ($document->contact)
                            {{ $document->contact }}
                        @endif &nbsp;</div>
                    <div class="div-table-col w-15 font-bold font-xs margin_b_8 desc">Vendedor</div>
                    <div class="div-table-col w-15 font-xs margin_b_8 align-center desc">: @if ($document->seller)
                            {{ $document->seller->name }}
                        @else
                            {{ $document->user->name }}
                        @endif &nbsp;</div>

                </div>
            </div>
        </div>
    </div>



    @if ($document->description)
        <table class="full-width mt-3">
            <tr>
                <td width="15%" class="align-top">Observación: </td>
                <td width="85%">{!! str_replace("\n", '<br/>', $document->description) !!}</td>
                {{-- <td width="85%">{{ $document->description }}</td> --}}
            </tr>
        </table>
    @endif


    <table class="full-width mt-2 mb-2">
        <thead class="">
            <tr class="">
                <th class="border-box desc text-center py-2" width="6%">Item</th>
                <th class="border-box desc text-center py-2" width="6%">Cant.</th>
                <th class="border-box desc text-center py-2" width="6%">U.M</th>
                <th class="border-box desc text-center py-2"width="44%">Descripción</th>
                <th class="border-box desc text-center py-2" width="10%">PRECIO <br> UNITARIO</th>
                <th class="border-box desc text-center py-2" width="10%">VALOR <br> UNITARIO</th>
                <th class="border-box desc text-center py-2" width="8%">DSCTO</th>
                <th class="border-box desc text-center py-2" width="10%">VALOR <br> VENTA</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
                @php
                    $cont_++;
                @endphp
                <tr>
                    <td class="desc border-left-right text-center align-top">{{ $cont_ }}</td>
                    <td class="desc border-left-right text-center align-top">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                    <td class="desc border-left-right text-center align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="desc border-left-right text-left align-top">
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
                    </td>
                    <td class="desc border-left-right text-center align-top">{{ number_format($row->unit_price, 2) }}
                    </td>
                    <td class="desc border-left-right text-center align-top">{{ number_format($row->unit_value, 2) }}
                    </td>
                    <td class="desc border-left-right text-center align-top">
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
                    <td class="desc border-left-right text-center align-top">{{ number_format($row->total, 2) }}</td>
                </tr>
            @endforeach

            @for ($i = 0; $i < $cycle_items; $i++)
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
                    @foreach (array_reverse((array) $document->legends) as $row)
                        @if ($row->code == '1000')
                            <p style="text-transform: uppercase;">Son: <span class="font-bold">{{ $row->value }}
                                    {{ $document->currency_type->description }}</span></p>
                            @if (count((array) $document->legends) > 1)
                                <p><span class="font-bold">Leyendas</span></p>
                            @endif
                        @else
                            <p> {{ $row->code }}: {{ $row->value }} </p>
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

                    <div class="div-table-col w-100 text-left no_pad_mar desc font-bold no_pad_mar">
                        {{ $document->hash }} </div>
                    <div class="div-table-col w-100 text-left no_pad_mar desc no_pad_mar">
                        <div class="font-bold">Cuentas Bancarias</div>
                        @foreach ($accounts as $account)
                            <div style="width: 40%; float: left" class="font-bold">{{ $account->bank->description }}
                                {{ $account->currency_type->description }}</div>
                            <div style="width: 60%; float: left">{{ $account->number }}
                                @if ($account->cci)
                                    <br><strong>CCI:</strong> {{ $account->cci }}
                                @endif
                            </div>
                        @endforeach
                        {{--@if (file_get_contents(public_path($rubrica)))
                            <div style="width: 100%; float: left; margin-top: 10px;">
                                <img src="data:{{ mime_content_type(public_path("{$rubrica}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$rubrica}"))) }}"
                                alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                            
                            </div>
                        @endif--}}
                        @if (isset($rubrica) && file_exists(public_path($rubrica)))
                            <div style="width: 100%; float: left; margin-top: 10px;">
                                <img src="data:{{ mime_content_type(public_path($rubrica)) }};base64, {{ base64_encode(file_get_contents(public_path($rubrica))) }}"
                                    alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                            </div>
                        @endif                
                    </div>

                </div>
                <div class="div-table-col w-39-2 text-center no_pad_mar border_redondo">

                    <div class="div-table-col w-45 text-left no_pad_mar border-box_right desc font-bold"
                        style="padding-left: 4px; padding-top: 2px; padding-bottom: 2px;">Total Ope. Gravadas </div>
                    <div class="div-table-col w-10 text-center no_pad_mar border-box_right desc font-bold"
                        style=" padding-top: 2px; padding-bottom: 2px;"> {{ $document->currency_type->symbol }} </div>
                    <div class="div-table-col w-40 text-right no_pad_mar border-box_bottom desc font-bold"
                        style="padding-right: 6px;  padding-top: 2px; padding-bottom: 2px;">
                        {{ number_format($document->total_taxed, 2) }} </div>

                    <div class="div-table-col w-45 text-left no_pad_mar border-box_right desc font-bold"
                        style="padding-left: 4px; padding-top: 2px; padding-bottom: 2px;">Total Ope. Inafectas </div>
                    <div class="div-table-col w-10 text-center no_pad_mar border-box_right desc font-bold"
                        style=" padding-top: 2px; padding-bottom: 2px;"> {{ $document->currency_type->symbol }} </div>
                    <div class="div-table-col w-40 text-right no_pad_mar border-box_bottom desc font-bold"
                        style="padding-right: 6px; padding-top: 2px; padding-bottom: 2px;">
                        {{ number_format($document->total_unaffected, 2) }} </div>

                    <div class="div-table-col w-45 text-left no_pad_mar border-box_right desc font-bold"
                        style="padding-left: 4px; padding-top: 2px; padding-bottom: 2px;">Total Ope. Exoneradas </div>
                    <div class="div-table-col w-10 text-center no_pad_mar border-box_right desc font-bold"
                        style=" padding-top: 2px; padding-bottom: 2px;"> {{ $document->currency_type->symbol }} </div>
                    <div class="div-table-col w-40 text-right no_pad_mar border-box_bottom desc font-bold"
                        style="padding-right: 6px; padding-top: 2px; padding-bottom: 2px;">
                        {{ number_format($document->total_exonerated, 2) }} </div>

                    <div class="div-table-col w-45 text-left no_pad_mar border-box_right desc font-bold"
                        style="padding-left: 4px; padding-top: 2px; padding-bottom: 2px;">Total Descuentos </div>
                    <div class="div-table-col w-10 text-center no_pad_mar border-box_right desc font-bold"
                        style=" padding-top: 2px; padding-bottom: 2px;"> {{ $document->currency_type->symbol }} </div>
                    <div class="div-table-col w-40 text-right no_pad_mar border-box_bottom desc font-bold"
                        style="padding-right: 6px; padding-top: 2px; padding-bottom: 2px;">
                        @if ($document->total_discount > 0)
                            {{ number_format($document->total_discount, 2) }}
                        @else
                            0.00
                        @endif
                    </div>

                    <div class="div-table-col w-45 text-left no_pad_mar border-box_right desc font-bold"
                        style="padding-left: 4px; padding-top: 2px; padding-bottom: 2px;">Total Ope. Gratuitas </div>
                    <div class="div-table-col w-10 text-center no_pad_mar border-box_right desc font-bold"
                        style=" padding-top: 2px; padding-bottom: 2px;"> {{ $document->currency_type->symbol }} </div>
                    <div class="div-table-col w-40 text-right no_pad_mar border-box_bottom desc font-bold"
                        style="padding-right: 6px; padding-top: 2px; padding-bottom: 2px;">
                        {{ number_format($document->total_free, 2) }} </div>

                    <div class="div-table-col w-45 text-left no_pad_mar border-box_right desc font-bold"
                        style="padding-left: 4px; padding-top: 2px; padding-bottom: 2px;">Total IGV 18% </div>
                    <div class="div-table-col w-10 text-center no_pad_mar border-box_right desc font-bold"
                        style=" padding-top: 2px; padding-bottom: 2px;"> {{ $document->currency_type->symbol }} </div>
                    <div class="div-table-col w-40 text-right no_pad_mar border-box_bottom desc font-bold"
                        style="padding-right: 6px; padding-top: 2px; padding-bottom: 2px;">
                        {{ number_format($document->total_igv, 2) }} </div>

                    <div class="div-table-col w-45 text-left no_pad_mar border-box_right2 desc font-bold"
                        style="padding-left: 4px; padding-bottom: 5px; padding-top: 5px;">IMPORTE TOTAL</div>
                    <div class="div-table-col w-10 text-center no_pad_mar border-box_right2 desc font-bold"
                        style="padding-bottom: 5px; padding-top: 5px;"> {{ $document->currency_type->symbol }} </div>
                    <div class="div-table-col w-40 text-right no_pad_mar border-box_bottoms desc font-bold"
                        style="padding-right: 6px; padding-bottom: 5px; padding-top: 5px;">
                        @if ($document->retention)
                            {{ number_format($document->total - $document->retention->amount, 2) }}
                        @else
                            {{ number_format($document->total, 2) }}
                        @endif
                    </div>


                </div>

                {{--  <div class="div-table-col w-100 text-left mt-2 mb-2">
                Representación impresa del Comprobante de Pago Electrónico.. <br>
                Consulte el documento en https://famavet.e.org.pe/buscar
            </div>

            <div class="div-table-col w-100 text-center font-bold desc mt-2 mb-2">
                POR FAVOR VERIFICAR LOS PRODUCTOS ENTREGADOS, LUEGO DE ESTO NO HABRA LUGAR A RECLAMOS NI DEVOLUCIONES.
            </div> --}}
            </div>
        </div>

    </div>
</body>

</html>
