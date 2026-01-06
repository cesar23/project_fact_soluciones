@php
    $path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
@endphp
<head>
    <link href="{{ $path_style }}" rel="stylesheet" />
</head>
<body>
<table class="full-width">
    <tr>
        <td class="">
        {{-- <td class="text-center font-bold"> --}}
            {{-- {!!$document->terms_condition!!} --}}
            Todo cambio o devolución del producto será dentro de 15 dias útiles de realizada la compra, con sus accesorios y empaques completos sin señales de uso
        </td>
    </tr>
    <br><br>
</table>
</body>