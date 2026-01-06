<html>
<style>
    @page {
        size: landscape;
        margin: 5px;
        margin-top: 45px;
    }

    body {
        font-family: 'Arial', sans-serif;
    }

    .w-100 {
        width: 100%;
    }

    .text-end {
        text-align: right;
    }

    .text-left {
        text-align: left;
    }

    .text-center {
        text-align: center;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        border: 1px solid #000;
    }

    td {
        border: 1px solid #000;
        padding: 5px;
        font-size: 12px;
    }

    th {
        border: 1px solid #000;
        padding: 5px;
        font-size: 12px;
    }

    h3,
    h4 {
        margin: 0;
    }

    .mt-5 {
        margin-top: 5px;
    }
</style>

<head>
    <title>Compras</title>
</head>

<body>
    <div class="header w-100">
        <div class="w-100 text-center">
            <h3>{{ $company_name }}</h3>
            <h4>{{ $company_number }}</h4>
        </div>
        @if ($establishment_description)
            <div class="w-100 text-center">
                <h4>Sucursal: {{ $establishment_description }}</h4>
            </div>
        @endif
    </div>
    <div class="w-100 mt-5">
        <table class="w-100">
            <thead>
                <tr>
                    <th>
                        F. Emisión
                    </th>
                    <th>Proveedor</th>
                    <th>Estado</th>
                    <th>Estado de pago</th>
                    <th>Número</th>
                    <th>
                        Moneda
                    </th>
                    <th>
                        Total
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td class="text-center">{{ $item['date_of_issue'] }}</td>
                        <td>{{ $item['supplier_name'] }}
                            <div>
                                <small>
                                    {{ $item['supplier_number'] }}
                                </small>
                            </div>
                        </td>
                        <td class="text-center">{{ $item['state_type_description'] }}</td>
                        <td class="text-center">{{ $item['state_type_payment_description'] }}</td>
                        <td class="text-center">{{ $item['number'] }}</td>
                        <td class="text-center">{{ $item['currency_type_id'] }}</td>
                        <td class="text-end">{{ $item['total'] }}</td>
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
