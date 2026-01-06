<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Productos</title>
</head>

<body>
    @if (!empty($records))
        <div class="">
            <div class=" ">
                <table class="">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Código Interno</th>
                            <th>Modelo</th>
                            <th>Código Sunat</th>
                            <th>Código Tipo de Unidad</th>
                            <th>Código Tipo de Moneda</th>
                            <th>Precio Unitario Venta</th>
                            <th>Codigo Tipo de Afectación del Igv Venta</th>
                            <th>Tiene Igv</th>
                            <th>Precio Unitario Compra</th>
                            <th>Codigo Tipo de Afectación del Igv Compra</th>
                            <th>Stock</th>
                            <th>Stock Mínimo</th>
                            <th>Categoria</th>
                            <th>Marca</th>
                            <th>Descripcion</th>
                            <th>Nombre secundario</th>
                            <th>Código lote</th>
                            <th>Fec. Vencimiento</th>
                            <th>Cód barras</th>
                            <th>Hipervínculos</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $value)
                            @php
                            @endphp

                            <tr>
                                <td class="celda"></td>
                                <td class="celda">
                                    {{ $value["internal_id"] }}
                                </td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda">{{ $value["sale_unit_price"] }}</td>
                                <td class="celda">SI</td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda">
                                    {{$value["quantity"]}}
                                </td>


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
