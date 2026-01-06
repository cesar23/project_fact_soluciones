@php
// dd($document->establishment);
    // $establishment = $document->establishment;
    $path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    // $template_data = \App\Models\Tenant\Establishment::where('code', '=', $establishment->code)->first();
    $template_data = \App\Models\Tenant\Establishment::first();
@endphp
<head>
    <link href="{{ $path_style }}" rel="stylesheet" />
</head>
<body>
<table class="full-width">
    <tr>
        <td class="text-center desc-xs">
        {{--  <img src="{{ app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.''.$template_data->template_pdf.''.DIRECTORY_SEPARATOR.'logo.png') }}" width="100" />
         <br>
         <span style="font-size: 12px">Emitido desde <b>www.facturaperu.com.pe</b></span> --}}
     	</td>
    </tr>
</table>
</body>