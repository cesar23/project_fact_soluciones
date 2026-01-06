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



$document_number = $document->prefix.'-'.str_pad($document->id, 8, '0', STR_PAD_LEFT);

@endphp
<html>

<head>
</head>

<body>
    @if($document->state_type->id == '11'  || $document->state_type->id == '09')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
    @endif
    @if($document->soap_type_id == '01')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."demo.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."demo.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
    @endif
    <table class="full-width">
        <tr>
            @if ($company->logo)
                <td width="20%">
                    <div class="company_logo_box">
                        <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                            alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                    </div>
                </td>
            @else
                <td width="20%">
                    {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px"> --}}
                </td>
            @endif
            <td width="50%" class="pl-3">
                <div class="text-left">
                    <h4 class="">{{ $company_name }}</h4>
                    @if ($company_owner)
                        De: {{ $company_owner }}
                    @endif
                    <h5>{{ 'RUC '.$company->number }}</h5>
                    <h6 style="text-transform: uppercase;">
                        {{ ($establishment->address !== '-')? $establishment->address : '' }}
                        {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                        {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                        {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                    </h6>

                    @isset($establishment->trade_address)
                    <h6>{{ ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : '' }}</h6>
                    @endisset

                    <h6>{{ ($establishment->telephone !== '-')? 'Central telefónica: '.$establishment->telephone : '' }}</h6>

                    <h6>{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</h6>

                    @isset($establishment->web_address)
                    <h6>{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</h6>
                    @endisset

                    @isset($establishment->aditional_information)
                    <h6>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</h6>
                    @endisset
                </div>
            </td>
            <td width="30%" class="border-box py-4 px-2 text-center">
                <h5 class="text-center">{{ 'DEVOLUCIÓN' }}</h5>
                <h3 class="text-center">{{ $document_number }}</h3>
            </td>
        </tr>
    </table>
    <table class="full-width mt-5">
        <tr>
            <td width="120px">Fecha de emisión</td>
            <td width="8px">:</td>
            <td>{{$document->date_of_issue->format('Y-m-d')}}</td>
        </tr>
        <tr>
            <td width="120px">MOTIVO</td>
            <td width="8px">:</td>
            <td>{{$document->devolution_reason->description}}</td>
        </tr>
        <tr>
            <td width="120px">OBSERVACIÓN</td>
            <td width="8px">:</td>
            <td>{{$document->observation}}</td>
        </tr>

    </table>


    <table class="full-width mt-10 mb-10">
        <thead class="">
            <tr class="bg-grey">
                <th class="border-top-bottom text-center py-2" width="8%">Cant.</th>
                <th class="border-top-bottom text-center py-2" width="8%">Unidad</th>
                <th class="border-top-bottom text-left py-2">Descripción</th>
                <th class="border-top-bottom text-center py-2" width="8%">Lote</th>
                <th class="border-top-bottom text-center py-2" width="8%">Serie</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->items as $row)
            <tr>
                <td class="text-center align-top">
                    @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                    @else
                    {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                <td class="text-center align-top">{{symbol_or_code( symbol_or_code($row->item->unit_type_id))}}</td>
                <td class="text-left align-top">
                    {!!$row->item->description!!}

                </td>
                <td class="text-center align-top">
                    @inject('itemLotGroup', 'App\Services\ItemLotsGroupService')
                    {{ $itemLotGroup->getLote($row->item->IdLoteSelected) }}

                </td>
                <td class="text-center align-top">

                    @isset($row->item->lots)
                    @foreach($row->item->lots as $lot)
                    @if( isset($lot->has_sale) && $lot->has_sale)
                    <span style="font-size: 9px">{{ $lot->series }}</span><br>
                    @endif
                    @endforeach
                    @endisset

                </td>
            </tr>
            <tr>
                <td colspan="5" class="border-bottom"></td>
            </tr>
            @endforeach

        </tbody>
    </table>
</body>

</html>