@php
    $establishment = $document->user->establishment;
    $tittle = str_pad($document->id, 8, '0', STR_PAD_LEFT);
@endphp
<html>

<head>
    {{-- <title>{{ $tittle }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
    <style>
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 5px;
        }

        .text-center {
            text-align: center;
        }

        .full-width {
            width: 100%;
        }

        .half-width {
            width: 50%;
        }

        .fourteen-width {
            width: 40%;
        }

        .ten-width {
            width: 10%;
        }

        .celda {
            text-align: center;
            padding: 5px;
            border: 0.1px solid black;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .five-width {
            width: 5%;
        }

        html {
            font-family: sans-serif;
            font-size: 12px;
        }

        .page-break {
            page-break-after: always;
        }

        table {
            width: 100%;
            border-spacing: 0;
            border: 1px solid black;
        }

        table.no-border {
            border: 0px solid white;

        }


        th {
            padding: 5px;
            text-align: center;
            border-color: #0088cc;
            border: 0.1px solid black;
        }

        .title {
            font-weight: bold;
            padding: 5px;
            font-size: 20px !important;
            text-decoration: underline;
        }

        p>strong {
            margin-left: 5px;
            font-size: 13px;
        }

        thead {
            font-weight: bold;
            background: #eeeeee;
            color: rgb(0, 0, 0);
            text-align: center;
        }
        .bk{
            background-color: #eeeeee;
            color: rgb(0, 0, 0);
            padding: 5px;
            margin: 5px;
        }
    </style>

</head>

<body>
    <table class="full-width">
        <tr>
            @if ($company->logo)
                <td width="40%">
                    <div class="company_logo_box">
                        <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                            alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                    </div>
                </td>
            @else
                <td width="40%">
                    {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px"> --}}
                </td>
            @endif

            <td width="10%" class="border-box py-4 px-2 text-center">
                {{-- <h5 class="text-center">{{ get_document_name('technical_service', 'SERVICIO TÉCNICO') }}</h5>
            <h3 class="text-center">{{ $tittle }}</h3> --}}
            </td>
            <td rowspan="2" width="50%" class="pl-3" valign="middle">
                <div class="text-center bk">
                    <h2>{{ 'RUC ' . $company->number }}</h2>
                    <h2>COMUNICACION DE BAJA</h2>
                    @php
                        $id = $document->id;
                        $number = str_pad($id, 8, '0', STR_PAD_LEFT);
                    @endphp
                    <h3>NÚMERO: {{ $number }}</h3>
                    <h4>NÚMERO DE TICKET: {{ $voided->ticket }}</h4>
                </div>

            </td>
        </tr>
        <tr>
            <td>
                <h3 class="">{{ $company->name }}</h3>
                <h5>
                    {{ $establishment->address !== '-' ? $establishment->address : '' }}
                    {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                    {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                    {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                </h5>

                @isset($establishment->trade_address)
                    <h5>{{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                    </h5>
                @endisset
                <h5>{{ $establishment->telephone !== '-' ? 'Central telefónica: ' . $establishment->telephone : '' }}
                </h5>
                <br>
                <h4>
                    <strong>FECHA DE EMISION:</strong> {{ $voided->date_of_issue->format('Y-m-d') }}

                </h4>
                <h4>
                    <strong>FECHA DE GENERACIÓN:</strong> {{ $voided->date_of_reference->format('Y-m-d') }}
                </h4>
            </td>
        </tr>

    </table>



    <table class="full-width mt-4 mb-5">
        <thead>
            <tr>
                <th>FECHA</th>
                <th>TIPO DE DOCUMENTO</th>
                <th>SERIE</th>
                <th>NÚMERO</th>
                <th>MOTIVO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="celda">{{ $voided->date_of_issue->format('Y-m-d') }}</td>
                <td class="celda">{{ $document->document_type->description }}</td>
                <td class="celda">{{ $document->series }}</td>
                <td class="celda">{{ $document->number }}</td>
                <td class="celda">{{ $voided_document->description }}</td>
            </tr>
        </tbody>

    </table>


</body>

</html>
