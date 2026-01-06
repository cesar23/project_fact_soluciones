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
        html {
            font-family: sans-serif;
            font-size: 12px;
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
    <div>
        <h3 align="center" class="title"><strong>RELACION DE TRANSPORTE ACUMULADO POR TRANSPORTISTA SEGUN PEDIDO DE VENTA 
            <br>
            ({{$date_start}} - {{$date_end}})</strong></h3>

    </div>
    <br>
    <div style="margin-top:20px; margin-bottom:15px;">
        <table>
            <tr>
                <td class="">
                    <p><b>Empresa: </b></p>
                </td>
                <td align="center">
                    <p><strong>{{ $company->name }}</strong></p>
                </td>
                <td class="">
                    <p><strong>Fecha: </strong></p>
                </td>
                <td align="center">
                    <p><strong>{{ date('Y-m-d') }}</strong></p>
                </td>
            </tr>
            <tr>
                <td class="">
                    <p><strong>Ruc: </strong></p>
                </td>
                <td align="center">{{ $company->number }}</td>
                <td class=""></td>
                <td class=""></td>
            </tr>
        </table>
    </div>
    <br>
     @php
        $sellers = [];
        foreach ($records as $record) {
            $seller_ids = explode(',', $record->seller_ids);
        
            $sellers = array_merge($sellers, $seller_ids);
            $sellers = array_unique($sellers);
        
        }
        $sellers = array_map(function ($seller_id) {
                $seller = App\Models\Tenant\User::find($seller_id);
                return $seller ? $seller->name : '';
            }, $sellers);

        
    @endphp
    @if (!empty($records))
        <div class="">
            <div class=" ">
                <div>
                    <strong>TRANSPORTISTA: </strong> {{ $dispatcher->name }} - {{ $dispatcher->number }}
                </div>
                <div>
                    <strong>VENDEDORES: </strong> {{ implode(', ', $sellers) }}
                </div>
                <table class="">
                    <thead>
                        <tr>
                            <th class="">#</th>
                            <th class="">Código</th>
                            <th class="">Descripción</th>
                            <th class="">Cantidad</th>
                            <th class="">Unidad de medida</th>
                            <th class="">Cantidad equivalente</th>
                            <th class="">Precio de venta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $value)
                            @php
                                $seller_ids = explode(',', $value->seller_ids);
                                $internal_id = null;
                                $description = null;
                                $quantity = $value->total_quantity;
                                $unit_type_id = null;
                                $presentations = $value->presentations;
                                $unit_totals = [];

                                $has_pipe = strpos($presentations, '|');
                                if ($has_pipe !== false) {
                                    $presentations = explode(',', $presentations);
                                    $presentations = array_map(function ($row) {
                                        $row = explode('|', $row);
                                        return [
                                            'description' => isset($row[0]) ? $row[0] : null,
                                            'unit_type_id' => isset($row[1]) ? $row[1] : null,
                                            'unit_quantity' => isset($row[2]) ? $row[2] : 0,
                                            'quantity' => isset($row[3]) ? $row[3] : 0,
                                        ];
                                    }, $presentations);

                                    foreach ($presentations as $presentation) {
                                        $unit_type = $presentation['description'];
                                        $quantity_ = (float) $presentation['quantity'];

                                        if (!isset($unit_totals[$unit_type])) {
                                            $unit_totals[$unit_type] = 0;
                                        }
                                        $unit_totals[$unit_type] += $quantity_;
                                    }
                                }

                                $formatted_result = [];
                                foreach ($unit_totals as $unit_type => $total_quantity) {
                                    $formatted_result[] = "{$total_quantity} {$unit_type}";
                                }
                                $quantity_presentation = null;
                                $total = null;
                                $item = App\Models\Tenant\Item::find($value->item_id);
                                if ($item) {
                                    $internal_id = $item->internal_id;
                                    $description = $item->description;

                                    $unit_type_id = $item->unit_type_id;
                                    $quantity_presentation = $value->presentations;
                                    $total = $value->total;
                                }
                                $record = (object) [
                                    'seller_ids' => $seller_ids,
                                    'internal_id' => $internal_id,
                                    'description' => $description,
                                    'quantity' => $quantity,
                                    'unit_type_id' => $unit_type_id,
                                    'quantity_presentation' => $quantity_presentation,
                                    'total' => $total,
                                    'presentations' => implode(', ', $formatted_result),
                                ];
                            @endphp
                            <tr>
                                <td class="celda">{{ $key + 1 }}</td>
                                <td class="celda">
                                    {{ $record->internal_id }}
                                </td>
                                <td class="celda">
                                    {{ $record->description }}
                                </td>
                                <td class="celda">
                                    {{ $record->quantity }}
                                </td>
                                <td class="celda">
                                    {{ $record->unit_type_id }}
                                </td>
                                <td class="celda">
                                    {{ $record->presentations }}
                                </td>
                                <td class="celda">
                                    {{ $record->total }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="celda" colspan="3">
                                TOTAL ITEMS {{ count($records) }}
                            </td>
                            <td class="celda" colspan="2"><strong></strong></td>
                            <td class="celda" colspan="2" style="text-align: end;"><strong>TOTAL VENTA {{$records->sum('total')}}</strong></td>
                        </tr>
                    </tfoot>
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
