<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Clientes</title>
    </head>
    <body>
        <div>
            <h3 align="center" class="title"><strong>{{ ($type == 'customers') ? 'Reporte Clientes':'Reporte Proveedores' }}</strong></h3>

        </div>
        <br>
        @if(!empty($records))
            <div class="">
                <div class=" ">
                    <table class="">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tipo de documento</th>
                                <th>Número de documento</th>
                                <th>Nombre</th>
                                <th>Nombre comercial</th>
                                <th>Código de pais</th>
                                <th>Ubigeo</th>
                                <th>Dirección</th>
                                <th>Departamento</th>
                                <th>Povincia</th>
                                <th>Distrito</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Tipo de cliente</th>
                                <th>Código interno</th>
                                <th>Zona</th>
                                <th>Website</th>
                                <th>Observacion</th>
                                <th>Vendedor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $key => $value)
                                <?php
                                    /** @var \App\Models\Tenant\Person $value*/

                                    $ubigeo = $value->district_id;
                                    $department = $value->department->description ?? '';
                                    $province = $value->province->description ?? '';
                                    $district = $value->district->description ?? '';
                                    $zone = $value->getZone();
                                    if($zone){
                                        $zone = $zone->name ;

                                    }else{
                                        $zone = '';
                                    }
                                    $seller = $value->seller ?$value->seller->getName():'';
                                    $observation = $value->observation ?:'';
                                    $type = null;
                                    if($value->person_type){
                                        $type = $value->person_type->description;
                                        // $type = 'CLIENTE';
                                    }
                                ?>
                            <tr>
                                <td class="celda">{{$loop->iteration}}</td>
                                <td class="celda">{{$value->identity_document_type->description}}</td>
                                <td class="celda">{{$value->number}}</td>
                                <td class="celda">{{$value->name }}</td>
                                <td class="celda">{{$value->trade_name }}</td>
                                <td class="celda">{{$value->country_id }}</td>
                                <td class="celda">{{$ubigeo }}</td>
                                <td class="celda">{{$value->address }}</td>
                                <td class="celda">{{$department }}</td>
                                <td class="celda">{{$province }}</td>
                                <td class="celda">{{$district }}</td>
                                <td class="celda">{{$value->email }}</td>
                                <td class="celda">{{$value->telephone }}</td>
                                <td class="celda">{{$type }}</td>
                                <td class="celda">{{$value->internal_code }}</td>
                                <td class="celda">{{$zone }}</td>
                                <td class="celda">{{$value->website }}</td>
                                <td class="celda">{{$observation }}</td>
                                <td class="celda">{{$seller }}</td>
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
