<html>
    <head>
        <title>Inventario por Almacenes</title>
    </head>
    <style>
        body{
            font-family: Arial, sans-serif;
        }
        @page{
            size: landscape;
            margin: 10px;
            margin-top: 45px;
        }
        .header{
            text-align: center;
            font-size: 14px;
        }
        h4{
            padding: 0;
            margin: 0;
        }
        .w-100{
            width: 100%;
        }
        table{
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td{
            border: 1px solid black;
        }
        td, th{
            font-size: 10px;
            padding: 3px;
        }
        .text-end{
            text-align: right;
        }
        .text-center{
            text-align: center;
        }
        .warehouse-header{
            writing-mode: vertical-rl;
            text-orientation: mixed;
            background-color: #f0f0f0;
            font-weight: bold;
            min-width: 100px;
            max-width: 115px;
        }
        .product-column{
            min-width: 50px;
            text-align: left;
        }
        .stock-cell{
            min-width: 30px;
            max-width: 30px;
            text-align: center;
            font-size: 9px;
        }
    </style>
    <body>

        <div class="header">
            <h4>Inventario por Almacenes</h4>
            <h4>{{ $company_name }}</h4>
            <h4>{{ $company_number }}</h4>
        </div>
        <div class="w-100">
            <table class="w-100">
                <thead>
                    <tr>
                        <th class="product-column">Producto</th>
                        @foreach($warehouses as $warehouse)
                            <th class="warehouse-header">{{ $warehouse->description }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($matrixData as $row)
                        <tr>
                            <td class="product-column">{{ $row['item_description'] }}</td>
                            @foreach($row['warehouses'] as $stock)
                                <td class="stock-cell">{{ $stock }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        <script type="text/php">
            if (isset($pdf)) {
                $date = date('d/m/Y');
                $time = date('H:i:s');
                $pdf->page_text($pdf->get_width() - 75, 10, "Página {PAGE_NUM}", null, 7);
                $pdf->page_text($pdf->get_width() - 75, 16, "Fecha: $date", null, 7);
                $pdf->page_text($pdf->get_width() - 75, 22, "Hora: $time", null, 7);
                $pdf->page_text($pdf->get_width() - 75, $pdf->get_height() - 20, "Página N°{PAGE_NUM} de {PAGE_COUNT}", null, 7);

            }
        </script>
    </body>
</html>