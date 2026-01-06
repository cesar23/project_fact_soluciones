<html>
    <head>
        <title>Inventario</title>
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
            font-size: 12px;
        }
        .text-end{
            text-align: right;
        }
    </style>
    <body>
    
        <div class="header">
            <h4>Inventario</h4>
            <h4>{{ $company_name }}</h4>
            <h4>{{ $company_number }}</h4>
            @if($warehouse_description)
                <h2>Almacén: {{ $warehouse_description }}</h2>
            @endif
        </div>
        <div class="w-100">
            <table class="w-100">
                <thead>
                    <tr>
                        <th>Producto</th>
                        @if($warehouse_description == null)
                            <th>Almacén</th>
                        @endif
                        <th>Stock </th>
                        
                    </tr>
                </thead>
                <tbody>
                    @foreach($allRecords as $record)
                        <tr>
                            <td>{{ $record['item_description'] }}</td>
                            @if($warehouse_description == null)
                                <td>{{ $record['warehouse_description'] }}</td>
                            @endif
                            <td class="text-end">{{ $record['stock'] }}</td>   
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