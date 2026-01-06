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
    $logo = str_replace("storage/uploads/logos/storage/uploads/logos/", "storage/uploads/logos/", $logo);
}


    $customer = $document->customer;
    $invoice = $document->invoice;
    $document_base = ($document->note) ? $document->note : null;

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();

    if($document_base) {

        $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    } else {

        $affected_document_number = null;
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
    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');

@endphp

<table class="full-width">
    <tr>
        <td width="60%" class="text-center pr-2">
            @if($company->logo)
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 100px;">
                </div>
            @endif
            <br>
            <h5 class="font-bold text-upp">{{ $company->name }}</h5>
            <hr>
            <h6 style="text-transform: uppercase;">
                {{ ($establishment->address !== '-')? $establishment->address.',' : '' }}
                {{ ($establishment->district_id !== '-')? $establishment->district->description : '' }}
                {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
            </h6>

            @isset($establishment->trade_address)
                <h6>{{ ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : '' }}</h6>
            @endisset

            <h6>{{ ($establishment->telephone !== '-')? 'Telf. '.$establishment->telephone : '' }}</h6>

            <h6>{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</h6>

            @isset($establishment->web_address)
                <h6>{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</h6>
            @endisset

            @isset($establishment->aditional_information)
                <h6>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</h6>
            @endisset
        </td>
        <td width="40%" class="border-box py-2 px-2 text-center">
            <h3 class="font-bold">{{ 'RUC '.$company->number }}</h3>
            <h3 class="text-center font-bold">{{ $document->document_type->description }}</h3>
            <br>
            <h3 class="text-center">{{ $document_number }}</h3>
        </td>
    </tr>
</table>
<table class="full-width mt-2">
    <tr>
        <td width="95%" class="border-box pl-3">
            <table class="full-width">
                <tr>
                    <td colspan="2" class="font-lg">
                        <strong>Señor(es):</strong>
                        {{ $customer->name }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="font-lg">
                        <strong>Dirección: </strong>
                        @if ($customer->address !== '')
                            <span style="text-transform: uppercase;">
                                {{ $customer->address }}
                                {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                                {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                                {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
                            </span>
                        @endif
                    </td>
                </tr>
                @if($customer->telephone !== '-')
                <tr>
                    <td colspan="2" class="font-lg">
                        <strong>Teléfono:</strong>
                        {{ $customer->telephone}}
                    </td>
                </tr>
                @endif
                <tr>
                    <td colspan="2"  class="font-lg">
                        <strong>RUC: </strong>
                        {{$customer->number}}
                    </td>
                </tr>
                <tr>
                    <td colspan="2"  class="font-lg">
                        <strong>Moneda:</strong>
                        <span class="text-upp">{{ $document->currency_type->description }}</span>
                    </td>
                </tr>
                <tr>
                    <td  class="font-lg">
                        <strong>Fecha:</strong>
                        {{$document->date_of_issue->format('Y-m-d')}}
                    </td>
                    <td  class="font-lg">
                        @if($invoice)
                            <strong>Fecha venc.:</strong>
                            {{$invoice->date_of_due->format('Y-m-d')}}
                        @endif
                    </td>
                </tr>
            </table>
        </td>
        <td width="5%" class="p-0 m-0">
            <img src="data:image/png;base64, {{ $document->qr }}" class="p-0 m-0" style="width: 120px;" />
        </td>
    </tr>
</table>
<table class="full-width my-3 text-center" border="1">
    <tr>
        <td width="16.6%" class="desc">Ubigeo</td>
        <td width="16.6%" class="desc">O/C</td>
        <td width="16.6%" class="desc">Condiciones de pago</td>
        <td width="16.6%" class="desc">Vendedor</td>
        <td width="16.6%" class="desc">GUÍA DE REMISIÓN</td>
        <td width="16.6%" class="desc">Agencia de transporte</td>
    </tr>
    <tr>
        <td class="desc"></td>
        <td class="desc"></td>
        <td class="desc">
            @php
                $payment = 0;
            @endphp
            @foreach($payments as $row)
                {{ $row->payment_method_type->description }}
            @endforeach
        </td>
        <td class="desc">{{ $document->user->name }}</td>
        <td class="desc">
            @if ($document->guides)
                @foreach($document->guides as $guide)
                    {{ $guide->number }}
                @endforeach
            @endif

            @if ($document->reference_guides)
                @foreach($document->reference_guides as $guide)
                    {{ $guide->number }}
                @endforeach
            @endif
        </td>
        <td class="desc"></td>
    </tr>
</table>

<div style="border: 1px solid #000;height: 455px;padding-left: -1px;width:100%;position: relative ;display: table; z-index: 1;">
@if($document->state_type->id == '11'  || $document->state_type->id == '09')
    <div class="company_logo_box" style="position: all; text-align: center; top:40%; z-index: 2">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
@else
    @if($company->logo)
        <div class="company_logo_box" style="position: all; text-align: center; margin-top: 90px">
            <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="logo" class="" style="opacity: 0.1;">
        </div>
    @endif
@endif
<div style="top: -100px;">
<table class="full-width my-0 py-0 align-top" border="1" style="margin-top: -218px">
    <thead >
        <tr class="mt-0">
            <th class="border-bottom text-center py-1 desc" width="10%">Código</th>
            <th class="border-bottom text-center py-1 desc" width="10%">MARCA</th>
            <th class="border-bottom text-center py-1 desc" width="">Descripción</th>
            <th class="border-bottom text-center py-1 desc" width="10%">Cant.</th>
            <th class="border-bottom text-center py-1 desc" width="10%">U.M.</th>
            <th class="border-bottom text-center py-1 desc" width="10%">P.U</th>
            <th class="border-bottom text-center py-1 desc" width="10%">Importe</th>
        </tr>
    </thead>
    <tbody class="">
        @foreach($document->items as $row)
            <tr>
                <td class="p-1 text-center align-top desc">{{ $row->item->internal_id }}</div></td>
                <td class="p-1 text-center align-top desc">{{ $row->m_item->brand != null ? $row->m_item->brand->name : '' }}</div></td>
                <td class="p-1 text-left align-top desc text-upp">
                    @if($row->name_product_pdf)
                        {!!$row->name_product_pdf!!}
                    @else
                        {!!$row->item->description!!}
                    @endif

                      

                    @if($row->attributes)
                        @foreach($row->attributes as $attr)
                            <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                        @endforeach
                    @endif
                    {{-- @if($row->discounts)
                        @foreach($row->discounts as $dtos)
                            <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                        @endforeach
                    @endif --}}

                    @if($row->item->is_set == 1)
                     <br>
                     @inject('itemSet', 'App\Services\ItemSetService')
                        {{join( "-", $itemSet->getItemsSet($row->item_id) )}}
                    @endif
                </td>
                <td class="p-1 text-center align-top desc">
                    @if(((int)$row->quantity != $row->quantity))
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                <td class="p-1 text-center align-top desc">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                <td class="p-1 text-right align-top desc">{{ number_format($row->unit_price, 2) }}</td>
                <td class="p-1 text-right align-top desc">{{ number_format($row->total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>

</div>
@if($document != null)
    <table class="full-width border-box my-2">
        <tr>
            <td class="text-upp p-2">SON:
                @foreach(array_reverse( (array) $document->legends) as $row)
                    @if ($row->code == "1000")
                        {{ $row->value }} {{ $document->currency_type->description }}
                    @else
                        {{$row->code}}: {{ $row->value }}
                    @endif
                @endforeach
            </td>
        </tr>
    </table>
    <table class="full-width border-box my-2">
        <tr>
            <td class="text-upp p-2">OBSERVACIONES:
                @if($document->additional_information)
                    @foreach($document->additional_information as $information)
                        @if ($information)
                            {{ $information }}
                        @endif
                    @endforeach
                @endif
            </td>
        </tr>
    </table>
    <table class="full-width mt-10 mb-10 border-bottom">
        <tr>
            <th class="border-box text-center py-1 desc">Importe bruto</th>
            <th class="border-box text-center py-1 desc">Descuentos</th>
            <th class="border-box text-center py-1 desc">Total valor venta</th>
            <th class="border-box text-center py-1 desc">I.G.V. 18%</th>
            <th class="border-box text-center py-1 desc">Total precio venta</th>
            <th class="border-box text-center py-1 desc">Pago a cuenta</th>
            <th class="border-box text-center py-1 desc">Neto a pagar</th>
        </tr>
        <tr>
            <td class="border-box text-center py-1 desc">
                @if($document->total_taxed > 0)
                    {{ $document->currency_type->symbol }} {{ number_format($document->total_taxed, 2) }}
                @endif
            </td>
            <td class="border-box text-center py-1 desc">
                @if($document->total_discount > 0)
                    {{ $document->currency_type->symbol }} {{ number_format($document->total_discount, 2) }}
                @endif
            </td>
            <td class="border-box text-center py-1 desc">
                @if($document->total_taxed > 0)
                    {{ $document->currency_type->symbol }} {{ number_format($document->total_taxed, 2) }}
                @endif
            </td>
            <td class="border-box text-center py-1 desc">
                {{ $document->currency_type->symbol }} {{ number_format($document->total_igv, 2) }}
            </td>
            <td class="border-box text-center py-1 desc">
                {{ $document->currency_type->symbol }} {{ number_format($document->total, 2) }}
            </td>
            <td class="border-box text-center py-1 desc"></td>
            <td class="border-box text-center py-1 desc">
                {{ $document->currency_type->symbol }} {{ number_format($document->total, 2) }}
            </td>
        </tr>
    </table>
    <table class="full-width border-box my-2">
        <tr>
            <th class="p-1" width="25%">Banco</th>
            <th class="p-1">Moneda</th>
            <th class="p-1" width="30%">Código de Cuenta Interbancaria</th>
            <th class="p-1" width="25%">Código de Cuenta</th>
        </tr>
        @foreach($accounts as $account)
        <tr>
            <td class="text-center">{{$account->bank->description}}</td>
            <td class="text-center text-upp">{{$account->currency_type->description}}</td>
            <td class="text-center">
                @if($account->cci)
                {{$account->cci}}
                @endif
            </td>
            <td class="text-center">{{$account->number}}</td>
        </tr>
        @endforeach
    </table>
    @endif
    @if ($document->terms_condition)
    <br>
    <table class="full-width">
        <tr>
            <td>
                <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                {!! $document->terms_condition !!}
            </td>
        </tr>
    </table>
@endif
<table class="full-width">
    <tr>
        @php
            $document_description = null;
             if (is_object($document)) {
                if ($document && $document->prefix == 'NV') {
                    $document_description = 'NOTA DE VENTA ELECTRÓNICA';
                }
                if ($document && $document->document_type_id) {
                    $document_description = $document->document_type->description;
                }
            }

        @endphp
        @if ($document_description)
            <td class="text-center desc">Representación impresa de la {{ $document_description }} <br />Esta puede
                ser consultada en <a href="{!! route('search.index', ['external_id' => $document->external_id]) !!}" style="text-decoration: none; font-weight: bold;color:black;">{!! searchUrl() !!}</a></td>
        @else
            <td class="text-center desc">Representación impresa del Comprobante de Pago Electrónico. <br />Esta
                puede ser consultada en <a href="{!! route('search.index', ['external_id' => $document->external_id]) !!}" style="text-decoration: none; font-weight: bold;color:black;">{!! searchUrl() !!}</a></td>
        @endif
    </tr>
</table>