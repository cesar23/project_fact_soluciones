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
            <td width="40%" class="border-box bg-blue-light" style="padding: 0px;">
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
                <th colspan="4" class="bg-blue-light text-left p-5">
                    DATOS GENERALES
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%" colspan="2" class="text-left desc">
                    <strong>Punto de partida:</strong> <br>
                    {{ optional($document->sender_address_data)['address'] }}

                    @if(isset($document->sender_address_data)&&isset($document->sender_address_data['location_id']))
                    - {{ func_get_location($document->sender_address_data['location_id'] ) }}
                    @endif
                </td>
                <td width="50%" colspan="2" class="text-left desc">
                    <strong>
                        Autorización especial:
                    </strong>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-left desc">
                    <strong>Fecha de emisión:</strong>
                    {{ $document->date_of_issue->format('d/m/Y') }}
                </td>
                <td colspan="2" class="text-left desc">
                    <strong>
                        Punto de llegada:
                    </strong> <br>
                    {{ optional($document->receiver_address_data)['address'] }}

                    @if(isset($document->receiver_address_data)&&isset($document->receiver_address_data['location_id']))
                    - {{ func_get_location($document->receiver_address_data['location_id'] ) }}
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="4" class="text-left desc">
                    <strong>Documentos relacionados:</strong>
                    {{ $document->reference_check() }}
                </td>

            </tr>
            <tr>
                <td colspan="4" class="text-left desc">
                    <strong>Fecha de traslado:</strong>
                    {{ $document->date_of_shipping->format('d/m/Y') }}
                </td>
            </tr>
        </tbody>
        <thead>
            <tr>
                <th colspan="4" class="bg-blue-light text-left p-5">
                    DATOS DEL REMITENTE
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%" colspan="2" class="text-left desc ">
                    <strong>Nombre o razón social:</strong> <br>
                    {{ $document->sender_data ? $document->sender_data['name'] : '' }}
                </td>
                <td width="50%" colspan="2" class="text-left desc">
                    <strong>
                        Tipo y número de identificación:
                    </strong> <br>
                    {{ $document->sender_data ? $document->sender_data['identity_document_type_description'] : '' }}-
                    {{ $document->sender_data ? $document->sender_data['number'] : '' }}
                </td>
            </tr>
        </tbody>
        <thead>
            <tr>
                <th colspan="4" class="bg-blue-light text-left p-5">
                    DATOS DEL DESTINATARIO
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%" colspan="2" class="text-left desc ">
                    <strong>Nombre o razón social:</strong> <br>
                    {{ $document->receiver_data ? $document->receiver_data['name'] : '' }}
                </td>
                <td width="50%" colspan="2" class="text-left desc">
                    <strong>
                        Tipo y número de identificación:
                    </strong> <br>
                    {{ $document->receiver_data ? $document->receiver_data['identity_document_type_description'] : '' }}-
                    {{ $document->receiver_data ? $document->receiver_data['number'] : '' }}
                </td>
            </tr>
        </tbody>
        <thead>
            <tr>
                <th colspan="4" class="bg-blue-light text-left p-5">
                    DATOS DEL TRANSPORTE Y TRASLADO
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%" colspan="2" class="text-left desc ">
                    <strong>Marca y placa:</strong>
                    @php
                        $secondary_plate_number = '';
                        if (isset($document->transport_data['secondary_plate_number'])) {
                            $secondary_plate_number = $document->transport_data['secondary_plate_number'];
                        }
                    @endphp
                    {{ $document->transport_data ? $document->transport_data['brand'] : '' }} /
                    {{ $document->transport_data ? $document->transport_data['plate_number'] : '' }}
                    {{ $secondary_plate_number }}
                </td>
                <td width="50%" colspan="2" class="text-left desc">
                    <strong>
                        Número de la tarjeta única de circulación:
                    </strong>
                    @php
                        $tuc = '';
                        if (isset($document->transport_data['tuc'])) {
                            $tuc = $document->transport_data['tuc'];
                        }
                    @endphp
                    {{ $tuc }}
                </td>
            </tr>
            <tr>
                <td width="50%" colspan="2" class="text-left desc ">
                    <strong>Conductor:</strong><br>
                    {{ optional($document->driver)->name }}
                </td>
                <td width="50%" colspan="2" class="text-left desc">
                    <strong>
                        Tipo y número de identificación:
                    </strong> <br>
                    @php
                        $document_type_driver = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->driver->identity_document_type_id);
                    @endphp
                    {{ $document_type_driver ? $document_type_driver->description : '' }}-{{ optional($document->driver)->number }}
                </td>
            </tr>
            <tr>
                <td width="50%" colspan="2" class="text-left desc ">
                    <strong>Número de licencia de conducir:</strong>
                    {{ optional($document->driver)->license }}
                </td>
                <td width="50%" colspan="2" class="text-left desc">
                    <strong>
                        Peso bruto total:
                    </strong>
                    {{ $document->total_weight }} {{ $document->unit_type_id }}
                </td>
            </tr>
        </tbody>
    </table>

    <table class="full-width mt-10">
        <thead>
            <tr>
                <th class="border-box text-center bg-grey desc">N°</th>
                <th class="border-box text-center bg-grey desc">Bien
                    <br>
                    normalizado
                </th>
                <th class="border-box text-center bg-grey desc">Código de <br>
                    bien</th>
                <th class="border-box text-center bg-grey desc"h>Código
                    <br>Producto <br>SUNAT

                </th>
                <th class="border-box text-center bg-grey desc">
                    Partida <br>
                    arancelaria
                </th>
                <th class="border-box text-center bg-grey desc">

                    Código <br>GTIN
                </th>
                <th class="border-box text-center bg-grey desc">
                    Descripción detallada
                </th>
                <th class="border-box text-center bg-grey desc">Unidad de
                    <br>
                    medida
                </th>
                <th class="border-box text-center bg-grey desc">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $idx => $row)
                <tr>
                    <td class="border-box bg-grey-light text-center desc">{{ $idx + 1 }}</td>
                    <td class="border-box bg-grey-light text-center desc">NO</td>
                    <td class="border-box bg-grey-light text-center desc">{{ $row->item->internal_id }}</td>
                    <td class="border-box bg-grey-light text-center desc">{{ $row->item->item_code }}</td>
                    <td class="border-box bg-grey-light text-center desc"></td>
                    <td class="border-box bg-grey-light text-center desc"></td>
                    <td class="border-box bg-grey-light text-center desc">{{ $row->item->description }}</td>
                    <td class="border-box bg-grey-light text-center desc">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="border-box bg-grey-light text-center desc">{{ $row->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>




</body>

</html>
