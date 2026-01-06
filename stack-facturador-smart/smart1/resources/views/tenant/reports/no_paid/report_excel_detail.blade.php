<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte</title>

    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .celda {
            text-align: right;
            border: 1px solid black !important;
        }

        .title {
            font-size: 20px;
        }

    </style>
</head>

<body>
    <div>
        <h3 align="center" class="title"><strong>Reporte Cuentas Por Cobrar</strong></h3>
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
                            <th>#</th>
                            <th class="text-center">Fecha Emisión</th>
                            <th># Nota de venta</th>
                            <th># Boleta/Factura</th>
                            <th>Cliente</th>
                            <th>Nombre Comercial</th>
                            <th>Código interno</th>
                            <th>Contacto cliente</th>
                            <th>Direccion</th>
                            <th>Vendedor</th>
                            <th>Pagado/Crédito</th>
                            <th>Días atrasados</th>
                            <th>Monto total</th>
                            <th>Monto pagados</th>
                            <th>Fecha de pagos</th>
                            <th>Metodo de pagos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $value)
                            @if ($value['total_to_pay'] > 0)
                                <tr>
                                    <td class="celda">{{ $loop->iteration }}</td>
                                    <td class="celda">{{ $value['date_of_issue'] }}</td>
                                    <td class="celda">{{ $value['type'] !== 'document' ? $value['number_full'] : '' }}
                                    </td>
                                    <td class="celda">{{ $value['type'] == 'document' ? $value['number_full'] : '' }}
                                    </td>
                                    <td class="celda">{{ $value['customer_name'] }}</td>
                                    <td class="celda">{{ $value['customer_trade_name'] }}</td>
                                    <td class="celda">{{ $value['customer_internal_code'] }}</td>
                                    <td class="celda">{{ $value['customer_telephone'] }}</td>
                                    <td class="celda">{{ $value['customer_address'] }}</td>
                                    <td class="celda">{{ $value['seller_name'] }}</td>
                                    <td class="celda">Crédito</td>
                                    <td class="celda">{{ $value['delay_payment'] }}</td>
                                    <td class="celda">{{ $value['total'] }}</td>
                                    @if (count($value['payments']) > 0)
                                        <td class="celda">{{ $value['payments'][0]['payment'] }}</td>
                                        <td class="celda">{{ $value['payments'][0]['date_of_payment'] }}</td>
                                        <td class="celda">{{ $value['payments'][0]['payment_method_type_description'] }}</td>
                                    @else
                                        <td class="celda"></td>
                                        <td class="celda"></td>
                                        <td class="celda"></td>
                                    @endif
                                </tr>
                                {{-- //si payments tiene mas de un registro crear otra fila y recorrer el array de payments excluyendo el primer registro --}}
                                @if (count($value['payments']) > 1)
                                    @foreach ($value['payments'] as $key => $payment)
                                        @if ($key > 0)
                                            <tr>
                                                <td class="celda" colspan="13"></td>
                                                <td class="celda">{{ $payment['payment'] }}</td>
                                                <td class="celda">{{ $payment['date_of_payment'] }}</td>
                                                <td class="celda">{{ $payment["payment_method_type_description"] }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                                <tr>
                                    <td class="celda" colspan="12"></td>
                                    <td class="celda" >Saldo</td>
                                    <td class="celda">{{ $value['total_to_pay'] }}</td>
                                    <td class="celda"></td>
                                    <td class="celda"></td>
                                </tr>
                            @endif
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
