<?php

use App\Models\Tenant\Configuration;

$configuration = Configuration::first();
function getLocationData($value, $type = 'sale')
{
    $customer = null;
    $district = '';
    $department = '';
    $province = '';
    $type_doc = null;
    if ($type == 'sale') {
        $type_doc = $value->document;
    }
    if ($value && $type_doc && $type_doc->customer) {
        $customer = $type_doc->customer;
    }
    if ($customer != null) {
        if ($customer->district && $customer->district->description) {
            $district = $customer->district->description;
        }
        if ($customer->department && $customer->department->description) {
            $department = $customer->department->description;
        }
        if ($customer->province && $customer->province->description) {
            $province = $customer->province->description;
        }
    }
    return [
        'district' => $district,
        'department' => $department,
        'province' => $province,
    ];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REPORTE PRODUCTOS</title>
    <style>
        html {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-spacing: 0;
            border: 1px solid black;
        }

        .celda {
            text-align: center;
            padding: 5px;
            border: 0.1px solid black;
            font-size: 9px;
            word-wrap: break-word;
        }

        th {
            padding: 5px;
            font-size: 9px;
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
            background: #0088cc;
            color: white;
            text-align: center;
        }

        td {
            border: 0.1px solid black;
        }

        @page {
            margin: 10px;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    @if (!empty($records))
        <div class="">
                    
            <p><strong>Empresa: </strong>{{$company->name}}
                <strong>RUC: </strong>{{$company->number}}</p>
            <div class=" ">
                <table class="" style="table-layout:fixed;">
                    <thead>
                
                        <tr>
                            <th colspan="5" style="font-weight: bold; font-size: 14px;">RESUMEN DE VENTAS DE
                                PRODUCTOS
                                DE <br> {{ \Carbon\Carbon::parse($data_of_period['d_start'])->format('d-m-Y') }} AL
                                {{ \Carbon\Carbon::parse($data_of_period['d_end'])->format('d-m-Y') }}
                            </th>
                        </tr>
                        <tr>
                            <th width="10%">#</th>
                            <th width="40%">Producto</th>
                            <th>Cantidad</th>
                            <th>Ventas totales</th>
                            <th>Compras totales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total = 0;
                            $total_purchase = 0;
                        @endphp
                        @foreach ($records as $idx => $record)

                            @php
                                $total += $record['total_amount'];
                                $total_purchase += $record['purchase_amount'];
                            @endphp
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td class="text-left">{{ $record['item']->description }}</td>
                                <td class="text-right">{{ $record['total_quantity'] }}</td>
                                <td class="text-right">{{ number_format($record['total_amount'], 2) }}</td>
                                <td class="text-right">{{ number_format($record['purchase_amount'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right">
                                <strong>Total: </strong>
                            </td>
                            <td class="text-right">
                                {{ number_format($total, 2) }}</td>
                            <td class="text-right">
                                {{ number_format($total_purchase, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
