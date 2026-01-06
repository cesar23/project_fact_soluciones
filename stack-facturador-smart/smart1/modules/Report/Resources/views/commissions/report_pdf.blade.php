<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Comisiones vendedores</title>
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
            background: #0088cc;
            color: white;
            text-align: center;
        }
    </style>
</head>

<body>
    <div>
        <p align="center" class="title"><strong>Reporte de comisión de vendedores</strong></p>
    </div>
    <div style="margin-top:20px; margin-bottom:20px;">
        <table>
            <tr>
                <td>
                    <p><strong>Empresa: </strong>{{ $company->name }}</p>
                </td>
                <td>
                    <p><strong>Fecha: </strong>{{ date('Y-m-d') }}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p><strong>Ruc: </strong>{{ $company->number }}</p>
                </td>

            </tr>
        </table>
    </div>
    @if (!empty($records))
        @php
            $new_records = [];
            foreach ($records as $row) {
                $name = $row->name;
                $newRow = new \stdClass();
                $newRow->name = $name;
                $data = \Modules\Report\Helpers\UserCommissionHelper::getDataForReportCommission($row, $request);
                $newRow->total_transactions = $data['total_transactions'];
                $newRow->acum_sales = $data['acum_sales'];
                $newRow->total_commision = $data['total_commision'];
            
                $new_records[] = $newRow;
            }

            
            $must_sales = $request->must_sales == 'true' ? true : false;
            $must_transactions = $request->must_transactions == 'true' ? true : false;
            $new_records = collect($new_records);
            if ($must_sales) {
                //ordenar por ventas
                $new_records = $new_records->sortByDesc(function ($row) {
                    return $row->acum_sales;
                });
            }
            
            if ($must_transactions) {
                //ordenar por transacciones
                $new_records = $new_records->sortByDesc(function ($row) {
                    return $row->total_transactions;
                });

                
            }
  
            
        @endphp
        <div class="">
            <div class=" ">
                <table class="">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vendedor</th>
                            <th class="text-center">Cantidad transacciones</th>
                            <th class="text-center">Ventas acumuladas</th>
                            <th class="text-center">Total comisiones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($new_records as $row)
                            {{-- @php
                                    $data = \Modules\Report\Helpers\UserCommissionHelper::getDataForReportCommission($row, $request);
                                @endphp --}}
                            <tr>
                                <td class="celda">{{ $loop->iteration }}</td>
                                <td class="celda">{{ $row->name }}</td>
                                <td class="celda">{{ $row->total_transactions }}</td>
                                <td class="celda">{{ $row->acum_sales }}</td>
                                <td class="celda">{{ $row->total_commision }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="callout callout-info">
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
