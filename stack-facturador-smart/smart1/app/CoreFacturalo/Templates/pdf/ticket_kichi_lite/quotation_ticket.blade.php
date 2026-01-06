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
    $customer = $document->customer;
    $invoice = $document->invoice;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $accounts = \App\Models\Tenant\BankAccount::all();
    $tittle = $document->prefix . '-' . str_pad($document->number ?? $document->id, 8, '0', STR_PAD_LEFT);
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
            <td class="text-center">
                <h5>{{ $company_name }}</h5>
            </td>
        </tr>
        @if ($company_owner)
            <tr>
                <td class="text-center">
                    <h5>De: {{ $company_owner }}</h5>
                </td>
            </tr>
        @endif
        <tr>
            <td class="text-center">
                <h5>{{ 'RUC ' . $company->number }}</h5>
            </td>
        </tr>
        <tr>
            <td class="text-center">
                

                @isset($establishment->trade_address)
                    <h6>{{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                    </h6>
                @endisset
                <h6>{{ $establishment->telephone !== '-' ? 'Central telefónica: ' . $establishment->telephone : '' }}
                </h6>

                
            </td>
        </tr>

        <tr>
            <td class="text-center pt-3 border-top">
                <h4>{{ get_document_name('quotation', 'Cotización') }}</h4>
            </td>
        </tr>
        <tr>
            <td class="text-center pb-3 border-bottom">
                <h3>{{ $tittle }}</h3>
            </td>
        </tr>
    </table>
    <table class="full-width">
        <tr>
            <td width="" class="pt-3">
                <p class="desc">F. Emisión:</p>
            </td>
            <td width="" class="pt-3">
                <p class="desc">{{ $document->date_of_issue->format('Y-m-d') }}</p>
            </td>
        </tr>

        @if ($document->date_of_due)
            <tr>
                <td width="" class="">
                    <p class="desc">T. Validez:</p>
                </td>
                <td width="" class="">
                    <p class="desc">{{ $document->date_of_due }}</p>
                </td>
            </tr>
        @endif

        @if ($document->delivery_date)
            <tr>
                <td width="" class="">
                    <p class="desc">T. Entrega:</p>
                </td>
                <td width="" class="">
                    <p class="desc">{{ $document->delivery_date }}</p>
                </td>
            </tr>
        @endif

        <tr>
            <td class="align-top">
                <p class="desc">Cliente:</p>
            </td>
            <td>
                <p class="desc">{{ $customer->name }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p class="desc">{{ $customer->identity_document_type->description }}:</p>
            </td>
            <td>
                <p class="desc">{{ $customer->number }}</p>
            </td>
        </tr>
        @if ($customer->address !== '')
            <tr>
                <td class="align-top">
                    <p class="desc">Dirección:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $customer->address }}
                        {{ $customer->district_id !== '-' ? ', ' . $customer->district->description : '' }}
                        {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                        {{ $customer->department_id !== '-' ? '- ' . $customer->department->description : '' }}
                    </p>
                </td>
            </tr>
        @endif
        @if (isset($customer->location) && $customer->location)
            <tr>
                <td class="align-top">
                    <p class="desc">Ubicación:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $customer->location }}
                    </p>
                </td>
            </tr>
        @endif
        @if ($document->shipping_address)
            <tr>
                <td class="align-top">
                    <p class="desc">Dir. Envío:</p>
                </td>
                <td colspan="3">
                    <p class="desc">
                        {{ $document->shipping_address }}
                    </p>
                </td>
            </tr>
        @endif

        @if ($customer->telephone)
            <tr>
                <td class="align-top">
                    <p class="desc">Teléfono:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $customer->telephone }}
                    </p>
                </td>
            </tr>
        @endif
        @if ($document->payment_method_type)
            <tr>
                <td class="align-top">
                    <p class="desc">T. Pago:</p>
                </td>
                <td>
                    <p class="desc">
                        Crédito
                    </p>
                </td>
            </tr>
        @endif

        @if ($document->account_number)
            <tr>
                <td class="align-top">
                    <p class="desc">N° Cuenta:</p>
                </td>
                <td colspan="">
                    <p class="desc">
                        {{ $document->account_number }}
                    </p>
                </td>
            </tr>
        @endif
        @if ($document->sale_opportunity)
            <tr>
                <td class="align-top">
                    <p class="desc">O. Venta:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $document->sale_opportunity->number_full }}
                    </p>
                </td>
            </tr>
        @endif
        <tr>
            <td class="align-top">
                <p class="desc">Vendedor:</p>
            </td>
            <td>
                <p class="desc">
                    @if ($document->seller->name)
                        {{ $document->seller->name }}
                    @else
                        {{ $document->user->name }}
                    @endif
                </p>
            </td>
        </tr>
        @if ($document->description && !is_integrate_system())
            <tr>
                <td class="align-top">
                    <p class="desc">Observación:</p>
                </td>
                <td>
                    <p class="desc">{!! str_replace("\n", '<br/>', $document->description) !!}</p>
                </td>
                {{-- <td><p class="desc">{{ $document->description }}</p></td> --}}
            </tr>
        @endif

        @if ($document->contact)
            <tr>
                <td class="align-top">
                    <p class="desc">Contacto:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->contact }}</p>
                </td>
            </tr>
        @endif
        @if ($document->phone)
            <tr>
                <td class="align-top">
                    <p class="desc">Telf. Contacto:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->phone }}</p>
                </td>
            </tr>
        @endif

        @if ($document->purchase_order)
            <tr>
                <td>
                    <p class="desc">Orden de compra:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->purchase_order }}</p>
                </td>
            </tr>
        @endif
        @if ($document->quotation_id)
            <tr>
                <td>
                    <p class="desc">Cotización:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->quotation->identifier }}</p>
                </td>
            </tr>
        @endif
    </table>

    <table class="full-width mt-10 mb-10 ticket">
        <thead class="">
            <tr>
                <th class="border-top-bottom desc-9 text-left">Cant.</th>
                <th class="border-top-bottom desc-9 text-left">Unidad</th>
                <th class="border-top-bottom desc-9 text-left">Código</th>
                <th class="border-top-bottom desc-9 text-left">P.Unit</th>
                <th class="border-top-bottom desc-9 text-left">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
            <tr>
                    <td class="text-left desc-9 align-top" colspan="5">
 
                        @if ($row->item->name_product_pdf ?? false)
                            {!! $row->item->name_product_pdf ?? '' !!}
                        @else
                            {!! $row->item->description !!}
                        @endif
                        @if ($configuration->presentation_pdf && isset($row->item->presentation) && isset($row->item->presentation->description))
                        <div>
                            <span >{{ $row->item->presentation->description }}</span>
                        </div>
                    @endif
                        @if ($row->attributes)
                            @foreach ($row->attributes as $attr)
                                <br />{!! $attr->description !!} : {{ $attr->value }}
                            @endforeach
                        @endif
                        @if ($row->discounts)
                            @foreach ($row->discounts as $dtos)
                                <br /><small>{{ $dtos->factor * 100 }}% {{ $dtos->description }}</small>
                            @endforeach
                        @endif
                        @if ($row->item->is_set == 1 && $configuration->show_item_sets)
                            <br>
                            @inject('itemSet', 'App\Services\ItemSetService')
                            @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                                {{ $item }}<br>
                            @endforeach
                        @endif
                        @if ($row->item !== null && property_exists($row->item, 'extra_attr_value') && $row->item->extra_attr_value != '')
                            <br /><span style="font-size: 9px">{{ $row->item->extra_attr_name }}:
                                {{ $row->item->extra_attr_value }}</span>
                        @endif
                    </td>
            </tr>
                <tr>
                    <td class="text-center desc-9 align-top">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                    <td class="text-center desc-9 align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="text-center desc-9 align-top">
                        
                        {{-- {{ $row->item->internal_id}} --}}
                        @php
                            $internal_id = $row->item->internal_id;
                            if(!$internal_id){
                                $item = \App\Models\Tenant\Item::select('internal_id')->find($row->item_id);
                                $internal_id = $item->internal_id;
                            }
                        @endphp
                        {{ $internal_id }}
                    </td>

                
                    <td class="text-right desc-9 align-top">{{ number_format($row->unit_price, 2) }}</td>
                    <td class="text-right desc-9 align-top">{{ number_format($row->total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="border-bottom"></td>
                </tr>
            @endforeach
            @if ($document->total_exportation > 0)
                <tr>
                    <td colspan="4" class="text-right font-bold desc">Op. Exportación:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold desc">{{ number_format($document->total_exportation, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_free > 0)
                <tr>
                    <td colspan="4" class="text-right font-bold desc">Op. Gratuitas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold desc">{{ number_format($document->total_free, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_unaffected > 0)
                <tr>
                    <td colspan="4" class="text-right font-bold desc">Op. Inafectas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold desc">{{ number_format($document->total_unaffected, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_exonerated > 0)
                <tr>
                    <td colspan="4" class="text-right font-bold desc">Op. Exoneradas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold desc">{{ number_format($document->total_exonerated, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_taxed > 0)
                <tr>
                    <td colspan="4" class="text-right font-bold desc">Op. Gravadas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold desc">{{ number_format($document->total_taxed, 2) }}</td>
                </tr>
            @endif
            
            <tr>
                <td colspan="4" class="text-right font-bold desc">IGV: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_igv, 2) }}</td>
            </tr>
            @if ($document->total_discount > 0)
            @php
                $total_subtotal = $document->total + $document->total_discount;
                $discounts = $document->discounts;
                $firstDiscount = null;
                $discount_type_id = null;

                if (is_object($discounts)) {
                    $discounts_array = (array) $discounts;
                    $firstDiscount = reset($discounts_array); // Obtiene el primer elemento
                } else {
                    $firstDiscount = $discounts[0];
                }

                if ($firstDiscount) {
                    $discount_type_id = $firstDiscount->discount_type_id;
                    if ($discount_type_id == '02') {
                        $total_subtotal =$firstDiscount->base * 1.18;
                    }
                }
            @endphp
            <tr>
                <td colspan="4" class="text-right font-bold desc">Subtotal:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($total_subtotal, 2) }}</td>
            </tr>
        @endif
        @if ($document->total_discount > 0)
        <tr>
            <td colspan="4" class="text-right font-bold desc">
                {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento total' }}:
                {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc">{{ number_format($document->total_discount, 2) }}</td>
        </tr>
    @endif
            <tr>
                <td colspan="4" class="text-right font-bold desc">Total a pagar:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @if ($document->description && is_integrate_system())
        <table class="full-width">
            <tr>
                <td class="align-top">
                    <p class="desc">Observación:</p>
                </td>
                <td>
                    <p class="desc">{!! str_replace("\n", '<br/>', $document->description) !!}</p>
                </td>
                {{-- <td><p class="desc">{{ $document->description }}</p></td> --}}
            </tr>
        </table>
    @endif


    <table class="full-width">
        <tr>
            <td class="desc pt-3">
                <strong>Pagos:</strong>
            </td>
        </tr>
        @php
            $payment = 0;
        @endphp
        @foreach ($document->payments as $row)
            <tr>
                <td class="desc ">- {{ $row->payment_method_type->description }} -
                    {{ $row->reference ? $row->reference . ' - ' : '' }} {{ $document->currency_type->symbol }}
                    {{ $row->payment }}</td>
            </tr>
            @php
                $payment += (float) $row->payment;
            @endphp
        @endforeach
        <tr>
            <td class="desc pt-3"><strong>Saldo:</strong> {{ $document->currency_type->symbol }}
                {{ number_format($document->total - $payment, 2) }}</td>
        </tr>

    
    </table>

    <table class="full-width">
        <tr>
            <td class="text-center">
                <h6 style="font-size: 10px; font-weight: bold;">Términos y condiciones del servicio</h6>
                {!! $configuration->terms_condition !!}
            </td>
        </tr>
    </table>



</html>
