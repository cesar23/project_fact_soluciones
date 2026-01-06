<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
          content="application/pdf; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible"
          content="ie=edge">
    <title>Inventario</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
            width: 80mm;
        }
        html {
            margin: 5px;
            padding: 0;
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-spacing: 0;
            border: 0.5px solid black;
        }

        .celda {
            text-align: center;
            padding: 3px;
            font-size: 12px;
            border: 0.1px solid black;
        }

        th {
            font-size: 12px;
            padding: 3px;
            text-align: center;
            border: 0.1px solid black;
        }

        .title {
            font-weight: bold;
            padding: 3px;
            font-size: 14px !important;
            text-decoration: underline;
        }

        p > strong {
            margin-left: 2px;
            font-size: 12px;
            padding:0px;
        }
        p{
            margin:5px;
        }

        thead {
            font-weight: bold;
            /* background: gray; */
            color: black;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $configuration = \App\Models\Tenant\Configuration::first();
        $stock_decimal = $configuration->stock_decimal;
    @endphp
<div>
    <p align="center"
       class="title"><strong>Reporte Inventario</strong></p>
</div>
<div style="margin-top:20px; margin-bottom:20px;">
    <table>
        <tr>
            <td>
                <p><strong>Empresa: </strong>{{$company->name}}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p><strong>Fecha: </strong>{{date('Y-m-d')}}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p><strong>Ruc: </strong>{{$company->number}}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p><strong>Establecimiento: </strong>{{$establishment->description}}
                    - {{$establishment->address}} - {{$establishment->department->description}} - {{$establishment->district->description}}</p>
            </td>
        </tr>
    </table>
</div>
@if(!empty($reports))
    <div class="">
        <div class=" ">
            <table class="">
                <thead>
                <tr>
                    <th style="text-align: left;">Descripción</th>
                    <th style="text-align: right;">Stock</th>
                
                </tr>
                </thead>
                <tbody>
    
                @foreach($reports as $key => $value)
            
                    <tr>
                        <td class="celda" style="text-align: left;">{{$value->item->description ?? ''}}</td>
                        <td class="celda" style="text-align: right;">{{number_format($value->total_stock, $stock_decimal)}}</td>
                    
                    </tr>
                @endforeach
            
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="callout callout-info">
        <p>No se encontraron registros.</p>
    </div>
@endif
</body>
</html>
