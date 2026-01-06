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

    $supplier = $document->supplier;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $tittle = $document->prefix . '-' . str_pad($document->id, 8, '0', STR_PAD_LEFT);
@endphp
<html>

<head>
    {{-- <title>{{ $tittle }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>
    <table class="full-width">
        <tr>
            <td width="30%">
                @if ($company->logo)
                    <div class="company_logo_box">
                        <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                            alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                    </div>
                @endif
            </td>
            <td width="40%" class="text-center">
                <strong>
                    <h3>ORDEN DE COMPRA TOC</h3>
                </strong>
            </td>
            <td width="30%"></td>
        </tr>
    </table>
    <table class="full-width border-box2">
        <tr>
            <td valign="top" width="70%" class="border-box2">
                <table width="100%">
                    <tr>
                        <td width="40%">
                            <strong>Fecha:</strong>
                        </td>
                        <td>
                            {{ $document->date_of_issue->format('Y-m-d') }}
                        </td>
                    </tr>
                    <tr>
                        <td width="40%">
                            <strong>COMPRADOR:</strong>

                        </td>
                        <td>
                            {{ $company->name }}
                        </td>
                    </tr>
                    <tr>
                        <td width="40%">
                            <strong>
                                RUC:
                            </strong>
                        </td>
                        <td>
                            {{ $company->number }}
                        </td>
                    </tr>
                    <tr>
                        <td width="40%">
                            <strong>
                                DOMICILIO FISCAL:
                            </strong>
                        </td>
                        <td>
                            {{ $establishment->address !== '-' ? $establishment->address : '' }}
                            {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                            {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                            {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td width="40%">
                            <strong>
                                CELULAR:
                            </strong>
                        </td>
                        <td>
                            {{ $establishment->telephone }}
                        </td>
                    </tr>
                </table>
            </td>
            <td valign="top" width="30%" class="text-center border-box2" style="margin:0;padding:0;">
                <table width="100%">
                    <tr>
                        <td valign="top" style="background-color:#4897d9;">
                            <strong>N° OC</strong>
                        </td>

                    </tr>
                    <tr>
                        <td valign="middle" style="height: 80px;">
                            <h3>
                                {{ $tittle }}
                            </h3>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class=" mt-2 border-box2">
        <table class="full-width border-box2">
            <tr>
                <td width="60%">
                    <table class="full-width">
                        <tr>
                            <td width="40%">
                                <strong>PROVEEDOR:</strong>
                            </td>
                            <td>
                                {{ $supplier->name }}
                            </td>
                        </tr>

                        <tr>
                            <td width="40%">
                                <strong>
                                    RUC:
                                </strong>
                            </td>
                            <td>
                                {{ $supplier->number }}
                            </td>
                        </tr>
                        <tr>
                            <td width="40%">
                                <strong>
                                    DIRECCION:
                                </strong>
                            </td>
                            <td>
                                {{ $supplier->address }}
                            </td>

                        </tr>
                        <tr>
                            <td width="40%">
                                <strong>
                                    CELULAR:
                                </strong>
                            </td>
                            <td>
                                {{ $supplier->telephone }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="40%">
                    <table class="full-width">
                        <tr>
                            <td>
                                <strong>TIPO:</strong>
                            </td>
                            <td>
                                {{ $document->type == 'goods' ? 'BIENES' : 'SERVICIOS' }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>CORREO:</strong>
                            </td>
                            <td>
                                {{ $supplier->email }}
                            </td>
                        </tr>
                        <tr></tr>
                        <tr>
                            <td>
                                <strong>CÓDIGO IDENTIFICADOR:</strong>
                            </td>
                            <td>
                                {{ $document->client_internal_id }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <table class="full-width mt-10 mb-10">
        <thead>
            <tr
                style="background-color: #4897d9; color: white; font-weight: bold; font-size: 12px; text-align: center;
            border: 1px solid #000; 
            ">
                <th class="border-top-bottom text-center" width="10%">Item</th>
                {{-- <th class="border-top-bottom text-center" width="20%">PRODUCTO/SERVICIO</th> --}}
                <th class="border-top-bottom text-center">DESCRIPCIÓN</th>
                <th class="border-top-bottom text-center" width="20%">PRECIO UNIT</th>
                <th class="border-top-bottom text-center" width="15%">Cantidad</th>
                <th class="border-top-bottom text-center" width="10%">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
                <tr>
                    <td class="text-center border-box2">{{ $loop->iteration }}</td>
                    {{-- <td class="text-center border-box2">{{ $row->item->description }}</td> --}}
                    <td class="text-center border-box2">{{ $row->item->description }}</td>
                    <td class="text-center border-box2">{{ number_format($row->unit_price, 2) }}</td>
                    <td class="text-center border-box2">{{ $row->quantity }}</td>
                    <td class="text-center border-box2">{{ number_format($row->total, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5" class="border-box2" style="height: 20px;background-color:#4897d9">

                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="border-box2" style="height: 20px;">

                </td>
                <td class="text-center border-box2">
                    <strong>SUB TOTAL</strong>
                </td>
                <td class="text-center border-box2">
                    <strong>{{ number_format($document->total_taxed, 2) }}</strong>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="border-box2" style="height: 20px;">

                </td>
                <td class="text-center border-box2">
                    <strong>IGV 18%</strong>
                </td>
                <td class="text-center border-box2">
                    <strong>{{ number_format($document->total_igv, 2) }}</strong>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="border-box2" style="height: 20px;">

                </td>
                <td class="text-center border-box2">
                    <strong>TOTAL</strong>
                </td>
                <td class="text-center border-box2">
                    <strong>{{ number_format($document->total, 2) }}</strong>
                </td>
            </tr>
            <tr>
                <td colspan="5" class="border-box2" style="height: 20px;">
                    @php
                        $letters = \App\CoreFacturalo\Helpers\Number\NumberLetter::convertToLetter($document->total);
                    @endphp
                    <strong>SON: {{ $letters }}</strong>
                </td>
            </tr>
            <tr>
                <td colspan="5" class="border-box2" style="height: 20px;">
                    
                </td>
            </tr>
            <tr>
                <td colspan="5" class="border-box2" style="height: 20px;">
                    <strong>NOTAS:</strong>
                    {!!$document->observation!!}
                </td>
            </tr>
            <tr>
                <td colspan="5" class="border-box2" style="height: 20px;">
                    
                </td>
            </tr>
            <tr>
                <td colspan="5" class="border-box2" >
                    <strong>AUTORIZACION</strong> <br>
                    <p>
                        <strong>CREADO POR:</strong> 
                        @if($document->createdBy)
                            {{$document->createdBy->name}}
                        @endif
                    </p>
                    <p>
                        <strong>APROBADO POR:</strong>
                        @if($document->approvedBy)
                            {{$document->approvedBy->name}}
                        @endif
                    </p>
                </td>
            </tr>
        </tfoot>
    </table>

</body>

</html>
