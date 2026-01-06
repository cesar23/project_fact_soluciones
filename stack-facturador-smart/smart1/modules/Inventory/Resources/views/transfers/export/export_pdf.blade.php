<html>

<head>
    <title>Transferencias</title>
</head>
<style>
    body {
        font-family: Arial, sans-serif;
    }

    @page {
        size: landscape;
        margin: 20px;
        margin-top: 45px;
        margin-bottom: 35px;
    }

    .header {
        text-align: center;
        font-size: 14px;
    }

    h4 {
        padding: 0;
        margin: 0;
    }

    .w-100 {
        width: 100%;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table,
    th,
    td {
        border: 1px solid black;
    }

    td,
    th {
        font-size: 12px;
    }

    .text-end {
        text-align: right;
    }
    .mt-5{
        margin-top: 5px;
    }
</style>

<body>
    <div class="header">
        <h4>TRANSFERENCIAS</h4>
        <h4>{{ $company->name }}</h4>
        <h4>{{ $company->number }}</h4>
    </div>
    <div class="w-100 mt-5">
        <table class="w-100">
            <thead>
                <tr>
                    <th>FECHA</th>
                    <th>DOCUMENTO</th>
                    <th>A. INICIAL</th>
                    <th>A. FINAL</th>
                    <th>DETALLE</th>
                    <th>USUARIO</th>
                    <th>CANTIDAD</th>
                    <th>ESTADO</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td>{{ $item['created_at'] }}</td>
                        <td>{{ $item['series'] }} - {{ $item['number'] }}</td>
                        <td>{{ $item['warehouse'] }}</td>
                        <td>{{ $item['warehouse_destination'] }}</td>
                        <td>{{ $item['description'] }}</td>
                        <td>{{ $item['user_name'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ $item['state'] == 1 ? 'POR ACEPTAR' : ($item['state'] == 2 ? 'ACEPTADO' : 'RECHAZADO') }}</td>
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
