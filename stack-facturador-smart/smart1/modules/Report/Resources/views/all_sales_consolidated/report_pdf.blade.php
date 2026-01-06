<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Reporte Consolidado de Ventas</title>
        <style>
            html {
                font-family: sans-serif;
                font-size: 12px;
            }
            @page {
                margin: 3px;
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
                padding: 5px;
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
        </style>
    </head>
    <body>
        @php
            $apply_conversion_to_pen = apply_conversion_to_pen('reports/all-sales-consolidated');
        @endphp
        <div>
            <p align="center" class="title"><strong>Reporte Consolidado de Ventas</strong></p>
        </div>
        <div style="margin-top:20px; margin-bottom:20px;">
            <table>
                <tr>
                    <td>
                        <p><strong>Empresa: </strong>{{$company->name}}</p>
                    </td>
                    <td>
                        <p><strong>Fecha: </strong>{{date('Y-m-d')}}</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p><strong>Ruc: </strong>{{$company->number}}</p>
                    </td>
                    <td>
                        <p><strong>Establecimiento: </strong>{{$establishment->address}} - {{$establishment->department->description}} - {{$establishment->district->description}}</p>
                    </td>
                </tr>
            </table>
        </div>
        @if(!empty($records))
            <div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>F. Emisión</th>
                            <th>Doc.</th>
                            <th>RUC</th>
                            <th>R. Social</th>
                            <th>Desc.</th>
                            <th>Total</th>
                            <th>Detrac.</th>
                            <th>Desc.</th>
                            <th>Neto</th>
                            <th>Pagado</th>
                            <th>Pend.</th>
                            <th>Días atraso</th>
                            <th>Est. deuda</th>
                            <th>F. Pago</th>
                            <th>Met. pago</th>
                            <th>Año</th>
                            <th>Ejecutivo</th>
                            <th>Cat.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $key => $value)
                        @php
                            $total = $value->total;
                            $currency_type_id = $value->currency_type_id;
                            $exchange_rate_sale = $value->exchange_rate_sale;
                            $total_without_detraction = $value->total_without_detraction;
                            $total_payment = $value->total_payment;
                            $pending = $value->pending;
                            $total_discount = $value->total_discount;
                            $detraction = $value->detraction;
                            if($currency_type_id !== 'PEN' && $apply_conversion_to_pen){
                                $total = $total * $exchange_rate_sale;
                                $total_without_detraction = $total_without_detraction * $exchange_rate_sale;
                                $total_payment = $total_payment * $exchange_rate_sale;
                                $pending = $pending * $exchange_rate_sale;
                                $total_discount = $total_discount * $exchange_rate_sale;
                                $detraction = $detraction * $exchange_rate_sale;    
                            }
                        @endphp
                            <tr>
                                <td class="celda">{{$loop->iteration}}</td>
                                <td class="celda">{{$value->date_of_issue}}</td>
                                <td class="celda">{{$value->number_full}}</td>
                                <td class="celda">{{$value->customer_number}}</td>
                                <td class="celda">{{$value->customer_name}}</td>
                                <td class="celda">{{$value->observation}}</td>
                                <td class="celda">{{number_format($total, 2, '.', '')}}</td>
                                <td class="celda">{{number_format($detraction, 2, '.', '')}}</td>
                                <td class="celda">{{number_format($total_discount, 2, '.', '')}}</td>
                                <td class="celda">{{number_format($total_without_detraction, 2, '.', '')}}</td>
                                <td class="celda">{{number_format($total_payment, 2, '.', '')}}</td>
                                <td class="celda">{{number_format($pending, 2, '.', '')}}</td>
                                <td class="celda">{{$value->days_of_delay}}</td>
                                <td class="celda">{{$value->status_due}}</td>
                                <td class="celda">{{$value->last_payment_date}}</td>
                                <td class="celda">{{$value->last_payment_method_type_id}}</td>
                                <td class="celda">{{$value->year_of_issue}}</td>
                                <td class="celda">{{$value->seller_name}}</td>
                                <td class="celda">{{$value->customer_reg}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="callout callout-info">
                <p>No se encontraron registros.</p>
            </div>
        @endif
    </body>
</html>
