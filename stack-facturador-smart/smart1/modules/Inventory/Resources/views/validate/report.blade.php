<!DOCTYPE html>
<html>

<head>
    <title>Validación</title>
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

        th,
        td {
            padding: 5px;
            text-align: center;
            border: 0.1px solid black;
        }

        th {
            border-color: #0088cc;
        }

        .title {
            font-weight: bold;
            padding: 5px;
            font-size: 20px !important;
            text-decoration: underline;
        }

        p>strong {
            margin-left: 5px;
            font-size: 12px;
        }

        thead {
            font-weight: bold;
            background: #0088cc;
            color: white;
        }

        .text-left {
            text-align: left;
        }

        .serie {
            font-size: 10px;
            color: rgb(90, 88, 88);
            font-style: italic
        }

        @page {
            margin: 5px 25px;
        }
    </style>
</head>

<body>
    <div>
        <h1>Validación de inventario</h1>
        <p><strong>Fecha:</strong> {{ $date }}</p>
        <p><strong>Usuario:</strong> {{ $user_name }}</p>
        <p><strong>Almacén:</strong> {{ $warehouse_name }}</p>
        <p><strong>Observaciones:</strong> {{ $observations }}</p>
    </div>

    <div>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Contabilizado</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td class="text-left">
                            <strong>
                            {{$item->item->internal_id}}    - {{ $item->item->description }}
                            </strong>
                        </td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ $item['stock'] }}</td>
                    </tr>
                    @if (isset($item['lots']) && count($item['lots']) > 0)
                        <tr>
                            <td colspan="3" class="text-left">
                                <small>SERIES CONTADAS:</small>
                            </td>
                        </tr>
                        @foreach (array_chunk($item['lots'], 3) as $lotsChunk)
                            <tr>
                                @foreach ($lotsChunk as $lot)
                                    <td class="serie">{{ $lot['series'] }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endif
                    @if (isset($item['lots_not_count']) && count($item['lots_not_count']) > 0)
                        <tr>
                            <td colspan="3" class="text-left">
                                <small>SERIES NO CONTADAS:</small>
                            </td>
                        </tr>
                        @foreach (array_chunk($item['lots_not_count'], 3) as $lotsNotCountChunk)
                            <tr>
                                @foreach ($lotsNotCountChunk as $lot)
                                    <td class="serie">{{ $lot['series'] }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
