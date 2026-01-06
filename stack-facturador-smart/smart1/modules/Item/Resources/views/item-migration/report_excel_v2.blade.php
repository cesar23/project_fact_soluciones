<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Items</title>
</head>

<body>
    @if (!empty($records))
        <div class="">
            <div class=" ">
                <table class="">
                    <thead>
                        <tr>
                            <td>Código Interno</td>
                            <td>Nombre</td>
                            <td>Nombre secundario</td>
                            <td>Modelo</td>
                            <td>Código Tipo de Unidad</td>
                            <td>TIPO DE MONEDA</td>
                            <td>Precio Unitario Venta</td>
                            <td>Stock</td>
                            <td>Categoría</td>
                            <td>Marca</td>
                            <td>Descripcion</td>
                            <td>VISUALIZAR LOTE (LOTE)</td>
                            <td>Fec. Vencimiento</td>
                            <td>Cód barras</td>
                        </tr>
                    </thead>
                    
                    <tbody>
                        @foreach ($records as $key => $value)
                        @php
                            if ($value->lot_code === null && $value->date_of_due === null) {
                                $lots_group = $value->lots_group->where('warehouse_id', $warehouse_id);
                                $first_lot = $lots_group->first();
                            } else {
                                $first_lot = (object)[
                                    'lote_code' => $value->lot_code,
                                    'date_of_due' => $value->date_of_due
                                ];
                            }
                        @endphp
                            <tr>
                                <td class="celda">{{ $value->internal_id }}</td>
                                <td class="celda">{{ $value->description }}</td>
                                <td class="celda">{{ $value->second_name }}</td>
                                <td class="celda">{{ $value->model }}</td>
                                <td class="celda">{{ $value->unit_type_id }}</td>
                                <td class="celda">{{ $value->currency_type_id }}</td>
                                <td class="celda">{{ $value->sale_unit_price }}</td>
                                <td class="celda">{{ $value->stock }}</td>
                                <td class="celda">{{ $value->category ? $value->category->name : '-' }}</td>
                                <td class="celda">{{ $value->brand ? $value->brand->name : '-' }}</td>
                                <td class="celda">{{ $value->name }}</td>
                                <td class="celda">{{ $first_lot ? $first_lot->lote_code : '' }}</td>
                                <td class="celda">
                                    {{ $first_lot ? Carbon\Carbon::parse($first_lot->date_of_due)->format('d/m/Y') : null }}
                                </td>
                                <td class="celda">{{ $value->barcode }}</td>
                            </tr>
                        @endforeach
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
