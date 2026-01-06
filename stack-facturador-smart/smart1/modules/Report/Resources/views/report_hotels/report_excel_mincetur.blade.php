<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RH</title>
    <style>
        .td-custom {
            line-height: 0.1em;
        }

        .celda {
            text-align: center;
            padding: 5px;
            border: 0.1px solid black;
        }

        .width-custom {
            width: 50%
        }
    </style>
</head>

<body>
    <div>
        <h3 align="center" class="title"><strong>Reporte de Huespedes</strong></h3>
    </div>
    <br>
    <div style="margin-top:20px; margin-bottom:15px;">
        <table>
            <tr>
                <td class="td-custom width-custom">
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

            </tr>



        </table>
    </div>


    @if (!empty($records))
        <table >
            <thead>
                <tr>
                    <th align="center" colspan="15">
                        <p><strong>Reporte General</strong></p>
                    </th>
                </tr>
                <tr>

                    <th>ITEM</th>
                    <th>APELLIDOS Y NOMBRES</th>
                    <th>SEXO</th>
                    <th>PAIS DE RESIDENCIA</th>
                    <th>REGIÓN DE RESIDENCIA</th>
                    <th>MOTIVO DE VIAJE</th>
                    <th>TIPO DE DOCUMENTO</th>
                    <th>N° DE DOCUMENTO</th>
                    <th>FECHA DE INGRESO</th>
                    <th>FECHA DE SALIDA</th>
                    <th>TIPO DE HABITACION</th>
                    <th>N° DE HABITACION</th>
                    <th>TARIFA </th>

                </tr>
            </thead>
            <tbody>
               
                @foreach ($records as $key => $value)
                   
                    <tr>

                        <td>{{ $key + 1 }}</td>
                        <td>{{$value["customer_name"]}}</td>
                        <td>{{$value["sex"]}}</td>
                        <td>{{$value["country"]}}</td>
                        <td>{{$value["reg"]}}</td>
                        <td>{{$value["reason"]}}</td>
                        <td>{{$value["customer_document_type"]}}</td>
                        <td>{{$value["customer_document_number"]}}</td>
                        <td>{{$value["start_date"]}}</td>
                        <td>{{$value["end_date"]}}</td>
                        <td>{{$value["category"]}}</td>
                        <td>{{$value["room"]}}</td>
                        <td>{{$value["room_rastes"]}}</td>

                    </tr>
                @endforeach
              
               
            </tbody>
        </table>
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
