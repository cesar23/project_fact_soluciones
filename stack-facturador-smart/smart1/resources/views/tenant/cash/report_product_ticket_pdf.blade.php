@php

    $establishment = $cash->user->establishment;

@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte POS - {{ $cash->user->name }} - {{ $cash->date_opening }} {{ $cash->time_opening }}</title>
    <style>
        html {
            font-family: sans-serif;
            font-size: 8px;
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
            font-size: 8px;
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
            font-size: 10px !important;
            text-decoration: underline;
        }

        p>strong {
            margin-left: 5px;
            font-size: 9px;
        }

        thead {
            font-weight: bold;
            background: #0088cc;
            color: white;
            text-align: center;
        }

        .td-custom {
            line-height: 0.1em;
        }

        .width-custom {
            width: 50%
        }
        @page {
            margin: 0 5px;
        }
    </style>
</head>
@php
    $withIgvProductReportCash = \App\Models\Tenant\Configuration::select('with_igv_product_report_cash')->first()->with_igv_product_report_cash;
@endphp 
<body>
    <div>
        <p align="center" class="title"><strong>Reporte Punto de Venta</strong></p>
    </div>
    <div style="margin-top:20px; margin-bottom:20px;">
        <table>
            <tr>
                <td>
                    <strong>Empresa: </strong>{{ $company->name }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Fecha reporte: </strong>{{ date('Y-m-d') }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Ruc: </strong>{{ $company->number }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Establecimiento: </strong>{{ $establishment->address }}
                        - {{ $establishment->department->description }} - {{ $establishment->district->description }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Vendedor: </strong>{{ $cash->user->name }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Fecha y hora apertura: </strong>{{ $cash->date_opening }} {{ $cash->time_opening }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Estado de caja: </strong>{{ $cash->state ? 'Aperturada' : 'Cerrada' }}
                </td>
            </tr>
            @if (!$cash->state)
            <tr>
                <td>
                    <strong>Fecha y hora cierre: </strong>{{ $cash->date_closed }} {{ $cash->time_closed }}
                </td>
            </tr>
            @endif
            <tr>
                <td>
                    <strong>Montos de operación: </strong>
                </td>
            </tr>
        </table>
    </div>
    @if ($documents->count())
        @php
            $total = 0;
            $subTotal = 0;
            $total_taxes = 0;
        @endphp
        @php
            $items_id = [];
            foreach ($documents as $item) {
                $validate = in_array($item['item_id'], $items_id);
                if (!$validate) {
                    $items_id[] = $item['item_id'];
                }
            }

            $allTotal = 0;
            //dd($items_id);
        @endphp
        {{--  @if ($is_garage)
        @include('tenant.cash.partials.data_garage')
    @endif --}}

        <div class="">
            <div class=" ">
                <table class="">
                    <thead>
                        <tr>
                            <th>Can.</th>
                            <th>Producto</th>
                            <th>P uni.</th>
                        
                            <th>Total</th>
                            <th>CPE</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($documents as $item)
                            <tr>
                                <td class="celda">
                                    @php
                                        $quantity = 0;
                                        $g_item = $item['item'];
                                        $g_quantity = $item['quantity'];
                                        $factor_presentation = 1;
                                        if (isset($g_item->presentation)) {
                                            $presentation = $g_item->presentation;
                                            if (is_object($presentation)) {
                                                $factor_presentation = $presentation->quantity_unit;
                                            }
                                        }
                                        $quantity = $g_quantity * $factor_presentation;
                                    @endphp
                                    {{ $quantity }}

                                </td>
                                <td class="celda">{{ $item['description'] }}</td>
                            
                                <td class="celda" style="text-align: right">
                                    @if ($withIgvProductReportCash)
                                        {{ App\CoreFacturalo\Helpers\Template\ReportHelper::setNumber($item['unit_price']) }}
                                    @else
                                        {{ App\CoreFacturalo\Helpers\Template\ReportHelper::setNumber($item['unit_value']) }}
                                    @endif
                                </td>
                            
                                <td class="celda" style="text-align: right">
                                    @if ($withIgvProductReportCash)
                                        {{ App\CoreFacturalo\Helpers\Template\ReportHelper::setNumber($item['total']) }}
                                    @else
                                        {{ App\CoreFacturalo\Helpers\Template\ReportHelper::setNumber($item['sub_total']) }}
                                    @endif
                                </td>
                                <td class="celda">{{ $item['number_full'] }}</td>
                            </tr>
                            @php
                                if ($withIgvProductReportCash) {
                                    $subTotal += $item['total'];
                                } else {
                                    $subTotal += $item['sub_total'];
                                }
                            @endphp
                        @endforeach

                        <tr>
                            <td class="celda"></td>
                            <td class="celda"></td>
                            <td class="celda"> Totales </td>
                        
                            <td class="celda" style="text-align: right">
                                {{ App\CoreFacturalo\Helpers\Template\ReportHelper::setNumber($subTotal) }}
                            </td>
                            <td class="celda"></td>

                        </tr>
                    </tbody>
                </table>
                <br>

                {{-- TOTALES --}}
                {{-- @if (!$is_garage)
                    @include('tenant.cash.partials.totals_sold_items')
                @endif --}}

            </div>
        </div>
    @else
        <div class="callout callout-info">
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
