<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>HISTORICO STOCK</title>
</head>

<body>
    <div>
        {{-- <h3 align="center" class="title"><strong>FORMATO 13.1: "REGISTRO DE INVENTARIO PERMANENTE VALORIZADO - DETALLE DEL INVENTARIO VALORIZADO"</strong></h3> --}}
    </div>
    <br>
    <div style="margin-top:20px; margin-bottom:15px;">
        <table>
            <tr>
                <td colspan="8">
                    <p><b>HISTORICO STOCK</b></p>
                </td>
            </tr>
            <tr>
                <td>
                    <p><b>HASTA LA FECHA: </b> </p>
                </td>
                <td>
                    {{ $additionalData['date'] }}
                </td>

            </tr>
            <tr>
                <td>
                    <p><b>RUC: </b></p>
                </td>
                <td>
                    {{ $company->number }}
                </td>
            </tr>
            <tr>
                <td>
                    <p><b>APELLIDOS Y NOMBRES, DENOMINACIÓN O RAZÓN SOCIAL:</b> </p>
                </td>
                <td>
                    {{ $company->name }}
                </td>
            </tr>

        </table>

        <table>
            <tr>
                <td colspan="2"></td>
                <td colspan="3">
                    <p>
                        <b>
                            FISICO
                        </b>
                    </p>
                </td>
                <td colspan="3">
                    <p>
                        <b>
                            VALORADO
                        </b>
                    </p>
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <p>#</p>
                </td>
                <td>
                    <p><b>DESCRIPCION</b></p>
                </td>
                <td>
                    <p><b>INGRESO</b></p>
                </td>
                <td>
                    <p><b>SALIDA</b></p>
                </td>
                <td>
                    <p><b>SALDO</b></p>
                </td>
                <td>
                    <p><b>SALDO ANTERIOR</b></p>
                </td>
                <td>
                    <p><b>SALDO TOTAL</b></p>
                </td>
                <td>
                    <p><b>INGRESO</b></p>
                </td>

                <td>
                    <p><b>SALIDA</b></p>
                </td>
                <td>
                    <p><b>SALDO</b></p>
                </td>
                <td>
                    <p><b>SALDO ANTERIOR</b></p>
                </td>
                <td>
                    <p><b>SALDO TOTAL</b></p>
                </td>




            </tr>

            @php
                // dd($records);
                // $balance_quantity = 0;
                // $balance_total = 0;
                // $balance_cost = 0;
                
                //totals
                // $totals = [
                //     'input_quantity' => 0,
                //     'input_unit_price' => 0,
                //     'input_total' => 0,
                //     'output_quantity' => 0,
                //     'output_unit_price' => 0,
                //     'output_total' => 0,
                //     'balance_quantity' => 0,
                //     'balance_total' => 0,
                //     'balance_cost' => 0,
                // ];
            @endphp

            @foreach ($records as $key => $row)
                <tr>
                    <td>
                        {{ $key + 1 }}
                    </td>
                    <td>
                        {{ $row['description'] }}
                    </td>
                    <td>{{ $row['purchase_stock'] }}</td>
                    <td>
                        {{ $row['sale_stock'] }}

                    </td>
                    @php
                        $rest_stock = floatval($row['purchase_stock']) + floatval($row['sale_stock']);
                    @endphp
                    <td>{{ number_format($rest_stock, 4, '.', '') }}</td>
                    <td>{{ number_format($row['past_stock'], 4, '.', '') }}</td>
                    <td>
                        {{ number_format(floatval($row['past_stock']) + $rest_stock, 4, '.', '') }}
                    </td>
                    <td>
                        {{ $row['purchase_val'] }}

                    </td>
                    <td>
                        {{ $row['sale_val'] }}
                    </td>
                    @php
                        $rest_val = floatval($row['purchase_val']) + floatval($row['sale_val']);
                    @endphp
                    <td>
                        {{ number_format($rest_val, 2, '.', '') }}
                    </td>
                    <td>
                        {{ number_format($row['past_val'], 2, '.', '') }}

                    </td>
                    <td>

                        {{ number_format(floatval($row['past_val']) + $rest_val, 2, '.', '') }}
                    </td>

                    {{-- SALDO --}}
                    @php
                        
                        // $balance_quantity +=  $row['quantity'] * $row['factor'];
                        // $balance_total += $row['total'] * $row['factor'];
                        // $balance_cost = ($balance_quantity != 0) ? round($balance_total / $balance_quantity, 4) : null;
                        
                        // if ($row['type'] == 'input') {
                        //     $totals['input_quantity'] += $row['input_quantity'];
                        //     $totals['input_unit_price'] += $row['input_unit_price'];
                        //     $totals['input_total'] += $row['input_total'];
                        // } else {
                        //     $totals['output_quantity'] += $row['output_quantity'];
                        //     $totals['output_unit_price'] += $row['output_unit_price'];
                        //     $totals['output_total'] += $row['output_total'];
                        // }
                        
                        // $totals['balance_quantity'] += $row['balance_quantity'];
                        // $totals['balance_total'] += $row['balance_total_cost'];
                        // $totals['balance_cost'] += $row['balance_unit_cost'];
                    @endphp

                </tr>
            @endforeach
            <tr>

            </tr>
            {{-- <tr>
            <td colspan="5" align="right">
                TOTALES
            </td>
            <td>
                {{ $totals['input_quantity'] }}
            </td>
            <td>
                {{ $totals['input_unit_price'] }}
            </td>
            <td>
                {{ $totals['input_total'] }}
            </td>
            <td>
                {{ $totals['output_quantity'] }}
            </td>
            <td>
                {{ $totals['output_unit_price'] }}
            </td>
            <td>
                {{ $totals['output_total'] }}
            </td>
            <td>
                {{ $totals['balance_quantity'] }}
            </td>
            <td>
                {{ $totals['balance_cost'] }}
            </td>
            <td>
                {{ $totals['balance_total'] }}
            </td>
        </tr> --}}

        </table>
    </div>
    <br>
</body>

</html>
