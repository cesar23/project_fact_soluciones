@php
    $enabled_sales_agents = App\Models\Tenant\Configuration::getRecordIndividualColumn('enabled_sales_agents');
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Encomienda</title>
</head>

<body>
    <div>
        <h3 align="center" class="title"><strong>Reporte Ticket de encomiendas</strong></h3>
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

                @inject('reportService', 'Modules\Report\Services\ReportService')
                @if (isset($filters['seller_id']) && !empty($filters['seller_id']))
                    <td>
                        <p><strong>Usuario: </strong></p>
                    </td>
                    <td align="center">
                        {{ $reportService->getUserName($filters['seller_id']) }}
                    </td>
                @endif
            </tr>
        </table>
    </div>
    <br>
    @if (!empty($records))
        <div class="">
            <div class=" ">

                @php
                    
                    $acum_total = 0;
                    
                @endphp

                <table class="">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-center">Fecha Emisión</th>
                            <th class="text-center">Hora Emisión</th>
                            <th class="">Usuario/Vendedor</th>
                            <th>Remitente</th>
                            <th>Destinatario</th>
                            <th>Ticket</th>
                            <th class="text-center">Partida</th>
                            <th>Destino</th>
                            <th class="text-center">Vehiculo</th>
                            <th class="text-center">Total</th>


                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $value)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ Carbon\Carbon::parse($value->date_of_issue)->format('Y-m-d') }}</td>
                                <td class="celda">{{ $value->time_of_issue }}</td>
                                <td class="celda">{{ $value->user->name }}</td>
                                <td>{{ $value->sender->name }}</td>
                                <td>{{ $value->issuer->name }}</td>
                                <td>{{ $value->series }}-{{ $value->number }}</td>
                                <td>{{ $value->departure }}</td>
                                <td>{{ $value->arrival }}</td>
                                <td>{{ $value->license_plate }}</td>
                                <td>{{ $value->total }}</td>




                            </tr>

                            @php
                                
                                $acum_total += $value->total;
                                
                            @endphp
                        @endforeach
                        <tr>
                            <td class="celda" colspan="9"></td>
                            <td class="celda">Totales</td>

                            <td class="celda">{{ $acum_total }}</td>
                        </tr>

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
