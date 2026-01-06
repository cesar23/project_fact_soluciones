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
    <table>
        <tr>
            <td colspan="4" align="center">
                <h3 align="center" class="title"><strong>Reporte de comisión de vendedores</strong></h3>
            </td>
        </tr>
    </table>
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
                $data = \Modules\Report\Helpers\UserCommissionHelper::getDataForReportCommissionDetailedV2(
                    $row,
                    $request,
                );
                $newRow->items = $data['items'];
                $items = $data['items'];
            }

            $total_quantity = 0;
            $total_total = 0;
            $total_commision = 0;

        @endphp
        <div class="">
            <div class=" ">
                <table class="">
                    <thead>

                        <tr>
                            <th>#</th>
                            <th>Vendedor</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Documento</th>
                            <th class="text-center">N° Documento</th>
                            <th class="text-center">Cliente</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Total venta</th>
                            <th class="text-center">Comisión</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($items as $key => $item)
                            @php
                                $item = (object) $item;
                                $total_quantity += $item->quantity;
                                $total_total += $item->total;
                                $total_commision += $item->total_commision;
                            @endphp
                            <tr>
                                <td class="celda">{{ $key + 1 }}</td>
                                <td class="celda">{{ $item->seller_name }}</td>
                                <td class="celda">{{ $item->date_of_issue }}</td>
                                <td class="celda">{{ $item->number_full }}</td>
                                <td class="celda">{{ $item->customer_number }}</td>
                                <td class="celda">{{ $item->customer_name }}</td>
                                <td class="celda" style="text-align: right;">{{ $item->quantity }}</td>
                                <td class="celda" style="text-align: right;">{{ number_format($item->total, 2) }}</td>
                                <td class="celda" style="text-align: right;">
                                    {{ number_format($item->total_commision, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" style="text-align: right; font-weight: bold;">Total</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($total_quantity, 2) }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($total_total, 2) }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($total_commision, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
                <br>
            </div>
        </div>
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
