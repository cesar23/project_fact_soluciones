<!DOCTYPE html>
<html>
<head>
    <title>Plantilla</title>
    <style> 
        @page {
            margin: 0.5cm 0.5cm;
        }
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
        }
        .header p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $company->name }}</h1>
        <p>{{ $company->number }}</p>
        <p>Fecha: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Edad</th>
                <th>Sexo</th>
                <th>Puesto</th>
                <th>Fecha de ingreso</th>
                <th>Fecha de cese</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    <td>{{ $record->code }}</td>
                    <td>{{ $record->name }}</td>
                    <td>{{ $record->last_name }}</td>
                    <td>{{ $record->age }}</td>
                    <td>{{ $record->sex }}</td>
                    <td>{{ $record->job_title }}</td>
                    <td>{{ $record->admission_date }}</td>
                    <td>{{ $record->cessation_date }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>