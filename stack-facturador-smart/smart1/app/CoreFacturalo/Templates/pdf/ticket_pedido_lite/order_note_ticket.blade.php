@php
$establishment = $document->establishment;
$configuration = \App\Models\Tenant\Configuration::first();
$configurations = \App\Models\Tenant\Configuration::first();
$company_name = $company->name;
$company_owner = null;
if ($configurations->trade_name_pdf) {
    $company_name = $company->trade_name;
    $company_owner = $company->name;
}
$establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
$logo = $establishment__->logo ?? $company->logo;

if ($logo === null && !file_exists(public_path("$logo}"))) {
    $logo = "{$company->logo}";
}

if ($logo) {
    $logo = "storage/uploads/logos/{$logo}";
    $logo = str_replace("storage/uploads/logos/storage/uploads/logos/", "storage/uploads/logos/", $logo);
}


    $customer_db = \App\Models\Tenant\Person::find($document->customer_id);
    $person_type = null;
    if($customer_db){
        $person_type_db = $customer_db->person_type;
        if($person_type_db){
            $person_type = $person_type_db->description;
        }
    }


    $customer = $document->customer;
    $invoice = $document->invoice;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $accounts = \App\Models\Tenant\BankAccount::all();
    $tittle = $document->prefix.'-'.str_pad($document->id, 8, '0', STR_PAD_LEFT);

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

@endphp
<html>
<body>

@if($company->logo)
    <div class="text-center company_logo_box pt-3">
        <img src="data:{{mime_content_type(public_path("{$logo}"))}};base64, {{base64_encode(file_get_contents(public_path("{$logo}")))}}" alt="{{$company->name}}" class="">
    </div>
@endif

<table class="full-width">
    <tr>
        <td class="align-top pt-3"><p class="desc font-bold">COMEDOR: {{ $person_type}}</p></td>
    </tr>
    <tr>
        <td><p class="desc font-bold pt-2">{{ $customer->name }}</p></td>
    </tr>
    <tr>
        <td width=""><p class="desc font-bold pt-2">{{ $document->date_of_issue->format('d/m/Y') }} - {{ $document->time_of_issue }}</p></td>
    </tr>
</table>

<table class="full-width mt-10 mb-10">
    <thead class="">
    <tr>
        <th class="border-top-bottom desc-9 text-left">CANT.</th>
        <th class="border-top-bottom desc-9 text-left">PRODUCTO</th>
        <th class="border-top-bottom desc-9 text-left">PRECIO</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        <tr>
            <td class="text-center desc-9 align-top">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-left desc-9 align-top">
                {!!$row->getTemplateDescription()!!}   
                @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/>{!! $attr->description !!} : {{ $attr->value }}
                    @endforeach
                @endif
                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><small>{{ $dtos->factor * 100 }}% {{$dtos->description }}</small>
                    @endforeach
                @endif
            </td>
            <td class="text-right desc-9 align-top">{{ number_format($row->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="3" class="border-bottom"></td>
        </tr>
    @endforeach
        <tr>
            <td colspan="2" class="text-right font-bold desc-10 pt-4">TOTAL: {{ $document->currency_type->symbol }} {{ number_format($document->total, 2) }}</td>
        </tr>
    </tbody>
</table>
<table>
    <tr>
        <td class="text-right font-bold desc">GRACIAS POR SU COMPRA ...</td>
    </tr>
</table>
</body>
</html>
