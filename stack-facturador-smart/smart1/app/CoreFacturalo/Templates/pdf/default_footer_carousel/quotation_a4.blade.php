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

    $customer = $document->customer;
    $accounts = \App\Models\Tenant\BankAccount::all();
    $tittle = $document->prefix . '-' . str_pad($document->number ?? $document->id, 8, '0', STR_PAD_LEFT);

    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
    use App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate;
@endphp
<html>

<head>
</head>

<body>
    {{-- <table class="full-width">
        <tr>
            @if ($company->logo)
                <td width="30%">
                    <table style="width: 100%; height: 100%;">
                        <tr>
                            <td class="company_logo_box">
                                <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                                    alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                            </td>
                        </tr>
                    </table>
                </td>
            @else
                <td width="30%">
                </td>
            @endif
            <td width="50%" class="pl-3" valign="top">
                <table>
                <tr>
                    <td class="text-center">
                        <h3 class="font-bold">{{ $company->name }}</h3>
                        <h5 style="text-transform: uppercase;">
                            {{ $establishment->address !== '-' ? $establishment->address : '' }}
                            {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                            {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                            {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                        </h5>
    
                        @isset($establishment->trade_address)
                            <h6>{{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                            </h6>
                        @endisset
                    
    
                        <h6>{{ $establishment->email !== '-' ? $establishment->email : '' }}</h6>
    
                        @isset($establishment->web_address)
                            <h6>{{ $establishment->web_address !== '-' ? 'Web: ' . $establishment->web_address : '' }}</h6>
                        @endisset
                        <h6>{{ $establishment->telephone !== '-' ? 'Cel.: ' . $establishment->telephone : '' }}
                        </h6>
    
        
                </td>
                </tr>
                </table>
            </td>
            <td width="20%" class="text-center ">
                <table>
                    <tr>
                            <th
                            class="border-box w-100 border-box border-radius
                            bg-black 
                            "
                            >

                            </th>
                    </tr>
                </table>
            
            </td>
        </tr>
    </table> --}}
    <div class="w-100" style="height: 100px;">
        <div style="float:left;width:30%;">
            <div class="company-logo text-center">
                <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                    alt="{{ $company->name }}" class="company_logo" style="max-width: 250px;">
            </div>
        </div>
        <div style="float:left;width:40%;">
            <div class="text-center">
                <h2 class="font-bold">{{ $company->name }}</h2>
                <h4 style="text-transform: uppercase;">
                    {{ $establishment->address !== '-' ? $establishment->address : '' }}
                    {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                    {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                    {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                </h4>

                @isset($establishment->trade_address)
                    <h5>{{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                    </h5>
                @endisset


                @isset($establishment->aditional_information)
                        <h5>{{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
                        </h5>
                @endisset

                @isset($establishment->web_address)
                    <h5>{{ $establishment->web_address !== '-' ? ' ' . $establishment->web_address : '' }}</h5>
                @endisset
                <h5>{{ $establishment->telephone !== '-' ? 'CEL. ' . $establishment->telephone : '' }}
                </h5>
            </div>
        </div>
        <div style="float:left;width:5%;">
            <br>
        </div>
        <div style="float:left;width:25%;">
            <div class="w-100 border-box border-radius text-center" style="height: 100px">
                <h2 class="text-center mt-2">
                    RUC: {{ $company->number }}
                </h2>
                <h3 class="text-center w-100 bg-black">{{ get_document_name('quotation', 'COTIZACIÓN') }}</h3>
                <h2 class="text-center mt-2">{{ $tittle }}</h2>
            </div>
        </div>
    </div>
    <div class="border-box border-radius w-100 mt-1">
        <table class="w-100">
            <tr>
                <td class="bb" width="60%" valign="top">
                    <table class="w-100 ctn">
                        <tr>
                            <td colspan="2">
                                <strong>Señores:</strong>
                                {{ $customer->name }}
                            </td>

                        </tr>
                        <tr>

                            <td colspan="2">
                                <strong>Dirección</strong>
                                {{ $customer->address }}
                                {{ $customer->district_id !== '-' ? ', ' . $customer->district->description : '' }}
                                {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                                {{ $customer->department_id !== '-' ? '- ' . $customer->department->description : '' }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                <strong>RUC:</strong>
                                {{ $customer->number }}
                            </td>
                            <td>
                                <strong>Teléfono:</strong>
                                {{ $customer->telephone }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Atención:</strong>


                            </td>
                            <td>
                                <strong>Correo:</strong>
                                {{ $customer->email }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="bl bb" width="40%" valign="top">
                    <table class="w-100 ctn">
                        <tr>
                            <td colspan="2">
                                <strong>Fecha:</strong>
                                {{ $document->date_of_issue->format('Y-m-d') }}
                            </td>

                        </tr>
                        <tr>

                            <td colspan="2">
                                <strong>Moneda:</strong>
                                {{ $document->currency_type->description }}
                            </td>
                        </tr>
                        <tr>

                            <td colspan="2">
                                <strong>Referencia:</strong>
                                {{ $document->referential_information }}
                            </td>
                        </tr>
                        <tr>

                            <td colspan="2">
                                <strong>Forma de pago:</strong>
                                {{ $document->payment_method_type->description }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="" valign="top">
                    <table class="w-100 ctn">
                        <tr>
                            <td width="50%">
                                <strong>Tiempo de entrega:</strong>
                                {{ $document->delivery_date }}

                            </td>
                            <td>
                                <strong>Válidez de oferta:</strong>
                                {{ $document->date_of_due ? $document->date_of_due : '' }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <strong>Lugar de entrega:</strong>
                                {{ $document->shipping_address }}
                            </td>

                        </tr>
                        <tr>

                            <td colspan="2">
                                <strong>Observación</strong>
                                {{ $document->description }}
                            </td>
                        </tr>

                    </table>
                </td>
                <td class="bl" valign="top">

                    <table class="w-100 ctn">
                        <tr>
                            <td colspan="2">
                                <strong>Vendedor:</strong>
                                {{ $document->user->name }}
                            </td>

                        </tr>
                        <tr>

                            <td colspan="2">
                                <strong>Cargo:</strong>

                            </td>
                        </tr>
                        <tr>

                            <td colspan="2">
                                <strong>Correo:</strong>
                                {{ $document->user->email }}

                            </td>
                        </tr>
                        <tr>

                            <td colspan="2">
                                <strong>Télefono:</strong>
                                {{ $document->user->telephone }}

                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    {{-- <table class="full-width mt">
        <tr>
            <td width="15%">Cliente:</td>
            <td width="45%">{{ $customer->name }}</td>
            <td width="25%">Fecha de emisión:</td>
            <td width="15%">{{ $document->date_of_issue->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td>{{ $customer->identity_document_type->description }}:</td>
            <td>{{ $customer->number }}</td>
            @if ($document->date_of_due)
                <td width="25%">Tiempo de Validez:</td>
                <td width="15%">{{ $document->date_of_due }}</td>
            @endif
        </tr>
        @if ($customer->address !== '')
            <tr>
                <td class="align-top">Dirección:</td>
                <td colspan="">
                    {{ $customer->address }}
                    {{ $customer->district_id !== '-' ? ', ' . $customer->district->description : '' }}
                    {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                    {{ $customer->department_id !== '-' ? '- ' . $customer->department->description : '' }}
                </td>
                @if ($document->delivery_date)
                    <td width="25%">Tiempo de Entrega:</td>
                    <td width="15%">{{ $document->delivery_date }}</td>
                @endif
            </tr>
        @endif
        @if ($document->payment_method_type)
            <tr>
                <td class="align-top">T. Pago:</td>
                <td colspan="">
                    {{ $document->payment_method_type->description }}
                </td>
                @if ($document->sale_opportunity)
                    <td width="25%">O. Venta:</td>
                    <td width="15%">{{ $document->sale_opportunity->number_full }}</td>
                @endif
            </tr>
        @endif
        @if ($document->account_number)
            <tr>
                <td class="align-top">N° Cuenta:</td>
                <td colspan="3">
                    {{ $document->account_number }}
                </td>
            </tr>
        @endif
        @if ($document->shipping_address)
            <tr>
                <td class="align-top">Dir. Envío:</td>
                <td colspan="3">
                    {{ $document->shipping_address }}
                </td>
            </tr>
        @endif
        @if ($customer->telephone)
            <tr>
                <td class="align-top">Teléfono:</td>
                <td colspan="3">
                    {{ $customer->telephone }}
                </td>
            </tr>
        @endif
        <tr>
            <td class="align-top">Vendedor:</td>
            <td colspan="3">
                @if ($document->seller->name)
                    {{ $document->seller->name }}
                @else
                    {{ $document->user->name }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="align-top">
                {{ $document->quotations_optional }}:</td>
            <td colspan="3">

                {{ $document->quotations_optional_value }}

            </td>
        </tr>

        @if ($document->contact)
            <tr>
                <td class="align-top">Contacto:</td>
                <td colspan="3">
                    {{ $document->contact }}
                </td>
            </tr>
        @endif
        @if ($document->phone)
            <tr>
                <td class="align-top">Telf. Contacto:</td>
                <td colspan="3">
                    {{ $document->phone }}
                </td>
            </tr>
        @endif
    </table> --}}


    {{-- 
    @if ($document->guides)
        <table>
            @foreach ($document->guides as $guide)
                <tr>
                    @if (isset($guide->document_type_description))
                        <td>{{ $guide->document_type_description }}</td>
                    @else
                        <td>{{ $guide->document_type_id }}</td>
                    @endif
                    <td>:</td>
                    <td>{{ $guide->number }}</td>
                </tr>
            @endforeach
        </table>
    @endif --}}

    <table class="full-width  border-box mt-1">
        <thead class="">
            <tr class="bg-black">
                <th class="text-white text-center py-2" width="5%">Item</th>
                <th class="text-white text-center py-2" width="63%">DESCRIPTION</th>
                <th class="text-white text-center py-2" width="8%">UM</th>
                <th class="text-white text-center py-2" width="8%">CANT.</th>
                <th class="text-white text-center py-2" width="8%">PRECIO</th>
                <th class="text-white text-center py-2" width="8%">TOTAL</th>
            </tr>
        </thead>
        @php

            $all_items = $document->items->count();
            $rest_items_count = 34 - $all_items;
            // $fakeItems = collect(range(1, 36))->map(function ($i) {
            //     return (object) [
            //         'item' => (object) [
            //             'name_product_pdf' => "Producto $i",
            //             'description' => "Descripción del producto $i",
            //             'unit_type_id' => 'unit',

            //             'is_set' => 0,
            //             'info_link' => null,
            //         ],
            //         'attributes' => collect([]),
            //         'discounts' => collect([]),
            //         'quantity' => rand(1, 10),
            //         'unit_price' => rand(10, 100),
            //         'total' => rand(100, 1000),
            //     ];
            // });
        @endphp
        <tbody>
            @foreach ($document->items as $idx => $row)
                <tr>

                    <td class="text-center align-top br">
                        {{ $idx + 1 }}
                    </td>

                    </td>
                    <td class="text-left br text-upp">
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
                    <td class="text-center align-top br">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="text-center align-top br">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif

                    <td class="text-right align-top br">{{ number_format($row->unit_price, 2) }}</td>

                    <td class="text-right align-top br">{{ number_format($row->total, 2) }}</td>
                </tr>
                <tr>
                    <td class="br"></td>
                    <td class="br"></td>
                    <td class="br"></td>
                    <td class="br"></td>
                    <td class="br"></td>
                    <td class="br"></td>
                </tr>
            @endforeach
            @for ($idx = 0; $idx < $rest_items_count; $idx++)

            <tr>

                <td class="text-center align-top br">
                    <br>
                </td>

                </td>
                <td class="text-left br text-upp">
                

                </td>
                <td class="text-center align-top br"></td>
                <td class="text-center align-top br">
                    

                <td class="text-right align-top br"></td>

                <td class="text-right align-top br"></td>
            </tr>
            <tr>
                <td class="br"></td>
                <td class="br"></td>
                <td class="br"></td>
                <td class="br"></td>
                <td class="br"></td>
                <td class="br"></td>
            </tr>
        @endfor
        </tbody>
    </table>
    <div>
        <div style="float: left;width:50%">
            @php
                $value = \App\CoreFacturalo\Helpers\Number\NumberLetter::convertToLetter($document->total);
            @endphp
            <span class="font-bold text-upp">{{ $value }}</span>
        </div>
        <div style="float: left;width:50%" class=" text-end">
            <span class="text-danger font-sm">
                Los precios unitarios de los productos INCLUYEN IGV
            </span>
        </div>
    </div>
    <div>
        <div style="float:left;width:55%">
            <div class="border-radius border-box p-2" >
                <table class="w-100">
                    <tbody>
                        <tr>
                            <td class="font-bold text-upp text-center" colspan="2" >
                                {{ $company->name }}
                            </td>
                        </tr>
                        @foreach ($accounts as $account)
                            <tr>
                                <td class="text-upp font-bold bb" colspan="2">
                                    {{ $account->bank->description }} - {{ $account->currency_type->description }}
                                </td>
                            </tr>
                            <tr>
                                <td>

                                    <span class="font-bold">N°:</span> {{ $account->number }}

                                </td>
                                <td>
                                    @if ($account->cci)
                                        <span class="font-bold">CCI:</span> {{ $account->cci }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
        <div style="float:left;width:15%">
            <br>
        </div>
        <div style="float:left;width:30%">
            <table class="w-100 border-box">
                <tr>
                    <td class="bg-black font-lg font-bold p-3" width="60%">
                        SUB TOTAL
                    </td>
                    <td class="bg-black font-lg font-bold p-3" width="10%">{{ $document->currency_type->symbol }}
                    </td>
                    <td width="30%" class=" font-lg font-bold p-3 bb text-end">{{ $document->total_value }}</td>
                </tr>
                <tr>
                    <td class="bg-black font-lg font-bold p-3" width="60%">
                        IGV 18%
                    </td>
                    <td class="bg-black font-lg font-bold p-3" width="10%">{{ $document->currency_type->symbol }}
                    </td>
                    <td width="30%" class=" font-lg font-bold p-3 bb text-end">{{ $document->total_taxes }}</td>
                </tr>
                <tr>
                    <td class="bg-black font-lg font-bold p-3" width="60%">
                        TOTAL GENERAL
                    </td>
                    <td class="bg-black font-lg font-bold p-3" width="10%">{{ $document->currency_type->symbol }}
                    </td>
                    <td width="30%" class=" font-lg font-bold p-3 bb text-end">{{ $document->total }}</td>
                </tr>
            </table>
        </div>
    </div>
    {{-- <table>
        <tbody>
            @if ($document->total_exportation > 0)
                <tr>
                    <td colspan="5" class="text-right font-bold">Op. Exportación:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_free > 0)
                <tr>
                    <td colspan="5" class="text-right font-bold">Op. Gratuitas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_unaffected > 0)
                <tr>
                    <td colspan="5" class="text-right font-bold">Op. Inafectas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_exonerated > 0)
                <tr>
                    <td colspan="5" class="text-right font-bold">Op. Exoneradas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_taxed > 0)
                <tr>
                    <td colspan="5" class="text-right font-bold">Op. Gravadas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_discount > 0)
                <tr>
                    <td colspan="5" class="text-right font-bold">
                        {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento TOTAL' }}:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="5" class="text-right font-bold">IGV: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right font-bold">Total a pagar: {{ $document->currency_type->symbol }}
                </td>
                <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
            </tr>
            @if ($document->currency_type->id == 'USD')
                <tr>
                    <td colspan="5" class="text-right font-bold">Total a pagar: S/
                    </td>
                    <td class="text-right font-bold">
                        {{ number_format($document->total * $document->exchange_rate_sale, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table> --}}
    {{-- <table class="full-width">
        <tr>
            <td width="65%" style="text-align: top; vertical-align: top;">
                <br>
                @foreach ($accounts as $account)
                    <p>
                        <span class="font-bold">{{ $account->bank->description }}</span>
                        {{ $account->currency_type->description }}
                        <span class="font-bold">N°:</span> {{ $account->number }}
                        @if ($account->cci)
                            - <span class="font-bold">CCI:</span> {{ $account->cci }}
                        @endif
                    </p>
                @endforeach
            </td>
        </tr>
        <tr>
        
        </tr>
    </table> --}}
    {{-- <br> --}}
    {{-- <table class="full-width">
        <tr>
            <td width="80%">
                <table class="full-width">
                    <tr>
                        <td width="80%">
                            <strong>Pagos:</strong>
                        </td>
                        <td width="20%"></td>
                    </tr>
                    @php
                        $payment = 0;
                    @endphp
                    @foreach ($document->payments as $row)
                        <tr>
                            <td>- {{ $row->payment_method_type->description }} -
                                {{ $row->reference ? $row->reference . ' - ' : '' }}
                                {{ $document->currency_type->symbol }} {{ $row->payment }}</td>
                            <td></td>
                        </tr>
                        @php
                            $payment += (float) $row->payment;
                        @endphp
                    @endforeach
                    <tr>
                        <td><strong>Saldo:</strong> {{ $document->currency_type->symbol }}
                        <td></td>
                    </tr>
                </table>
            </td>
            <td width="20%">
                <table class="full-width">
                    @php
                        $text = join('|', [
                            $company->number,
                            $document->prefix,
                            $document->prefix,
                            $document->id,
                            $document->total_igv,
                            $document->total,
                            $document->date_of_issue->format('Y-m-d'),
                            $customer->identity_document_type_id,
                            $customer->number,
                            $document->hash,
                        ]);
                        if (substr($text, -1) !== '|') {
                            $text .= '|';
                        }
                        $qrCode = new QrCodeGenerate();
                        $qr = $qrCode->displayPNGBase64($text);
                    @endphp
                    <tr>
                        <td width="80%">
                            @if ($qr)
                                <img src="data:image/png;base64, {{ $qr }}"
                                    style="margin-right: -10px; width: 120px" />
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

    </table> --}}
</body>

</html>
