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
    $accounts = \App\Models\Tenant\BankAccount::all();
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
    <table class="full-width mt-5">
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
        @if(isset($customer->location) && $customer->location!= '')
        <tr>
            <td class="align-top">Ubicación:</td>
            <td colspan="3">{{ $customer->location }}</td>
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
        @if ($document->quotations_optional)
        <tr>
            <td class="align-top">
                {{ $document->quotations_optional }}:</td>
            <td colspan="3">

                {{ $document->quotations_optional_value }}

            </td>
        </tr>
        @endif

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
    </table>

    <table class="full-width mt-3">
        @if ($document->description && !is_integrate_system())
        <tr>
    <td width="15%" class="align-top">Observación: </td>
    <td width="85%">
        {!! nl2br(e($document->description)) !!}
    </td>
</tr>
        @endif
    </table>

    @if ($document->guides)
        <br />
        {{-- <strong>Guías:</strong> --}}
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
    @endif

    <table class="full-width mt-10 mb-10">
        <thead class="">
            <tr class="bg-grey">
                <th class="border-top-bottom text-center py-2" width="8%">Codigo</th>
                <th class="border-top-bottom text-left py-2">Descripción</th>
                <th class="border-top-bottom text-center py-2" width="8%">Unidad</th>
                <th class="border-top-bottom text-right py-2" width="6%">Cant.</th>
                <th class="border-top-bottom text-right py-2" width="10%">V.v.u</th>
                <th class="border-top-bottom text-center py-2" width="10%">Dsto</th>
                <th class="border-top-bottom text-right py-2" width="10%">V.v.p</th>
                <th class="border-top-bottom text-right py-2" width="12%">Val. Venta</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
                @php
                    $brand = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getBrandFormItem($row);
                    
                @endphp
                <tr>
                    <td class="text-center align-top">
                    {{ $row->item->internal_id }}
                    </td>

                    <td class="text-left">
                        @if ($row->item->name_product_pdf ?? false)
                            {!! $row->item->name_product_pdf ?? '' !!}
                        @else
                            {!! $row->item->description !!}
                        @endif
                        @if ($configuration->name_pdf)
                        @php
                            $item_name = \App\Models\Tenant\Item::select('name')
                                ->where('id', $row->item_id)
                                ->first();
                        @endphp
                        
                        @if ($configuration->presentation_pdf && isset($row->item->presentation) && isset($row->item->presentation->description))
                            <div>
                                <span style="font-size: 9px">{{ $row->item->presentation->description }}</span>
                            </div>
                        @endif
                        @if ($item_name->name)
                        <div>
                            <span style="font-size: 9px">{{ $item_name->name }}</span>
                            </div>    
                        @endif
                    @endif
                        @if ($row->attributes)
                            @foreach ($row->attributes as $attr)
                                <br /><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                            @endforeach
                        @endif


                        @if ($row->item !== null && property_exists($row->item, 'extra_attr_value') && $row->item->extra_attr_value != '')
                            <br /><span style="font-size: 9px">{{ $row->item->extra_attr_name }}:
                                {{ $row->item->extra_attr_value }}</span>
                        @endif

                        @if ($row->item->is_set == 1 && $configuration->show_item_sets)
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
                    <td class="text-center align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="text-center align-top">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                    <td class="text-right align-top">
                        @php
                            $affectation_igv_type_id = $row->affectation_igv_type_id;
                            if ($affectation_igv_type_id >= 10 && $affectation_igv_type_id < 20) {
                                $unit_value = $row->unit_value * (1 + $row->percentage_igv / 100);
                            } else {
                                $unit_value = $row->unit_value;
                            }

                        @endphp
                        {{ number_format($unit_value, 2) }}</td>
                    <td class="text-right align-top">
                        @if ($row->discounts)
                                @foreach ($row->discounts as $dtos)
                                    <span style="">{{ number_format($dtos->factor * 100,2) }}%</span>
                                @endforeach
                        @endif
                    </td>
                    <td class="text-right align-top">{{ number_format($row->unit_price, 2) }}</td>
                    <td class="text-right align-top">{{ number_format($row->total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="8" class="border-bottom"></td>
                </tr>
            @endforeach
            @if ($document->total_exportation > 0)
                <tr>
                    <td colspan="7" class="text-right font-bold">Op. Exportación:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_free > 0)
                <tr>
                    <td colspan="7" class="text-right font-bold">Op. Gratuitas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_unaffected > 0)
                <tr>
                    <td colspan="7" class="text-right font-bold">Op. Inafectas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_exonerated > 0)
                <tr>
                    <td colspan="7" class="text-right font-bold">Op. Exoneradas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_taxed > 0)
                <tr>
                    <td colspan="7" class="text-right font-bold">Op. Gravadas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
                </tr>
            @endif

            <tr>
                <td colspan="7" class="text-right font-bold">IGV: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
            </tr>
            <tr>
                <td colspan="7" class="text-right font-bold">Total a pagar: {{ $document->currency_type->symbol }}
                </td>
                <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
            </tr>
            @if ($document->total_discount > 0)
                <tr>
                    <td colspan="7" class="text-right font-bold">
                        {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento total' }}:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">{{ number_format($document->total_discount * 1.18, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="full-width mt-3">
        @if ($document->description && is_integrate_system())
            <tr>
                <td style="font-weight: bold;text-transform:uppercase;" width="15%" class="align-top">Observación: </td>
                <td style="font-weight: bold;text-transform:uppercase;" width="85%">{!! str_replace("\n", '<br/>', $document->description) !!}</td>
                {{-- <td width="85%">{{ $document->description }}</td> --}}
            </tr>
        @endif
    </table>
    <table class="full-width">
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
