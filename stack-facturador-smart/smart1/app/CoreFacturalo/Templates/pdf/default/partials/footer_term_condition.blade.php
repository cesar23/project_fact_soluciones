@php
    $path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $font_size = null;
    $prefix = isset($document->prefix) ? $document->prefix : '';
    if($prefix){
        $contains_cot = strpos($prefix, 'COT') !== false;
        if($contains_cot){
            $font_size_table = \App\Models\Tenant\FontToDocumentsPdf::where('document_type', 'quotation')->where('format', 'a4')->first();
            if($font_size_table){
                $font_size = $font_size_table->font_size;
            }
        }
    }
@endphp
<head>
    <link href="{{ $path_style }}" rel="stylesheet" />
</head>
<body>
<table class="full-width">
    <tr>
        <td class="">
            @if($font_size)
                @php
                    $terms_condition = setFontSizeToElements($document->terms_condition, $font_size);
                @endphp
                {!!$terms_condition!!}
            @else
                {!!$document->terms_condition!!}
            @endif
        </td>
    </tr>
    <br><br>
</table>
</body>