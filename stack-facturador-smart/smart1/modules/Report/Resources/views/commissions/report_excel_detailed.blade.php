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
    @php
        $apply_conversion_to_pen = true;
    @endphp
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
                $data = \Modules\Report\Helpers\UserCommissionHelper::getDataForReportCommissionDetailed($row, $request);
                $newRow->total_transactions = $data['total_transactions'];
                $newRow->acum_sales = $data['acum_sales'];
                $newRow->total_commision = $data['total_commision'];
                $newRow->items = $data['items'];
            
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
            
            
        @endphp
        <div class="">
            <div class=" ">
                @foreach ($new_records as $row)
                    <table class="">
                        <thead>
                            <tr>
                                <th colspan="5" style="background-color: #e8e8e8;">
                                    <strong>Vendedor: {{ $row->name }}</strong>
                                </th>
                            </tr>
                            <tr>
                                <th>Total transacciones: {{ $row->total_transactions }}</th>
                                <th>Ventas acumuladas: {{ $row->acum_sales }}</th>
                                <th colspan="3">Total comisiones: {{ $row->total_commision }}</th>
                            </tr>
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Unidad</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($row->items as $key => $item)
                                <tr>
                                    <td class="celda">{{ $key + 1 }}</td>
                                    <td class="celda">{{ $item->item_name }}</td>
                                    <td class="celda">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="celda">{{ $item->unit_type_id }}</td>
                                    <td class="celda">{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <br>
                @endforeach
            </div>
        </div>
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
