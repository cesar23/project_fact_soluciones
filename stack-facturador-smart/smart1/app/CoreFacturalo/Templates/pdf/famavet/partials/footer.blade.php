@php
    $path_style = app_path(
        'CoreFacturalo' .
            DIRECTORY_SEPARATOR .
            'Templates' .
            DIRECTORY_SEPARATOR .
            'pdf' .
            DIRECTORY_SEPARATOR .
            'style.css',
    );
    // $company = "storage/uploads/logos/{$company->footer_logo}";
@endphp

<head>
    <link href="{{ $path_style }}" rel="stylesheet" />
</head>

<body>
    <table class="full-width">
        @php
            $company = \App\Models\Tenant\Company::first();
            $isCot = false;
            
            if (is_object($document)) {
                if (get_class($document) == 'App\Models\Tenant\Quotation') {
                    $isCot = true;
                }
            }
        @endphp
        @if ($company->footer_logo && $isCot)
            @php
                $footer_logo = "storage/uploads/logos/{$company->footer_logo}";
            @endphp
            <tr>
                <td class="text-center">
                    <h3>
                        NUESTROS ALIADOS COMERCIALES
                    </h3>
                </td>
            </tr>
            <tr>
                <td class="text-center">
                    <img 
                        src="data:{{ mime_content_type(public_path("{$footer_logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$footer_logo}"))) }}"
                        alt="{{ $company->name }}">
                </td>
            </tr>
        @endif


    </table>
</body>
