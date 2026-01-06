<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Recibo Plan de Suministro</title>
    <style>
        @page {
            margin: 5px;
        }

        html {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-spacing: 0;
            border-collapse: collapse;
        }

        .mp-0 {
            margin: 0;
            padding: 0;
        }

        .celda {
            text-align: center;
            padding: 5px;
            border: 0.1px solid black;
        }

        th {
            padding: 5px;
            text-align: center;
        }

        .border-bottom {
            border-bottom: 1px dashed black;
        }

        .border-top {
            border-top: 1px dashed black;
        }

        .title {
            font-weight: bold;
            font-size: 13px !important;
            text-decoration: underline;
        }

        p > strong {
            margin-left: 5px;
            font-size: 12px;
        }

        thead {
            font-weight: bold;
            text-align: center;
        }

        .td-custom {
            line-height: 0.1em;
        }

        .width-custom {
            width: 50%
        }

        .font-bold {
            font-weight: bold;
        }

        .desc-9 {
            font-size: 9px;
        }

        .desc {
            font-size: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .mb {
            margin-bottom: 0.3rem;
        }

        .mt {
            margin-top: 0.3rem;
        }

        .mb-2 {
            margin-bottom: 0.2rem;
        }

        .mt-1 {
            margin-top: 0.2rem;
        }

        table, tr, td, th {
            padding: 0px;
            margin: 0px;
        }

        .status-badge {
            background-color: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9px;
        }

        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }

        .status-generated {
            background-color: #007bff;
        }

        .status-sent {
            background-color: #6f42c1;
        }

        .status-paid {
            background-color: #28a745;
        }

        .status-cancelled {
            background-color: #dc3545;
        }
    </style>
</head>

<body>
    <div style="margin-top:-15px">
        <p align="center" class="title"><strong>{{ strtoupper($company->name) }}</strong></p>
        <p align="center" class="mp-0 title"><strong>{{ $establishment->address }}</strong></p>
        @if (isset($establishment->district->description) && $establishment->district->description != '')
            <p align="center" class="mp-0 title"><strong>{{ $establishment->district->description }}</strong></p>
        @endif
        @if (isset($establishment->province->description) && isset($establishment->department->description))
            <p align="center" class="mp-0 title"><strong>{{ $establishment->province->description }} -
                    {{ $establishment->department->description }}</strong></p>
        @endif

        <p class="desc">
            <strong>RUC:</strong> {{ $company->number }}
            <strong>Cel:</strong> {{ $establishment->telephone ?? $company->telephone }}
        </p>

        <p align="center" class="mp-0 desc"><strong>RECIBO - PLAN DE SUMINISTRO</strong></p>
        <p align="center" class="mp-0 desc">{{ $data['planDocument']->period }}</p>
        @if($data['contract'])
            <p align="center" class="mp-0 desc">Contrato: {{ $data['contract'] }}</p>
        @endif
    </div>

    <div>
        <table class="mb mt">
            <tr>
                <td width="40%" class="desc font-bold">Fecha Emisión:</td>
                <td width="60%" class="desc">{{ $data['planDocument']->generation_date ? $data['planDocument']->generation_date->format('d/m/Y') : $data['date'] }}</td>
            </tr>
            @if($data['planDocument']->due_date)
            <tr>
                <td width="40%" class="desc font-bold">Fecha Vencimiento:</td>
                <td width="60%" class="desc">{{ $data['planDocument']->due_date->format('d/m/Y') }}</td>
            </tr>
            @endif
            <tr>
                <td width="40%" class="desc font-bold">Estado:</td>
                <td width="60%" class="desc">
                    <span class="status-badge status-{{ $data['planDocument']->status }}">
                        {{ $data['planDocument']->status_label }}
                    </span>
                </td>
            </tr>
            @if($data['planDocument']->full_document_number)
            <tr>
                <td width="40%" class="desc font-bold">Documento:</td>
                <td width="60%" class="desc">{{ $data['planDocument']->full_document_number }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div>
        <p class="desc font-bold border-bottom mb-2" style="padding-bottom: 2px; margin-top: 8px;">INFORMACIÓN DEL SUMINISTRO</p>
        <table class="mb-2 mt-1">
            <tr>
                <td width="30%" class="desc font-bold">Código:</td>
                <td width="70%" class="desc">{{ $data['supply']->code }}</td>
            </tr>
            <tr>
                <td width="30%" class="desc font-bold">Descripción:</td>
                <td width="70%" class="desc">{{ $data['supply']->description }}</td>
            </tr>
            <tr>
                <td width="30%" class="desc font-bold">Cliente:</td>
                <td width="70%" class="desc">{{ $data['person']->name ?? 'No asignado' }}</td>
            </tr>
            @if($data['person'] && $data['person']->number)
            <tr>
                <td width="30%" class="desc font-bold">Documento:</td>
                <td width="70%" class="desc">{{ $data['person']->number }}</td>
            </tr>
            @endif
            <tr>
                <td width="30%" class="desc font-bold">Zona:</td>
                <td width="70%" class="desc">{{ $data['zone']->name ?? 'No asignada' }} 
                    @if($data['zone']->code)({{ $data['zone']->code }})@endif
                </td>
            </tr>
            <tr>
                <td width="30%" class="desc font-bold">Sector:</td>
                <td width="70%" class="desc">{{ $data['sector']->name ?? 'No asignado' }} 
                    @if($data['sector']->code)({{ $data['sector']->code }})@endif
                </td>
            </tr>
        </table>
    </div>

    <div>
        <p class="desc font-bold border-bottom mb-2" style="padding-bottom: 2px; margin-top: 8px;">PLAN DE SUMINISTRO</p>
        <table class="mb-2 mt-1">
            <tr>
                <td width="30%" class="desc font-bold">Plan:</td>
                <td width="70%" class="desc">{{ $data['plan']->description }}</td>
            </tr>
            <tr>
                <td width="30%" class="desc font-bold">Estado Plan:</td>
                <td width="70%" class="desc">{{ $data['plan']->active ? 'Activo' : 'Inactivo' }}</td>
            </tr>
        </table>
    </div>

    <table class="border-top border-bottom" style="margin-top: 8px; margin-bottom: 5px;">
        <thead>
            <tr>
                <th class="desc text-center" width="60%" style="padding: 5px;">CONCEPTO</th>
                <th class="desc text-center" width="40%" style="padding: 5px;">IMPORTE S/</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="desc" style="padding: 3px;">
                    {{ $data['plan']->description }} - {{ $data['planDocument']->period }}
                    @if($data['supply']->description)
                        <br><small>{{ $data['supply']->description }}</small>
                    @endif
                </td>
                <td class="desc text-right" style="padding: 3px;">
                    {{ number_format($data['planDocument']->amount ?: $data['plan']->total, 2) }}
                </td>
            </tr>
            <tr>
                <td class="text-center desc" colspan="2" style="padding: 3px;">
                    -------------------------------------------------------------
                </td>
            </tr>
            <tr>
                <td class="desc font-bold text-right" style="padding: 3px;">TOTAL S/</td>
                <td class="desc font-bold text-right" style="padding: 3px;">
                    {{ number_format($data['planDocument']->amount ?: $data['plan']->total, 2) }}
                </td>
            </tr>
            <tr>
                <td class="text-center desc" colspan="2" style="padding: 3px;">
                    -------------------------------------------------------------
                </td>
            </tr>
        </tbody>
    </table>

    @if($data['planDocument']->observations)
    <div style="margin-top: 8px;">
        <p class="desc font-bold mb-2">Observaciones:</p>
        <p class="desc">{{ $data['planDocument']->observations }}</p>
    </div>
    @endif

    <table width="100%" style="margin-top: 10px;">
        <tr>
            <td class="desc font-bold">Generado por:</td>
            <td class="desc">{{ $data['planDocument']->user->name ?? 'Sistema' }}</td>
        </tr>
        <tr>
            <td class="desc font-bold">Fecha/Hora:</td>
            <td class="desc">{{ $data['date'] }} {{ $data['time'] }}</td>
        </tr>
    </table>

    <table width="100%" style="margin-top: 15px;">
        <thead>
            <tr>
                <th class="border-top text-center desc-9" width="50%" style="padding-top: 8px;">FIRMA AUTORIZADA</th>
                <th class="border-top text-center desc-9" width="50%" style="padding-top: 8px;">RECIBÍ CONFORME</th>
            </tr>
        </thead>
    </table>

    <p align="center" class="desc" style="margin-top: 10px;">
        <small>Este documento es un comprobante del plan de suministro registrado</small>
    </p>
</body>
</html>