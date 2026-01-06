@php
    $path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
@endphp
<head>
    <link href="{{ $path_style }}" rel="stylesheet" />
</head>
<body>
<table class="full-width">
    <tr>
        <td class="text-center desc font-bold">Representación impresa del Comprobante de Pago Electrónico. <br/>Esta puede ser consultada en {!! searchUrl() !!}</td>
    </tr>
</table>
</body>