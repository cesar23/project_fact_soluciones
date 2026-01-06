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

</head>

<body>
@php
    $apply_conversion_to_pen = apply_conversion_to_pen('reports/summary-sales');
@endphp

    <br>
    <div style="margin-top:20px; margin-bottom:15px;">
        <table>
            <tr>
                <td colspan="7" style="text-align: center;font-weight: bold;font-size: 16px;">
                    Reporte de venta resumida
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
        <div class="">
            <div class=" ">
                @php
                $total_general = 0;
                 $pending_general = 0;

             @endphp

                <table class="">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Doc</th>
                            <th class="text-center">Fecha Emisión</th>
                            <th class="text-center">Fecha Vencimiento</th>
                            <th class="text-center">Importe</th>
                            <th class="text-center">Debe</th>
                            <th class="text-center">Abonos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $value)
                            @php
                                $total_zone = 0;
                                $pending_zone = 0;
                            @endphp
                            @if ($loop->iteration !== 1)
                                <tr></tr>
                            @endif
                            <tr>
                                <td colspan="7" style="font-weight: bold;color:red;">
                                    ZONA:
                                    {{ $key }}
                                </td>
                            </tr>
                            @foreach ($value as $customer => $sellers)
                                @if ($loop->iteration !== 1)
                                    <tr></tr>
                                @endif
                                @php
                                    $total_customer = 0;
                                    $pending_customer = 0;
                                @endphp
                                @php
                                    $explode_customer = explode('|', $customer);
                                    $name = $explode_customer[0];
                                    $address = $explode_customer[1];
                                @endphp
                                <tr>
                                    <td colspan="7" style="font-weight: bold;color:rgb(30, 94, 30);">
                                        {{ $name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="7" style="font-size:8px;color:rgb(30, 94, 30);">
                                        <small>
                                            {{ $address }}
                                        </small>
                                    </td>
                                </tr>
                                @foreach ($sellers as $seller => $documents)
                                    @if ($loop->iteration !== 1)
                                        <tr></tr>
                                    @endif
                                    @php
                                        $total_seller = 0;
                                        $pending_seller = 0;
                                    @endphp
                                    <tr>
                                        <td colspan="7" style="font-weight: bold;color:blue;">
                                            VENDEDOR: {{ $seller }}
                                        </td>
                                    </tr>

                                    @foreach ($documents as $document)
                                        @php
                                            $total = $document->total;
                                            $pending = $document->pending;
                                            $currency_type_id = $document->currency_type_id;
                                            $exchange_rate_sale = $document->exchange_rate_sale;
                                            if($apply_conversion_to_pen && $currency_type_id !== 'PEN'){
                                                $total = $total * $exchange_rate_sale;
                                                $pending = $pending * $exchange_rate_sale;
                                            }
                                            $total_zone += $total;
                                            $total_customer += $total;
                                            $total_seller += $total;
                                            $pending_zone += $pending;
                                            $pending_customer += $pending;
                                            $pending_seller += $pending;
                                        @endphp
                                        <tr>
                                            <td style="text-align:right">{{ $loop->iteration }}</td>
                                            <td style="text-align:right">{{ $document->document_number }}</td>
                                            <td style="text-align:right">
                                                {{ \Carbon\Carbon::parse($document->date_of_issue)->format('d-m-Y') }}
                                            </td>
                                            <td style="text-align:right">
                                                {{ \Carbon\Carbon::parse($document->date_of_due)->format('d-m-Y') }}
                                            </td>
                                            <td style="text-align:right">{{ number_format($total, 2) }}</td>
                                            <td style="text-align:right">
                                                {{ number_format($total - $pending, 2) }}</td>
                                            <td style="text-align:right">{{ number_format($pending, 2) }}
                                            </td>

                                        </tr>
                                    @endforeach

                                    <tr>
                                        <td colspan="4" style="text-align: right;color:blue;">
                                            TOTAL VENDEDOR
                                        </td>
                                        <td>{{ number_format($total_seller, 2) }}</td>
                                        <td>{{ number_format($total_seller - $pending_seller, 2) }}</td>
                                        <td>{{ number_format($pending_seller, 2) }}</td>

                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="4" style="text-align: right;color:rgb(30, 94, 30);">
                                        TOTAL CLIENTE
                                    </td>
                                    <td>{{ number_format($total_customer, 2) }}</td>
                                    <td>{{ number_format($total_customer - $pending_customer, 2) }}</td>

                                    <td>{{ number_format($pending_customer, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                @php
                                $total_general += $total_zone;
                                $pending_general += $pending_zone;
                            @endphp
                                <td colspan="4" style="text-align: right;color:red;">
                                    TOTAL ZONA
                                </td>
                                <td>{{ number_format($total_zone, 2) }}</td>
                                <td>{{ number_format($total_zone - $pending_zone, 2) }}</td>

                                <td>{{ number_format($pending_zone, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="4" style="border:1px solid black; text-align: right;color:red;">
                                TOTAL GENERAL
                            </td>
                            <td style="border:1px solid black; text-align:right">
                                {{ number_format($total_general, 2) }}</td>
                            <td style="border:1px solid black; text-align:right">
                                {{ number_format($total_general - $pending_general, 2) }}</td>
                            <td style="border:1px solid black; text-align:right">
                                {{ number_format($pending_general, 2) }}</td>
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
</body>

</html>
