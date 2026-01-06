<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Envio de reporte</title>
    <style>
        body {
            color: #000;
        }
        ul {
            list-style: none;
        }
    </style>
</head>
<body>
<p>Estimad@: {{ $company->name }}, informamos que su reporte del periodo <strong>{{ $month }}</strong> de TIPO: <strong>{{ $type }}</strong> fue generado exitosamente y lo adjuntamos a la presente.</p>

<ul>

</ul>
</body>
</html>