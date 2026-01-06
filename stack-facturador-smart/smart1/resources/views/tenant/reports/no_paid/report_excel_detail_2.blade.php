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
        html, body {
            font-family: Helvetica, sans-serif;
            font-size: 10px;
        }
        @page {
            size: landscape;
            margin-left: 10px;
            margin-right: 10px;
            margin-top:45px;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
        }

        th,
        td {
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #70ad47;
            color: white;
            text-transform: uppercase;
            text-align: center;
        }

        .celda {
            text-align: right;
            border: 1px solid black !important;
        }

        .title {
            font-size: 15px;
        }

        .full-width {
            width: 100%;
        }



        .customer-info div {
            font-size: 10px;
        }
        .border {
            border: 1px solid black;
        }


    </style>
</head>

<body>
    @php
        $bank_accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', 1)->get();
        $establishment__ = \App\Models\Tenant\Establishment::find($establishment_id);
        $logo = $establishment__->logo ?? $company->logo;
        if ($logo === null && !file_exists(public_path("$logo}"))) {
            $logo = "{$company->logo}";
        }

        if ($logo) {
            $logo = "storage/uploads/logos/{$logo}";
            $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
        }
    @endphp
    <table class="full-width">
        <tr>
            <td width="25%">
                @if ($company->logo)
                    <div class="company_logo_box" style="width: 100%;text-align: center;">
                        <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                            alt="{{ $company->name }}" class="company_logo" style="max-width: 100px;">
                    </div>
                @else
                    <br>
                @endif
            </td>
            <td width="50%">
                <div>
                    <h4 align="center" class="title" style="margin-top: 5px; margin-bottom: 2px;"><strong>CUENTAS POR
                            COBRAR POR VENCIMIENTO</strong></h4>
                    <h4 align="center" class="title" style="margin-top: 5px; margin-bottom: 2px;">
                        <strong>{{ $company->name }}</strong>
                    </h4>
                    <h4 align="center" class="title" style="margin-top: 5px; margin-bottom: 2px;">
                        <strong>{{ $company->number }}</strong>
                    </h4>
                </div>
            </td>
            <td width="25%"></td>
        </tr>
    </table>
    @if($customer)
        <div class="customer-info">
            <div>
                <strong>R.U.C.: </strong>{{ $customer->number }}
            </div>
            <div>
                <strong>Cliente: </strong>{{ $customer->name }}
            </div>
            <div>
                <strong>Dirección: </strong>{{ $customer->address }}
            </div>
            <div>
                <strong>Ubigeo: </strong>{{ $customer->ubigeo_full }}
            </div>
            
        </div>
    @endif
<br>
    @if (!empty($records))
        <div class="top-15">
            <div class=" ">
                <table class="border">
                    <thead>
                        <tr>
                            <th class="border">#</th>
                            <th class="border">Cliente</th>
                            <th class="border">Galones</th>
                            <th class="border" width="5%">Placa</th>
                            <th class="border text-center">Fecha Emisión</th>
                            <th class="border">Número de comprobante <br>
                                o Nota de venta</th>

                            <th class="border">Monto total</th>
                            <th class="border">Monto pagados</th>
                            <th class="border">Saldo a pagar</th>
                            <th class="border">Días atrasados</th>
                            <th class="border">Vencimiento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sum_total = 0;
                            $sum_total_payment = 0;
                            $sum_total_subtraction = 0;
                        @endphp
                        @foreach ($records as $key => $value)
                            @if ($value['total_to_pay'] > 0)
                                <tr>
                                    <td class="celda">{{ $loop->iteration }}</td>
                                    <td class="celda" style="text-align: left;">{{ $value['customer_name'] }}</td>
                                    <td class="celda">{{ number_format($value['quantity_total_items'], 2, '.', ',') }}</td>
                                    <td class="celda">{{ $value['plate_number'] }}</td>
                                    <td class="celda">{{ $value['date_of_issue'] }}</td>
                                    <td class="celda">{{ $value['number_full'] }}
                                    </td>

                                    <td class="celda">{{ number_format($value['total'], 2, '.', ',') }}</td>
                                    <td class="celda">{{ number_format($value['total_payment'], 2, '.', ',') }}</td>
                                    <td class="celda">{{ number_format($value['total_subtraction'], 2, '.', ',') }}</td>
                                    <td class="celda">{{ $value['delay_payment'] }}</td>
                                    <td class="celda">{{ $value['date_of_due'] }}</td>
                                </tr>
                                @php
                                    $sum_total += $value['total'];
                                    $sum_total_payment += $value['total_payment'];
                                    $sum_total_subtraction += $value['total_subtraction'];
                                @endphp
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="celda" colspan="6" style="text-align: right; font-weight: bold; font-size: 12px;background-color: #70ad47;color: white;">TOTAL:</td>
                            <td class="celda" style="font-weight: bold; font-size: 12px;background-color: #70ad47;color: white;">{{ number_format($sum_total, 2, '.', ',') }}</td>
                            <td class="celda" style="font-weight: bold; font-size: 12px;background-color: #70ad47;color: white;">{{ number_format($sum_total_payment, 2, '.', ',') }}</td>
                            <td class="celda" style="font-weight: bold; font-size: 12px;background-color: #70ad47;color: white;">{{ number_format($sum_total_subtraction, 2, '.', ',') }}</td>
                            <td class="celda" style="font-weight: bold; font-size: 12px;background-color: #70ad47;color: white;"></td>
                            <td class="celda" style="font-weight: bold; font-size: 12px;background-color: #70ad47;color: white;"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <br>
            <div style="width: 50%; margin: 0 auto;">
                <table style="width: 100%; border-collapse: collapse; >
                    <thead>
                        <thead>
                            <tr>
                                <th colspan="3" style="text-align: center;border: 1px solid black;">
                                    CUENTAS BANCARIAS
                                </th>
                            </tr>
                            <tr>
                                <th style="text-align: center;border-bottom: 1px solid black;">Banco</th>
                                <th style="text-align: center;border-bottom: 1px solid black;">Número de cuenta</th>
                                <th style="text-align: center;border-bottom: 1px solid black;">CCI</th>
                            </tr>
                            @foreach ($bank_accounts as $bank_account)
                                <tr>
                                    <td class="celda" style="text-align: center;">{{ $bank_account->description }}</td>
                                    <td class="celda" style="text-align: center;">{{ $bank_account->number }}</td>
                                    <td class="celda" style="text-align: center;">{{ $bank_account->cci }}</td>
                                </tr>
                            @endforeach
                        </thead>
                    </thead>
                </table>
            </div>
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
            $rest_width = 85;
            $rest_height = 5;
            $font_size = 8;

            $pdf->page_text($pdf->get_width() - $rest_width, $rest_height, "Página {PAGE_NUM} de {PAGE_COUNT}", null, $font_size);
            $pdf->page_text($pdf->get_width() - $rest_width, $rest_height + 8, "Fecha: $date", null, $font_size);
            $pdf->page_text($pdf->get_width() - $rest_width, $rest_height + 16, "Hora: $time", null, $font_size);
        }
    </script>
</body>

</html>
