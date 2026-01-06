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
    <title>Ventas resumidas</title>

</head>

@php
    $apply_conversion_to_pen = apply_conversion_to_pen('reports/summary-sales');

@endphp

<body>


    <br>
    <div style="margin-top:20px; margin-bottom:15px;">
        <table width="100%">
            <tr>
                <td colspan="7" style="text-align: center;font-weight: bold;font-size: 20px;">
                    REPORTE DE VENTA RESUMIDA
                    DE <br> {{ \Carbon\Carbon::parse($d_start)->format('d-m-Y') }} AL
                    {{ \Carbon\Carbon::parse($d_end)->format('d-m-Y') }}

                </td>
            </tr>
            <tr>

                <td>
                    <b>EMPRESA: </b>
                </td>
                <td colspan="4">
                    <p><strong>{{ $company->name }}</strong>
                </td>

            </tr>
            <tr>

                <td>
                    <strong>RUC: </strong>
                </td>
                <td colspan="4">{{ $company->number }}</td>

                <td colspan="4"></td>
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

                <table width="100%" style="border-collapse: collapse">
                    <thead>
                        <tr style="border-bottom: 1px solid  black;">
                            <th>#</th>
                            <th>DOC</th>
                            <th class="text-center">FECHA EMISIÓN</th>
                            <th class="text-center">FECHA VENCIMIENTO</th>
                            <th class="text-center">IMPORTE</th>
                            <th class="text-center">DEBE</th>
                            <th class="text-center">ABONOS</th>
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
                                    <td colspan="7">
                                        <br>
                                    </td>
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
                                    <td colspan="7" style="font-size:15px;color:rgb(30, 94, 30);">
                                        <small>
                                            {{ $address }}
                                        </small>
                                    </td>
                                </tr>
                                @foreach ($sellers as $seller => $documents)
                                    @if ($loop->iteration !== 1)
                                        <tr>
                                            <td colspan="7">
                                                <br>
                                            </td>
                                        </tr>
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
                                            if($apply_conversion_to_pen && $document->currency_type_id !== "PEN"){
                                                $total = $document->total * $document->exchange_rate_sale;
                                                $pending = $document->pending * $document->exchange_rate_sale;
                                            }
                                            $total_zone += $total;
                                            $total_customer += $total;
                                            $total_seller += $total;
                                            $pending_zone += $pending;
                                            $pending_customer += $pending;
                                            $pending_seller += $pending;
                                        
                                        @endphp
                                        <tr>
                                            <td style="border:1px solid black;text-align:right">{{ $loop->iteration }}
                                            </td>
                                            <td style="border:1px solid black;text-align:right">
                                                {{ $document->document_number }}</td>
                                            <td style="border:1px solid black;text-align:right">
                                                {{ \Carbon\Carbon::parse($document->date_of_issue)->format('d-m-Y') }}
                                            </td>
                                            <td style="border:1px solid black;text-align:right">
                                                {{ \Carbon\Carbon::parse($document->date_of_due)->format('d-m-Y') }}
                                            </td>
                                            {{-- total original --}}
                                            <td style="border:1px solid black;text-align:right">
                                                {{ number_format($total, 2) }}</td>

                                            {{-- el total menos lo que pagó  --}}
                                            <td style="border:1px solid black;text-align:right">
                                                {{ number_format($total - $pending, 2) }}</td>

                                            {{-- lo que pagó --}}

                                            <td style="border:1px solid black;text-align:right">
                                                {{ number_format($pending, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr>
                                        <td colspan="4" style="border:1px solid black;text-align: right;color:blue;">
                                            TOTAL VENDEDOR
                                        </td>
                                        <td style="border:1px solid black;text-align:right">
                                            {{ number_format($total_seller, 2) }}</td>
                                        <td style="border:1px solid black;text-align:right">
                                            {{ number_format($total_seller - $pending_seller, 2) }}</td>
                                        <td style="border:1px solid black;text-align:right">
                                            {{ number_format($pending_seller, 2) }}</td>

                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="4"
                                        style="border:1px solid black; text-align: right;color:rgb(30, 94, 30);">
                                        TOTAL CLIENTE
                                    </td>
                                    <td style="border:1px solid black; text-align:right">
                                        {{ number_format($total_customer, 2) }}</td>
                                    <td style="border:1px solid black; text-align:right">
                                        {{ number_format($total_customer - $pending_customer, 2) }}</td>
                                    <td style="border:1px solid black; text-align:right">
                                        {{ number_format($pending_customer, 2) }}</td>

                                </tr>
                            @endforeach
                            <tr>
                                @php
                                    $total_general += $total_zone;
                                    $pending_general += $pending_zone;
                                @endphp
                                <td colspan="4" style="border:1px solid black; text-align: right;color:red;">
                                    TOTAL ZONA
                                </td>
                                <td style="border:1px solid black; text-align:right">
                                    {{ number_format($total_zone, 2) }}</td>
                                <td style="border:1px solid black; text-align:right">
                                    {{ number_format($total_zone - $pending_zone, 2) }}</td>
                                <td style="border:1px solid black; text-align:right">
                                    {{ number_format($pending_zone, 2) }}</td>

                            </tr>
                            <tr>
                                <td colspan="7">
                                    <br>
                                </td>
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
