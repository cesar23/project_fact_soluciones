<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Nota de ventas</title>
        <style>
            @page {
              margin: 5;
            }
            html {
                font-family: sans-serif;
                font-size: 10px;
            }

            table {
                width: 100%;
                border-spacing: 0;
                border: 1px solid black;
                border-collapse: collapse;
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
            <p align="center" class="title"><strong>Reporte Nota de Venta</strong></p>
        </div>
        <div style="margin-top:20px; margin-bottom:20px;">
            <table>
                <tr>
                    <td>
                        <p><strong>Empresa: </strong>{{$company->name}}</p>
                    </td>
                    <td>
                        <p><strong>Fecha: </strong>{{date('Y-m-d')}}</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p><strong>Ruc: </strong>{{$company->number}}</p>
                    </td>
                    <td>
                        
                    </td>
                
                </tr>
            </table>
        </div>
        @if(!empty($records))
            <div class="">
                <div class=" ">

                

                    <table style="border: 1px solid black;">
                        <thead>
                            <tr>
                                <th style="border: 1px solid black;">#</th>
                                <th style="border: 1px solid black;">Fecha Emisión</th>
                                <th style="border: 1px solid black;">Hora Emisión</th>
                                <th style="border: 1px solid black;">Usuario/Vendedor</th>
    
                                <th style="border: 1px solid black;">Cliente</th>
                                <th style="border: 1px solid black;">Nota de Crédito</th>
                                <th style="border: 1px solid black;">Estado</th>
                                <th style="border: 1px solid black;">Moneda</th>
                                <th style="border: 1px solid black;">Región</th>
                                <th style="border: 1px solid black;">Documento donde descontó</th>
                                <th style="border: 1px solid black;">Documento afectado</th>
                                <th style="border: 1px solid black; text-align: end;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $key => $value)
                    
                                <tr>
                                    <td style="border: 0.5px solid black;">{{ $key + 1 }}</td>
                                    <td style="border: 0.5px solid black;">{{ $value['date_of_issue'] }}</td>
                                    <td style="border: 0.5px solid black;">{{ $value['time_of_issue'] }}</td>
                                    <td style="border: 0.5px solid black;">{{ $value['user_name'] }}</td>
                                    <td style="border: 0.5px solid black;">{{ $value['customer_name'] }}</td>
                                    <td style="border: 0.5px solid black;">{{ $value['number_full'] }}</td>
                                    <td style="border: 0.5px solid black;background-color: {{ $value['internal'] ? '#ffc107' : '#28a745' }}; color: white;">
                                        @if($value['internal'])
                                            <span style="background-color: #ffc107; color: black;">Interno</span>
                                        @else
                                            <span style="background-color: #28a745; color: black;">Registrado</span>
                                        @endif
                                    </td>
                                    <td style="border: 0.5px solid black;">{{ $value['currency_type_id'] }}</td>
                                    <td style="border: 0.5px solid black;">{{ $value['region'] }}</td>
                                    <td style="border: 0.5px solid black;">{{ $value['document_used'] }}</td>
                                    <td style="border: 0.5px solid black;">{{ $value['document_affected'] }}</td>
                                    <td style="border: 0.5px solid black; text-align: end;">{{ $value['total'] }}</td>
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
