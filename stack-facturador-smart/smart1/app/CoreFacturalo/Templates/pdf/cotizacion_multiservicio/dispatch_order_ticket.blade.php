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
                                        $invoice = $document->invoice;
                                        //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
                                        $tittle = $document->prefix . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
                                        $payments = $document->sale_note->payments;
                                        $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
                                        
                                        $logo = "storage/uploads/logos/{$company->logo}";
                                        if ($establishment->logo) {
                                            $logo = "{$establishment->logo}";
                                        }
                                        $is_integrate_system = Modules\BusinessTurn\Models\BusinessTurn::isIntegrateSystem();
                                        $quotation = null;
                                        if ($is_integrate_system) {
                                            $sale_note = \App\Models\Tenant\SaleNote::where('id', $document->sale_note_id)->first();
                                            $quotation = \App\Models\Tenant\Quotation::select(['number', 'prefix', 'shipping_address'])
                                                ->where('id', $sale_note->quotation_id)
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
                                                    <h4>Orden de despacho</h4>
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
                                            @if($quotation && $quotation->shipping_address)
                                            <tr>
                                                <td class="align-top">
                                                    <p class="desc">Dirección de envío:</p>
                                                </td>
                                                <td>
                                                    <p class="desc">
                                                        {{ strtoupper($quotation->shipping_address) }}
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
                                            <tr>
                                                <td>
                                                    <p class="desc">
                                                        Teléfono:
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="desc">
                                                        {{ $customer->telephone }}
                                                    </p>

                                                </td>
                                            </tr>
                                            @if (is_integrate_system())
                                            <tr>
                                                <td class="align-top">
                                                    <p class="desc">Estado:</p>
                                                </td>
                                                <td>
                                                    <p class="desc">
                                                        @if ($document->sale_note->total_canceled)
                                                            PAGADO
                                                        @else
                                                            PENDIENTE DE PAGO
                                                        @endif
                                                    </p>
                                                </td>
                                            </tr>
                                            @if ($document->sale_note->total_canceled)
                                                <tr>
                                                    <td class="align-top">
                                                        <p class="desc">Condición de pago:</p>
                                                    </td>
                                                    <td>
                                                        <p class="desc">
                                                            @if ($document->sale_note->payment_condition)
                                                                {{ $document->sale_note->payment_condition->name }}
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
                                                            @isset($document->sale_note->payments)
                                                                @php
                                                                    $first_payment = $document->sale_note->payments->first();
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
                                                <td>
                                                    <p class="desc">

                                                        Vendedor:
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="desc">
                                                        @if ($document->seller_id != 0)
                                                        {{ $document->seller->name }}
                                                    @else
                                                        {{ $document->user->name }}
                                                    @endif
                                                    </p>
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

                                        <table class="full-width mt-10 mb-10">
                                            <thead class="">
                                                <tr>
                                                    <th class="border-top-bottom desc-9 text-left">Cant.</th>
                                                    <th class="border-top-bottom desc-9 text-left">Unidad</th>
                                                    <th class="border-top-bottom desc-9 text-left">Descripción</th>
                                                    <th class="border-top-bottom desc-9 text-left">P.Unit</th>
                                                    <th class="border-top-bottom desc-9 text-left">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($document->items as $row)
                                                    <tr>
                                                        <td class="text-center desc-9 align-top">
                                                            @if ((int) $row->quantity != $row->quantity)
                                                                {{ $row->quantity }}
                                                            @else
                                                                {{ number_format($row->quantity, 0) }}
                                                            @endif
                                                        </td>
                                                        <td class="text-center desc-9 align-top">
                                                            {{ symbol_or_code($row->item->unit_type_id) }}</td>
                                                        <td class="text-left desc-9 align-top">

                                                            @if ($row->name_product_pdf)
                                                                {!! $row->name_product_pdf !!}
                                                            @else
                                                                {!! $row->item->description !!}
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
                                                            @if ($row->item->is_set == 1)
                                                                <br>
                                                                @inject('itemSet', 'App\Services\ItemSetService')
                                                                @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                                                                    {{ $item }}<br>
                                                                @endforeach
                                                            @endif

                                                            @if ($row->item->used_points_for_exchange ?? false)
                                                                <br>
                                                                <small>*** Canjeado por
                                                                    {{ $row->item->used_points_for_exchange }} puntos
                                                                    ***</small>
                                                            @endif

                                                        </td>
                                                        <td class="text-right desc-9 align-top">
                                                            {{ number_format($row->unit_price, 2) }}</td>
                                                        <td class="text-right desc-9 align-top">
                                                            {{ number_format($row->total, 2) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="5" class="border-bottom"></td>
                                                    </tr>
                                                @endforeach
                                                @if ($document->total_exportation > 0)
                                                    <tr>
                                                        <td colspan="4" class="text-right font-bold desc">Op.
                                                            Exportación: {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold desc">
                                                            {{ number_format($document->total_exportation, 2) }}</td>
                                                    </tr>
                                                @endif
                                                @if ($document->total_free > 0)
                                                    <tr>
                                                        <td colspan="4" class="text-right font-bold desc">Op.
                                                            Gratuitas: {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold desc">
                                                            {{ number_format($document->total_free, 2) }}</td>
                                                    </tr>
                                                @endif
                                                @if ($document->total_unaffected > 0)
                                                    <tr>
                                                        <td colspan="4" class="text-right font-bold desc">Op.
                                                            Inafectas: {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold desc">
                                                            {{ number_format($document->total_unaffected, 2) }}</td>
                                                    </tr>
                                                @endif
                                                @if ($document->total_exonerated > 0)
                                                    <tr>
                                                        <td colspan="4" class="text-right font-bold desc">Op.
                                                            Exoneradas: {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold desc">
                                                            {{ number_format($document->total_exonerated, 2) }}</td>
                                                    </tr>
                                                @endif
                                                {{-- @if ($document->total_taxed > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">Op. Gravadas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif --}}
                                                @if ($document->total_discount > 0)
                                                    <tr>
                                                        <td colspan="4" class="text-right font-bold desc">
                                                            {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento TOTAL' }}:
                                                            {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold desc">
                                                            {{ number_format($document->total_discount, 2) }}</td>
                                                    </tr>
                                                @endif
                                                {{-- <tr>
            <td colspan="4" class="text-right font-bold desc">IGV: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc">{{ number_format($document->total_igv, 2) }}</td>
        </tr> --}}

                                                @if ($document->total_charge > 0 && $document->charges)
                                                    <tr>
                                                        <td colspan="4" class="text-right font-bold desc">CARGOS
                                                            ({{ $document->getTotalFactor() }}%):
                                                            {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold desc">
                                                            {{ number_format($document->total_charge, 2) }}</td>
                                                    </tr>
                                                @endif

                                                <tr>
                                                    <td colspan="4" class="text-right font-bold desc">Total a pagar:
                                                        {{ $document->currency_type->symbol }}</td>
                                                    <td class="text-right font-bold desc">
                                                        {{ number_format($document->total, 2) }}</td>
                                                </tr>

                                                @php
                                                    $change_payment = $document->getChangePayment();
                                                @endphp

                                                @if ($change_payment < 0)
                                                    <tr>
                                                        <td colspan="4" class="text-right font-bold desc">Vuelto:
                                                            {{ $document->currency_type->symbol }}</td>
                                                        <td class="text-right font-bold desc">
                                                            {{ number_format(abs($change_payment), 2, '.', '') }}</td>
                                                    </tr>
                                                @endif

                                            </tbody>
                                        </table>
                                        <table class="full-width">
                                            {{-- @if ($document->observation)
                                                <tr>
                                                    <td width="30%">
                                                        <p class="desc">Observación log:</p>
                                                    </td>
                                                    <td>
                                                        <p class="desc">{{ $document->observation }}</p>
                                                    </td>
                                                </tr>
                                            @endif --}}
                                            @php
                                                $sale_note = \App\Models\Tenant\SaleNote::where('id', $document->sale_note_id)->first();
                                            @endphp
                                            @if($quotation)
                                            <tr>
                                                <td width="30%">
                                                    <p class="desc">Cotización:</p>
                                                </td>
                                                <td>
                                                    <p class="desc">{{$quotation->prefix }}-{{$quotation->number}}</p>
                                                </td>
                                            </tr>
                                            @endif
                                            @if ($sale_note)
                                                <tr>
                                                    <td width="30%">
                                                        <p class="desc">Observación adm:</p>
                                                    </td>
                                                    <td>
                                                        <p class="desc">{{ $sale_note->additional_information }}</p>
                                                    </td>
                                                </tr>
                                            @endif
                                            @php
                                                $cot = null;
                                                if ($sale_note) {
                                                    $cot = \App\Models\Tenant\Quotation::where('id', $sale_note->quotation_id)->first();
                                                }
                                            @endphp
                                            @if ($cot)
                                                <tr>
                                                    <td width="30%">
                                                        <p class="desc">Observación com:</p>
                                                    </td>
                                                    <td>
                                                        <p class="desc">{{ $cot->description }}</p>
                                                    </td>
                                                </tr>
                                            @endif
                                            {{-- @php
                                            $prod = null;
                                            if ($sale_note) {
                                                $prod = \App\Models\Tenant\ProductionOrder::where('sale_note_id', $sale_note->id)->first();
                                            }
                                        @endphp
                                        @if ($prod)
                                            <tr>
                                                <td width="30%">
                                                    <p class="desc">Observación prod:</p>
                                                </td>
                                                <td>
                                                    <p class="desc">{{ $prod->observation }}</p>
                                                </td>
                                            </tr>
                                        @endif --}}
                                        </table>
                                        <table class="full-width">
                                            <tr>

                                                @foreach (array_reverse((array) $document->legends) as $row)
                                            <tr>
                                                @if ($row->code == '1000')
                                                    <td class="desc pt-3" style="text-transform: uppercase;">Son: <span
                                                            class="font-bold">{{ $row->value }}
                                                            {{ $document->currency_type->description }}</span></td>
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

                                            {{-- <tr>
                                                <td class="desc pt-3">
                                                    <br>
                                                    @foreach ($accounts as $account)
                                                        <span
                                                            class="font-bold">{{ $account->bank->description }}</span>
                                                        {{ $account->currency_type->description }}
                                                        <br>
                                                        <span class="font-bold">N°:</span> {{ $account->number }}
                                                        @if ($account->cci)
                                                            - <span class="font-bold">CCI:</span> {{ $account->cci }}
                                                        @endif
                                                        <br>
                                                    @endforeach

                                                </td>
                                            </tr> --}}

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
                                        <table class="full-width">
                                            @php
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
                                                                            alt="{{ $company->name }}"
                                                                            class="company_logo"
                                                                            style="max-width: 150px;">
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
                                                                            alt="{{ $company->name }}"
                                                                            class="company_logo"
                                                                            style="max-width: 150px;">
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
                                        <br>
                                    </body>

                                    </html>
