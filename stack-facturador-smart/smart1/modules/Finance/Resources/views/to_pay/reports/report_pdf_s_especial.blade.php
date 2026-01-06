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
        html {
            font-family: sans-serif;
            font-size: 12px;
            margin-top: 60px;
            margin-bottom: 60px;
        }

        table {
            width: 100%;
            border-spacing: 0;
            /* border: 1px solid black; */
        }

        .celda {
            /* text-align: center; */
            padding: 5px;
            font-size: 10px;
            /* border: 0.1px solid black; */
        }

        th {
            padding: 3px;
            text-align: center;
            /* border-color: #0088cc; */
            /* border: 0.1px solid black; */
        }

        .title {
            font-weight: bold;
            padding: 3px;
            font-size: 20px !important;
            text-decoration: underline;
        }

        p>strong {
            margin-left: 5px;
            font-size: 13px;
        }

        thead {
            font-weight: bold;
            color: black;
            text-align: center;
            font-size: 10px;
            border-bottom: 1px solid black;
        }

        .company_logo_box {
            text-align: center;
        }

        .company_logo {
            max-width: 150px;
        }

        .text-danger {
            color: red;
        }

        .text-center {
            text-align: center;
        }

        .text-xl {
            font-size: 20px;
        }

        .text-l {
            font-size: 18px;
        }

        .text-m {
            font-size: 16px;
        }

        .text-sm {
            font-size: 12px;
        }

        .text-end {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .page-number:after {
            content: counter(page);
        }

        .btd {
            border-top: 1px dashed black;
        }

        .bbd {
            border-bottom: 1px dashed black;
        }

        .w-supplier { width: 20%; }
        .w-numero { width: 10%; }
        .w-fecha { width: 8%; }
        .w-moneda { width: 6%; }
        .w-monto { width: 8%; }
        .w-telefono { width: 10%; }
        .w-estado { width: 8%; }
        .w-dias { width: 7%; }
        .w-vencimiento { width: 8%; }

        /* Para los totales */
        .w-total-label { width: 40%; }
        .w-total-monto { width: 12%; }
        .w-total-empty { width: 36%; }

        .text-left {
            text-align: left;
        }
        .f14{
            font-size: 14px;
        }

    </style>
</head>

<body>
    @php
        // Agrupamos los registros por zona del proveedor (equivalente a ubigeo en cuentas por cobrar)
        $records_by_zone = collect($records)->groupBy(function($record) {
            // Obtener la zona del proveedor
            if (isset($record['supplier_id']) && $record['supplier_id']) {
                $supplier = \App\Models\Tenant\Person::find($record['supplier_id']);
                if ($supplier && $supplier->getZone()) {
                    return $supplier->getZone()->name;
                }
            }
            return 'Sin zona definida';
        });

        // Para mantener los totales generales
        $total = [
            'total_pen' => 0,
            'total_usd' => 0,
            'total_payment_pen' => 0,
            'total_payment_usd' => 0,
            'total_to_pay_pen' => 0,
            'total_to_pay_usd' => 0,
        ];
        $logo = $company->logo;

        if ($logo === null && !file_exists(public_path("$logo}"))) {
            $logo = "{$company->logo}";
        }

        if ($logo) {
            $logo = "storage/uploads/logos/{$logo}";
            $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
        }
    @endphp

    <table class="table" style="border: none;">
        <tr>
            <td width="20%">
                @if ($company->logo)
                    <div class="company_logo_box">
                        <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                            alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                    </div>
                @endif
            </td>
            <td width="60%" class="text-center">
                <div class="text-xl text-center">CUENTAS POR PAGAR POR VENCIMIENTO</div>
                <div class="text-m text-center">
                    {{ $company->name }}
                </div>
                <div class="text-m text-center">
                    {{ $company->number }}
                </div>
            </td>
            <td width="20%" class="text-end">

            </td>
        </tr>
    </table>

    <br>

    @if (!empty($records))
        @foreach ($records_by_zone as $zone => $zone_records)
            <div class="zone-group">

                <table>
                    <thead>
                        <tr>
                            <th colspan="11" class="text-left f14">
                                <h3>{{ $zone }}</h3>
                            </th>
                        </tr>
                        <tr>
                            <th class="f14 w-supplier">PROVEEDOR</th>
                            <th class="f14 w-numero">NÚMERO</th>
                            <th class="f14 w-fecha">FECHA EMISIÓN</th>
                            <th class="f14 w-moneda">MONEDA</th>
                            <th class="f14 w-monto">TOTAL</th>
                            <th class="f14 w-monto">PAGADO</th>
                            <th class="f14 w-monto">SALDO</th>
                            <th class="f14 w-telefono">TELÉFONO</th>
                            <th class="f14 w-estado">ESTADO</th>
                            <th class="f14 w-dias">DIAS ATRASO</th>
                            <th class="f14 w-vencimiento">VENCIMIENTO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $zone_totals = [
                                'total_pen' => 0,
                                'total_usd' => 0,
                                'total_payment_pen' => 0,
                                'total_payment_usd' => 0,
                                'total_to_pay_pen' => 0,
                                'total_to_pay_usd' => 0,
                            ];
                        @endphp

                        @foreach ($zone_records as $value)
                            @php
                                $currency_type_id = $value['currency_type_id'];
                                $total_to_pay = is_string($value['total_to_pay']) ? (float)str_replace(',', '', $value['total_to_pay']) : (float)$value['total_to_pay'];
                                $total_value = is_string($value['total']) ? (float)str_replace(',', '', $value['total']) : (float)$value['total'];
                                $total_payment = is_string($value['total_payment']) ? (float)str_replace(',', '', $value['total_payment']) : (float)$value['total_payment'];

                                if ($currency_type_id === 'PEN') {
                                    $total['total_pen'] += $total_value;
                                    $total['total_payment_pen'] += $total_payment;
                                    $total['total_to_pay_pen'] += $total_to_pay;
                                    $zone_totals['total_pen'] += $total_value;
                                    $zone_totals['total_payment_pen'] += $total_payment;
                                    $zone_totals['total_to_pay_pen'] += $total_to_pay;
                                } else {
                                    $total['total_usd'] += $total_value;
                                    $total['total_payment_usd'] += $total_payment;
                                    $total['total_to_pay_usd'] += $total_to_pay;
                                    $zone_totals['total_usd'] += $total_value;
                                    $zone_totals['total_payment_usd'] += $total_payment;
                                    $zone_totals['total_to_pay_usd'] += $total_to_pay;
                                }
                                $dued = (isset($value['delay_payment']) && $value['delay_payment'] > 0) ? 'text-danger' : '';

                                // Obtener información del proveedor
                                $supplier_telephone = '-';
                                if (isset($value['supplier_id']) && $value['supplier_id']) {
                                    $supplier = \App\Models\Tenant\Person::find($value['supplier_id']);
                                    $supplier_telephone = $supplier ? ($supplier->telephone ?? '-') : '-';
                                }
                            @endphp

                            @if ($total_to_pay > 0)
                            <tr>
                                <td class="f14 celda w-supplier {{ $dued }}">{{ $value['supplier_name'] }}</td>
                                <td class="f14 celda w-numero text-center {{ $dued }}">{{ $value['number_full'] }}</td>
                                <td class="f14 celda w-fecha text-center {{ $dued }}">{{ $value['date_of_issue'] }}</td>
                                <td class="f14 celda w-moneda text-center {{ $dued }}">{{ $value['currency_type_id'] === 'PEN' ? 'S/' : '$' }}</td>
                                <td class="f14 celda w-monto text-center {{ $dued }}">{{ number_format($total_value, 2) }}</td>
                                <td class="f14 celda w-monto text-center {{ $dued }}">{{ number_format($total_payment, 2) }}</td>
                                <td class="f14 celda w-monto text-center {{ $dued }}">{{ number_format($total_to_pay, 2) }}</td>
                                <td class="f14 celda w-telefono text-center {{ $dued }}">{{ $supplier_telephone }}</td>
                                <td class="f14 celda w-estado text-center {{ $dued }}">{{ $dued ? 'VENCIDO' : 'PENDIENTE' }}</td>
                                <td class="f14 celda w-dias text-center {{ $dued }}">{{ $value['delay_payment'] ?? 0 }}</td>
                                <td class="f14 celda w-vencimiento text-center {{ $dued }}">{{ $value['date_of_due'] ?? '-' }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        <!-- Totales generales -->
        <div class="total-general">
            <table>
                <tbody>
                    <tr>
                        <td class="f14 w-total-label" colspan="4"></td>
                        <td class="f14 celda text-end" colspan="1">TOTAL GENERAL S/</td>
                        <td class="f14 celda w-monto text-center btd">{{ number_format($total['total_pen'], 2) }}</td>
                        <td class="f14 celda w-monto text-center btd">{{ number_format($total['total_payment_pen'], 2) }}</td>
                        <td class="f14 celda w-monto text-center btd">{{ number_format($total['total_to_pay_pen'], 2) }}</td>
                        <td class="f14 w-total-empty" colspan="4"></td>
                    </tr>
                    <tr>
                        <td class="f14 w-total-label" colspan="4"></td>
                        <td class="f14 celda text-end" colspan="1">TOTAL GENERAL $</td>
                        <td class="f14 celda w-monto text-center bbd">{{ number_format($total['total_usd'], 2) }}</td>
                        <td class="f14 celda w-monto text-center bbd">{{ number_format($total['total_payment_usd'], 2) }}</td>
                        <td class="f14 celda w-monto text-center bbd">{{ number_format($total['total_to_pay_usd'], 2) }}</td>
                        <td class="f14 w-total-empty" colspan="4"></td>
                    </tr>
                </tbody>
            </table>
        </div>
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