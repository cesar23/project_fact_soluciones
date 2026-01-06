<!DOCTYPE html>
<html>

<head>

    <title>Reporte General de Ventas por Cajas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #00b0f0;
            color: white;
            text-align: center;
            padding: 5px 0;
            font-weight: bold;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Importante para que respete los anchos definidos */
        }

        td {
            border: 1px solid #000;
            padding: 4px 6px;
            overflow: hidden;
            /* Para evitar que el contenido rompa el diseño */
        }

        .info-header {
            /* background-color: #e5b8e8; */
        }

        .date {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
        }

        .purple-text {
            color: #800080;
            font-weight: bold;
        }
    </style>
</head>
@php
    if (!function_exists('get_string_payment_method')) {
        function get_string_payment_method($payment_description, $methods_payment)
        {
            foreach ($methods_payment as $method) {
                if (strtolower($method['name']) == strtolower($payment_description)) {
                    return $method['sum'];
                }
            }
            return 0;
        }
    }
@endphp

<body>
    <!-- Cabecera principal -->
    <div class="header">REPORTE GENERAL DE VENTAS POR CAJAS</div>

    <!-- Tabla principal -->
    <table>

        <tr>
            <td colspan="2"></td>
            <td class="total-row">Fecha de Inicio</td>
            <td class="total-row">Fecha de final</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td class="total-row">CAJA {{ isset($data['cash']) ? $data['cash']->id : '1' }}</td>
            <td>{{ isset($data['cash_user_name']) ? $data['cash_user_name'] : 'USUARIO' }}</td>
            <td class="date">{{ $date_start ?? $cash_general_info['date_start'] }}</td>
            <td class="date">{{ $date_end ?? $cash_general_info['date_end'] }}</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td class="total-row">INFORMACION</td>
            <td></td>
            <td class="info-header">MONTOS DE OPERACION</td>
            <td></td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td class="total-row"  width="16.6%">Empresa:</td>
            <td width="16.6%">
                {{ $company->name }}
            </td>
            <td class="total-row" width="16.6%">Saldo inicial:</td>
            <td width="16.6%" class="text-right">
                {{ $data['cash_beginning_balance'] }}
            </td>
            <td class="total-row" width="16.6%">Por cobrar:</td>
            <td width="16.6%" class="text-right">
                {{ $data['document_credit_total'] }}
            </td>
        </tr>
        <tr>
            <td class="total-row">Ruc:</td>
            <td>
                {{ $company->number }}
            </td>
            <td class="total-row">Ingreso efectivo:</td>
            <td class="text-right">
                {{ $data['total_cash_efectivo'] }}
            </td>
            <td class="total-row">Notas de Débito:</td>
            <td class="text-right">
                {{ $data['nota_debito'] }}
            </td>
        </tr>
        <tr>
            <td class="total-row">Establecimiento:</td>
            <td>
                {{ $establishment->description }}
            </td>
            <td class="total-row">Egreso efectivo:</td>
            <td class="text-right">
                {{ $data['cash_egress'] }}
            </td>
            <td class="total-row">Notas de Crédito:</td>
            <td class="text-right">
                {{ $data['nota_credito'] }}
            </td>
        </tr>
        <tr>
            <td class="total-row">Usuario:</td>
            <td>
                {{ $data['cash_user_name'] }}
            </td>
            <td class="total-row">Total efectivo:</td>
            <td class="text-right">
                {{ $data['total_cash_efectivo'] - $data['cash_egress'] + $data['cash_beginning_balance'] }} 
            </td>
            <td class="total-row">Total propinas:</td>
            <td class="text-right">
                {{ $data['total_tips'] }}
            </td>
        </tr>
        <tr>
            <td class="total-row">Estado de caja:</td>
            <td></td>
            <td class="total-row">Billetera digital:</td>
            <td class="text-right">
                {{ $data['total_virtual'] }}
            </td>
            <td class="total-row">Total efectivo CPE:</td>
            <td class="text-right">
                {{ $data['cpe_total_cash'] }}
            </td>
        </tr>
        <tr>
            <td class="total-row">Fecha y hora apertura:</td>
            <td>
                {{ $cash_general_info['date_start'] }} {{ $cash_general_info['time_start'] }}
            </td>
            <td class="total-row">Dinero en bancos:</td>
            <td class="text-right">
                {{ $data['total_bank'] }}
            </td>
            <td class="total-row">Total efectivo NOTA DE VENTA:</td>
            <td class="text-right">
                {{ $data['sale_notes_total_cash'] }}
            </td>
        </tr>
        <tr>
            <td class="total-row">Fecha y hora cierre:</td>
            <td>
                {{ $cash_general_info['date_end'] }} {{ $cash_general_info['time_end'] }}
            </td>
            <td class="total-row">Ingreso total :</td>
            <td class="text-right">
                {{ $data['cash_income'] }}
            </td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td class="total-row">Saldo final:</td>
            <td class="text-right">
                {{ $data['cash_income'] + $data['cash_beginning_balance'] - $data['cash_egress'] }}
            </td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="text-center total-row">DESCRIPCION</td>
            <td class="info-header text-center total-row" colspan="2">SUMA DE INGRESO A CAJA y BANCOS</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        @php 
            $rightData = [
                ['title' => 'VENTAS A CREDITO', 'value' => $data['document_credit_total']],
                ['title' => 'VENTAS AL CONTADO', 'value' => $data['cash_income']],
                ['title' => 'EGRESO GENERAL', 'value' => $data['cash_egress']],
                ['title' => 'TOTAL', 'value' => $data['cash_income'] - $data['cash_egress'], 'is_total' => true]
            ];
            $payment_methods = collect($data['methods_payment'])->sortByDesc('sum')->values()->all();
        @endphp
        @foreach($payment_methods as $key => $method)
            <tr>
                <td class="total-row">{{ strtoupper($method['name']) }}:</td>
                <td >S/</td>
                <td class="text-right">{{ number_format((float)str_replace(',', '', $method['sum']), 2) }}</td>
                
                @if($key < 4)
                    <td @if(isset($rightData[$key]['is_total'])) @endif  class="total-row">{{ $rightData[$key]['title'] }}</td>
                    <td @if(isset($rightData[$key]['is_total'])) class="total-row" @endif>S/</td>
                    <td @if(isset($rightData[$key]['is_total']))  @endif class="text-right">
                        {{ number_format((float)str_replace(',', '', $rightData[$key]['value']), 2) }}
                    </td>
                @else
                    <td></td>
                    <td></td>
                    <td></td>
                @endif
            </tr>
        @endforeach

        @if(count($payment_methods) < 4)
            @for($i = count($payment_methods); $i < 4; $i++)
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td @if(isset($rightData[$i]['is_total']))  @endif class="total-row">{{ $rightData[$i]['title'] }}</td>
                    <td @if(isset($rightData[$i]['is_total'])) class="total-row" @endif>S/</td>
                    <td @if(isset($rightData[$i]['is_total'])) class="total-row text-right" @endif >
                        {{ number_format((float)str_replace(',', '', $rightData[$i]['value']), 2) }}
                    </td>
                </tr>
            @endfor
        @endif
{{-- 
        @foreach($payment_methods as $key => $method)
            @if($key >= 4)
                <tr>
                    <td class="total-row">{{ strtoupper($method['name']) }}:</td>
                    <td class="total-row">S/</td>
                    <td class="text-right">{{ number_format((float)str_replace(',', '', $method['sum']), 2) }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endif
        @endforeach --}}

        <!-- Totales -->
        <tr>
            <td class="total-row">SUMA TOTAL</td>
            <td class="total-row">S/</td>
            <td class="text-right total-row">
                {{ isset($data['cash_income']) ? number_format((float)str_replace(',', '', $data['cash_income']), 2) : '' }}
            </td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
</body>

</html>
