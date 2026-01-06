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

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

    $customer = $document->customer;
    $invoice = $document->invoice;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $tittle = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
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
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();

    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
    $is_integrate_system = Modules\BusinessTurn\Models\BusinessTurn::isIntegrateSystem();
    $quotation = null;
    if ($is_integrate_system) {
        $quotation = \App\Models\Tenant\Quotation::select(['number', 'prefix', 'shipping_address'])
            ->where('id', $document->quotation_id)
            ->first();
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
            <td class="text-center" style="text-transform:uppercase;">{{ $company_name }}</td>
            @if ($company_owner)
                De: {{ $company_owner }}
            @endif
        </tr>
        <tr>
            <td class="text-center">{{ 'RUC ' . $company->number }}</td>
        </tr>
        

        @isset($establishment->trade_address)
            <tr>
                <td class="text-center ">
                    {{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}</td>
            </tr>
        @endisset
        <tr>
            <td class="text-center ">
                {{ $establishment->telephone !== '-' ? 'Central telefónica: ' . $establishment->telephone : '' }}</td>
        </tr>

        

        @isset($establishment->aditional_information)
            <tr>
                <td class="text-center pb-3">
                    {{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}</td>
            </tr>
        @endisset

        <tr>
            <td class="text-center">
                <h4>{{ get_document_name('sale_note', 'NOTA DE VENTA') }}</h4>
            </td>
        </tr>
        <tr>
            <td class="text-center">
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
        <tr>
            <td width="">
                <p class="desc">H. Emisión:</p>
            </td>
            <td width="">
                <p class="desc">{{ $document->time_of_issue }}</p>
            </td>
        </tr>
        @isset($invoice->date_of_due)
            <tr>
                <td>
                    <p class="desc">F. Vencimiento:</p>
                </td>
                <td>
                    <p class="desc">{{ $invoice->date_of_due->format('Y-m-d') }}</p>
                </td>
            </tr>
        @endisset
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
        @if ($quotation && $quotation->shipping_address)
            <tr>
                <td class="align-top">
                    <p class="desc">Dir. de envío:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $quotation->shipping_address }}
                    </p>
                </td>
            </tr>
        @endif
        @if (isset($customer->location) && $customer->location !== '')
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
        @if ($customer->telephone && $customer->telephone !== '')
            <tr>
                <td class="align-top">
                    <p class="desc">Teléfono:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ strtoupper($customer->telephone) }}
                    </p>
                </td>
            </tr>
        @endif
    

    
        @if ($document->plate_number !== null)
            <tr>
                <td class="align-top">
                    <p class="desc">N° Placa:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->plate_number }}</p>
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
        @if ($document->observation && !is_integrate_system())
            <tr>
                <td>
                    <p class="desc">Observación:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->observation }}</p>
                </td>
            </tr>
        @endif
        @if ($document->reference_data)
            <tr>
                <td class="align-top">
                    <p class="desc">D. Referencia:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $document->reference_data }}
                    </p>
                </td>
            </tr>
        @endif
        @if ($document->quotation_id)
            <tr>
                <td>
                    <p class="desc font-bold">Cotización:</p>
                </td>
                <td>
                    <p class="desc font-bold">{{ $document->quotation->identifier }}</p>
                </td>
            </tr>
        @endif
        @if ($configuration->show_dispatcher_documents_sale_notes_order_note)
            @isset($document->order_note)
                @php
                    $order_note = $document->order_note;
                    $ship_address = $order_note->shipping_address;
                    $observation = $order_note->observation;
                @endphp
                @if ($ship_address)
                    <tr>
                        <td class="align-top">
                            <p class="desc">

                                Dirección de envío:
                            </p>
                        </td>
                        <td class="desc" style="text-transform: capitalize;">
                            {{ $ship_address }}
                        </td>
                    </tr>
                @endif
                @if ($observation)
                    <tr>
                        <td class="align-top">
                            <p class="desc">
                                Observación Pd:
                            </p>
                        </td>
                        <td class="desc" style="text-transform: capitalize;">
                            {{ $observation }}
                        </td>
                    </tr>
                @endif
            @endisset
        @endif
    

    </table>


    <table class="full-width mt-10">
        <thead class="">
            <tr>
                <th class="border-top-bottom desc-9 text-left">Cant.</th>
                <th class="border-top-bottom desc-9 text-left">Unidad</th>
                <th class="border-top-bottom desc-9 text-left">Código</th>
                <th class="border-top-bottom desc-9 text-right">P.Unit</th>
                <th class="border-top-bottom desc-9 text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
            <tr>
                <td class="text-left desc-l align-top" colspan="5">
                    @php
                        $row_description = $row->name_product_pdf ?? $row->item->description;
                        $row_description = removePTag($row_description);
                        $row_description = '<p>' . $row_description . '</p>';
                    @endphp

                    {!! $row_description !!}
                    @if ($configuration->presentation_pdf && isset($row->item->presentation) && isset($row->item->presentation->description))
                        <div>
                            <span>{{ $row->item->presentation->description }}</span>
                        </div>
                    @endif
                    @if ($row->attributes)
                        @foreach ($row->attributes as $attr)
                            <br />{!! $attr->description !!} : {{ $attr->value }}
                        @endforeach
                    @endif
                    @if ($row->discounts)
                        @foreach ($row->discounts as $dtos)
                            <br /><small>{{ $dtos->factor * 100 }}%
                                {{ $dtos->description }}</small>
                        @endforeach
                    @endif
                    @if ($row->item->is_set == 1 && $configuration->show_item_sets)
                        <br>
                        @inject('itemSet', 'App\Services\ItemSetService')
                        @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                            {{ $item }}<br>
                        @endforeach
                    @endif
                    @if (isset($row->item->item_complements_selected) && count($row->item->item_complements_selected) > 0)
                        <div>
                            <small>
                                Especificaciones:
                                <strong>
                                    {{ implode(', ', $row->item->item_complements_selected) }}
                                </strong>
                            </small>
                        </div>
                    @endif
                    @if ($row->item->used_points_for_exchange ?? false)
                        <br>
                        <small>*** Canjeado por
                            {{ $row->item->used_points_for_exchange }} puntos
                            ***</small>
                    @endif

                </td>
            </tr>
                <tr>
                    <td class="text-center desc-l align-top">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ number_format($row->quantity, 2) }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                    <td class="text-center desc-9 align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    @php
                        $internal_id = $row->item->internal_id;
                        if($internal_id == null){
                            $item_db = \App\Models\Tenant\Item::select('internal_id')->where('id', $row->item_id)->first();
                            $internal_id = $item_db->internal_id;
                        }
                    @endphp
                    <td class="text-center desc-9 align-top">{{ $internal_id }}</td>
                    
                    <td class="text-right desc-l align-top">
                        {{ number_format($row->unit_price, 2) }}</td>
                    <td class="text-right desc-l align-top">
                        {{ number_format($row->total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="border-bottom"></td>
                </tr>
            @endforeach

            @if ($document->total_taxed >= 0)
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
            @if ($document->total_discount > 0 && $document->subtotal > 0)
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
                <td colspan="4" class="text-right font-bold desc">SUBtotal:
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
            @php
                $change_payment = $document->getChangePayment();
            @endphp

            @if ($change_payment < 0)
                <tr>
                    <td colspan="4" class="text-right font-bold">Vuelto:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-right font-bold">
                        {{ number_format(abs($change_payment), 2, '.', '') }}</td>
                </tr>
            @endif

        </tbody>
    </table>
    @php
        $quotation = \App\Models\Tenant\Quotation::select(['number', 'prefix', 'shipping_address'])
            ->where('id', $document->quotation_id)
            ->first();

    @endphp



    @php
        $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
    @endphp
    {{-- Condicion de pago  Crédito / Contado --}}
    <div class="desc">
        <strong>Condición de Pago: {{ $paymentCondition }} </strong>
    </div>

    @if ($document->payment_method_type_id)
        <div class="desc">
            <strong>Método de Pago: </strong>{{ $document->payment_method_type->description }}
        </div>
    @endif

    <div class="desc">
        <strong>Vendedor:</strong> {{ $document->seller ? $document->seller->name : $document->user->name }}
    </div>


    @if ($document->payment_method_type_id && $payments->count() == 0)
        <table class="full-width">
            <tr>
                <td class="desc">
                    <strong>Pago:
                    </strong>{{ $document->payment_method_type->description }}
                </td>
            </tr>
        </table>
    @endif
    @if ($document->payment_condition_id !== '02')
        @if ($payments->count())
            <table class="full-width">
                <tr>
                    <td><strong>Pagos:</strong> </td>
                </tr>
                @php
                    $payment = 0;
                @endphp
                @foreach ($payments as $row)
                    <tr>
                        <td>- {{ $row->date_of_payment->format('d/m/Y') }} -
                            {{ $row->payment_method_type->description }} -
                            {{ $row->reference ? $row->reference . ' - ' : '' }}
                            {{ $document->currency_type->symbol }}
                            {{ $row->payment + $row->change }}</td>
                    </tr>
                    @php
                        $payment += (float) $row->payment;
                    @endphp
                @endforeach
                <tr>
                    <td class="pb-10"><strong>Saldo:</strong>
                        {{ $document->currency_type->symbol }}
                        {{ number_format($document->total - $payment, 2) }}</td>
                </tr>
            </table>
        @endif
    @endif
    @if ($document->fee && count($document->fee) > 0)

        @foreach ($document->fee as $key => $quote)
            @if (!$configuration->show_the_first_cuota_document)
                <div class="desc">
                    <span>&#8226;
                        {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                        / Fecha: {{ $quote->date }} / Monto:
                        {{ $quote->currency_type->symbol }}{{ $quote->amount }}</span>
                </div>
            @else
                @if ($key == 0)
                    <div class="desc">
                        <span>&#8226;
                            {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                            / Fecha: {{ $quote->date }} / Monto:
                            {{ $quote->currency_type->symbol }}{{ $quote->amount }}</span>
                    </div>
                @endif
            @endif
        @endforeach
    @endif
    <table class="full-width">
        <tr>
            <td class="text-center">
                <h6 style="font-size: 10px; font-weight: bold;">Términos y condiciones del servicio</h6>
                {!! $configuration->terms_condition_sale !!}
            </td>
        </tr>
    </table>


</body>

</html>
