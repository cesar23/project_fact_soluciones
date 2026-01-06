<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Gastos diversos</title>
    </head>
    <style>
        @page {
            size: auto;
            margin: 10px;
            margin-top: 45px;
        }
        body{
            font-family: Arial, sans-serif;

        }
        .title {
            font-size: 18px;
            font-weight: bold;
        }
        table{
            border-collapse: collapse;
            width: 100%;
        }   
        td{
            border: 1px solid #000;
            padding: 5px;
            font-size: 12px;
        }
        th{
            border: 1px solid #000;
            padding: 5px;
            font-size: 12px;
        }
        .celda{
            border: 1px solid #000;
            padding: 5px;
            font-size: 12px;
        }
        h3{
            margin: 0px;
        }
        .text-end{
            text-align: right;
        }
        .text-center{
            text-align: center;
        }
        .text-left{
            text-align: left;
        }
    </style>

    <body>
        <div>
            <h3 align="center" class="title"><strong>Gastos diversos</strong></h3>
        </div>
        <br>
        <div style="margin-top:5px; margin-bottom:5px;">
            <strong>Establecimiento: </strong> {{$establishment->address}} - {{$establishment->department->description}} - {{$establishment->district->description}}
        </div>
        <br>
        @if(!empty($records))
            <div class="">
                <div class=" ">
                    <table class="">
                        <thead>
                            <tr>
                                <th class="">#</th>
                                <th class="">Fecha de emisión</th>
                                <th class="">Proveedor</th>
                                <th class="">N° Doc. identidad</th>
                                <th class="">Tipo documento</th>
                                <th class="">Número</th>
                                <th class="">Motivo</th>
                                <th class="">Moneda</th>
                                <th class="">Total</th>
                                <th class="">Saldo</th>
                                <th class="">Estado</th>
                                <th class="">Sede</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($records as $record)
                            
                            <tr>
                                <td class="celda">
                                    {{$loop->iteration}}
                                </td>
                                <td class="celda">
                                    {{$record->date_of_issue->format('d/m/Y')}}
                                </td>
                                <td class="celda">
                                    {{$record->supplier->name}}
                                </td>
                                <td class="celda">
                                    {{$record->supplier->number}}
                                </td>
                                <td class="celda">
                                    {{$record->expense_type->description}}
                                </td>
                                <td class="celda">
                                    {{$record->number}}
                                </td>
                                <td class="celda">
                                    {{$record->expense_reason->description}}
                                </td>
                                
                                <td class="celda">
                                    {{$record->currency_type_id}}
                                </td>
                                <td class="text-end">
                                    {{$record->total}}
                                </td>
                                <td class="text-end">
                                    {{$record->total - $record->payments->sum('payment')}}
                                </td>
                                <td class="celda">
                                    {{ $record->state_type['description'] }}
                                </td>
                                <td class="celda">
                                    {{$record->sede}}
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
        <script type="text/php">
            if (isset($pdf)) {
                $date = date('d/m/Y');
                $time = date('H:i:s');
                $pdf->page_text($pdf->get_width() - 75, 10, "Página {PAGE_NUM}", null, 7);
                $pdf->page_text($pdf->get_width() - 75, 16, "Fecha: $date", null, 7);
                $pdf->page_text($pdf->get_width() - 75, 22, "Hora: $time", null, 7);    
                $pdf->page_text($pdf->get_width() - 75, $pdf->get_height() - 20, "Página N°{PAGE_NUM} de {PAGE_COUNT}", null, 7);
                
            }
        </script>
    </body>
</html>
