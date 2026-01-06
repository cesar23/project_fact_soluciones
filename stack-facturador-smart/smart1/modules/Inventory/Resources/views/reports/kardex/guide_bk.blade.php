<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Guia</title>
    <style>
        html {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-spacing: 0;
            border-collapse: collapse;
        }

        th {
            padding: 5px;
            text-align: center;
            border: thin solid black;
        }

        thead {
            font-weight: bold;
            background: #0088cc;
            color: white;
            text-align: center;
        }
        .border-box {
            border: 1px solid black;
        }
    </style>
</head>
<body>
<table style="border:2px solid black;">
    <tr>
        <td style="text-align: center">
            <strong>R.U.C. {{$company_number}}</strong>
        </td>
    </tr>
    <tr>
        <td style="text-align: center">
            <strong>{{$document_type_name}}
        </td>
    </tr>
    <tr>
        <td style="text-align: center">
            <strong>{{$document_number}}</strong>
        </td>
    </tr>
</table>
<table>
    <tr>
        <td>ALMACÉN:</td>
        <td>{{ $warehouse_name }}</td>
        <td>FECHA DE DOCUMENTO:</td>
        <td>{{ $document_date_of_issue }}</td>
    </tr>
    <tr>
        <td>MOTIVO:</td>
        <td>{{ $transaction_name }}</td>
        @if ($reference)
            <td>REFERENCIA:</td>
            <td>{{ $reference }}</td>
            @else
            <td></td>
        <td></td>
        @endif
    </tr>
</table>
<table style="border: 1px solid black;">
    <thead>
    <tr>
        <th style="text-align: center">ITEM</th>
        <th>CODIGO<br/>INTERNO</th>
        <th>DESCRIPCIÓN</th>
        <th>UNIDAD</th>
        <th style="text-align: right">CANTIDAD</th>
        <th>LOTE</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $row)
        <tr>
            <td class="border-box" style="text-align: center">{{$loop->iteration}}</td>
            <td class="border-box">{{$row['item_internal_id']}}</td>
            <td class="border-box">{{$row['item_name']}}</td>
            <td class="border-box">{{$row['unit_type_id']}}</td>
            <td class="border-box" style="text-align: right">{{$row['quantity']}}</td>
            <td class="border-box" style="text-align: right">{{$row['lot']}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
