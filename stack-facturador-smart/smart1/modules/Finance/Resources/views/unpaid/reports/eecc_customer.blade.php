<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte</title>
    <style>
        html {
            font-family: sans-serif;
            font-size: 15px !important;
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
            border-color: #0088cc;
            border: 0.1px solid black;
        }

        .title {
            font-weight: bold;

            font-size: 20px !important;
            text-decoration: underline;
        }

        p>strong {
            margin-left: 5px;
            font-size: 13px;
        }

        thead {
            font-weight: bold;
            background: #0088cc;
            color: white;
            text-align: center;
        }


        .header {
            top: 0px;
            text-align: center;
        }


        /* Ajuste para el contenido principal */
        .content-wrapper {}

        .customer-info {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            font-size: 16px !important;
        }

        @page {
            margin-top: 65px;
            margin-left: 20px;
            margin-right: 20px;
            margin-bottom: 20px;
        }

        /* Aseguramos que la tabla tenga un margen inferior mínimo */
        table {
            margin-bottom: 7px;
        }

        .credit-info {
            width: 100%;
            margin: 0px 0;
        }

        .credit-info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
        }

        .credit-box {
            padding: 10px;
            width: 33%;
            vertical-align: top;
        }

        .credit-box-title {
            margin: 0 0 10px 0;
            font-size: 14px;
            text-decoration: underline;
            font-weight: bold;
        }

        .credit-item-table {
            width: 100%;
            margin-top: 5px;
        }

        .credit-item-table td {
            padding: 3px 3px;
            font-size: 12px;
            border: none;
        }

        .credit-item-label {
            font-weight: bold;
            text-align: left;
        }

        .credit-item-value {
            text-align: right;
        }

        .no-border {
            border: none !important;
        }

        .bank-info {
            width: 100%;
            margin-top: 20px;
        }

        /* Tabla contenedora para centrar */
        .bank-info-container {
            width: 100%;
            border: none;
        }

        .bank-info-container td {
            text-align: center;
        }

        /* Tabla de cuentas bancarias */
        .bank-accounts-table {
            width: 50%;
            margin: 0 auto;
            /* Esto no funciona en DomPDF, por eso usamos la tabla contenedora */
            border-collapse: collapse;
            border: 1px solid black;
        }

        .bank-accounts-table th,
        .bank-accounts-table td {
            border: 1px solid black;
            padding: 5px;
        }

        .payment-note {
            width: 100%;
            margin-top: 20px;
        }

        .payment-note-container {
            width: 100%;
            border: none;
        }

        .payment-note-container td {
            text-align: center;
        }

        .payment-note-table {
            width: 50%;
            margin: 0 auto;
            border-collapse: collapse;
            border: 1px solid black;
        }

        .payment-note-table td {
            padding: 5px;
            border: 1px solid black;
            font-size: 12px;
            text-align: left;
        }

        .payment-note-title {
            font-weight: bold;
            color: white;
            background-color: #0088cc;
        }

        .text-end {
            text-align: right;
        }

        .text-danger {
            color: red;
        }
        .f14{
            font-size: 15px !important;
        }
    </style>
</head>

<body>

    @php
        $logo = $company->logo;
        if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }
        $month_spanish = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre',
        ];

        $credit_line = $customer->line_credit;
        $total = 0;
        $total_payed = 0;
        $total_debt = 0;
        $total_debt_dued = 0;
        $fee_month = 0;
        $next_months = [];

        // Obtenemos los próximos 3 meses
        $current = \Carbon\Carbon::now();
        for ($i = 1; $i <= 3; $i++) {
            $next = $current->copy()->addMonths($i);
            $next_months[] = [
                'month' => $next->format('m'),
                'year' => $next->format('Y'),
                'name' => $month_spanish[$next->formatLocalized('%B')],
                'amount' => 0,
            ];
        }

        // Una sola iteración para calcular todos los totales
        foreach ($records as $value) {
            $debt = $value['total'] - $value['total_payment'];

            // Totales generales
            $total += $value['total'];
            $total_payed += $value['total_payment'];
            $total_debt += $debt;

            // Convertimos la fecha de string a Carbon
            try {
                // La fecha viene en formato d/m/Y, así que usamos ese formato directamente
                $due_date = \Carbon\Carbon::createFromFormat('d/m/Y', $value['date_of_due']);

                // Deuda vencida
                if ($value['delay_payment'] > 0) {
                    $total_debt_dued += $debt;
                }

                // Deuda del mes actual
                if (
                    $due_date->format('m') == $current->format('m') &&
                    $due_date->format('Y') == $current->format('Y')
                ) {
                    $fee_month += $debt;
                }

                // Deudas de los próximos 3 meses
                foreach ($next_months as $key => $month) {
                    if ($due_date->format('m') == $month['month'] && $due_date->format('Y') == $month['year']) {
                        $next_months[$key]['amount'] += $debt;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Aseguramos que setlocale esté configurado para español
        setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain', 'Spanish');


    @endphp


    <!-- Contenido principal -->
    <div class="content-wrapper">
        <div class="header">
            <table class="no-border">
                <tr>
                    <td width="33%">
                        @if (file_exists(public_path("{$logo}")) && !is_dir(public_path("{$logo}")))
                            <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                            alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                        @endif
                    </td>
                    <td width="33%" style="text-align: center;">
                        <h1>Estado de cuenta al {{ $date }}</h1>
                    </td>
                    <td width="33%">
                    </td>
                </tr>
            </table>
        </div>
        <div class="customer-info">
            <div>
                <strong >RUC:</strong>
                <span>{{ $customer->number }}</span>
            </div>
            <div>
                <strong>Cliente:</strong>
                <span>{{ $customer->name }}</span>
            </div>
            <div>
                <strong>Dirección:</strong>
                <span>{{ $customer->address }}</span>
            </div>
            <div>
                <strong>Ubigeo:</strong>
                <span>{{ $customer->ubigeo_full }}</span>
            </div>
            <div>
                <strong>Vendedor:</strong>
                <span>{{ optional($customer->seller)->name }}</span>
            </div>
        </div>
        <div class="credit-info">
            <table class="credit-info-table no-border" style="font-size: 14px !important;">
                <tr>
                    <td class="credit-box">
                        <table class="credit-item-table">
                            <thead>
                                <tr>
                                    <th colspan="2" class="credit-box-title f14">Resumen de línea de crédito</th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="credit-item-label f14">Línea de crédito:</td>
                                    <td class="credit-item-value f14">
                                        {{ number_format($credit_line, 2, '.', ',') }} </td>
                                </tr>
                                <tr>
                                    <td class="credit-item-label f14">Línea utilizada:</td>
                                    <td class="credit-item-value f14">{{ number_format($total, 2, '.', ',') }} </td>
                                </tr>
                                <tr>
                                    <td class="credit-item-label f14">Línea disponible:</td>
                                    <td class="credit-item-value f14">
                                        {{ number_format($credit_line - $total_debt, 2, '.', ',') }} </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td class="credit-box">
                        <table class="credit-item-table">
                            <thead>
                                <tr>
                                    <th colspan="2" class="credit-box-title f14">Resumen de línea de crédito</th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="credit-item-label f14">DEUDA TOTAL:</td>
                                    <td class="credit-item-value f14">{{ number_format($total_debt, 2, '.', ',') }} </td>
                                </tr>
                                <tr>
                                    <td class="credit-item-label f14">TOTAL VENCIDO:</td>
                                    <td class="credit-item-value f14">{{ number_format($total_debt_dued, 2, '.', ',') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="credit-item-label f14">CUOTA DEL MES:</td>
                                    <td class="credit-item-value f14">{{ number_format($fee_month, 2, '.', ',') }} </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td class="credit-box">
                        <table class="credit-item-table">
                            <thead>
                                <tr>
                                    <th colspan="2" class="credit-box-title f14">Cuota de los 3 próximos meses</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($next_months as $month)
                                    <tr>
                                        <td class="credit-item-label f14">{{ $month['name'] }}:</td>
                                        <td class="credit-item-value f14">
                                            {{ number_format($month['amount'], 2, '.', ',') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <div>
            <h2>Estado de cuenta actual</h2>
        </div>
        @if (!empty($records))

            <div class="">
                <div class=" ">
                    <table class="">
                        <thead>
                            <tr>
                                <th>Doc. Relac.</th>
                                <th class="text-center">Serie - Número</th>
                                <th>N° Único</th>
                                <th>Emisión</th>
                                <th>Vencimiento</th>
                                <th>Moneda</th>
                                <th>Total</th>
                                <th>Cobrado</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                                <th>Días atraso</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($records as $key => $value)
                                    @php
                                        $debt = $value['total'] - $value['total_payment'];
                                        $payed = $value['total_payment'];
                                        $isDued = $value['delay_payment'] > 0 ? true : false;
                                    @endphp
                                    <tr>
                                        <td class="celda {{ $isDued ? 'text-danger' : '' }}">
                                            {{ $value['doc_related'] }}
                                        </td>
                                        <td class="
                                            celda {{ $isDued ? 'text-danger' : '' }}" "
                                        >{{ $value['number_full'] }}</td>
                                        <td class="celda {{ $isDued ? 'text-danger' : '' }}">{{ $value['code'] }}</td>
                                        <td class="celda {{ $isDued ? 'text-danger' : '' }}">{{ $value['date_of_issue'] }}</td>
                                        <td class="celda {{ $isDued ? 'text-danger' : '' }}">{{ $value['date_of_due'] }}</td>
                                        <td class="celda {{ $isDued ? 'text-danger' : '' }}">{{ $value['currency_type_id'] }}</td>
                                        <td class="celda text-end {{ $isDued ? 'text-danger' : '' }}">{{ number_format($value['total'], 2, '.', ',') }}</td>
                                        <td class="celda text-end {{ $isDued ? 'text-danger' : '' }}">{{ number_format($payed, 2, '.', ',') }}</td>
                                        <td class="celda text-end {{ $isDued ? 'text-danger' : '' }}">{{ number_format($debt, 2, '.', ',') }}</td>
                                        <td class="celda {{ $isDued ? 'text-danger' : '' }}">{{ $value['state'] }}</td>
                                        <td class="celda {{ $isDued ? 'text-danger' : '' }}">{{ $value['delay_payment'] }}</td>
                                        
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="6" class="celda text-end">TOTAL DE DEUDA</td>
                                    <td class="celda text-end">{{ number_format($total, 2, '.', ',') }}</td>
                                    <td class="celda text-end">{{ number_format($total_payed, 2, '.', ',') }}</td>
                                    <td class="celda text-end">{{ number_format($total_debt, 2, '.', ',') }}</td>
                                </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div>
                <p>No se encontraron registros.</p>
            </div>
        @endif
        <div class="bank-info">
            <table class="bank-info-container">
                <tr>
                    <td>
                        <table class="bank-accounts-table">
                            <thead>
                                <tr>
                                    <th colspan="3">CUENTAS BANCARIAS</th>
                                </tr>
                                <tr>
                                    <th>Banco</th>
                                    <th>Número de cuenta</th>
                                    <th>CCI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bank_accounts as $bank_account)
                                    <tr>
                                        <td>{{ $bank_account['description'] }}</td>
                                        <td>{{ $bank_account['number'] }}</td>
                                        <td>{{ $bank_account['cci'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <div class="payment-note">
            <table class="payment-note-container">
                <tr>
                    <td>
                        <table class="payment-note-table">
                            <tr>
                                <td colspan="2" class="payment-note-title">NOTA:</td>
                            </tr>
                            <tr>
                                <td colspan="2">(*) Toda solicitud de devolución de mercadería debe ser informada a
                                    su analista de cartera, representante de ventas y servicio al cliente para su
                                    aprobación <strong> 7 días después de la entrega.</strong></td>
                            </tr>
                            <tr>
                                <td colspan="2">(*) Toda factura afecta a detracción, por favor de cancelar el 1.5%
                                    del importe a la siguiente cuenta bancaria:</td>
                            </tr>
                            <tr>
                                <td width="20%">BANCO DE LA NACIÓN:</td>
                                <td>{{ $company->detraction_account }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <script type="text/php">
                if (isset($pdf)) {
                    $date = date('d/m/Y');
                    $time = date('H:i:s');
                    $pdf->page_text($pdf->get_width() - 75, 10, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 7);
                    $pdf->page_text($pdf->get_width() - 75, 16, "Fecha: $date", null, 7);
                    $pdf->page_text($pdf->get_width() - 75, 22, "Hora: $time", null, 7);    
                    
                }
            </script>
    </div>

</body>

</html>
