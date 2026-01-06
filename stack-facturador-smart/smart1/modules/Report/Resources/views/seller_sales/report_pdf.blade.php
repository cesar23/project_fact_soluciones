@php
    $enabled_sales_agents = App\Models\Tenant\Configuration::getRecordIndividualColumn('enabled_sales_agents');
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Compras</title>
    <style>
        @page {
          margin: 5;
        }
        html {
            font-family: sans-serif;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-spacing: 0;
            border: 1px solid black;
            border-collapse: collapse;
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
            padding: 5px;
            font-size: 20px !important;
            text-decoration: underline;
        }

        p>strong {
            margin-left: 5px;
            font-size: 13px;
        }
        .brd td {
            padding: 5px;
            text-align: center;
            border: 0.1px solid black;
        }
        thead {
            font-weight: bold;
            background: #0088cc;
            color: white;
            text-align: center;
        }
    </style>
</head>

<body>
    @php
        $apply_conversion_to_pen = apply_conversion_to_pen('reports/seller-sales');
    @endphp

    <br>
    <div style="margin-top:20px; margin-bottom:15px;">
        <table>
            <tr>
                <td colspan="7" style="text-align: center;font-weight: bold;font-size: 16px;">
                    Reporte detallado por vendedores
                    de {{ \Carbon\Carbon::parse($d_start)->format('d-m-Y') }} al
                    {{ \Carbon\Carbon::parse($d_end)->format('d-m-Y') }}

                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>
                    <p><b>Empresa: </b></p>
                </td>
                <td align="center">
                    <p><strong>{{ $company->name }}</strong></p>
                </td>
                <td>
                    <p><strong class="seller">Fecha: </strong></p>
                </td>
                <td align="center">
                    <p><strong>{{ date('Y-m-d') }}</strong></p>
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>
                    <p><strong>Ruc: </strong></p>
                </td>
                <td align="center">{{ $company->number }}</td>

                @inject('reportService', 'Modules\Report\Services\ReportService')
                @if (isset($filters['seller_id']) && !empty($filters['seller_id']))
                    <td>
                        <p><strong>Usuario: </strong></p>
                    </td>
                    <td align="center">
                        {{ $reportService->getUserName($filters['seller_id']) }}
                    </td>
                @endif
            </tr>
        </table>
    </div>
    <br>
    @if (!empty($records))
        <table class="brd">
            <thead>
                <tr>
                    <th>NV</th>
                    <th>BV/FT</th>
                    <th>FECHA DE EMISION</th>
                    <th>CLIENTE</th>
                    <th>CONTACTO</th>
                    <th>DIRECCION</th>
                    <th>VENDEDOR</th>
                    <th>PAGADO/CREDITO</th>
                    <th>ARMADO/SIN ARMAR</th>
                    <th>ARMADOR</th>
                    <th>BOX</th>
                    <th>DESPACHADO/SIN DESPACHAR</th>
                    <th>DESPACHADOR</th>
                    <th>MONTO TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $seller => $sales)
                    @php
                        $sales = $sales->sortBy('date_of_issue');
                        $sale_total = 0;
                    @endphp
                    @foreach ($sales as $sale)
                        @php
                            $doc = $sale->document_number;
                            $is_sale_note = true;
                            if (substr($doc, 0, 1) == 'B' || substr($doc, 0, 1) == 'F') {
                                $is_sale_note = false;
                            }
                            $customer = $sale->customer;
                            $customer_array = explode('|', $customer);
                            $name = $customer_array[0];
                            $address = $customer_array[1];
                            $telephone = $customer_array[2];
                            $currency_type_id = $sale->currency_type_id;
                            $exchange_rate_sale = $sale->exchange_rate_sale;
                            $total = $sale->total;
                            if($currency_type_id !== 'PEN' && $apply_conversion_to_pen){
                                $total = $total * $exchange_rate_sale;
                            }
                            $sale_total += $total;

                        @endphp
                        <tr>
                            <td>
                                @if ($is_sale_note)
                                    {{ $sale->document_number }}
                                @endif
                            </td>
                            <td>
                                @if (!$is_sale_note)
                                    {{ $sale->document_number }}
                                @endif
                            </td>
                            <td>{{ $sale->date_of_issue }}</td>
                            <td>{{ $name }}</td>
                            <td>{{ $telephone }}</td>
                            <td>{{ $address }}</td>
                            <td>{{ $seller }}</td>
                            <td>{{$sale->total_canceled ? 'PAGADO' : 'CREDITO'}}</td>
                            <td>
                                @if ($sale->packer_name)
                                    ARMADO
                                @else
                                    SIN ARMAR
                                @endif
                            </td>
                            <td>{{ $sale->packer_name }}</td>
                            <td>{{ $sale->box }}</td>
                            <td>{{ $sale->dispatcher_name ? 'DESPACHADO' : 'SIN DESPACHAR' }}</td>
                            <td>{{ $sale->dispatcher_name }}</td>
                            <td>{{ number_format($total, 2, '.', '') }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="13" style="border-top: 1px solid black;"></td>
                        <td style="border-top: 1px solid black;">{{ number_format($sale_total, 2, '.', '') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
