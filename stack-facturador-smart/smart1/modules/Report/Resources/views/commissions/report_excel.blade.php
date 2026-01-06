<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Comisión vendores</title>
</head>

<body>
    <div>
        <h3 align="center" class="title"><strong>Reporte de comisión de vendedores</strong></h3>
    </div>
    <br>
    <div style="margin-top:20px; margin-bottom:15px;">
        <table>
            <tr>
                <td>
                    <p><b>Empresa: </b></p>
                </td>
                <td align="center">
                    <p><strong>{{ $company->name }}</strong></p>
                </td>
                <td>
                    <p><strong>Fecha: </strong></p>
                </td>
                <td align="center">
                    <p><strong>{{ date('Y-m-d') }}</strong></p>
                </td>
            </tr>
            <tr>
                <td>
                    <p><strong>Ruc: </strong></p>
                </td>
                <td align="center">{{ $company->number }}</td>

            </tr>
        </table>
    </div>
    <br>
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
                    $acum_sales = $row->acum_sales;
                    //replace comma for empty string
                    $acum_sales = str_replace(',', '', $acum_sales);
                    return $acum_sales;
                    // return $row->acum_sales;
                });
            }
            
            if ($must_transactions) {
                //ordenar por transacciones
                $new_records = $new_records->sortByDesc(function ($row) {
                    return $row->total_transactions;
                });
            }
            // $new_records = $new_records->map(function ($row) {
            //     return [
            //         'name' => $row->name,
            //         'total_transactions' => $row->total_transactions,
            //         'total_sales' => $row->total_sales,
            //         'total_commision' => $row->total_commision,
            //     ];
            // });
            
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
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
