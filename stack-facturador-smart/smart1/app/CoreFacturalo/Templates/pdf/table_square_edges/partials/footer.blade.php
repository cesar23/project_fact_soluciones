@php
    $path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
@endphp
<head>
    <link href="{{ $path_style }}" rel="stylesheet" />
</head>
<body>
<table class="full-width">
    {{-- <tr>
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
    </tr> --}}
</table>
</body>