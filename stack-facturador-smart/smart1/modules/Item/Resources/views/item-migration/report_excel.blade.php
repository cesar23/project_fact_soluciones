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
                            <td>Nombre</td>
                            <td>Código Interno</td>
                            <td>Modelo</td>
                            <td>Código Sunat</td>
                            <td>Código Tipo de Unidad</td>
                            <td>Código Tipo de Moneda</td>
                            <td>Precio Unitario Venta</td>
                            <td>Codigo Tipo de Afectación del Igv Venta</td>
                            <td>Tiene Igv</td>
                            <td>Precio Unitario Compra</td>
                            <td>Codigo Tipo de Afectación del Igv Compra</td>
                            <td>Stock</td>
                            <td>Stock Mínimo</td>
                            <td>Categoria</td>
                            <td>Marca</td>
                            <td>Descripcion</td>
                            <td>Nombre secundario</td>
                            <td>Código lote</td>
                            <td>Fec. Vencimiento</td>
                            <td>Cód barras</td>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($records as $key => $value)
                            <tr>
                                <td class="celda">{{ $value->description }}</td>
                                <td class="celda">{{ $value->internal_id }}</td>
                                <td class="celda">{{ $value->model }}</td>
                                <td class="celda">{{ $value->item_code }}</td>
                                <td class="celda">{{ $value->unit_type_id }}</td>
                                <td class="celda">{{ $value->currency_type_id }}</td>
                                <td class="celda">{{ $value->sale_unit_price }}</td>
                                <td class="celda">{{ $value->sale_affectation_igv_type_id }}</td>
                                <td class="celda">{{ $value->has_igv ? 'SI' : 'NO' }}</td>
                                <td class="celda">{{ $value->purchase_unit_price }}</td>
                                <td class="celda">{{ $value->purchase_affectation_igv_type_id }}</td>
                                @php
                                    $wh = \App\Models\Tenant\ItemWarehouse::where('item_id', $value->id)->where('warehouse_id', $warehouse_id)->first();
                                    $stock = $wh ? $wh->stock : $value->stock;
                                
                                @endphp
                                <td class="celda">{{ $stock }}</td>
                                <td class="celda">{{ $value->stock_min ?? 0 }}</td>
                                <td class="celda">{{ $value->category ? $value->category->name : '-' }}</td>
                                <td class="celda">{{ $value->brand ? $value->brand->name : '-' }}</td>
                                <td class="celda">{{ $value->name }}</td>
                                <td class="celda">{{ $value->second_name }}</td>
                                <td class="celda">{{ $value->lote_code }}</td>
                                <td class="celda">
                                    {{ $value->date_of_due ? Carbon\Carbon::parse($value->date_of_due)->format('d/m/Y') : null }}
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
