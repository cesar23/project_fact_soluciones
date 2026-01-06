<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte POS - {{ $data['cash_user_name'] }}
        - {{ $data['cash_date_opening'] }} {{ $data['cash_time_opening'] }}
    </title>
    <style>
        body {
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
            border: 0.1px solid #0088cc;
        }

        .title {
            font-weight: bold;
            padding: 5px;
            font-size: 20px !important;
            text-decoration: underline;
        }

        p>strong {
            margin-left: 5px;
            font-size: 12px;
        }

        thead tr th {
            font-weight: bold;
            background: #0088cc;
            color: white;
            text-align: center;
        }

        .width-custom {
            width: 50%
        }
    </style>
</head>

<body>
    <div>
        <p align="center" class="title">
            <strong>Reporte Punto de Venta
            </strong>
        </p>
    </div>
    <div style="margin-top:20px; margin-bottom:20px;">
        <table>
            <tr>
                <td class="td-custom width-custom">
                    <p>
                        <strong>Empresa:
                        </strong>{{ $data['company_name'] }}
                    </p>
                </td>
                <td class="td-custom">
                    <p>
                        <strong>Fecha reporte:
                        </strong>{{ date('Y-m-d') }}
                    </p>
                </td>
            </tr>
            <tr>
                <td class="td-custom">
                    <p>
                        <strong>Ruc:
                        </strong>
                        {{ $data['company_number'] }}
                    </p>
                </td>
                <td class="width-custom">
                    <p>
                        <strong>Establecimiento:
                        </strong>
                        {{ $data['establishment_address'] }} -
                        {{ $data['establishment_department_description'] }}
                        - {{ $data['establishment_district_description'] }}
                    </p>
                </td>
            </tr>
            <tr>
                <td class="td-custom">
                    <p>
                        <strong>Vendedor:
                        </strong>
                        {{ $data['cash_user_name'] }}
                    </p>
                </td>
                <td class="td-custom">
                    <p>
                        <strong>Fecha y hora
                            apertura:
                        </strong>
                        {{ $data['cash_date_opening'] }} {{ $data['cash_time_opening'] }}
                    </p>
                </td>
            </tr>
            <tr>
                <td class="td-custom">
                    <p>
                        <strong>
                            Estado de caja:
                        </strong>
                        {{ $data['cash_state'] ? 'Aperturada' : 'Cerrada' }}
                    </p>
                </td>
                @if (!$data['cash_state'])
                    <td class="td-custom">
                        <p>
                            <strong>
                                Fecha y hora cierre:
                            </strong>
                            {{ $data['cash_date_closed'] }} {{ $data['cash_time_closed'] }}
                        </p>
                    </td>
                @endif
            </tr>
            <tr>
                <td colspan="2" class="td-custom">
                    <p>
                        <strong>
                            Montos de operación:
                        </strong>
                    </p>
                </td>
            </tr>
            <tr>
                <td class="td-custom">
                    <p>
                        <strong>
                            Saldo inicial:
                        </strong>
                        S/ {{ $data['cash_beginning_balance'] }}
                    </p>
                </td>
                <td class="td-custom">
                    <p>
                        <strong>
                            Ingreso efectivo:
                        </strong>
                        S/ {{ number_format($data['cash_income_x'], 2) }}
                    </p>
                </td>
            </tr>
            {{-- <tr>
                <td class="td-custom">
                    <p>
                        <strong>
                            Total efectivo:
                        </strong>
                        S/ {{ $data['cash_final_balance'] }}
                    </p>
                </td>
                <td class="td-custom">
                    <p>
                        <strong>
                            Egreso:
                        </strong>
                        S/ {{ $data['cash_egress'] }}
                    </p>
                </td>
            </tr> --}}
            {{-- <tr>
                <td class="td-custom">
                    <p>
                        <strong>
                            Saldo final:
                        </strong>
                        S/ {{ $data['cash_final_balance'] }}
                    </p>
                </td>
                <td class="td-custom">
                    <p>
                        <strong>
                            Egreso:
                        </strong>
                        S/ {{ $data['cash_egress'] }}
                    </p>
                </td>
            </tr> --}}



            <tr>
                <td class="td-custom">
                    @php
                        $total_cash = $data['cash_beginning_balance'] + $data['cash_income_x'];
                        if (isset($data['total_cash_egress_pmt_01'])) {
                            $total_cash = $total_cash - $data['total_cash_egress_pmt_01'];
                        }
                    @endphp
                    <p>
                        <strong>
                            Total efectivo:
                        </strong>
                        S/ {{ $total_cash ?? 0.0 }}
                        {{-- total de ingresos en efectivo y destino caja --}}
                    </p>
                </td>
                <td class="td-custom">
                    <p>
                        <strong>
                            Egreso efectivo:
                        </strong>
                        S/
                        @if (isset($data['total_cash_egress_pmt_01']))
                            {{ $data['total_cash_egress_pmt_01'] }}
                        @else
                            0.00
                        @endif

                        {{-- total de egresos (compras + gastos) en efectivo y destino caja --}}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <hr>
                </td>
                <td>
                    <hr>
                </td>
            </tr>
            <tr>
                <td class="td-custom">
                    <p>
                        <strong>
                            Total efectivo:
                        </strong>
                        S/ {{ $total_cash ?? 0.0 }}
                    </p>
                </td>
                <td class="td-custom">
                    <p>
                        <strong>
                            Billeteras digital:
                        </strong>
                        S/ {{ $data['cash_digital_x'] ?? 0.0 }}

                        {{-- (Saldo inicial + ingreso caja - egreso caja) --}}
                    </p>
                </td>

            </tr>
            <tr>
                <td class="td-custom">
                    <p>
                        <strong>
                            Total caja:
                        </strong>
                        @php
                            $total_cash_xx = $total_cash + $data['cash_digital_x'] + $data['cash_bank_x'];
                        @endphp
                        S/ {{ $total_cash_xx ?? 0.0 }}
                    </p>
                </td>
                <td class="td-custom">
                    <p>
                        <strong>
                            Dinero en bancos:
                        </strong>
                        S/ {{ $data['cash_bank_x'] ?? 0.0 }}

                        {{-- (Saldo inicial + ingreso caja - egreso caja) --}}
                    </p>
                </td>

            </tr>

            <tr>
                <td>
                    <hr>
                </td>
                <td>
                    <hr>
                </td>
            </tr>
            {{-- <tr>
                <td class="td-custom">
                    @php
                        $total_operations = $total_cash_xx + $data['receivable_x'];
                    @endphp
                    <p>
                        <strong>
                            Total operaciones:
                        </strong>
                        S/ {{ $total_operations }}
                    </p>
                </td>
                <td class="td-custom">
                    <p>
                        <strong>
                            Saldo final:
                        </strong>
                        S/ {{ $data['cash_final_balance'] }}
                    </p>
                </td>
             
            </tr> --}}
            <tr>
                <td>
                    <hr>
                </td>
                <td>
                    <hr>
                </td>
            </tr>
            <tr>
                <td class="td-custom">
                    <p>
                        <strong>
                            Notas de Débito:
                        </strong>
                        S/ {{ $data['nota_debito'] }}
                    </p>
                </td>
                <td class="td-custom">
                    <p>
                        <strong>
                            Notas de Crédito:
                        </strong>
                        S/ {{ $data['nota_credito'] }}
                    </p>
                </td>
            </tr>


            <tr>
                <td class="td-custom">
                    <p>
                        <strong>
                            Total efectivo CPE:
                        </strong>
                        S/ {{ $data['total_payment_cash_01_document'] ?? 0 }}
                    </p>
                </td>
                <td class="td-custom">
                    <p>
                        <strong>
                            Total efectivo NOTA DE VENTA:
                        </strong>
                        S/ {{ $data['total_payment_cash_01_sale_note'] ?? 0 }}
                    </p>
                </td>
            </tr>
            <tr>
                <td class="td-custom">
                    <p>
                        <strong>
                            Por cobrar:
                        </strong>
                        S/ {{ $data['document_credit_total'] }}
                    </p>
                </td>

                <td class="td-custom">
                    <p>
                        <strong>
                            Total propinas:
                        </strong>
                        S/ {{ $data['total_tips'] ?? 0 }}
                    </p>
                </td>
            </tr>
        </table>
    </div>
    @if ($data['cash_documents_total'] > 0)
        <div class="">
            <div class=" ">
                <table>
                    <thead>
                        <tr>
                            <th>
                                #
                            </th>
                            <th>
                                Descripcion
                            </th>
                            <th>
                                Suma
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $acum = 0;
                        @endphp
                        @foreach ($data['methods_payment'] as $item)
                            <tr>
                                <td class="celda">
                                    {{ $item['iteracion'] }}
                                </td>
                                <td class="celda">
                                    {{ $item['name'] }}
                                </td>
                                <td class="celda">
                                    @php
                                        $acum += $item['sum'];
                                    @endphp
                                    {{ $item['sum'] }}
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="celda"></td>
                            <td class="celda">Total</td>
                            <td class="celda">
                                {{ number_format($acum, 2, '.', '') }}

                            </td>
                        </tr>
                    </tbody>
                </table>
                @if (isset($data['document_credit']) && count($data['document_credit']) > 0)
                    @include('pos::cash.partials.document_credit_table_detail')
                @endif

                @if (isset($data['separate_cash_transactions']))
                    
                    @include('pos::cash.partials.cash_transactions_table_detail',['detail' => true])
                @else
                    <br>
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    #
                                </th>
                                <th>
                                    Tipo transacción
                                </th>
                                <th>
                                    Tipo documento
                                </th>
                                <th>
                                    Documento
                                </th>
                                <th>
                                    N° Operación
                                </th>
                                {{-- <th>
                            Fecha emisión
                        </th> --}}
                                <th>
                                    Fecha de pago
                                </th>
                                <th>
                                    Cliente/Proveedor
                                </th>
                                <th>
                                    N° Documento
                                </th>
                            
                            
                                <th>
                                    Moneda
                                </th>
                                <th>
                                    T.Pagado
                                </th>
                                <th>
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['all_documents'] as $key => $value)
                                <tr>
                                    <td class="celda">
                                        {{ $loop->iteration }}{{--
                                <br> {!! $value['usado'] !!}  <br> <strong>{{$value['tipo']}}</strong> --}}
                                    </td>
                                    <td class="celda">
                                        {{ $value['type_transaction'] }}
                                    </td>
                                    <td class="celda">
                                        {{ $value['document_type_description'] }}
                                    </td>
                                    <td class="celda">
                                        {{ $value['number'] }}
                                    </td>
                                    <td class="celda"></td>
                                    <td class="celda">
                                        {{ $value['date_of_issue'] }}
                                    </td>
                                    <td class="celda">
                                        {{ $value['customer_name'] }}
                                    </td>
                                    <td class="celda">
                                        {{ $value['customer_number'] }}
                                    </td>
                                    <td class="celda">
                                        {{ $value['currency_type_id'] }}
                                    </td>
                                    <td class="celda">
                                        {{ $value['total_payments'] ?? '0.00' }}
                                    </td>
                                    <td class="celda">
                                        {{ $value['total_string'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

            </div>
        </div>
    @else
        <div class="callout callout-info">
            <p>No se encontraron registros.
            </p>
        </div>
    @endif

</body>

</html>
