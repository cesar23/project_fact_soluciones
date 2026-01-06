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

    $document_base = $document->note;
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $document_type_description_array = [
        '01' => 'Factura',
        '03' => 'Boleta DE VENTA',
        '07' => 'NOTA DE CREDITO',
        '08' => 'NOTA DE DEBITO',
    ];
    $identity_document_type_description_array = [
        '-' => 'S/D',
        '0' => 'S/D',
        '1' => 'DNI',
        '6' => 'RUC',
    ];
    $currency_type_description_array = [
        'PEN' => 'S/D',
        '0' => 'S/D',
        '1' => 'DNI',
        '6' => 'RUC',
    ];

    $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    // $optional = $document->optional;
    $accounts = \App\Models\Tenant\BankAccount::all();
@endphp
<html>
<head>
    {{--<title>{{ $document_number }}111111111</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>

@if($company->logo)
    <div class="text-center company_logo_box pt-5">
        <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo_ticket contain">
    </div>
@else
    <div class="text-center company_logo_box pt-5">
        <img src="{{ asset('logo/logo.jpg') }}" class="company_logo_ticket contain">
    </div>
@endif
<table class="full-width">
    <tr>
        <td class="text-center"><h3><strong>{{ $company->name }}</h3></td>
    </tr>
    <tr>
        <td class="text-center"><h4><strong>{{ 'R.U.C. '.$company->number }}</h4></td>
    </tr>
    <tr>
        <td class="text-center" style="text-transform: uppercase;">
            {{ ($establishment->address !== '-')? $establishment->address : '' }}
            {{ ($establishment->district_id !== '-')? '- '.$establishment->district->description : '' }}
            {{ ($establishment->province_id !== '-')? '- '.$establishment->province->description : '' }}
            {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
        </td>
    </tr>
    <tr>
        <td class="text-center">{{ ($establishment->e-mail !== '-')? $establishment->e-mail : '' }}</td>
    </tr>
    <tr>
        <td class="text-center pb-3">{{ ($establishment->telephone !== '-')? $establishment->telephone : '' }}</td>
    </tr>
    <tr>
        <td class="text-center pt-3 border-top"><h4><strong>{{ $document->document_type->description }}</h4></td>
    </tr>
    <tr>
        <td class="text-center pb-3 border-bottom"><h3><strong>{{ $document_number }}</h3></td>
    </tr>
</table>
<table class="full-width">
    <tr>
        <td width="45%" class="pt-3"><p class="desc">Fecha de emisión:</p></td>
        <td width="" class="pt-3"><p class="desc">{{ $document->date_of_issue->format('Y-m-d') }}</p></td>
    </tr>
    @isset($document->date_of_due)
    <tr>
        <td><p class="desc">FECHA DE VENC.</p></td>
        <td><p class="desc">{{ $document->date_of_due->format('Y-m-d') }}</p></td>
    </tr>
    @endisset

    <tr>
        <td><p class="desc">Cliente:</p></td>
        <td><p class="desc"><strong>{{ $customer->name }}</p></td>
    </tr>
    <tr>
        <td><p class="desc">{{ $customer->identity_document_type->description }}:</p></td>
        <td><p class="desc"><strong>{{ $customer->number }}</p></td>
    </tr>
    @if ($customer->address !== '')
        <tr>
            <td class="align-top"><p class="desc"><strong>Dirección:</p></td>
            <td>
                <p class="desc">
                    {{ $customer->address }}
                    {{ ($customer->district_id !== '-')? '- '.$customer->district->description : '' }}
                    {{ ($customer->province_id !== '-')? '- '.$customer->province->description : '' }}
                    {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
                </p>
            </td>
        </tr>
    @endif
    @if ($document->purchase_order)
        <tr>
            <td><p class="desc"><strong>ORDEN DE SERV.</p></td>
            <td><p class="desc">{{ $document->purchase_order }}</p></td>
        </tr>
    @endif
    @if ($document->guides)
        @foreach($document->guides as $guide)
            <tr>
                <td><p class="desc">{{ $guide->document_type_id }}</p></td>
                <td><p class="desc">{{ $guide->number }}</p></td>
            </tr>
        @endforeach
    @endif
    <tr>
        <td class="desc">Documento Afectado:</td>
        <td class="desc">{{ $affected_document_number }}</td>
    </tr>
    <tr>
        <td class="desc">Tipo de nota:</td>
        <td class="desc">{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>
    </tr>
    <tr>
        <td class="align-top desc">Descripción:</td>
        <td class="text-left desc">{{ $document_base->note_description }}</td>
    </tr>
    {{-- @if ($optional->salesman)
        <tr>
            <td class="desc">Vendedor:</td>
            <td class="desc">{{ $optional->salesman  }}</td>
        </tr>
    @endif
    @if ($optional->box_number)
        <tr>
            <td class="desc">N° Caja:</td>
            <td class="desc">{{ $optional->box_number  }}</td>
        </tr>
    @endif
    @if ($optional->method_payment)
        <tr>
            <td class="desc">Cond. de Pago:</td>
            <td class="desc">{{ $optional->method_payment  }}</td>
        </tr>
    @endif --}}
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
    @foreach($document->items as $row)
        <tr>
            <td class="text-center desc-9 align-top">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center desc-9 align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
            <td class="text-left desc-9 align-top">
                {!! $row->item->description !!}
                @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/>{!! $attr->description !!} : {{ $attr->value }}
                    @endforeach
                @endif
                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><small>{{ $dtos->factor * 100 }}% {{$dtos->description }}</small>
                    @endforeach
                @endif
            </td>
            <td class="text-right desc-9 align-top">{{ number_format($row->unit_price, 2) }}</td>
            <td class="text-right desc-9 align-top">{{ number_format($row->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="5" class="border-bottom"></td>
        </tr>
    @endforeach
        @if($document->total_exportation > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">Op. Exportación: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_exportation, 2) }}</td>
            </tr>
        @endif
        @if($document->total_free > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">Op. Gratuitas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_free, 2) }}</td>
            </tr>
        @endif
        @if($document->total_unaffected > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">Op. Inafectas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_unaffected, 2) }}</td>
            </tr>
        @endif
        @if($document->total_exonerated > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">Op. Exoneradas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_exonerated, 2) }}</td>
            </tr>
        @endif
        @if($document->total_taxed > 0)
            <tr>
                <td colspan="4" class="text-right font-bold desc">Op. Gravadas: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold desc">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif
        @if($document->total_discount > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'Anticipo':'Descuento TOTAL')}}: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="4" class="text-right font-bold desc">IGV: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc">{{ number_format($document->total_igv, 2) }}</td>
        </tr>
        <tr>
            <td colspan="4" class="text-right font-bold desc">Total a pagar: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold desc">{{ number_format($document->total, 2) }}</td>
        </tr>
    </tbody>
</table>
<table class="full-width">
    <tr>
        @foreach($document->legends as $row)
            <td class="desc pt-3" style="text-transform: uppercase;">Son: <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></td>
        @endforeach
    </tr>
    @foreach($accounts as $account)
        <tr>
            <td class="desc" >
                <span class="font-bold">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                <span class="font-bold">N°:</span> {{$account->number}}
                @if($account->cci)
                    <span class="font-bold">CCI:</span> {{$account->cci}}
                @endif
            </td>
        </tr>
    @endforeach
    @if(isset($document->optional->observations))
        <tr>
            <td class="desc">Obsevaciones</td>
        </tr>
        <tr>
            <td class="desc">{{ $document->optional->observations }}</td>
        </tr>
    @endif
    <tr>
        <td class="text-center pt-5"><img class="qr_code" src="data:image/png;base64, {{ $document->qr }}" /></td>
    </tr>
    <tr>
        <td class="text-center desc"><strong>Código Hash: {{ $document->hash }}</td>
    </tr>
    <tr>
        <td class="text-center desc pt-5">Representación Impresa del <strong>COMPROBANTE ELECTRONICO</strong>, generado desde el Sistema del Contribuyente.
    </tr>
</table>
</body>
</html>
