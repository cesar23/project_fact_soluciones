@php
    $customer = $document->customer;
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
@endphp
$configurations = \App\Models\Tenant\Configuration::first();
$configuration = \App\Models\Tenant\Configuration::first();
$company_name = $company->name;
$company_owner = null;
if ($configurations->trade_name_pdf) {
    $company_name = $company->trade_name;
    $company_owner = $company->name;
}
<html>
<head>
</head>
<body class="ticket">


<table class="full-width">
    <tr>
        <td class="text-center"><h4>{{ $company_name }}</h4></td>
    </tr>
    @if ($company_owner)
    <tr>
        <td class="text-center"><h5>De: {{ $company_owner }}</h5></td>
    </tr>
    @endif
    <tr>
        <td class="text-center"><h5>{{ 'RUC '.$company->number }}</h5></td>
    </tr>
    <tr>
        <td class="text-center pb-1"><h5>{{ $document_number }}</h5></td>
    </tr>
</table>
<table class="full-width">
    <tr >
        <td width="" class="pt-1"><p class="desc">F. Emisión:</p></td>
        <td width="" class="pt-1"><p class="desc">{{ $document->date_of_issue->format('Y-m-d') }}</p></td>
    </tr>
    <tr>
        <td width="" ><p class="desc">H. Emisión:</p></td>
        <td width="" ><p class="desc">{{ $document->time_of_issue }}</p></td>
    </tr>
    <tr>
        <td class="align-top"><p class="desc">Cliente:</p></td>
        <td><p class="desc">{{ $customer->name }}</p></td>
    </tr>

    @if ($document->reference_data)
        <tr>
            <td class="align-top"><p class="desc">D. Referencia:</p></td>
            <td>
                <p class="desc">
                    {{ $document->reference_data }}
                </p>
            </td>
        </tr>
    @endif
  
</table>
 
    
<table class="full-width mt-10 mb-10">
    <thead class="">
    <tr>
        <th class="border-top-bottom desc-9 text-center">Cant.</th>
        <th class="border-top-bottom desc-9 text-center">Unidad</th>
        <th class="border-top-bottom desc-9 text-left">Descripción</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        <tr>
            <td class="text-center desc-9 align-top font-bold">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center desc-9 align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
            <td class="text-left desc-9 align-top font-bold">
                @if($row->name_product_pdf)
                    {!!$row->name_product_pdf!!}
                @else
                    {!!$row->item->description!!}
                @endif

                  

                @if($row->item->is_set == 1)
                <br>
                @inject('itemSet', 'App\Services\ItemSetService')
                @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                    {{$item}}<br>
                @endforeach
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="3" class="border-bottom"></td>
        </tr>
    @endforeach
    </tbody>
</table> 

</body>
</html>
