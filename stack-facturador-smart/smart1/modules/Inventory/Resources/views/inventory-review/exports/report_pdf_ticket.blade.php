<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Revisión Inventario</title>
    <style>
        html {
            font-family: sans-serif;
            font-size: 14px;
            margin:5px;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        .title {
            font-weight: 500;
            text-align: center;
            font-size: 24px;
        }

        .label {
            font-weight: 500;
        }
        .celda{
        }
        .table-records {
            margin-top: 24px;
        }

        .table-records tr th {
            font-weight: bold;
            color: black;
        }

        .table-records tr th,
        .table-records tr td {
            border: 1px solid #000;
            font-size: 14px;
        }
        .text-danger{
            color: red;
            background-color: red;
        }
        
    </style>
</head>
<body>
<table style="width: 100%">
    <tr>
        <td colspan="6"
            class="title"><strong>Revisión Inventario</strong></td>
    </tr>
    <tr>
    
        <td colspan="6">{{$company->name}}</td>
    </tr>
    <tr>
    
        <td colspan="6">{{$company->number}}</td>
    </tr>
    <tr>
        <td colspan="6"
            class="label">Fecha: {{ date('d/m/Y')}}
        </td>
    
    </tr>
</table>
<table style="width: 100%"  class="table-records">
    <thead>
        <tr>
            <th align="left">Producto</th>
            <th align="center">Stock sistema</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $idx => $row)
            @php
    
            @endphp
            <tr>
                <td class="celda" align="left">{{$row['item_fulldescription']}}</td>
                <td class="celda" align="right">{{$row['stock']}}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
