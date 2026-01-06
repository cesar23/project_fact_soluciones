<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kardex</title>
    <style>
        html {
            font-family: sans-serif;
            font-size: 12px;
        }
        table{
            border: 1px solid #ccc;
        }
        .table {
            width: 100%;
            border-spacing: 0;
         }

        .celda {
            text-align: center;
            padding: 5px;
            border: 0.1px solid black;
        }

        th {
            padding: 5px;
            text-align: center;
         }
        td{
            padding: 5px;
            text-align: left;
            border-bottom: 1px solid #ccc;
         }
        .title {
            font-weight: bold;
            padding: 5px;
            font-size: 20px !important;
            margin: 0px !important;
         }

        p > strong {
            margin-left: 5px;
            font-size: 13px;
        }

        thead {
            font-weight: bold;
            background: #dddd;
            text-align: center;
            border: 0.1px solid black;

        }
    </style>
</head>
<body>
<div>
    <p align="right" class="title"><strong>Reporte Kardex por Atributos</strong></p>
</div>
    <div style="margin-top: 20px;">
        <table class="table">
            <tr>
                <td>
                    <strong>Empresa: </strong>{{$company->name}}
                </td>
                <td>
                    <strong>Fecha: </strong>{{date('Y-m-d')}}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Ruc: </strong>{{$company->number}} 
                </td>
                <td>
                    <strong>Establecimiento: </strong>{{$establishment->address}}
                        - {{$establishment->department->description}} - {{$establishment->district->description}} 
                </td>
            </tr>
            <?php
    
            $producto_name = $item->internal_id ? $item->internal_id . ' - ' . $item->description : $item->description;
            $warehousePrices = $item->warehousePrices;

            ?>
            <tr>
                <td>
                
                        <strong>Producto: </strong>{{$producto_name}}
                    
                </td>
                <td>
                    @if(!empty($warehousePrices)&& count($warehousePrices)> 0)
                        <strong>Precios por almacenes:</strong>
                        @foreach($warehousePrices as $wprice)
                            <br><strong>{{$wprice->getWarehouseDescription() }}:</strong> {{ $wprice->getPrice() }}
                        @endforeach
                    @endif
                </td>
            </tr>
        </table>
    </div>
    <div style="margin-top: 20px;">
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Almacen</th>
                <th>Marca</th>
                <th>Año de Modelo</th>
                <th>Color</th>
                <th>Motor</th>
                <th>Modelo</th>
                <th>Serie / Chasis</th>
                <th class="text-center">Estado</th>
            </tr>
            </thead>
            <tbody>
                <?php
                ?>
                @foreach($records as $key => $row)
                <tr>
                    <td>{{$loop->iteration}}</td>
                    <td>{{ optional($row->warehouse)->description }}</td>
                    <td>{{ $row['attribute'] }}</td>
                    <td>{{ $row->attribute5 }}</td>
                    <td>{{ $row->attribute3 }}</td>
                    <td>{{ $row->attribute4 }}</td>
                    <td>{{ $row->attribute2 }}</td>
                    <td>{{ $row->chassis }}</td>
                    <td>{{ $row->has_sale==false ? "Disponible" : "Vendido" }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
</html>
