<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Envio de nota de venta</title>
    <style>
        body {
            color: #000;
        }

        ul {
            list-style: none;
        }
        /* estilos para las tablas, bordes y demas */

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table th,
        table td {
            padding: 5px;
            border: 1px solid #000;
        }

        table th {
            text-align: left;
        }

        
        .w-100 {
            width: 100%;
        }
        text-center {
            text-align: center;
        }

    </style>
</head>

<body>
    <p>
        Estimad@:
        {{ $customer->name }}
        @if ($localmessage == null)
            , informamos que su nota de venta ha sido emitida exitosamente.
        @else
            {{ $localmessage }}
        @endif

        @if ($isIntegrateSystem)
            @php
                $items = $document->items;
                $payment_method_type = $document->payment_method_type;
                $payment_method_type_description = 'EFECTIVO';
                if ($payment_method_type) {
                    $payment_method_type_description = $payment_method_type->description;
                }
            @endphp
            <div class="w-100 text-center">
                <table width="85%">
                    <thead>
                        <tr>
                            <th>PRODUCTO</th>
                            <th>CANTIDAD</th>
                            <th>PRECIO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item->item->description }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->unit_price ,2)}}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2">
                                <strong>
                                    SUBTOTAL
                                </strong>
                            </td>
                            <td>{{ number_format($document->total,2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <strong>
                                    METODO DE PAGO
                                </strong>
                            </td>
                            <td>{{ $payment_method_type_description }}</td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <strong>TOTAL</strong>
                            </td>
                            <td>{{ number_format($document->total,2) }}
                                (incluye igv {{ $document->total_igv }} 18%)
    
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </p>

    <ul>

    </ul>
</body>

</html>
