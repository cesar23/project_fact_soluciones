<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Registro de Control de Armas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #000;
        }

        .page {
            padding: 15px;
        }

        .header {
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header-top {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }

        .logo {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
        }

        .company-info {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .ruc {
            font-size: 11px;
            color: #666;
        }

        .title {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            margin-top: 8px;
            margin-bottom: 10px;
            text-transform: uppercase;
            border: 1px solid #333;
            padding: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table th {
            font-weight: bold;
            font-size: 7px;
            padding: 5px 2px;
            text-align: center;
            border: 1px solid #000;
            vertical-align: middle;
        }

        table td {
            border: 1px solid #333;
            padding: 4px 2px;
            font-size: 8px;
            vertical-align: middle;
            min-height: 20px;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .col-item {
            width: 3%;
            text-align: center;
        }

        .col-fecha {
            width: 7%;
            text-align: center;
        }

        .col-hora {
            width: 6%;
            text-align: center;
        }

        .col-marca {
            width: 8%;
        }

        .col-serie {
            width: 10%;
        }

        .col-tarjeta {
            width: 8%;
        }

        .col-nombres {
            width: 18%;
        }

        .col-destino {
            width: 15%;
        }

        .col-firma {
            width: 10%;
        }

        .col-observaciones {
            width: 15%;
        }

        .ingreso {
        }

        .egreso {
        }

        .fuera {
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            border-top: 1px solid #333;
            padding-top: 10px;
        }

        .signature-box {
            display: inline-block;
            width: 48%;
            text-align: center;
            margin-top: 40px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 9px;
        }

        .page-break {
            page-break-after: always;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }

        .badge-fuera {
        }

        .badge-dentro {
        }

        .info-box {
            border: 1px solid #ccc;
            padding: 8px;
            margin-bottom: 10px;
            font-size: 8px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="logo">
                    @if($company->logo)
                        <img src="{{ public_path('storage/uploads/logos/'.$company->logo) }}" alt="Logo" style="max-width: 70px; max-height: 60px;">
                    @endif
                </div>
                <div class="company-info">
                    <div class="company-name">{{ $company->name }}</div>
                    <div class="ruc">RUC: {{ $company->number }}</div>
                </div>
            </div>
            <div class="title">
                REGISTRO DE CONTROL DE INGRESO, ASIGNACIÓN Y SALIDA DE ARMAS DE FUEGO DE ARMERÍA
            </div>
        </div>

        <!-- Información del reporte -->
        <div class="info-box">
            <div>
                <span class="info-label">Fecha de reporte:</span> {{ date('d/m/Y') }}
            </div>
            <div>
                <span class="info-label">Período:</span> 
                @if($date_start && $date_end)
                    Del {{ \Carbon\Carbon::parse($date_start)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($date_end)->format('d/m/Y') }}
                @else
                    Todos los registros
                @endif
            </div>
            <div>
                <span class="info-label">Total de registros:</span> {{ count($records) }}
            </div>
        </div>

        <!-- Tabla de registros -->
        <table>
            <thead>
                <tr>
                    <th class="col-item">ÍTEM</th>
                    <th class="col-fecha">FECHA</th>
                    <th class="col-hora">HORA<br>INGRESO/<br>SALIDA</th>
                    <th class="col-marca">MARCA</th>
                    <th class="col-serie">N° SERIE<br>DEL ARMA</th>
                    <th class="col-tarjeta">TARJETA DE<br>PROPIEDAD</th>
                    <th class="col-nombres">NOMBRES Y<br>APELLIDOS</th>
                    <th class="col-destino">DESTINO /<br>LUGAR</th>
                    <th class="col-firma">FIRMA</th>
                    <th class="col-observaciones">OBSERVACIONES</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $counter = 1;
                @endphp
                @forelse($records as $record)
                    <tr class="{{ $record->type === 'ingreso' ? 'ingreso' : 'egreso' }} {{ $record->is_last_record && $record->current_status === 'fuera' ? 'fuera' : '' }}">
                        <td class="col-item text-center">{{ $counter++ }}</td>
                        <td class="col-fecha text-center">{{ \Carbon\Carbon::parse($record->date_of_issue)->format('d/m/Y') }}</td>
                        <td class="col-hora text-center">
                            {{ \Carbon\Carbon::parse($record->time_of_issue)->format('H:i') }}<br>
                            <strong>{{ strtoupper($record->type) }}</strong>
                        
                        </td>
                        <td class="col-marca">
                            {{ $record->item && $record->item->brand ? $record->item->brand->name : '-' }}
                        </td>
                        <td class="col-serie text-center">
                            {{ $record->item_lot ? $record->item_lot->series : 'S/N' }}
                        </td>
                        <td class="col-tarjeta text-center">
                            {{ $record->item && $record->item->internal_id ? $record->item->internal_id : '-' }}
                        </td>
                        <td class="col-nombres">
                            {{ $record->person->name ?? '' }}
                        </td>
                        <td class="col-destino">
                            {{ $record->destiny }}
                        </td>
                        <td class="col-firma">
                            
                        </td>
                        <td class="col-observaciones">
                            {{ $record->observation ?? '' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 20px;">
                            No hay registros para mostrar
                        </td>
                    </tr>
                @endforelse

                @if(count($records) < 15)
                    @for($i = count($records); $i < 15; $i++)
                        <tr>
                            <td class="col-item text-center">{{ $counter++ }}</td>
                            <td class="col-fecha">&nbsp;</td>
                            <td class="col-hora">&nbsp;</td>
                            <td class="col-marca">&nbsp;</td>
                            <td class="col-serie">&nbsp;</td>
                            <td class="col-tarjeta">&nbsp;</td>
                            <td class="col-nombres">&nbsp;</td>
                            <td class="col-destino">&nbsp;</td>
                            <td class="col-firma">&nbsp;</td>
                            <td class="col-observaciones">&nbsp;</td>
                        </tr>
                    @endfor
                @endif
            </tbody>
        </table>

        <!-- Footer con firmas -->
        <div class="footer">
            <div style="width: 100%;">
                <div class="signature-box" style="float: left;">
                    <div class="signature-line">
                        Responsable de control
                    </div>
                </div>
                <div class="signature-box" style="float: right;">
                    <div class="signature-line">
                        Jefatura de Operaciones
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

