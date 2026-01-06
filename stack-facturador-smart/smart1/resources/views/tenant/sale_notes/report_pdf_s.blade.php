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
            margin: 5px 15px;
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
    </style>
</head>

<body>
    @php
        $totals = [
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
                <div class="text-xl text-center">NOTAS DE VENTA SIN CONFIRMACIÓN DE PAGO</div>
                <div class="text-m text-center">

                    {{ $company->name }}
                </div>
                <div class="text-m text-center">
                    {{ $company->number }}

                </div>
                < </td>
            <td width="20%" class="text-end">
                @php
                    $date = new \DateTime();
                    $now = \Carbon\Carbon::now();
                    $timeZone = new \DateTimeZone('America/Lima');
                @endphp
                <div class="text-m">Fecha: {{ $date->setTimezone($timeZone)->format('Y-m-d') }}</div>
                <div class="text-m">Hora: {{ $date->setTimezone($timeZone)->format('H:i:s') }}
                </div>
                <div class="text-m page-number">Página </div>

            </td>
        </tr>
    </table>

    <br>

    @if (!empty($records))
        <div class="">
            <div class=" ">
                <table class="">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>CLIENTE</th>
                            <th>VENDEDOR</th>
                            <th>NÚMERO</th>
                            <th>FECHA EMISIÓN</th>
                            <th>MONEDA</th>
                            <th>TOTAL</th>
                            <th>COBRADO</th>
                            <th>SALDO</th>
                            <th>TELÉFONO</th>
                            <th>ESTADO</th>
                            <th>DIAS ATRASO</th>
                            <!-- <th>VENCIMIENTO</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $value)
                            @php
                                $currency_type_id = $value->currency_type_id;
                        
                                $total_payment = $value->payments()->sum('payment');
                                $total = $value->total;
                                $total_to_pay = $total - $total_payment;
                                $dued = '';
                                $date_of_due = \Carbon\Carbon::parse($value->date_of_issue);
                                $delay_payment = $date_of_due->diffInDays($now);
                                if ($delay_payment > 0 && $total_to_pay > 0) {
                                    $dued = 'text-danger';
                                }
                                if ($currency_type_id === 'PEN') {
                                    $totals['total_pen'] += $total;
                                    $totals['total_payment_pen'] += $total_payment;
                                    $totals['total_to_pay_pen'] += $total_to_pay;
                                } else {
                                    $totals['total_usd'] += $total;
                                    $totals['total_payment_usd'] += $total_payment;
                                    $totals['total_to_pay_usd'] += $total_to_pay;
                                }
                                
                            @endphp
                                <tr>

                                    <td class="celda {{ $dued }}">{{ $key + 1}}</td>
                                    <td class="celda {{ $dued }}">{{ $value->customer->name}}</td>
                                    <td class="celda {{ $dued }}">{{ $value->seller ? $value->seller->name : $value->user->name}}</td>
                                    <td class="celda text-center {{ $dued }}">
                                        {{ $value->number_full }}</td>
                                    <td class="celda text-center {{ $dued }}">{{ $value->date_of_issue }}
                                    </td>
                                    <td class="celda text-center {{ $dued }}">
                                        ({{ $value->currency_type->symbol }})
                                    </td>
                                    <td class="celda text-end {{ $dued }}">{{ number_format($total ,2)}}</td>
                                    <td class="celda text-end {{ $dued }}">{{ number_format($total_payment ,2)}}</td>
                                    <td class="celda text-end {{ $dued }}">
                                    @if($total_to_pay == 0)
                                        <div><small class="text-danger">
                                            Pago sin confirmar
                                        </small></div>
                                    @else
                                    {{ number_format($total_to_pay ,2)}}
                                    @endif

                                    </td>
                                    <td class="celda {{ $dued }}">{{ $value->customer->telephone }}</td>
                                    <td class="celda {{ $dued }}">{{ $dued ? 'VENCIDO' : 'PENDIENTE' }}</td>

                                    <td class="celda text-end {{ $dued }}">{{ $delay_payment }}</td>
                                    <!-- <td class="celda text-center {{ $dued }}">{{ $value->due_date }}</td> -->
                                </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2"></td>
                            <td class="celda text-end" colspan="2">TOTAL S/</td>
                            <td class="celda text-end btd">{{ number_format($totals['total_pen'] ,2)}}</td>
                            <td class="celda text-end btd">{{ number_format($totals['total_payment_pen'] ,2)}}</td>
                            <td class="celda text-end btd">{{ number_format($totals['total_to_pay_pen'] ,2)}}</td>
                            <td colspan="4"></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td class="celda text-end" colspan="2">TOTAL $</td>
                            <td class="celda text-end bbd">{{ number_format($totals['total_usd'] ,2)}}</td>
                            <td class="celda text-end bbd">{{ number_format($totals['total_payment_usd'] ,2)}}</td>
                            <td class="celda text-end bbd">{{ number_format($totals['total_to_pay_usd'] ,2)}}</td>
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
