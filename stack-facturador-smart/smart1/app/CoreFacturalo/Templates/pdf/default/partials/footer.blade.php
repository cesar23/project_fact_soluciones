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
            @if(file_exists(public_path("{$footer_logo}")))
            <tr>
                <td class="text-center pt-0">
                    <img style="max-height: 100px;"
                        src="data:{{ mime_content_type(public_path("{$footer_logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$footer_logo}"))) }}"
                        alt="{{ $company->name }}">
                </td>
            </tr>
            @endif
        @endif
        @if ($company->footer_text_template)
            <tr>
                <td class="text-center desc pt-1">
                    {!! func_str_find_url($company->footer_text_template) !!}
                </td>
            </tr>
        @endif
        @if(is_object($document) && isset($document->document_type_id) && isLegalDocument($document->document_type_id))
        <tr>
            <td class="pt-1"></td>
        </tr>
        <tr>
            @php
                $document_description = null;
                 if (is_object($document)) {
                    if ($document && $document->prefix == 'NV') {
                        $document_description = 'NOTA DE VENTA ELECTRÓNICA';
                    }
                    if ($document && $document->document_type_id && $document->document_type) {
                   
                            $document_description = $document->document_type->description;
                      
                 
                    }
                }
            @endphp
            @if ($document_description)
                <td class="text-center desc" style="font-size: 9px;">Representación impresa de la {{ $document_description }}. Esta puede
                    ser consultada en {!! searchUrl() !!}</td>
            @else
                <td class="text-center desc" style="font-size: 9px;">Representación impresa del Comprobante de Pago Electrónico. Esta
                    puede ser consultada en {!! searchUrl() !!}</td>
            @endif
        </tr>
        @endif
    </table>
</body>
