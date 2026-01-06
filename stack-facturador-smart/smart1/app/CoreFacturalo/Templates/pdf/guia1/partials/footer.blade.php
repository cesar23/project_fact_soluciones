  {{-- @php
    $path_style = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'style.css');
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
@endphp

<head>
    <link href="{{ $path_style }}" rel="stylesheet" />
</head>

<body>
@if($document)
    <table class="full-width">
        <tr>
            <td class="font-bold">CUENTAS:</td>
        </tr>

        @if (in_array($document->document_type->id, ['01', '03']))
            @foreach ($accounts as $account)
            <tr>
                <td class="font-xs">{{ $account->bank->description }}:{{ $account->number }}  @if ($account->cci) CCI: {{ $account->cci }} @endif - {{ $account->currency_type->description }}</td>
            </tr>
            @endforeach
        @endif
        <tr>
            <td class="font-xs">Representacion impresa de la {{ $document->document_type->description }}, esta puede ser consultada en {!! searchUrl() !!}</td>
        </tr>
    </table>
@endif
   {{--  <table class="full-width">
        @php
            $company = \App\Models\Tenant\Company::first();
        @endphp
        @if ($company->footer_logo)
            @php
                $footer_logo = "storage/uploads/logos/{$company->footer_logo}";
            @endphp
            <tr>
                <td class="text-center pt-5">
                    <img style="max-height: 40px;"
                        src="data:{{ mime_content_type(public_path("{$footer_logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$footer_logo}"))) }}"
                        alt="{{ $company->name }}">
                </td>
            </tr>
        @endif
        @if ($company->footer_text_template)
            <tr>
                <td class="text-center desc pt-5">
                    {!! func_str_find_url($company->footer_text_template) !!}
                </td>
            </tr>
        @endif
        <tr>
            <td class="pt-5"></td>
        </tr>
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
    </table> --}}
</body>
