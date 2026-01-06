@php
$company = \App\Models\Tenant\Company::first();
$accounts = \App\Models\Tenant\BankAccount::all();
$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');

if ($document != null) {
    $establishment = $document->establishment;
    $logo = $establishment->logo ?? $company->logo;

    if ($logo === null) {
        $logo = $company->logo;
    } elseif (!file_exists(public_path($logo))) {
        $logo = $company->logo;
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace("storage/uploads/logos/storage/uploads/logos/", "storage/uploads/logos/", $logo);
    }

    $customer = $document->customer;
    $invoice = $document->invoice;
    $document_base = ($document->note) ? $document->note : null;
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);

    if ($document_base) {
        $affected_document_number = ($document_base->affected_document)
            ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT)
            : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);
    } else {
        $affected_document_number = null;
    }

    $payments = $document->payments;

    if ($document->payments) {
        $total_payment = $document->payments->sum('payment');
        $balance = ($document->total - $total_payment) - $document->payments->sum('change');
    }
}
@endphp

<head>
    <link href="{{ $path_style }}" rel="stylesheet" />
</head>
<body>
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
    @if ($company->footer_logo)
    @php
        $footer_logo = "storage/uploads/logos/{$company->footer_logo}";
    @endphp
    <tr>
        <td class="text-center pt-5">
            <img  style="max-height: 40px;" src="data:{{mime_content_type(public_path("{$footer_logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$footer_logo}")))}}" alt="{{$company->name}}">
        </td>
    </tr>
    @endif
    @if($company->footer_text_template)
    <tr>
        <td class="text-center desc pt-5">
            {!!func_str_find_url($company->footer_text_template)!!}
        </td>
    </tr>
    @endif
<tr>
            @php
                $document_description = null;
            @endphp
            @if ($document_description)
                <td class="text-center desc">Representación impresa de la {{ $document_description }} <br />Esta puede
                    ser consultada en {!! searchUrl() !!}</td>
            @else
                <td class="text-center desc">Representación impresa del Comprobante de Pago Electrónico. <br />Esta
                    puede ser consultada en {!! searchUrl() !!}</td>
            @endif
        </tr>
</table>
</body>
