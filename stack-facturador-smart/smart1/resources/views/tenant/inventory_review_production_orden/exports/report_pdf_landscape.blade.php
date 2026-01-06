<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Revisión Inventario</title>
    <style>
        @page {
            margin: 10px;
        }

        html {
            font-family: sans-serif;
            font-size: 14px;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        h3,
        h4,
        h5,
        h6,
        h1,
        h2 {
            margin: 0;
        }

        .title {
            font-weight: 500;
            text-align: center;
            font-size: 24px;
        }

        .label {
            font-weight: 500;
        }

        .table-records {
            margin-top: 24px;
        }

        .table-records tr th {
            font-weight: bold;
            background: #0088cc;
            color: white;
        }

        .table-records tr th,
        .table-records tr td {
            border: 1px solid #000;
            font-size: 12px;
        }

        .text-danger {
            color: red;
            background-color: red;
        }

        .company_logo_ticket {
            max-width: 140px;
        }

        .text-center {
            text-align: center !important;
        }

        .pt-2 {
            padding-top: 2px;
        }

        .pt-5 {
            padding-top: 5px;
        }

        .pt-10 {
            padding-top: 10px;
        }

        .pt-3 {
            padding-top: 3px;
        }

        .pt-4 {
            padding-top: 4px;
        }

        .to-uppercase {
            text-transform: uppercase;
        }

        .container-address {
            min-height: 50px;
        }

        .text-right {
            text-align: right !important;
        }
    </style>
</head>
@php
    $logo = $company->logo;
    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }
@endphp

<body>

    <table style="width: 100%">
        <tr>
            <td width="160px">
                @if ($logo && file_exists(public_path("{$logo}")))
                    <div class="text-center">
                        <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                            alt="{{ $company->name }}" class="company_logo_ticket contain">
                    </div>
                @endif
            </td>
            <td>
                <div>
                    <h3>
                        {{ $company->name }}
                    </h3>
                </div>
                <div>
                    <h4>
                        RUC {{ $company->number }}
                    </h4>
                </div>
                <div>
                    <h5>
                        {{ $establishment->address }}
                        {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                        {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                        {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                    </h5>
                </div>
                <div>
                    <h5>
                        {{ $establishment->email }}
                    </h5>
                </div>
                <div>
                    <h5>
                        {{ $establishment->telephone }}
                    </h5>
                </div>
            </td>
            <td></td>
            <td></td>
            <td>
                <h4>
                    Fecha: {{ date('d/m/Y') }}
                </h4>
            </td>
        </tr>

    </table>
    <div class="text-center">
        <strong>
            <h2>
                REPORTE DE INVENTARIO - PICKING
            </h2>
        </strong>
    </div>
    <table style="width: 100%" class="table-records">
        <thead>

            <tr>
                <th>#</th>
                <th align="left">Producto</th>
                <th align="center">Stock disponible</th>
                <th align="center">Cantidad Solicitada</th>
                <th align="center">Requerimiento</th>
                <th align="center">Cantidad de Pedidos</th>
                <th align="center"># Pedidos</th>

            </tr>
        </thead>
        <tbody>
            @foreach ($records as $idx => $row)
                @php
                    if ($idx == 0) {
                    }
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td align="left">{{ $row['item_fulldescription'] }}</td>
                    <td align="right">{{ $row['stock'] }}</td>
                    <td class="text-right">
                        {{ $row['total_quantity'] }}
                    </td>
                    <td class="text-right">
                        {{ $row['requirement'] }}
                    </td>
                    <td class="text-right">
                        {{ $row['count_production_order'] }}
                    </td>
                    <td class="text-right">
                        {{ $row['production_orders_number']->implode(', ') }}
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
