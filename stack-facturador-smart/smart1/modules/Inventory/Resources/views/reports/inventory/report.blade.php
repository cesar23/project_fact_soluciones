<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if ($format === 'pdf')
        <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
    @else
        <meta http-equiv="Content-Type"
            content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    @endif
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>

<body>
    @php
          $configuration = \App\Models\Tenant\Configuration::first();
          $columns = [];
        $in_stock = false;
        $out_stock = false;
        $future_stock = false;
        $inventory_reports_index = \App\Models\Tenant\ColumnsToReport::where('user_id', $user_id)->where('report', 'inventory_reports_index')->first();
        $columns = [];
        if($inventory_reports_index){
            $columns = $inventory_reports_index->columns;
            $in_stock = isset($columns->future_stock->visible) && $columns->future_stock->visible;
            $out_stock = isset($columns->out_stock->visible) && $columns->out_stock->visible;
            $future_stock = isset($columns->future_stock->visible) && $columns->future_stock->visible;
        }

    @endphp
    <table class="full-width">
        <tr>
            <td colspan="13" class="title"><strong>Reporte Inventario</strong></td>
        </tr>
        <tr>
            <td colspan="2" class="label">Empresa:
            </td>
            <td>{{ $company->name }}</td>
        </tr>
        <tr>
            <td colspan="2" class="label">RUC:
            </td>
            <td align="left">{{ $company->number }}</td>
        </tr>
        <tr>
            <td colspan="2" class="label">Establecimiento:
            </td>
            <td>{{ $establishment->address }} - {{ $establishment->department->description }}
                - {{ $establishment->district->description }}</td>
        </tr>
        <tr>
            <td colspan="2" class="label">Fecha:
            </td>
            <td>{{ date('d/m/Y') }}</td>
        </tr>
    </table>
    <table class="full-width mt-10 mb-10">
        <thead>
            <tr class="bg-grey">
                <th class="border-top-bottom text-center py-2 text-left" width="3%"><strong>#</strong></th>
                <!-- <th class="border-top-bottom text-center py-2" width="8%"><strong>Cod. de barras</strong></th>-->
                <th class="border-top-bottom text-center py-2 text-left" width="8%"><strong>Cod. Interno</strong>
                </th>
                <th class="border-top-bottom text-center py-2 text-left" width="8%"><strong>Descripción</strong>
                </th>
                <th class="border-top-bottom text-center py-2" width="8%"><strong>Categoria</strong></th>
                @if ($configuration->is_pharmacy)
                    <th class="border-top-bottom text-center py-2" width="8%"><strong>Laboratorio</strong></th>
                    <th class="border-top-bottom text-center py-2" width="8%"><strong>Registro sanitario</strong>
                    </th>
                    <th class="border-top-bottom text-center py-2" width="8%"><strong>Lotes</strong></th>
                @endif
                @if ($showSomeColumns)
                    <th class="border-top-bottom text-center py-2" width="6%"><strong>Stock mínimo</strong></th>
                @endif
                <th class="border-top-bottom text-center py-2" width="6%"><strong>Stock actual</strong></th>
                @if ($in_stock)
                    <th class="border-top-bottom text-center py-2" width="6%"><strong>Entradas futuras</strong></th>
                @endif
                @if ($out_stock)
                    <th class="border-top-bottom text-center py-2" width="6%"><strong>Salidas futuras</strong></th>
                @endif
                @if ($future_stock)
                    <th class="border-top-bottom text-center py-2" width="6%"><strong>Stock futuro</strong></th>
                @endif
                <th class="border-top-bottom text-center py-2" width="6%"><strong>Vendidos</strong></th>
                @if ($showSomeColumns)
                <th class="border-top-bottom text-center py-2" width="6%"><strong>Costo</strong></th>
                <th class="border-top-bottom text-center py-2" width="6%"><strong>Costo Total</strong></th>
                @endif
                @if ($currency == 'MIX')
                    <th class="border-top-bottom text-center py-2" width="6%"><strong>Moneda</strong></th>
                @endif
                <th class="border-top-bottom text-center py-2" width="7%"><strong>Precio de venta</strong></th>
                @if ($showSomeColumns)
                <th class="border-top-bottom text-center py-2" width="7%"><strong>Ganancia</strong></th>
                <th class="border-top-bottom text-center py-2" width="7%"><strong>Ganancia Total</strong></th>
                @endif
                <th class="border-top-bottom text-center py-2 text-right" width="8%"><strong>Marca</strong></th>
                <th class="border-top-bottom text-center py-2" width="8%"><strong>Modelo</strong></th>
                <th class="border-top-bottom text-center py-2" width="8%"><strong>F. vencimiento</strong></th>
                <th class="border-top-bottom text-center py-2 text-right" width="8%"><strong>Almacén</strong></th>

            </tr>
        </thead>
        <tbody>
            @php
                $total_purchase_unit_price = 0;
                $total_sale_unit_price = 0;
                $total = 0;
                $total_profit = 0;
                $total_all_profit = 0;
            @endphp
            @foreach ($records as $key => $row)
                @php
                    $total_line = $row['stock'] * $row['purchase_unit_price'];
                    $profit = $row['sale_unit_price'] - $row['purchase_unit_price'];
                    $total += $total_line;
                    $total_profit += $profit;
                    $total_all_profit += $profit * $row['stock'];
                    $profit = number_format($profit, 2, '.', '');
                    
                    $total_purchase_unit_price += $row['purchase_unit_price'];
                    $total_sale_unit_price += $row['sale_unit_price'];
                    
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <!--<td>{{ $row['barcode'] }}</td> -->
                    <td>{{ $row['internal_id'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['item_category_name'] }}</td>
                    @if ($configuration->is_pharmacy)
                        <td>{{ $row['laboratory'] }}</td>
                        <td>{{ $row['num_reg_san'] }}</td>
                        <td>
                            @foreach ($row['lots_group'] as $lot)
                                {{ $lot['code'] }} <br>
                                <small>{{ $lot['date_of_due'] }}</small>
                            @endforeach
                        </td>
                    @endif
                    @if ($showSomeColumns)
                        <td>{{ $row['stock_min'] }}</td>
                    @endif
                    <td>{{ $row['stock'] }}</td>
                    @if ($in_stock)
                        <td>{{ $row['in_stock'] }}</td>
                    @endif
                    @if ($out_stock)
                        <td>{{ $row['out_stock'] }}</td>
                    @endif
                    @if ($future_stock)
                        <td>{{ $row['future_stock'] }}</td>
                    @endif
                    <td>{{ $row['kardex_quantity'] }}</td>
                    @if ($showSomeColumns)
                        <td>{{ number_format($row['purchase_unit_price'], 2, '.', '') }} </td>
                        <td>{{ number_format($total_line, 2, '.', '') }}</td>
                    @endif
                    @if ($currency == 'MIX')
                        <td>{{ $row['currency_type_id'] == 'PEN' ? 'S/' : '$' }}</td>
                    @endif
                    <td>{{ number_format($row['sale_unit_price'], 2, '.', '') }} </td>
                    @if ($showSomeColumns)
                        <td>{{ $profit }}</td>
                        <td>{{ number_format(abs($profit * $row['stock']), 2, '.', '') }}</td>
                    @endif
                    <td>{{ $row['brand_name'] }}</td>
                    <td>{{ $row['model'] }}</td>
                    <td>{{ $row['date_of_due'] }}</td>
                    <td>{{ $row['warehouse_name'] }}</td>
                </tr>
            @endforeach
        </tbody>
        {{-- <tfoot>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th><strong>Costo</strong></th>
                <th><strong>Costo Total de Inventario</strong></th>
                <th><strong>Precio de venta</strong></th>
                <th><strong>Ganancia</strong></th>
                <th><strong>Ganancia Total</strong></th>
                <th colspan="4"></th>
            </tr>
            <tr>
                <td colspan="7" class="celda"></td>
                <td class="celda">{{ number_format($total_purchase_unit_price, 2, '.', '') }}</td>
                <td class="celda">{{ $total }}</td>
                <td class="celda">{{ number_format($total_sale_unit_price, 2, '.', '') }}</td>
                <td class="celda">S/ {{ number_format($total_profit, 2, '.', '') }} </td>
                <td class="celda">S/ {{ number_format($total_all_profit, 2, '.', '') }}</td>
                <td colspan="4" class="celda"></td>
            </tr>
        </tfoot> --}}
        <tfoot>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th align="right"></th>
                <th align="right"></th>
                <th align="right"><strong>Costo</strong></th>
                <th align="right"><strong>Costo Total de Inventario</strong></th>
                @if ($currency == 'MIX')
                    <th align="right"></th>
                @endif
                <th align="right"><strong>Precio de venta</strong></th>

                <th align="right"><strong>Ganancia</strong></th>
                <th align="right"><strong>Ganancia Total</strong></th>
                <th colspan="4"></th>
            </tr>

            @if ($currency == 'MIX')
                <tr>
                    @if ($currency == 'MIX')
                        <td align="right"></td>
                    @endif
                    <td colspan="5" class="celda"></td>
                    <td>Total global (S/)</td>
                    <td class="celda text-end">S/ {{ number_format($totals['purchase_unit_price_pen'], 2, '.', '') }}
                    </td>
                    <td class="celda text-end">S/ {{ number_format($totals['total_pen'], 2, '.', '') }}</td>
                    <td></td>
                    <td class="celda text-end">S/ {{ number_format($totals['sale_unit_price_pen'], 2, '.', '') }}</td>
                    <td class="celda text-end">S/ {{ number_format($totals['total_profit_pen'], 2, '.', '') }}</td>
                    <td class="celda text-end">S/ {{ number_format($totals['total_all_profit_pen'], 2, '.', '') }}</td>
                    <td colspan="4" class="celda"></td>
                </tr>
                <tr>
                    @if ($currency == 'MIX')
                        <td align="right"></td>
                    @endif
                    <td colspan="5" class="celda"></td>
                    <td>Total global ($)</td>
                    <td class="celda text-end">$ {{ number_format($totals['purchase_unit_price_usd'], 2, '.', '') }}
                    </td>
                    <td class="celda text-end">$ {{ number_format($totals['total_usd'], 2, '.', '') }}</td>
                    <td></td>
                    <td class="celda text-end">$ {{ number_format($totals['sale_unit_price_usd'], 2, '.', '') }}</td>
                    <td class="celda text-end">$ {{ number_format($totals['total_profit_usd'], 2, '.', '') }}</td>
                    <td class="celda text-end">$ {{ number_format($totals['total_all_profit_usd'], 2, '.', '') }}</td>
                    <td colspan="4" class="celda"></td>
                </tr>
            @else
                <tr>
                    

                    <td colspan="6" class="celda"></td>
                    @php
                        $currency_symbol = $currency == 'PEN' ? 'S/' : '$';
                    @endphp
                    <td>Total global</td>
                    <td class="celda">{{ $currency_symbol }}
                        {{ number_format($totals['purchase_unit_price'], 2, '.', '') }}</td>
                    <td class="celda">{{ $currency_symbol }} {{ number_format($totals['total'], 2, '.', '') }}</td>
                    <td class="celda">{{ $currency_symbol }}
                        {{ number_format($totals['sale_unit_price'], 2, '.', '') }}</td>
                    <td class="celda">{{ $currency_symbol }} {{ number_format($totals['total_profit'], 2, '.', '') }}
                    </td>
                    <td class="celda">{{ $currency_symbol }}
                        {{ number_format($totals['total_all_profit'], 2, '.', '') }}</td>
                    <td colspan="4" class="celda"></td>
                </tr>
            @endif

        </tfoot>
    </table>
</body>

</html>
