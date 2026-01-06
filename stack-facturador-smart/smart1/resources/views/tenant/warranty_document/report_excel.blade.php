<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Botellas</title>
    </head>
    <body>  
        @if(!empty($records))
            <div class="">
                <div class=" ">
                    <table class="">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha Emisión</th>
                                <th>Comprobante</th>
                                <th>Cliente</th>
                                <th>Devuelto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $key => $value)

                    

                            <tr>
                                <td class="celda">{{$loop->iteration}}</td>
                                <td class="celda">{{$value['date_of_issue']}}</td>
                                <td class="celda">{{$value['series'] }}-{{ $value['number']}}</td>
                                <td class="celda">{{$value['customer_name']}}</td>
                                <td class="celda">{{$value['total_returned']}}</td> 
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
