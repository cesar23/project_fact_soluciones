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
        border: 2px solid #000;
        border-radius: 15px;
        padding: 5px;
    }
    .company_logo_ticket {
        max-width: 120px;
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
    .text-end{
        text-align: right;
    }
    .text-left{
        text-align: left;
    }
    .w-100{
        width: 100%;
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
        <table class="w-100">
            <tr>
                <td valign="top" class="text-left">
                    <strong style="font-size: 13px;">{{$document->prefix}}-{{$document->number}}</strong>
                </td>
                <td valign="top" class="text-end">
                    <strong style="font-size: 13px;">Nro. Bultos: {{number_format($document->package_number, 0)}}</strong>
                </td>
            </tr>
        </table>
    </div>
    <div class="text-center pt-2">
        <strong>
            {{$agency}}
        </strong>
    </div>
    <div class="text-center pt-3">
        <strong class="to-uppercase">
            {{$district_name}} - {{$province_name}} - {{$department_name}}
        </strong>
    </div>
    <div class="text-center pt-5">
        <table class="w-100">
            <tr>
                <td valign="top" class="text-left" width="50%">
                    {{$reason}}
                </td>
                <td valign="top" class="text-end" width="50%">
                    {{$address}}
                </td>
            </tr>
        </table>
    </div>
    <div class="text-center pt-5">
        <table class="w-100">
            <tr>
                <td valign="top" class="text-left" width="50%">
                    {{$customer_name}}
                </td>
                <td valign="top" class="text-end" width="50%">
                    {{$customer_document}}
                </td>
            </tr>
        </table>
    </div>
    <div class="text-center pt-3">
        <strong>RECOGE:</strong>{{$person}}
    </div>
    <div class="text-center pt-5">
        <table class="w-100">
            <tr>
                <td valign="top" class="text-left" width="50%">
                @if($identity_document_type_description)
                    {{$identity_document_type_description}}:
                @endif
                    {{$person_document}}
                </td>
                <td valign="top" class="text-end" width="50%">
                    TLF: {{$person_telephone}}
                </td>
            </tr>
        </table>
    </div>
    <div class="text-center pt-2">
        <img src="data:image/png;base64, {{$qr}}" alt="QR Code" width="100">
    </div>


    

</body>
</html>