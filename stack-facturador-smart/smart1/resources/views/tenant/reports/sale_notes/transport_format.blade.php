<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Compras</title>
    </head>
    <body>
        <div>
            <h3 align="center" class="title"><strong>Formato de Transporte</strong></h3>
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
                  
                </tr>
                @if($seller)
                <tr>
                    <td>
                        <p><strong>Vendedor: </strong></p>
                    </td>
                    <td align="center">{{$seller->name}}</td>
                </tr>
                @endif
            </table>
        </div>
        <br>
        @if(!empty($records))
            <div class="">
                <div class="">
                    <table class="table table-bordered" style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                        <thead>
                            <tr style="background-color: #f2f2f2; border-bottom: 2px solid #ddd;">
                                <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">#</th>
                                <th class="text-center" style="padding: 8px; text-align: center; border: 1px solid #ddd;">Producto</th>
                                <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $key => $value)
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px; border: 1px solid #ddd;">{{$loop->iteration}}</td>
                                <td style="padding: 8px; border: 1px solid #ddd;">{{$value['item_description']}}</td>
                                <td style="padding: 8px; border: 1px solid #ddd;">{{$value['total_quantity']}}</td>
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
