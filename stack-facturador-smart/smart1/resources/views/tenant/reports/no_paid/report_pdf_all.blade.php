<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte General</title>
</head>

<style>
    @page{
        margin: 5px;
        margin-top: 40px;
        size: A4 landscape;
    }
    body{
        font-family: Arial, sans-serif;
        font-size: 7px;
        margin: 0;
        padding: 0;
    }
    .text-end{
        text-align: right;
    }

    .text-center{
        text-align: center;
    }

    .text-left{
        text-align: left;
    }
    .title{
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 8px;
    }
    table{
        border-collapse: collapse;
        width: 100%;
        border: 1px solid #333;
        table-layout: fixed;
        font-size: 6px;
    }
    th{
        background-color: #2599E2;
        color: white;
        border: 1px solid #333;
        padding: 2px 1px;
        font-size: 6px;
        text-align: center;
        word-wrap: break-word;
        font-weight: bold;
    }
    td{
        border: 1px solid #333;
        padding: 2px 1px;
        font-size: 6px;
        word-wrap: break-word;
        overflow: hidden;
        text-align: left;
        line-height: 1.1;
    }
    .danger{
        color: #d32f2f;
        font-weight: bold;
    }
    .celda{
        vertical-align: top;
    }

    /* Anchos fijos para columnas específicas */
    th:nth-child(1), td:nth-child(1) { width: 8%; }  /* RUC */
    th:nth-child(2), td:nth-child(2) { width: 12%; } /* Cliente */
    th:nth-child(3), td:nth-child(3) { width: 8%; }  /* Teléfono */
    th:nth-child(4), td:nth-child(4) { width: 8%; }  /* Zona */
    th:nth-child(5), td:nth-child(5) { width: 8%; }  /* Vendedores */
    th:nth-child(6), td:nth-child(6) { width: 8%; }  /* Línea */
    th:nth-child(7), td:nth-child(7) { width: 8%; }  /* Doc. Relac. */
    th:nth-child(8), td:nth-child(8) { width: 10%; } /* Serie-Número */
    th:nth-child(9), td:nth-child(9) { width: 6%; }  /* N° único */
    th:nth-child(10), td:nth-child(10) { width: 6%; } /* Emisión */
    th:nth-child(11), td:nth-child(11) { width: 6%; } /* Vencimiento */
    th:nth-child(12), td:nth-child(12) { width: 4%; } /* Moneda */
    th:nth-child(13), td:nth-child(13) { width: 6%; } /* Total */
    th:nth-child(14), td:nth-child(14) { width: 6%; } /* Cobrado */
    th:nth-child(15), td:nth-child(15) { width: 6%; } /* Saldo */
    th:nth-child(16), td:nth-child(16) { width: 5%; } /* Estado */
    th:nth-child(17), td:nth-child(17) { width: 5%; } /* Días atraso */

    /* Alineación para campos numéricos */
    td:nth-child(13), td:nth-child(14), td:nth-child(15), td:nth-child(17) {
        text-align: right;
    }

    /* Alineación para fechas */
    td:nth-child(10), td:nth-child(11) {
        text-align: center;
    }

    /* Alineación para estado */
    td:nth-child(16) {
        text-align: center;
        font-weight: bold;
    }
</style>

<body>
    <div class="title">Estado de cuenta al {{ date('Y-m-d') }}</div>
    
    @if (!empty($records))
        <table>
                    <thead>
                        <tr>
                            <th>RUC</th>
                            <th>Cliente</th>
                            <th>Telefono</th>
                            <th>Zona</th>
                            <th>Vendedores</th>
                            <th>Línea</th>
                            <th>Doc. Relac.</th>
                            <th>Serie-Número</th>
                            <th>N° único</th>
                            <th class="text-center">Emisión</th>
                            <th class="text-center">Vencimiento</th>
                            <th>Moneda</th>
                            <th>Total</th>
                            <th>Cobrado</th>
                            <th>Saldo</th>
                            <th>Estado</th>
                            <th>Días atraso</th>
                        


                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total_line_credit = 0;
                            $total_amount = 0;
                            $total_payment = 0;
                            $total_to_pay = 0;
                        @endphp
                        @foreach ($records as $key => $value)
                        @php
                            $class = $value['delay_payment'] > 0 ? 'danger' : '';
                            $total_line_credit += floatval(str_replace(',', '', $value['line_credit'] ?? '0'));
                            $total_amount += floatval(str_replace(',', '', $value['total'] ?? '0'));
                            $total_payment += floatval(str_replace(',', '', $value['total_payment'] ?? '0'));
                            $total_to_pay += floatval(str_replace(',', '', $value['total_to_pay'] ?? '0'));
                        @endphp
                                <tr>
                                    <td class="celda text-center {{ $class }}">{{ $value['customer_ruc'] ?? '-' }}</td>
                                    <td class="celda text-center {{ $class }}">{{ $value['customer_name'] ?? '-' }}</td>
                                    <td class="celda text-center {{ $class }}">{{ $value['customer_telephone'] ?? '-' }}</td>
                                    <td class="celda text-center {{ $class }}">{{ $value['customer_zone'] ?? '-' }}</td>
                                    <td class="celda text-center {{ $class }}">{{ $value['seller_name'] ?? '-' }}</td>
                                    <td class="celda text-end {{ $class }}">{{ $value['line_credit'] ?? '0.00' }}</td>

                                    <td class="celda {{ $class }}">{{ $value['document_related'] ?? '-' }}</td>
                                    <td class="celda text-center {{ $class }}">{{ $value['description'] ?? '-' }}</td>
                                    <td class="celda {{ $class }}">{{ $value['code'] ?? '-' }}</td>
                                    <td class="celda {{ $class }}">{{ $value['date_of_issue'] ?? '-' }}</td>
                                    <td class="celda {{ $class }}">{{ $value['date_of_due'] ?? '-' }}</td>
                                    <td class="celda {{ $class }}">{{ $value['currency_type_id'] ?? 'PEN' }}</td>
                                    <td class="celda {{ $class }}">{{ $value['total'] ?? '0.00' }}</td>
                                    <td class="celda {{ $class }}">{{ $value['total_payment'] ?? '0.00' }}</td>
                                    <td class="celda {{ $class }}">{{ $value['total_to_pay'] ?? '0.00' }}</td>
                                    <td class="celda {{ $class }}">{{ $value['delay_payment'] > 0 ? 'Vencido' : 'Pendiente' }}</td>
                                    <td class="celda {{ $class }}">{{ $value['delay_payment'] ?? 0 }}</td>

                                </tr>
                        @endforeach

                        <!-- Fila de totales -->
                        <tr style="background-color: #f5f5f5; font-weight: bold;">
                            <td class="celda text-center" colspan="5"><strong>TOTALES</strong></td>
                            <td class="celda text-end"><strong>{{ number_format($total_line_credit, 2) }}</strong></td>
                            <td class="celda" colspan="6"></td>
                            <td class="celda text-end"><strong>{{ number_format($total_amount, 2) }}</strong></td>
                            <td class="celda text-end"><strong>{{ number_format($total_payment, 2) }}</strong></td>
                            <td class="celda text-end"><strong>{{ number_format($total_to_pay, 2) }}</strong></td>
                            <td class="celda" colspan="2"></td>
                        </tr>
                    </tbody>
        </table>
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
    <script type="text/php">
        if (isset($pdf)) {
            $date = date('d/m/Y');
            $time = date('H:i:s');
            $pdf->page_text($pdf->get_width() - 75, 10, "Página {PAGE_NUM}", null, 7);
            $pdf->page_text($pdf->get_width() - 75, 16, "Fecha: $date", null, 7);
            $pdf->page_text($pdf->get_width() - 75, 22, "Hora: $time", null, 7);    
            $pdf->page_text($pdf->get_width() - 75, $pdf->get_height() - 20, "Página N°{PAGE_NUM} de {PAGE_COUNT}", null, 7);
            
        }
    </script>
</body>

</html>
