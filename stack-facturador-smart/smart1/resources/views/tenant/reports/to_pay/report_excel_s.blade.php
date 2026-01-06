<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte</title>
</head>

<body>
    @php
        $total = [
            'total_pen' => 0,
            'total_usd' => 0,
            'total_payment_pen' => 0,
            'total_payment_usd' => 0,
            'total_to_pay_pen' => 0,
            'total_to_pay_usd' => 0,
        ];

    @endphp
    <div>
        <h3 align="center" class="title"><strong>Reporte Cuentas Por Pagar</strong></h3>
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
        <div class="">
            <div class=" ">
                <table class="">
                    <thead>
                        <tr>
                            <th>ZONA</th>
                            <th>VENDEDOR</th>
                            <th>TIPO</th>
                            <th>RUC</th>
                            <th>PROVEEDOR</th>
                            <th>NÚMERO</th>
                            <th>FECHA EMISIÓN</th>
                            <th>MONEDA</th>
                            <th>TOTAL</th>
                            <th>PAGADO</th>
                            <th>SALDO</th>
                            <th>TELÉFONO</th>
                            <th>ESTADO</th>
                            <th>DIAS ATRASO</th>
                            <th>VENCIMIENTO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $value)
                            @php
                                $type = $value['type'];
                                $currency_type_id = $value['currency_type_id'];
                                $total_to_pay = is_string($value['total_to_pay']) ? (float)str_replace(',', '', $value['total_to_pay']) : (float)$value['total_to_pay'];
                                $total_value = is_string($value['total']) ? (float)str_replace(',', '', $value['total']) : (float)$value['total'];
                                $total_payment = is_string($value['total_payment']) ? (float)str_replace(',', '', $value['total_payment']) : (float)$value['total_payment'];

                                if ($currency_type_id === 'PEN') {
                                    $total['total_pen'] += $total_value;
                                    $total['total_payment_pen'] += $total_payment;
                                    $total['total_to_pay_pen'] += $total_to_pay;
                                } else {
                                    $total['total_usd'] += $total_value;
                                    $total['total_payment_usd'] += $total_payment;
                                    $total['total_to_pay_usd'] += $total_to_pay;
                                }
                                $dued = '';
                                if (isset($value['delay_payment']) && $value['delay_payment'] > 0) {
                                    $dued = 'text-danger';
                                }
                                $type = strtolower($type);
                                $type_name = '';
                                switch ($type) {
                                    case 'purchase':
                                        $type_name = 'COMPRA';
                                        break;
                                    case 'purchase_fee':
                                        $type_name = 'CUOTA';
                                        break;
                                    case 'expense':
                                        $type_name = 'GASTO';
                                        break;
                                    case 'bill_of_exchange':
                                        $type_name = 'LETRA DE CAMBIO';
                                        break;
                                }
                            @endphp
                            @if ($total_to_pay > 0)
                            <tr>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ $value['supplier_zone_name'] ?? '-' }}</td>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ $value['seller_name'] ?? '-' }}</td>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ strtoupper($type_name) }}</td>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ $value['supplier_number'] ?? '-' }}</td>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ $value['supplier_name'] }}</td>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ $value['number_full'] }}</td>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ $value['date_of_issue'] }}</td>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ $value['currency_type_id'] === 'PEN' ? 'S/' : '$' }}</td>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ number_format($total_value, 2) }}</td>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ number_format($total_payment, 2) }}</td>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ number_format($total_to_pay, 2) }}</td>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ $value['supplier_telephone'] ?? '-' }}</td>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ $dued ? 'VENCIDO' : 'PENDIENTE' }}</td>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ $value['delay_payment'] ?? 0 }}</td>
                                <td style="{{ $dued ? 'color: red;' : '' }}">{{ $value['date_of_due'] ?? '-' }}</td>
                            </tr>
                            @endif
                        @endforeach

                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6"></td>
                            <td class="celda text-end" colspan="2">TOTAL S/</td>
                            <td class="celda text-end btd">{{ number_format($total['total_pen'] ,2)}}</td>
                            <td class="celda text-end btd">{{ number_format($total['total_payment_pen'] ,2)}}</td>
                            <td class="celda text-end btd">{{ number_format($total['total_to_pay_pen'] ,2)}}</td>
                            <td colspan="4"></td>
                        </tr>
                        <tr>
                            <td colspan="6"></td>
                            <td class="celda text-end" colspan="2">TOTAL $</td>
                            <td class="celda text-end bbd">{{ number_format($total['total_usd'] ,2)}}</td>
                            <td class="celda text-end bbd">{{ number_format($total['total_payment_usd'] ,2)}}</td>
                            <td class="celda text-end bbd">{{ number_format($total['total_to_pay_usd'] ,2)}}</td>
                            <td colspan="4"></td>
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