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
    }    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();

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

    @if ($company->logo)
        <div class="text-center company_logo_box pt-5">
            <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                alt="{{ $company->name }}" class="company_logo_ticket contain">
        </div>
        {{-- @else --}}
        {{-- <div class="text-center company_logo_box pt-5"> --}}
        {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo_ticket contain"> --}}
        {{-- </div> --}}
    @endif
    <table class="full-width">
        <tr>
            <td class="text-center">
                <h4>{{ $company_name }}</h4>
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
            <td class="text-center" style="text-transform: uppercase;">
                {{ $establishment->address !== '-' ? $establishment->address : '' }}
                {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
            </td>
        </tr>
        <tr>
            <td class="text-center">
                {{ $establishment->email !== '-' ? $establishment->email : '' }}
            </td>
        </tr>
        <tr>
            <td class="text-center pb-3">
                {{ $establishment->telephone !== '-' ? $establishment->telephone : '' }}
            </td>
        </tr>
        <tr>
            <td class="text-center pt-3 border-top">
                <h4>{{ get_document_name('sale_note', 'NOTA DE VENTA') }}</h4>
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
                <p class="desc">{{ $document->date_of_issue->format('Y-m-d') }}
                    {{ $document->time_of_issue }}
                </p>
            </td>
        </tr>
        @if ($document->due_date)
            <tr>
                <td width="" class="pt-3">
                    <p class="desc">F. Vencimiento:</p>
                </td>
                <td width="" class="pt-3">
                    <p class="desc">{{ $document->getFormatDueDate() }}</p>
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
                <p class="desc">
                    {{ $customer->identity_document_type->description }}:</p>
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
                        {{ strtoupper($customer->address) }}
                        {{ $customer->district_id !== '-' ? ', ' . strtoupper($customer->district->description) : '' }}
                        {{ $customer->province_id !== '-' ? ', ' . strtoupper($customer->province->description) : '' }}
                        {{ $customer->department_id !== '-' ? '- ' . strtoupper($customer->department->description) : '' }}
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
        @if (is_integrate_system())
            <tr>
                <td class="align-top">
                    <p class="desc">Estado:</p>
                </td>
                <td>
                    <p class="desc">
                        @if ($document->total_canceled)
                            PAGADO
                        @else
                            PENDIENTE DE PAGO
                        @endif
                    </p>
                </td>
            </tr>
            @if ($document->total_canceled)
                <tr>
                    <td class="align-top">
                        <p class="desc">Condición de pago:</p>
                    </td>
                    <td>
                        <p class="desc">
                            @if ($document->payment_condition)
                                {{ $document->payment_condition->name }}
                            @else
                                CONTADO
                            @endif
                        </p>
                    </td>
                </tr>
                <tr>
                    <td class="align-top">
                        <p class="desc">Método de pago:</p>
                    </td>
                    <td>
                        <p class="desc">
                            @isset($document->payments)
                                @php
                                    $first_payment = $document->payments->first();
                                @endphp
                                @if ($first_payment)
                                    {{ $first_payment->payment_method_type->description }}
                                @endif
                            @endisset
                        </p>
                    </td>
                </tr>
            @endif
        @endif
        <tr>
            <td class="align-top">
                <p class="desc">Vendedor:</p>
            </td>
            <td class="desc">
                @if ($document->seller_id != 0)
                    {{ $document->seller->name }}
                @else
                    {{ $document->user->name }}
                @endif
            </td>
        </tr>

        @if ($document->hotelRent)
            <tr>
                <td>Destino:</td>
                <td>{{ $document->hotelRent->destiny }}</td>
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
        @if ($document->isPointSystem())
            <tr>
                <td>
                    <p class="desc">P. Acumulados:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->person->accumulated_points }}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Puntos por la compra:</p>
                </td>
                <td>
                    <p class="desc">{{ $document->getPointsBySale() }}</p>
                </td>
            </tr>
        @endif

    </table>
    @if ($document->transport)

        <p class="desc"><strong>Transporte de pasajeros</strong></p>

        @php
            $transport = $document->transport;
            $origin_district_id = (array) $transport->origin_district_id;
            $destinatation_district_id = (array) $transport->destinatation_district_id;
            $origin_district = Modules\Order\Services\AddressFullService::getDescription($origin_district_id[2]);
            $destinatation_district = Modules\Order\Services\AddressFullService::getDescription(
                $destinatation_district_id[2],
            );
        @endphp


        <table class="full-width mt-3">
            <tr>
                <td>
                    <p class="desc">
                        {{ $transport->identity_document_type->description }}:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->number_identity_document }}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Nombre:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->passenger_fullname }}</p>
                </td>
            </tr>
            @if ($transport->passenger_age)
                <tr>
                    <td>
                        <p class="desc">Edad
                        </p>
                    </td>
                    <td>
                        <p class="desc">{{ $transport->passenger_age }}</p>
                    </td>
                </tr>
            @endif


            <tr>
                <td>
                    <p class="desc">N° Asiento:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->seat_number }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">M. Pasajero:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->passenger_manifest }}</p>
                </td>
            </tr>

            <tr>
                <td>
                    <p class="desc">F. Inicio:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->start_date }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">H. Inicio:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->start_time }}</p>
                </td>
            </tr>


            <tr>
                <td>
                    <p class="desc">U. Origen:</p>
                </td>
                <td>
                    <p class="desc">{{ $origin_district }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">D. Origen:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->origin_address }}</p>
                </td>
            </tr>

            <tr>
                <td>
                    <p class="desc">U. Destino:</p>
                </td>
                <td>
                    <p class="desc">{{ $destinatation_district }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">D. Destino:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport->destinatation_address }}</p>
                </td>
            </tr>

        </table>
    @endif
    @if ($document->transport_dispatch)
        @php
            $transport_dispatch = $document->transport_dispatch;
            $sender_identity_document_type = $transport_dispatch->sender_identity_document_type->description;
            $recipient_identity_document_type = $transport_dispatch->recipient_identity_document_type->description;
        @endphp
        <p class="desc"><strong>Información de encomienda</strong></p>
        <table class="full-width mt-3">
            <tr>
                <td class="desc" colspan="2">
                    <strong>REMITENTE</strong>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">{{ $sender_identity_document_type }}:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $transport_dispatch->sender_number_identity_document }}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Nombre:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $transport_dispatch->sender_passenger_fullname }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Teléfono:</p>
                </td>
                <td>
                    <p class="desc">{{ $transport_dispatch->sender_telephone }}
                    </p>
                </td>
            </tr>
            <tr>
                <td class="desc" colspan="2">
                    <strong>DESTINATARIO</strong>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">{{ $recipient_identity_document_type }}:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $transport_dispatch->recipient_number_identity_document }}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Nombre:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $transport_dispatch->recipient_passenger_fullname }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="desc">Teléfono:</p>
                </td>
                <td>
                    <p class="desc">
                        {{ $transport_dispatch->recipient_telephone }}</p>
                </td>
            </tr>
        </table>
    @endif
    <table class="full-width mt-10 mb-10">
        <thead class="">
            <tr>
                <th class="border-top-bottom desc-9 text-left">Cant.</th>
                <th class="border-top-bottom desc-9 text-left">Descripción</th>
                <th class="border-top-bottom desc-9 text-left">P.Unit</th>
                <th class="border-top-bottom desc-9 text-left">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
                <tr>
                    <td class="text-center desc-l align-top">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ number_format($row->quantity, 2) }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                    <td class="text-left desc-l align-top">
                        @php
                            $row_description = $row->name_product_pdf ?? $row->item->description;
                            $row_description = removePTag($row_description);
                            $row_description = symbol_or_code($row->item->unit_type_id) . ' ' . $row_description;
                            $row_description = "<p>" . $row_description . "</p>";
                        @endphp
                
                {!! $row_description !!}
                    
                        {{-- {{ symbol_or_code($row->item->unit_type_id) }} --}}
                        {{--                                                                        
                                                            @if ($row->name_product_pdf)
                                                                {!! $row->name_product_pdf !!}
                                                            @else
                                                                {!! $row->item->description !!}
                                                            @endif --}}
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
                        @if(isset($row->item->item_complements_selected) && count($row->item->item_complements_selected) > 0)
                
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
                    <td class="text-right desc-l align-top">
                        {{ number_format($row->unit_price, 2) }}</td>
                    <td class="text-right desc-l align-top">
                        {{ number_format($row->total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="border-bottom"></td>
                </tr>
            @endforeach

            {{--
                                                @if ($document->total_exportation > 0)
                                                    <tr>
                                                        <td colspan="3" class="text-right font-bold ">Op.
                                                            Exportación: {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold ">
                                                            {{ number_format($document->total_exportation, 2) }}</td>
                                                    </tr>
                                                @endif
                                                
                                                @if ($document->total_free > 0)
                                                    <tr>
                                                        <td colspan="3" class="text-right font-bold ">Op.
                                                            Gratuitas: {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold ">
                                                            {{ number_format($document->total_free, 2) }}</td>
                                                    </tr>
                                                @endif
                                                @if ($document->total_unaffected > 0)
                                                    <tr>
                                                        <td colspan="3" class="text-right font-bold ">Op.
                                                            Inafectas: {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold ">
                                                            {{ number_format($document->total_unaffected, 2) }}</td>
                                                    </tr>
                                                @endif
                                                @if ($document->total_exonerated > 0)
                                                    <tr>
                                                        <td colspan="3" class="text-right font-bold ">Op.
                                                            Exoneradas: {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold ">
                                                            {{ number_format($document->total_exonerated, 2) }}</td>
                                                    </tr>
                                                @endif
                                                @if ($document->total_taxed > 0)
                                                    <tr>
                                                        <td colspan="3" class="text-right font-bold ">Op.
                                                            Gravadas: {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold ">
                                                            {{ number_format($document->total_taxed * 1.18, 2) }}</td>
                                                    </tr>
                                                @endif
                                                @if ($document->total_discount > 0)
                                                    <tr>
                                                        <td colspan="3" class="text-right font-bold ">
                                                            {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento TOTAL' }}:
                                                            {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold ">
                                                            {{ number_format($document->total_discount, 2) }}</td>
                                                    </tr>
                                                @endif
                                                @if ($configuration->taxed_igv_visible_nv)
                                                    <tr>
                                                        <td colspan="3" class="text-right font-bold ">IGV:
                                                            {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold ">
                                                            {{ number_format($document->total_igv, 2) }}</td>
                                                    </tr>
                                                @endif

                                                @if ($document->total_charge > 0 && $document->charges)
                                                    <tr>
                                                        <td colspan="3" class="text-right font-bold ">CARGOS
                                                            ({{ $document->getTotalFactor() }}%):
                                                            {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold ">
                                                            {{ number_format($document->total_charge, 2) }}</td>
                                                    </tr>
                                                @endif
                                                --}}


            <tr>
                <td colspan="3" class="text-right font-bold font-lg">Total a
                    pagar:
                    {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold font-lg">
                    {{ number_format($document->total, 2) }}</td>
            </tr>

            @php
                $change_payment = $document->getChangePayment();
            @endphp

            @if ($change_payment < 0)
                <tr>
                    <td colspan="3" class="text-right font-bold">Vuelto:
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

    @if (is_integrate_system())
        <table class="full-width">
            @php
                $cot = \App\Models\Tenant\Quotation::where('id', $document->quotation_id)->first();
            @endphp
            @if ($cot)
                <tr>


                    <td>
                        <p class="desc">Cotización:</p>
                    </td>
                    <td>
                        <p class="desc">{{ $cot->prefix }}-{{ $cot->number }}
                        </p>
                    </td>

                </tr>
                <tr>


                    <td>
                        <p class="desc">Observación cot.:</p>
                    </td>
                    <td>
                        <p class="desc">{{ $cot->description }}</p>
                    </td>

                </tr>
            @endif
            {{-- <tr>
                                                    <td>
                                                        <p class="desc">Observación adm.:</p>
                                                    </td>
                                                    <td>
                                                        <p class="desc">{{ $document->additional_information }}</p>
                                                    </td>
                                                </tr> --}}
            {{-- @php
                                                    $prod = \App\Models\Tenant\ProductionOrder::where(
                                                        'sale_note_id',
                                                        $document->id,
                                                    )->first();
                                                @endphp
                                                @if ($prod)
                                                    <tr>
                                                        <td>
                                                            <p class="desc">Observación prod.:</p>
                                                        </td>
                                                        <td>
                                                            <p class="desc">

                                                                {{ $prod->observation }}
                                                            </p>
                                                        </td>

                                                    </tr>
                                                @endif --}}
        </table>
    @endif
    <table class="full-width">
        <tr>

            @foreach (array_reverse((array) $document->legends) as $row)
        <tr>
            @if ($row->code == '1000')
                <td class="desc pt-3" style="text-transform: uppercase;">Son:
                    <span class="font-bold">{{ $row->value }}
                        {{ $document->currency_type->description }}</span>
                </td>
                @if (count((array) $document->legends) > 1)
        <tr>
            <td class="desc pt-3"><span class="font-bold">Leyendas</span></td>
        </tr>
        @endif
    @else
        <td class="desc pt-3">{{ $row->code }}: {{ $row->value }}</td>
        @endif
        </tr>
        @endforeach
        </tr>

        @if (!$is_integrate_system)
            <tr>
                <td class="desc pt-3">
                    <br>
                    @foreach ($accounts as $account)
                        <span class="font-bold">{{ $account->bank->description }}</span>
                        {{ $account->currency_type->description }}
                        <br>
                        <span class="font-bold">N°:</span> {{ $account->number }}
                        @if ($account->cci)
                            - <span class="font-bold">CCI:</span> {{ $account->cci }}
                        @endif
                        <br>
                    @endforeach

                </td>
            </tr>
        @endif

    </table>

    @if ($document->payment_method_type_id && $payments->count() == 0)
        <table class="full-width">
            <tr>
                <td class="desc pt-5">
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
        @php
            $establishment_data = \App\Models\Tenant\Establishment::find($document->establishment_id);
        @endphp
        <tbody>
            <tr>
                @if ($configuration->yape_qr_sale_notes && $establishment_data->yape_logo)
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
                                            Nombre:
                                            {{ $establishment_data->yape_owner }}
                                        </strong>
                                    @endif
                                    @if ($establishment_data->yape_number)
                                        <br>
                                        <strong>
                                            Número:
                                            {{ $establishment_data->yape_number }}
                                        </strong>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                @endif
                @if ($configuration->plin_qr_sale_notes && $establishment_data->plin_logo)
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
                                            Nombre:
                                            {{ $establishment_data->plin_owner }}
                                        </strong>
                                    @endif
                                    @if ($establishment_data->plin_number)
                                        <br>
                                        <strong>
                                            Número:
                                            {{ $establishment_data->plin_number }}
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
    @if ($document->terms_condition)
        <br>
        <table class="full-width">
            <tr>
                <td>
                    <h6 style="font-size: 10px; font-weight: bold;">Términos y
                        condiciones del servicio</h6>
                    {!! $document->terms_condition !!}
                </td>
            </tr>
        </table>
    @endif

    <!-- Coupon Details Section -->
    @if ($document->items->where('item.id_cupones', '!=', null)->count())
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>

        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <div
            style="border: 1px solid #ccc; padding: 15px; margin-top: 20px; background-color: #f9f9f9; border-radius: 8px;">
            <h3 style="margin-bottom: 15px; color: #2c3e50; font-size: 16px;">Cupon
            </h3>
            @foreach ($document->items as $row)
                @if ($row->item->id_cupones)
                    @php
                        $coupon = \App\Models\Tenant\Coupon::find($row->item->id_cupones);
                    @endphp
                    @if ($coupon)
                        <div
                            style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ddd;text-align: center;">
                            @if ($coupon->imagen)
                                @php
                                    $coupon_image = 'storage/' . $coupon->imagen;
                                @endphp
                                <img src="data:{{ mime_content_type(public_path("{$coupon_image}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$coupon_image}"))) }}"
                                    alt="Coupon Image" style="max-width: 120px; display: block; margin-bottom: 10px;">
                            @endif
                            @if ($coupon->titulo)
                                <p style="margin: 5px 0; font-size: 14px; font-weight: bold; color: #34495e;">
                                    {{ $coupon->titulo }}</p>
                            @endif
                            @if ($coupon->descripcion)
                                <p style="margin: 5px 0; font-size: 13px; color: #7f8c8d;">
                                    {{ $coupon->descripcion }}</p>
                            @endif
                            @if ($coupon->descuento)
                                <p style="margin: 5px 0; font-size: 13px; color: #27ae60;">
                                    <strong>Descuento:</strong>
                                    {{ $coupon->descuento }}%
                                </p>
                            @endif
                            @if ($coupon->fecha_caducidad)
                                <p style="margin: 5px 0; font-size: 13px; color: #c0392b;">
                                    <strong>Fecha de caducidad:</strong>
                                    {{ \Carbon\Carbon::parse($coupon->fecha_caducidad)->format('Y-m-d') }}
                                </p>
                            @endif
                            @if ($coupon->barcode)
                                <p style="margin: 5px 0; font-size: 13px; color: #34495e; text-align: center;">
                                    <strong>Código de barras:</strong>
                                </p>
                                <div style="text-align: center;">
                                    <img src="{{ $coupon->barcode }}" alt="Barcode Image"
                                        style="max-width: 120px; display: inline-block; margin-bottom: 10px;">
                                </div>
                            @endif
                        </div>
                    @else
                        <p style="margin: 5px 0; font-size: 13px; color: #e74c3c;">
                            Coupon not found</p>
                    @endif
                @endif
            @endforeach
        </div>
    @endif

</body>

</html>
