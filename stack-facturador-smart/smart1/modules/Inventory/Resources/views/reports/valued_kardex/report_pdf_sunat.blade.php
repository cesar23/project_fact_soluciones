<!DOCTYPE html>
<html lang="es">
@php
    //function check if a key exists in an array and return the value or return a default value
    function checkKey($array, $key, $default = null)
    {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }
    function get_operation($n_operation)
    {
        $catalogo = [
            '01' => 'Venta',
            '02' => 'Compra',
            '04' => 'Traslado entre establecimientos de la misma empresa',
            '08' => 'Importación',
            '09' => 'Exportación',
            '13' => 'Otros no comprendido en ningún código del presente catálogo',
            '14' => 'Venta sujeta a confirmación del comprador',
            '16' => 'Saldo inicial',
            '18' => 'Traslado emisor itinerante de comprobantes de pago Aquí no se está considerando el traslado a zona primaria.',
            '19' => 'Traslado a zona primaria',
            '03' => 'Venta con entrega a terceros',
            '05' => 'Devolución',
            '07' => 'Recojo de bienes transformados',
            '17' => 'Traslado de bienes para transformación',
        ];
        return $catalogo[$n_operation];
    }
    function get_month($n_month)
    {
        $months = [
            '01' => 'ENERO',
            '02' => 'FEBRERO',
            '03' => 'MARZO',
            '04' => 'ABRIL',
            '05' => 'MAYO',
            '06' => 'JUNIO',
            '07' => 'JULIO',
            '08' => 'AGOSTO',
            '09' => 'SETIEMBRE',
            '10' => 'OCTUBRE',
            '11' => 'NOVIEMBRE',
            '12' => 'DICIEMBRE',
        ];
        return $months[$n_month];
    }
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>13.1</title>

    <style>
        * {
            margin: 3px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .border-top-d {
            border-top: 1px dotted black;

        }
        .border-top {
            border-top: 1px solid black;
        }
        .border-y{
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }
    </style>
</head>

<body>
    <div>
        {{-- <h3 align="center" class="title"><strong>FORMATO 13.1: "REGISTRO DE INVENTARIO PERMANENTE VALORIZADO - DETALLE DEL INVENTARIO VALORIZADO"</strong></h3> --}}
    </div>
    <br>
    <div style="margin-top:10px; margin-bottom:15px;">
        <table>
            <tr>
                <td colspan="8">
                    <p><b>FORMATO 13.1: "REGISTRO DE INVENTARIO PERMANENTE VALORIZADO" </b></p>
                </td>
            </tr>
            <tr>
                <td>
                    <p><b>PERÍODO: </b>
                        @if ($additionalData['month'])
                            {{ get_month($additionalData['month']) }}
                        @endif
                        {{ $additionalData['period'] }}
                    </p>
                </td>
                <td>

                </td>

                <td>
                </td>
                <td>
                </td>

            </tr>
            <tr>
                <td>
                    <p><b>RUC: </b>
                        {{ $company->number }}</p>
                </td>
                <td>

                </td>
            </tr>
            <tr>
                <td>
                    <p><b> {{ strtoupper($company->name) }}</b> </p>
                </td>
                <td>

                </td>
            </tr>
            <tr>
                <td>
                    <p><b>ESTABLECIMIENTO (1):</b> </p>
                </td>
                <td>
                    {{ optional($establishment)->description }}
                </td>
            </tr>





        </table>
        <table style="width: 100%;border-collapse: collapse;">
            <thead>
                <tr>
                    <th class="border-top-d text-left">FECHA</th>
                    <th class="border-top-d text-left" style="width:200px;">DOCUMENTO</th>
                    <th class="border-top-d text-left" style="width: 150px;">OPERACION</th>
                    <th class="border-top-d" style="width:200px;" colspan="3">ENTRADAS</th>
                    <th class="border-top-d" style="width:200px;" colspan="3">SALIDAS</th>
                    <th class="border-top-d" style="width:200px;" colspan="3">SALDO</th>
                </tr>

            </thead>
            <tbody>
                <tr>
                    <td colspan="2"></td>
                    <td class="text-center">
                        Código: {{ $additionalData['internal_id'] }}
                    </td>
                    <td colspan="9"></td>
                </tr>
                <tr>
                    <td class="border-top-d"></td>
                    <td class="border-top-d">
                        Unidad de medida: {{ $additionalData['unit_type_table_six']['code'] }} 
                        {{-- {{ $additionalData['unit_type_table_six']['description'] }} --}}
                    </td>
                    <td class="border-top-d">
                        Saldo anterior
                    </td>
                    <td class="border-top-d" colspan="3"></td>
                    <td class="border-top-d" colspan="3"></td>
                    @if ($show_init)
                        <td class="border-top-d text-right">
                            {{-- {{ $balance_quantity }} --}}
                            {{ number_format($init_stock,0) }}
                        </td>
                        <td class="border-top-d text-right">
                            {{-- {{ $balance_cost }} --}}
                            {{ number_format($init_cost_unit,4) }}

                        </td>
                        <td class="border-top-d text-right">
                            {{-- {{ $balance_total }} --}}
                            {{ number_format($init_cost_total,2) }}
                        </td>
                    @else
                        <td class="border-top-d" colspan="3"></td>
                    @endif

                </tr>
                <tr>
                    <td colspan="2">
                        Descripción: <u><b>{{ strtoupper($additionalData['description']) }}</b></u>
                    </td>
                    <td colspan="11"></td>
                </tr>
                <tr>
                    <td colspan="3">
                        Método de evaluación: COSTO PROMEDIO
                    </td>
                    <td colspan="10"></td>
                </tr>

                @php
                    $totals = [
                        'input_quantity' => 0,
                        'input_unit_price' => 0,
                        'input_total' => 0,
                        'output_quantity' => 0,
                        'output_unit_price' => 0,
                        'output_total' => 0,
                        'balance_quantity' => $init_stock,
                        'balance_total' => $init_cost_total,
                        'balance_cost' => $init_cost_unit,
                    ];
                    // get the last row with key 'type' = 'output'
                    $last_output =
                        array_reverse(
                            array_filter($records, function ($row) {
                                return $row['type'] == 'output';
                            }),
                        )[0] ?? [];
                    
                    // get the last row with key 'type' = 'input'
                    $last_input =
                        array_reverse(
                            array_filter($records, function ($row) {
                                return $row['type'] == 'input';
                            }),
                        )[0] ?? [];
                    
                    //get the last row of records
                    $last_row = array_reverse($records)[0] ?? [];
                    
                @endphp
                @foreach ($records as $row)
                    <tr>
                        <td>
                            {{ \Carbon\Carbon::parse($row['date_of_issue'])->format('d/m/Y') }}
                        </td>
                        {{-- <td>
                        {{ $row['document_type_id'] }}
                    </td> --}}
                        <td>
                            {{ $row['series'] }} - {{ $row['number'] }}
                        </td>
                        {{-- <td>
                     
                    </td> --}}
                        <td>
                            {{ $row['operation_type_code'] }}
                            {{ get_operation($row['operation_type_code']) }}
                        </td>

                        {{-- ENTRADAS --}}
                        <td class="text-right">
                            {{ number_format($row['input_quantity'],0)}}
                        </td>
                        <td class="text-right">
                            {{ number_format($row['input_unit_price'],4) }}
                        </td>
                        <td class="text-right">
                            {{ number_format($row['input_total'],2) }}
                        </td>

                        {{-- SALIDAS --}}
                        @if ($row['type'] == 'output')
                        <td class="text-right">
                                {{ number_format($row['output_quantity'],0) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($row['output_unit_price'],4) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($row['output_total'],2) }}
                            </td>
                        @else
                            <td></td>
                            <td></td>
                            <td></td>
                        @endif

                        {{-- SALDO --}}
                        @php
                            
                            // $balance_quantity +=  $row['quantity'] * $row['factor'];
                            // $balance_total += $row['total'] * $row['factor'];
                            // $balance_cost = ($balance_quantity != 0) ? round($balance_total / $balance_quantity, 4) : null;
                            
                            if ($row['type'] == 'input') {
                                $totals['input_quantity'] += $row['input_quantity'];
                                $totals['input_unit_price'] += $row['input_unit_price'];
                                $totals['input_total'] += $row['input_total'];
                            } else {
                                $totals['output_quantity'] += $row['output_quantity'];
                                $totals['output_unit_price'] += $row['output_unit_price'];
                                $totals['output_total'] += $row['output_total'];
                            }
                            $totals['balance_quantity'] += $row['balance_quantity'];
                            $totals['balance_total'] += $row['balance_total_cost'];
                            $totals['balance_cost'] += $row['balance_unit_cost'];
                            
                        @endphp

                        <td class="text-right">
                            {{-- {{ $balance_quantity }} --}}
                            {{ number_format($row['balance_quantity'],0) }}
                        </td>
                        <td class="text-right">
                            {{-- {{ $balance_cost }} --}}
                            {{ number_format($row['balance_unit_cost'],4) }}
                        </td>
                        <td class="text-right">
                            {{-- {{ $balance_total }} --}}
                            {{ number_format($row['balance_total_cost'],2) }}
                        </td>
                    </tr>
                @endforeach
                <tr>

                </tr>
                <tr>


                </tr>
                <tr class="border-top">
                    <td colspan="3" align="right" class="border-top text-right">
                    </td>
                    <td class="border-top text-right">
                        {{ number_format(checkKey($last_input, 'input_quantity', 0),0) }}
                    </td>
                    <td class="border-top text-right">
                        {{ number_format(checkKey($last_input, 'input_unit_price', 0),4) }}
                    </td>
                    <td class="border-top text-right">
                        {{ number_format(checkKey($last_input, 'input_total', 0),2) }}
                    </td>
                    <td class="border-top text-right">
                        {{ number_format(checkKey($last_output, 'output_quantity', 0),0) }}
                    </td>
                    <td class="border-top text-right">
                        {{ number_format(checkKey($last_output, 'output_unit_price', 0),4) }}
                    </td>
                    <td class="border-top text-right">
                        {{ number_format(checkKey($last_output, 'output_total', 0),2) }}
                    </td>
                    <td class="border-top text-right">
                        {{ number_format(checkKey($last_row, 'balance_quantity', 0),0) }}
                    </td>
                    <td class="border-top text-right">
                        {{ number_format(checkKey($last_row, 'balance_unit_cost', 0),4) }}
                    </td>
                    <td class="border-top text-right">
                        {{ number_format(checkKey($last_row, 'balance_total_cost', 0),2) }}
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
    <br>
</body>

</html>
