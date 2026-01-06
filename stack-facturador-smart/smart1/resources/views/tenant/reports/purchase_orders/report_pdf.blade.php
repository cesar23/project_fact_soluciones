<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Compras</title>
    <style>
        html {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-spacing: 0;
            border: 1px solid black;
        }

        .celda {
            text-align: center;
            padding: 5px;
            border: 0.1px solid black;
        }

        th {
            padding: 5px;
            text-align: center;
            border-color: #0088cc;
            border: 0.1px solid black;
        }

        .title {
            font-weight: bold;
            padding: 5px;
            font-size: 20px !important;
            text-decoration: underline;
        }

        p>strong {
            margin-left: 5px;
            font-size: 13px;
        }

        thead {
            font-weight: bold;
            background: #0088cc;
            color: white;
            text-align: center;
        }
    </style>
</head>

<body>
    <div>
        <h3 align="center" class="title"><strong>Reporte Orden de Compra</strong></h3>
    </div>
    <br>
    <div style="margin-top:20px; margin-bottom:15px;">
        <table>
            <tr>
                <td>
                    <p><b>Empresa: </b></p>
                </td>
                <td align="center">
                    <p><strong>{{ $company->name }}</strong></p>
                </td>
                <td>
                    <p><strong>Fecha: </strong></p>
                </td>
                <td align="center">
                    <p><strong>{{ date('Y-m-d') }}</strong></p>
                </td>
            </tr>
            <tr>
                <td>
                    <p><strong>Ruc: </strong></p>
                </td>
                <td align="center">{{ $company->number }}</td>
                <td>
                    <p><strong>Establecimiento: </strong></p>
                </td>
                <td align="center">{{ $establishment->address }} - {{ $establishment->department->description }} -
                    {{ $establishment->district->description }}</td>
            </tr>
        </table>
    </div>
    <br>
    @if (!empty($records))
        <div class="">
            <div class=" ">
                <table class="">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th class="celda">F. Emisión</th>
                            <th class="celda">F. Vencimiento</th>
                            <th class="celda">Proveedor</th>
                            <th class="celda">Tipo</th>
                            <th class="celda">O. Compra</th>
                            <th class="celda">Estado</th>
                            <th class="celda">O. Venta</th>
                            <th class="celda">Moneda</th>

                            <th class="celda">T.Gravado</th>
                            <th class="celda">T.Igv</th>
                            <th class="celda">Total</th>
                            <th class="celda">Cliente</th>
                            <th class="celda">Cotización</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $value)
                            <tr>
                                <td class="celda">{{ $key  + 1}}</td>
                                <td class="celda">{{ $value["date_of_issue"]}}</td>
                                <td class="celda">{{ $value["date_of_due"]}}</td>
                                <td>
                                    {{ $value["supplier_name"]}}
                                    <br />
                                    {{ $value["supplier_number"]}}
                                </td>
                                <td class="celda">
                                    {{ $value['type'] == "goods" ? "Bienes" : "Servicios" }}
                                </td>
                                <td class="celda">
                                    {{ $value["number"]}}
                                
                                </td>
        
                                <td class="celda">
                                    
                                        {{ $value["state_type_description"]}}
                                </td>
        
                                <td class="celda">{{ $value["sale_opportunity_number_full"]}}</td>
        
                                <td class="celda">{{ $value["currency_type_id"]}}</td>
        
                                <td class="celda">{{ $value["total_taxed"]}}</td>
                                <td class="celda">{{ $value["total_igv"]}}</td>
                                <td class="celda">{{ $value["total"]}}</td>
                                <td class="celda">
                                    {{ $value["customer_name"]}}
                                </td>
                                <td class="celda">
                                    {{ $value["quotation_number"]}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
