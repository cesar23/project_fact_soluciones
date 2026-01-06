@php
    $path_style = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'style.css');
    // $company = "storage/uploads/logos/{$company->footer_logo}";
@endphp

<head>
    <link href="{{ $path_style }}" rel="stylesheet" />
</head>

<body>
    <table class="full-width">
        @php
            $company = \App\Models\Tenant\Company::first();
        @endphp
        @if ($company->footer_logo)
            @php
                $footer_logo = "storage/uploads/logos/{$company->footer_logo}";
            @endphp
            <tr>
                <td class="text-center pt-0">
                    <img style="max-height: 100px;"
                        src="data:{{ mime_content_type(public_path("{$footer_logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$footer_logo}"))) }}"
                        alt="{{ $company->name }}">
                </td>
            </tr>
        @endif
        @if ($company->footer_text_template)
            <tr>
                <td class="text-center desc pt-1">
                    {!! func_str_find_url($company->footer_text_template) !!}
                </td>
            </tr>
        @endif
        <tr>
            <td class="pt-1 text-center" style="font-size: 10px;">
                Todo cambio o devolución del producto será dentro de 15 dias útiles de realizada la compra, con sus accesorios y empaques completos sin señales de uso
            </td>
        </tr>
        <tr>
        
        </tr>
    </table>
</body>
