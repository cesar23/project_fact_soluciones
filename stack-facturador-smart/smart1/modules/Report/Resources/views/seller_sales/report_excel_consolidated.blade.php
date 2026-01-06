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


    <br>
    <div style="margin-top:20px; margin-bottom:15px;">
        <table>
            <tr>
                <td colspan="7" style="text-align: center;font-weight: bold;font-size: 16px;">
                    Reporte consolidado por vendedores
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

            
            </tr>
        </table>
    </div>
    <br>
    @php
        $apply_conversion_to_pen = apply_conversion_to_pen('reports/seller-sales');
    @endphp
    @if (!empty($records))
        <table>
            <thead>
                <tr>
                    <th>FECHA INICIO</th>
                    <th>FECHA FINAL</th>
                    <th>VENDEDOR</th>
                    <th>TOTAL VENTA</th>
                    
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $seller => $sales)
                    @php
                        $sales = $sales->sortBy('date_of_issue');
                        $first_date = $sales->first()->date_of_issue;
                        $last_date = $sales->last()->date_of_issue;
                        $total = $sales->sum(function($sale) use ($apply_conversion_to_pen) {
                            $total = $sale->total;
                            $currency_type_id = $sale->currency_type_id;
                            $exchange_rate_sale = $sale->exchange_rate_sale;
                            if($currency_type_id !== 'PEN' && $apply_conversion_to_pen){
                                $total = $total * $exchange_rate_sale;
                            }
                            return $total;
                        });
                    @endphp
                        
                        <tr>
                            <td>{{ $first_date }}</td>
                            <td>{{ $last_date }}</td>
                            <td>{{ $seller }}</td>
                            <td>{{ number_format($total, 2, '.', '') }}</td>
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
