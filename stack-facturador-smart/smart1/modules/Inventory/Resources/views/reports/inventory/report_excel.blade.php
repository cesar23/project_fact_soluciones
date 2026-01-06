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
    <title>Inventario</title>
    <style>
        html {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        .title {
            font-weight: 500;
            text-align: center;
            font-size: 24px;
        }

        .label {
            width: 120px;
            font-weight: 500;
            font-family: sans-serif;
        }

        .table-records {
            margin-top: 24px;
        }

        .table-records tr th {
            font-weight: bold;
            background: #0088cc;
            color: white;
        }

        .table-records tr th,
        .table-records tr td {
            border: 1px solid #000;
            font-size: 9px;
        }

        .text-end {
            text-align: right;
        }
    </style>
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
    <table style="width: 100%">
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
            <td>{{ $establishment->description }} - {{ $establishment->address }} -
                {{ $establishment->department->description }}
                - {{ $establishment->district->description }}</td>
        </tr>
        <tr>
            <td colspan="2" class="label">Fecha:
            </td>
            <td>{{ date('d/m/Y') }}</td>
        </tr>
    </table>
    <table style="width: 100%" class="table-records">
        <thead>
            <tr>
                <th><strong>#</strong></th>
                <th><strong>Cod. de barras</strong></th>
                <th><strong>Cod. Interno</strong></th>
                <th><strong>Nombre</strong></th>
                <th><strong>Descripción</strong></th>
                <th><strong>Categoria</strong></th>
                @if ($configuration->is_pharmacy)
                    <th><strong>Laboratorio</strong></th>
                    <th><strong>Registro sanitario</strong></th>
                    <th><strong>Lotes</strong></th>
                @endif
                @if ($showSomeColumns)
                    <th align="right"><strong>Stock mínimo</strong></th>
                @endif
                <th align="right"><strong>Stock actual</strong></th>
                @if ($in_stock)
                    <th align="right"><strong>Entradas futuras</strong></th>
                @endif
                @if ($out_stock)
                    <th align="right"><strong>Salidas futuras</strong></th>
                @endif
                @if ($future_stock)
                    <th align="right"><strong>Stock futuro</strong></th>
                @endif

                <th align="right"><strong>Vendidos</strong></th>
                @if ($showSomeColumns)
                    <th align="right"><strong>Costo</strong></th>
                    <th align="right"><strong>Costo Total</strong></th>
                @endif
                @if ($currency == 'MIX')
                    <th align="right"><strong>Moneda</strong></th>
                @endif
                <th align="right"><strong>Precio de venta</strong></th>
                @if ($showSomeColumns)
                    <th align="right"><strong>Ganancia</strong></th>
                    <th align="right"><strong>Ganancia Total</strong></th>
                @endif
                <th><strong>Marca</strong></th>
                <th><strong>Modelo</strong></th>
                <th><strong>F. vencimiento</strong></th>

                <th><strong>Almacén</strong></th>
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
                    <td>{{ $row['barcode'] }}</td>
                    <td>{{ $row['internal_id'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>
                        @php
                            $description = $row['description'];
                            $item = \App\Models\Tenant\Item::where('internal_id', $row['internal_id'])->first();
                            if ($item) {
                                $description = $item->name;
                            }
                        @endphp
                        {{ $description }}

                    </td>
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
                        <td align="right">{{ $row['stock_min'] }}</td>
                    @endif
                    <td align="right">{{ $row['stock'] }}</td>
                    @if ($in_stock)
                        <td align="right">{{ $row['in_stock'] }}</td>
                    @endif
                    @if ($out_stock)
                        <td align="right">{{ $row['out_stock'] }}</td>
                    @endif
                    @if ($future_stock)
                        <td align="right">{{ $row['future_stock'] }}</td>
                    @endif
                    <td align="right">{{ $row['kardex_quantity'] }}</td>
                    @if ($showSomeColumns)
                        <td align="right">{{ $row['purchase_unit_price'] }}</td>
                        <td align="right">{{ $total_line }}</td>
                    @endif
                    @if ($currency == 'MIX')
                        <td align="right">{{ $row['currency_type_id'] == 'PEN' ? 'S/' : '$' }}</td>
                    @endif
                    <td align="right">{{ $row['sale_unit_price'] }}</td>
                    @if ($showSomeColumns)
                        <td align="right">{{ $profit }}</td>
                        <td align="right">{{ number_format(abs($profit * $row['stock']), 2, '.', '') }}</td>
                    @endif
                    <td>{{ $row['brand_name'] }}</td>
                    <td>{{ $row['model'] }}</td>
                    <td>{{ $row['date_of_due'] }}</td>
                    <td>{{ $row['warehouse_name'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th align="right"></th>
                <th align="right"></th>
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
                    <td colspan="7" class="celda"></td>
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
                    <td colspan="7" class="celda"></td>
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


                    <td colspan="8" class="celda"></td>
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
