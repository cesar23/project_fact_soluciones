@php
    use App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate;
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
    $configuration = \App\Models\Tenant\Configuration::first();
    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

    $supplier = $document->supplier;
    $configurations = \App\Models\Tenant\Configuration::first();

    $payments = $document->payments;

    $company_name = $company->name;
    $company_owner = null;
    if ($configurations->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }
    $tittle = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $showSalePrice = $configuration->show_sale_price_pdf;
    $supplier_db = \App\Models\Tenant\Person::find($document->supplier_id);
    $supplier_name = $supplier_db->name;
    $supplier_number = $supplier_db->number;
    $supplier_ubigeo = $supplier_db->getUbigeoFullAttribute();

    $supplier_telephone = $supplier_db->telephone ?? '';
    $supplier_address = $supplier_db->address ?? '';
    $supplier_email = $supplier_db->email ?? '';
    $items = $document->items;
    $same_warehouse_id = $items->pluck('warehouse_id')->unique()->count() == 1;
    $warehouse_id_destination = null;
    $warehouse_destination = null;
    if($same_warehouse_id){
        $warehouse_id_destination = $items->first()->warehouse_id;
        $warehouse_destination = \App\Models\Tenant\Warehouse::where('id', $warehouse_id_destination)->first();
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
                        <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
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
                    <h4 class="">{{ $supplier_name }}</h4>
                    <h5>{{ 'RUC ' . $supplier_number }}</h5>
                    @if ($company_owner)
                        <h6 class="">De: {{ $company_owner }}</h6>
                    @endif
                    <h6 style="text-transform: uppercase;">
                        {{ $supplier_address !== '-' ? $supplier_address : '' }}
                        {{ $supplier_ubigeo !== '-' ? ', ' . $supplier_ubigeo : '' }}
                    </h6>


                    <h6>{{ $supplier_telephone !== '-' ? 'Central telefónica: ' . $supplier_telephone : '' }}</h6>

                    <h6>{{ $supplier_email !== '-' ? 'Email: ' . $supplier_email : '' }}</h6>


                </div>
            </td>
            <td width="30%" class="border-box py-4 px-2 text-center">
                <h5 class="text-center">{{ $document->document_type->description }}</h5>
                <h3 class="text-center">{{ $tittle }}</h3>
            </td>
        </tr>
    </table>
    <table class="full-width mt-5">
        <tr>
            <td width="15%">Cliente:</td>
            <td width="45%">{{ $company_name }}</td>
            <td width="25%">Fecha de emisión:</td>
            <td width="15%">{{ $document->date_of_issue->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td>{{ 'RUC' }}:</td>
            <td>{{ $company->number }}</td>
            @if ($document->date_of_due)
                <td width="25%">Fecha de vencimiento:</td>
                <td width="15%">{{ $document->date_of_due->format('Y-m-d') }}</td>
            @endif
        </tr>
        <tr>
            <td class="align-top">Dirección:</td>
            <td colspan="3">
                {{ $establishment->address !== '-' ? $establishment->address : '' }}
                {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
            </td>
        </tr>

        <tr>
            <td class="align-top">Usuario:</td>
            <td colspan="3">
                {{ $document->user->name }}
            </td>
        </tr>
        @if($warehouse_destination)
    <tr>
        <td class="align-top">Almacén:</td>
        <td colspan="3">{{ $warehouse_destination->description }}</td>
    </tr>
    @endif
        @if ($document->observation)
            <tr>
                <td class="align-top">Observación:</td>
                <td colspan="3">
                    {{ $document->observation }}
                </td>
            </tr>
        @endif
        @if ($document->purchase_order)
            <tr>
                <td class="align-top">O. Compra:</td>
                <td colspan="3">{{ $document->purchase_order->number_full }}</td>
            </tr>
        @endif
    </table>


    <table class="full-width mt-10 mb-10">
        <thead>
            <tr class="bg-grey">
                <th class="border-top-bottom text-center py-2" width="10%">Cant.</th>
                <th class="border-top-bottom text-center py-2" width="10%">Unidad</th>
                <th class="border-top-bottom text-center py-2" width="10%">Cod. Interno</th>
                <th class="border-top-bottom text-left py-2">Descripción</th>
                @if ($showSalePrice)
                    <th class="border-top-bottom text-right py-2" width="15%">P.Vent</th>
                @endif
                <th class="border-top-bottom text-right py-2" width="15%">P.Unit</th>
                <th class="border-top-bottom text-right py-2" width="10%">Dto.</th>
                <th class="border-top-bottom text-right py-2" width="15%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
                <tr>
                    <td class="text-center align-top">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                    <td class="text-center align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="text-center align-top">{{isset($row->item->internal_id) ? $row->item->internal_id : ''}}</td>
                    <td class="text-left">
                        {!! $row->item->description !!}
                        @isset($row->item->sizes_added)
                            @if (count($row->item->sizes_added) > 0)
                                @foreach ($row->item->sizes_added as $size)
                                    <br>
                                    <small> Talla {{ $size->size }} | {{ $size->stock }} und.</small>
                                @endforeach
                            @endif
                        @endisset
                        @if ($row->attributes)
                            @foreach ($row->attributes as $attr)
                                <br /><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                            @endforeach
                        @endif
                        @isset ($row->item->attributes)
                            @if(is_iterable($row->item->attributes))
                                @foreach ($row->item->attributes as $attr)
                                    <br /><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                                @endforeach
                            @endif
                        @endisset
                        @if ($row->discounts)
                            @foreach ($row->discounts as $dtos)
                                <br /><span style="font-size: 9px">{{ $dtos->factor * 100 }}%
                                    {{ $dtos->description }}</span>
                            @endforeach
                        @endif
                        @if(isset($row->item->idAttributeSelect))
                        <hr><br>
                        @foreach ($row->item->idAttributeSelect as $row_data)
                        Marca: {{ $row_data->attribute}}<br>
                        Año de Modelo:    {{ $row_data->attribute5}}<br>
                        Color:  {{ $row_data->attribute3}}<br>
                        Motor:  {{ $row_data->attribute4}}<br>
                        Modelo: {{ $row_data->attribute2}}<br>                                                                           
                        Serie / Chasis  {{ $row_data->chassis}}<br>
                        <hr><br>
                        @endforeach
                    @endif
                    </td>
                    @if ($showSalePrice)
                        <td class="text-right align-top">{{ number_format($row->item->sale_unit_price, 2) }}</td>
                    @endif
                    <td class="text-right align-top">{{ number_format($row->unit_price, 2) }}</td>
                    <td class="text-right align-top">
                        @if ($row->discounts)
                            @php
                                $total_discount_line = 0;
                                foreach ($row->discounts as $disto) {
                                    $total_discount_line += $disto->amount;
                                }
                            @endphp
                            {{ number_format($total_discount_line, 2) }}
                        @else
                            0.00
                        @endif
                    </td>
                    <td class="text-right align-top">{{ number_format($row->total, 2) }}</td>
                </tr>
                <tr>
                    <!-- <td colspan="6" class="border-bottom"></td> -->
                    <!-- @if ($showSalePrice)
-->

                    <td colspan="{{ $showSalePrice ? '7' : '6' }}" class="border-bottom"></td>
                    <!--
@endif -->

                </tr>
            @endforeach
            @if ($document->total_exportation > 0)
                <tr>
                    <td colspan="{{ $showSalePrice ? '7' : '6' }}" class="text-right font-bold">Op. Exportación:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_free > 0)
                <tr>
                    <td colspan="{{ $showSalePrice ? '7' : '6' }}" class="text-right font-bold">Op. Gratuitas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_unaffected > 0)
                <tr>
                    <td colspan="{{ $showSalePrice ? '7' : '6' }}" class="text-right font-bold">Op. Inafectas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_exonerated > 0)
                <tr>
                    <td colspan="{{ $showSalePrice ? '7' : '6' }}" class="text-right font-bold">Op. Exoneradas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_taxed > 0)
                <tr>
                    <td colspan="{{ $showSalePrice ? '7' : '6' }}" class="text-right font-bold">Op. Gravadas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_discount > 0)
                <tr>
                    <td colspan="{{ $showSalePrice ? '7' : '6' }}" class="text-right font-bold">
                        {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento TOTAL' }}:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="{{ $showSalePrice ? '7' : '6' }}" class="text-right font-bold">IGV:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
            </tr>
            <tr>
                <td colspan="{{ $showSalePrice ? '7' : '6' }}" class="text-right font-bold">Total a pagar:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($payments->count())
        <table class="full-width">
            <tr>
                <td>
                    <strong>Pagos:</strong>
                </td>
            </tr>
            @php
                $payment = 0;
            @endphp
            @foreach ($payments as $row)
                <tr>
                    <td>&#8226; {{ $row->payment_method_type->description }} -
                        {{ $row->reference ? $row->reference . ' - ' : '' }} {{ $document->currency_type->symbol }}
                        {{ $row->payment + $row->change }}</td>
                </tr>
            @endforeach
        </table>
    @endif
    <table class="full-width">
        @php
            $ruc = $supplier_number;
            $document_type_id = $document->document_type_id;
            $series = $document->series;
            $number = $document->number;
            $total_igv = $document->total_igv;
            $total = $document->total;
            $date_of_issue = $document->date_of_issue->format('Y-m-d');
            $txt_to_qr = "$ruc|$document_type_id|$series|$number|$total_igv|$total|$date_of_issue|x|x|x";
            $qrCode = new QrCodeGenerate();
            $qr = $qrCode->displayPNGBase64($txt_to_qr);
        @endphp
        <tr>

            <td width="80%" class="text-right">
                @if($qr)
                    <img src="data:image/png;base64, {{ $qr }}" style="margin-right: -10px; width: 120px"/>
                @endif
            </td>
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
</body>

</html>
