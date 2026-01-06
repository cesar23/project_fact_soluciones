<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<style>
    @page {
        margin: 5px;
    }
    body{
        font-family: Arial, sans-serif;
        font-size: 11px;
        border: 1px solid #000;
        border-radius: 5px;
        padding: 5px;
    }
    .company_logo_ticket {
        max-width: 80px;
    }
    .text-center {
        text-align: center !important;
    }
    .pt-2{
        padding-top: 2px;
    }
    .pt-5{
        padding-top: 5px;
    }
    .pt-10{
        padding-top: 10px;
    }
    .pt-3{
        padding-top: 3px;
    }
    .pt-4{
        padding-top: 4px;
    }
    .to-uppercase{
        text-transform: uppercase;
    }
    .container-address{
        min-height: 50px;
    }

</style>
@php
$logo = $company->logo;
if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }
@endphp
<body>
    @if ($logo && file_exists(public_path("{$logo}")))
    <div class="text-center">
        <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
            alt="{{ $company->name }}" class="company_logo_ticket contain">
    </div>
@endif
    <div class="text-center pt-5">
        
        {{$document->prefix}}-{{$document->number}} | Bultos: {{number_format($document->package_number, 0)}}
    </div>
    <div class="text-center pt-2">
        <strong>
            {{$person}}
        </strong>
    </div>
    <div class="text-center pt-2">
        {{$person_document}} | {{$person_telephone}}
    </div>
    <div class="text-center pt-3">
        {{$reason}}
    </div>
    <div class="text-center pt-3 container-address">
        {{$district_name}} | {{$address}}
    </div>
    <div class="text-center pt-4">
        <strong class="to-uppercase">
            {{$description_payments}}
        </strong>
    </div>
    <div class="text-center pt-4">
        <strong>
            SALDO: S/ {{number_format($pending, 2)}}
        </strong>
    </div>

</body>
</html>