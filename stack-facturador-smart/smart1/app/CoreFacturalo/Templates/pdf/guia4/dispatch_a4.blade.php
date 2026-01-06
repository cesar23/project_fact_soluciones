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
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }
    
    $customer = $document->customer;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    
    $document_number = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
    // $document_type_driver = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->driver->identity_document_type_id);
    // dd($document->items);
@endphp
<html>

<head>
    {{-- <title>{{ $document_number }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>
    @if ($document->state_type->id == '11' || $document->state_type->id == '09' || $document->state_type->id == '55' || $document->state_type->id == '01' || $document->state_type->id == '03')
        <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
            <img src="data:{{ mime_content_type(public_path('status_images' . DIRECTORY_SEPARATOR . 'anulado.png')) }};base64, {{ base64_encode(file_get_contents(public_path('status_images' . DIRECTORY_SEPARATOR . 'anulado.png'))) }}"
                alt="anulado" class="" style="opacity: 0.6;">
        </div>
    @endif
    @if ($document->soap_type_id == '01')
        <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
            <img src="data:{{ mime_content_type(public_path('status_images' . DIRECTORY_SEPARATOR . 'demo.png')) }};base64, {{ base64_encode(file_get_contents(public_path('status_images' . DIRECTORY_SEPARATOR . 'demo.png'))) }}"
                alt="anulado" class="" style="opacity: 0.6;">
        </div>
    @endif
    <table class="full-width">
        <tr>
            @if ($company->logo)
                <td width="10%">
                    <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                        alt="{{ $company->name }}" alt="{{ $company->name }}" class="company_logo" style="max-width: 300px">
                </td>
            @else
                <td width="10%">
                    {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px"> --}}
                </td>
            @endif
            <td width="50%" class="pl-3">
                <div class="text-left">
                    <h3 class="">{{ $company->name }}</h3>
                    <h4>{{ 'RUC ' . $company->number }}</h4>
                    <h5 style="text-transform: uppercase;">
                        {{ $establishment->address !== '-' ? $establishment->address : '' }}
                        {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                        {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                        {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                    </h5>
                    <h5>{{ $establishment->email !== '-' ? $establishment->email : '' }}</h5>
                    <h5>{{ $establishment->telephone !== '-' ? $establishment->telephone : '' }}</h5>
                </div>
            </td>
            <td width="40%" class="border-box" style="padding: 0px;">
                <table width="100%">
                    <tr>
                        <td class="text-center h4">
                            R.U.C. N° {{ $company->number }}
                        </td>
                    </tr>
                    <tr>
                        <td class=" h4 text-center">
                            {{ $document->document_type->description }}
                        </td>
                    </tr>
                    <tr>
                        <td class="h3 text-center">
                            {{ $document_number }}
                        </td>
                    </tr>
                </table>


            </td>
        </tr>
    </table>
    <table class="full-width mt-10 ">
        <thead>
            <tr>
                <th class="text-left desc-10" colspan="2" width="50%">
                    DATOS DEL DESTINATARIO
                </th>
                <th class="text-left desc-10" colspan="2" width="50%">
                    DATOS DEL PUNTO PARTIDA Y LLEGADA
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="20%" valign="top" class="desc">Nombre:</td>
                <td width="30%" valign="top" class="desc">{{$customer->name}}</td>
                <td width="20%" valign="top" class="desc">Dirección de partida:</td>
                <td width="30%" valign="top" class="desc">{{$document->origin->address}}
                
                    @isset($document->origin->location_id)
                    - {{ func_get_location($document->origin->location_id) }}
                @endisset
                </td>
            </tr>
            <tr>
                <td width="20%" valign="top" class="desc">RUC:</td>
                <td width="30%" valign="top" class="desc">{{$customer->number}}</td>
                <td width="20%" valign="top" class="desc">Dirección de llegada:</td>
                <td width="30%" valign="top" class="desc">{{$document->delivery->address}}
                    @isset($document->delivery->location_id)
                    - {{ func_get_location($document->delivery->location_id) }}
                @endisset
                </td>
            </tr>
        </tbody>
    </table>
    <table class="full-width mt-10">
        <thead>
            <tr>
                <th colspan="4" class="text-left desc-10" colspan="2" width="50%">
                    DATOS DEL TRASLADO
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="20%" class="desc">Fecha y hora Emisión:</td>
                <td width="30%" class="desc">{{$document->date_of_issue->format('d/m/Y h:i:s')}}</td>
                <td width="20%" class="desc">Documento relacionado:</td>
                <td width="30%" class="desc"> {{ $document->reference_check() }}</td>
            </tr>
            <tr>
                <td class="desc">Fecha de entrega de bienes al trasnportista:</td>
                <td class="desc">{{$document->date_of_shipping->format('d/m/y')}}</td>
                <td class="desc">Modalidad de traslado:</td>
                <td class="desc">{{optional($document->transport_mode_type)->description }}</td>
            </tr>
            <tr>
                <td class="desc">Nro. Bultos:</td>
                <td class="desc">{{$document->packages_number}}</td>
                <td class="desc">Peso bruto:</td>
                <td class="desc">{{$document->total_weight}} {{$document->unit_type_id}}</td>
            </tr>
            <tr>
                <td class="desc">Nro Precinto:</td>
                <td class="desc"></td>
                <td class="desc">Nro Contenedor:</td>
                <td class="desc"></td>
            </tr>
            <tr>
                <td class="desc">Motivo traslado:</td>
                <td class="desc">{{ optional($document->transfer_reason_type)->description }}</td>
                <td class="desc"></td>
                <td class="desc"></td>
            </tr>
        </tbody>
    </table>

    <table class="full-width mt-10">
        <thead>
            <tr>
                <th colspan="4" class="text-left desc-10"  width="50%">
                    DATOS DEL TRANSPORTE
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="20%" class="desc">Razon social transportista:</td>
                <td width="30%" class="desc">{{optional($document->dispatcher)->name}}</td>
                <td width="20%" class="desc">Placa Vehiculo Principal:</td>
                <td width="30%" class="desc">  {{ $document->transport_data ? $document->transport_data['plate_number']:'' }}</td>
            </tr>
            <tr>
                <td class="desc">RUC Transportista:</td>
                <td class="desc">{{optional($document->dispatcher)->number}}</td>
                <td class="desc">Placa Vehiculo Secundario:</td>
                <td class="desc">
                    @php
                        $second_plate = "";
                        if(isset($document->transport_data['secondary_plate_number'])) 
                        {
                            $second_plate = $document->transport_data['secondary_plate_number'];
                        }

                    @endphp
                    {{ $second_plate }}

                </td>
            </tr>
            <tr>
                <td class="desc">Registro MTC:</td>
                <td class="desc">{{optional($document->dispatcher)->number_mtc}}</td>
                <td class="desc">Nro de Licencia:</td>
                <td class="desc">     {{  optional($document->driver)->license }}</td>
            </tr>
            <tr>
                <td class="desc">Conductor:</td>
                <td class="desc">{{ optional($document->driver)->name }}</td>
                <td class="desc"></td>
                <td class="desc"></td>
            </tr>
           
        </tbody>
    </table>
    <table class="full-width mt-10">
        <thead>
            <tr>
                <th
                colspan="5"
                class="text-left desc-10"
                >
                DATOS DEL  BIEN TRANSPORTADO
            </th>
            </tr>
            <tr>
                <th class="text-center desc border-box">
                   Item
                </th>
                <th class="text-center desc border-box">
                    Código
                 </th>
                 <th class="text-center desc border-box">
                    Descripción
                 </th>
                 <th class="text-center desc border-box">
                    Und
                 </th>
                 <th class="text-center desc border-box">
                    Cantidad
                 </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $idx => $row)
            <tr>
                <td class="desc text-center border-box">{{ $idx + 1 }}</td>
                <td class="desc text-center border-box">{{ $row->item->internal_id }}</td>
                <td class="desc text-center border-box">{{ $row->item->description }}</td>
                <td class="desc text-center border-box">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                <td class="desc text-center border-box">{{ $row->quantity }}</td>
                </td>
            </tr>
        @endforeach
           
        </tbody>
    </table>

 
    <div class="mt-10">
        <span>OBSERVACIONES: {{$document->observations}}</span>
    </div>
  
   
</body>

</html>
