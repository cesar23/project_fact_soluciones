<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Balance</title>
    </head>
    <body>
        <div>
            <h3 align="center" class="title"><strong>Detalle de balance</strong></h3>
        </div>
        <br>
        <div style="margin-top:20px; margin-bottom:15px;">
            <table>
                <tr>
                    <td>
                        <p><b>Empresa: </b></p>
                    </td>
                    <td align="center">
                        <p><strong>{{$company->name}}</strong></p>
                    </td>
                    <td>
                        <p><strong>Fecha: </strong></p>
                    </td>
                    <td align="center">
                        <p><strong>{{date('Y-m-d')}}</strong></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p><strong>Ruc: </strong></p>
                    </td>
                    <td align="center">{{$company->number}}</td>
                    <td>
                        <p><strong>Establecimiento: </strong></p>
                    </td>
                    <td align="center">{{$establishment->address}} - {{$establishment->department->description}} - {{$establishment->district->description}}</td>
                </tr>
            </table>
        </div>
        <br>
        @php
            $balance = 0;
        @endphp
        @if(!empty($records))
            <div class="">
                <div class=" ">
                    <table class="">
                        <thead>
                            <tr>
                                <th class="">#</th>
                                <th class="">FECHA</th>
                                <th class="">DOCUMENTO</th>
                                <th class="">REFERENCIA</th>
                                <th class="">GLOSA</th>
                                <th class="">ENTRADA</th>
                                <th class="">SALIDA</th>
                                <th class="">SALDO</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($records as $key => $value)
                            <?php
                                $date = $value['date'];
                                $reference = $value['reference'];
                                $full_number = $value['full_number'];
                                $glosa = $value['glosa'];
                                $payment = $value['payment'];
                                $output = $value['output'];
                                $input = $value['input'];

                                $balance += $input - $output;
                            ?>
                            <tr>
                                <td class="celda">
                                    {{$key + 1}}
                                </td>
                                <td class="celda">
                                    {{$date}}
                                </td>
                                <td class="celda">
                                    {{$full_number}}
                                </td>
                                <td class="celda">
                                    {{$reference}}
                                </td>
                                <td class="celda">
                                    {{$glosa}}
                                </td>
                                <td class="celda" style="text-align: right;">
                                    &nbsp;{{ number_format((float)$input, 2, '.', ',') }}
                                </td>
                                <td class="celda" style="text-align: right;">
                                    &nbsp;{{ number_format((float)$output, 2, '.', ',') }}
                                </td>
                                <td class="celda" style="text-align: right;">
                                    &nbsp;{{ number_format((float)$balance, 2, '.', ',') }}
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
