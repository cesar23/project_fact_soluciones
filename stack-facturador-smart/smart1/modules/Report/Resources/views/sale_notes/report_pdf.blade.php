<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Nota de ventas</title>
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
    @php
        $apply_conversion_to_pen = apply_conversion_to_pen('reports/sale-notes');
    @endphp
    <body>
        <div>
            <p align="center" class="title"><strong>Reporte Nota de Venta</strong></p>
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

                    @inject('reportService', 'Modules\Report\Services\ReportService')
                    @if($filters['seller_id'])
                    <td>
                        <p><strong>Usuario: </strong>{{$reportService->getUserName($filters['seller_id'])}}</p>
                    </td>
                    @endif
                </tr>
            </table>
        </div>
        @if(!empty($records))
            <div class="">
                <div class=" ">

                    @php
                        $acum_total_taxed=0;
                        $acum_total_igv=0;
                        $acum_total=0;

                        $acum_total_taxed_usd=0;
                        $acum_total_igv_usd=0;
                        $acum_total_usd=0;
                    @endphp

                    <table class="">
                        <thead>
                            <tr>
                               <th>#</th>
                                <th class="text-center">Fecha Emisión</th>
                                <th class="text-center">Hora Emisión</th>
                                <th>Cliente</th>
                                <th>Nota de Venta</th>
                                <th>Estado</th>
                                <th class="text-center">Estado pago</th>
                                <th class="text-center">Moneda</th>
                                <th class="text-center">Plataforma</th>
                                <th class="text-center">Orden de compra</th>
                                <th class="text-center">Region</th>
                                <th class="text-center">Comprobantes</th>
                                <th class="text-right" >T.Exportación</th>
                                <th class="text-right" >T.Inafecta</th>
                                <th class="text-right" >T.Exonerado</th>
                                <th class="text-right">T.Gravado</th>
                                <th class="text-right">T.Igv</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $key => $value)
                            @php
                                $currency_type_id = $value->currency_type_id;
                                $exchange_rate_sale = $value->exchange_rate_sale;
                                $total = $value->total;
                                $total_taxed = $value->total_taxed;
                                $total_igv = $value->total_igv;
                                $total_unaffected = $value->total_unaffected;
                                $total_exonerated = $value->total_exonerated;
                                $total_discount = $value->total_discount;
                                $total_exportation = $value->total_exportation;
                                if($currency_type_id !== 'PEN' && $apply_conversion_to_pen){
                                    $total = $total * $exchange_rate_sale;
                                    $total_taxed = $total_taxed * $exchange_rate_sale;
                                    $total_igv = $total_igv * $exchange_rate_sale;
                                    $total_unaffected = $total_unaffected * $exchange_rate_sale;
                                    $total_exonerated = $total_exonerated * $exchange_rate_sale;
                                    $total_discount = $total_discount * $exchange_rate_sale;
                                    $total_exportation = $total_exportation * $exchange_rate_sale;
                                    $currency_type_id = 'PEN';
                                }
                            @endphp
                                <tr>
                                    <td class="celda">{{$loop->iteration}}</td>
                                    <td class="celda">{{$value->date_of_issue->format('Y-m-d')}}</td>
                                    <td class="celda">{{$value->time_of_issue}}</td>
                                    <td class="celda">{{$value->customer->name}}</td>
                                    <td class="celda">{{$value->number_full}}</td>
                                    <td class="celda">{{$value->state_type->description}}</td>
                                    <td class="celda">
                                        {{$value->total_canceled ? 'Pagado':'Pendiente'}}
                                    </td>

                                    <td class="celda">{{$currency_type_id}}</td>

                                    <td class="celda">
                                        @foreach ($value->getPlatformThroughItems() as $platform)
                                            <label class="d-block">{{$platform->name}}</label>
                                        @endforeach
                                    </td>
                                    <td class="celda">{{$value->purchase_order}}</td>
                                    <td class="celda">{{$value->customer->department->description}}</td>
                                    <td class="celda">
                                        @foreach ($value->documents as $doc)
                                            <label class="d-block">{{$doc->number_full}}</label>
                                        @endforeach
                                    </td>

                                    @if($value->state_type_id == '11')

                                        <td class="celda">0</td>
                                        <td class="celda">0</td>
                                        <td class="celda">0</td>
                                        <td class="celda">0</td>
                                        <td class="celda">0</td>
                                        <td class="celda">0</td>

                                    @else

                                        <td class="celda">{{ number_format($total_exportation, 2, '.', '') }}</td>
                                        <td class="celda">{{ number_format($total_unaffected, 2, '.', '') }}</td>
                                        <td class="celda">{{ number_format($total_exonerated, 2, '.', '') }}</td>
                                        <td class="celda">{{ number_format($total_taxed, 2, '.', '') }}</td>
                                        <td class="celda">{{ number_format($total_igv, 2, '.', '') }}</td>
                                        <td class="celda">{{ number_format($total, 2, '.', '') }}</td>

                                    @endif
                                </tr>


                                @php

                                    if($currency_type_id == 'PEN'){

                                        if($value->state_type_id == '11'){

                                            $acum_total += 0;
                                            $acum_total_taxed += 0;
                                            $acum_total_igv += 0;

                                        }else{

                                            $acum_total += $total;
                                            $acum_total_taxed += $total_taxed;
                                            $acum_total_igv += $total_igv;

                                        }

                                    }else if($currency_type_id == 'USD'){

                                        if($value->state_type_id == '11'){

                                            $acum_total_usd += 0;
                                            $acum_total_taxed_usd += 0;
                                            $acum_total_igv_usd += 0;

                                        }else{

                                            $acum_total_usd += $total;
                                            $acum_total_taxed_usd += $total_taxed;
                                            $acum_total_igv_usd += $total_igv;

                                        }

                                    }
                                @endphp
                            @endforeach
                            <tr>
                                <td class="celda" colspan="14"></td>
                                <td class="celda" >Totales PEN</td>
                                <td class="celda">{{number_format($acum_total_taxed, 2, '.', '')}}</td>
                                <td class="celda">{{number_format($acum_total_igv, 2, '.', '')}}</td>
                                <td class="celda">{{number_format($acum_total, 2, '.', '')}}</td>
                            </tr>
                            @if(!$apply_conversion_to_pen)
                            <tr>
                                <td class="celda" colspan="14"></td>
                                <td class="celda" >Totales USD</td>
                                <td class="celda">{{number_format($acum_total_taxed_usd, 2, '.', '')}}</td>
                                <td class="celda">{{number_format($acum_total_igv_usd, 2, '.', '')}}</td>
                                <td class="celda">{{number_format($acum_total_usd, 2, '.', '')}}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="callout callout-info">
                <p>No se encontraron registros.</p>
            </div>
        @endif
    </body>
</html>
