<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Detracciones</title>
    <style>
        @page {
            margin: 15px;
            margin-top: 45px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }
        .subtitle {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th {
            background-color: #2599E2;
            color: white;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .small {
            font-size: 10px;
            color: #666;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .company-info table {
            width: auto;
            margin: 0;
        }
        .company-info td {
            border: none;
            padding: 2px 10px 2px 0;
        }
    </style>
</head>
<body>
    <div>
        <h1 class="title">Reporte de Detracciones {{ date('Y-m-d') }}</h1>
    </div>
    
    @if(isset($company))
    <div class="company-info">
        <table>
            <tr>
                <td><strong>Empresa:</strong></td>
                <td>{{ $company->name }}</td>
                <td><strong>RUC:</strong></td>
                <td>{{ $company->number }}</td>
            </tr>
        </table>
    </div>
    @endif
    
    @if (!empty($records))
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha de detracción</th>
                    <th>Comprobante</th>
                    <th>Cliente</th>
                    <th class="text-center">Constancia Pago</th>
                    <th class="text-center">Total detracción</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $key => $row)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $row['date_of_issue'] ?? '' }}</td>
                        <td>
                            {{ $row['number'] ?? '' }}
                            @if(isset($row['document_type_description']))
                                <br><span class="small">{{ $row['document_type_description'] }}</span>
                            @endif
                        </td>
                        <td>
                            {{ $row['customer_name'] ?? '' }}
                            @if(isset($row['customer_number']))
                                <br><span class="small">{{ $row['customer_number'] }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ isset($row['detraction']->pay_constancy) ? $row['detraction']->pay_constancy : '-' }}
                        </td>
                        <td class="text-center">
                            {{ isset($row['detraction']->amount) ? number_format($row['detraction']->amount, 2) : '0.00' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        

    @else
        <div style="margin-top: 20px;">
            <p>No se encontraron registros de detracciones.</p>
        </div>
    @endif
    
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
