<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Recibo Predio - {{ $data['supply']->code }}</title>
    <style>
        @page {
            margin: 3mm;
            size: A5 portrait;
        }

        html {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.2;
        }

        body {
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0px;
            table-layout: fixed;
        }

        .recibo-container {
            border: 2px solid #000;
            margin-bottom: 2mm;
            padding: 3mm;
            page-break-inside: avoid;
        }

        .header-table {
            width: 100%;
            /* margin-bottom: 4mm; */
        }

        .recibo-cliente-cell {
            width: 40%;
            vertical-align: top;
            font-size: 10px;
        }

        .logo-cell {
            width: 10%;
            text-align: right;
            vertical-align: middle;
        }

        .logo {
            width: 55px;
            height: 60px;
            max-width: 55px;
            max-height: 60px;
        }

        .company-cell {
            width: 40%;
            vertical-align: top;
            text-align: center;
            padding-left: 5px;
        }

        .company-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .company-details {
            font-size: 9px;
            line-height: 1.1;
        }

        .recibo-number {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .facturacion {
            background-color: #f0f0f0;
            padding: 5px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #ccc;
        }

        .info-box {
            border: 1px solid #333;
            padding: 5px;
            font-size: 9px;
            vertical-align: top;
        }

        .info-box-title {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .tabla-importes {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #333;
            font-size: 10px;
            table-layout: fixed;
        }

        .tabla-importes th {
            background-color: #2f5da3;
            color: white;
            padding: 5px;
            text-align: left;
            font-weight: bold;
        }

        .tabla-importes td {
            padding: 5px;
            text-align: left;
            font-size: 9px;
        }

        .tabla-importes td.center {
            text-align: center;
        }

        .tabla-importes td.right {
            text-align: right;
        }

        .tabla-fechas {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .tabla-fechas td.info-box {
            border: 1px solid #333;
            vertical-align: top;
        }

        .total-section {
            background-color: #f9f9f9;
            font-size: 12px;
            border: 1px solid #ccc;
        }

        .total-amount {
            font-weight: bold;
            font-size: 14px;
        }

        .son-section {
            border: 1px solid #333;
            padding: 5px;
            font-size: 11px;
            font-weight: bold;
            box-sizing: border-box;
        }

        .footer-messages-table {
            margin-top: 4px;
            border: 1px solid #333;
            width: 100%;
            margin-bottom: 4px;
        }

        .message-left {
            border: 1px solid #333;
            width: 48%;
            padding: 10px;
            font-size: 10px;
            font-style: italic;
            text-align: center;
            vertical-align: top;
        }

        .message-left div {
            font-size: 10px;
            color: gray;
            font-weight: bold;
        }


        .message-right {
            border: 1px solid #333;
            width: 48%;
            padding: 2px;
            font-size: 10px;
            color: #d32f2f;
            font-weight: bold;
            text-align: center;
            vertical-align: top;
        }

        .message-right div {
            font-size: 10px;
            font-weight: bold;
        }
        .bold {
            font-weight: bold;
        }

        .emoji {
            width: 50px;
            height: 50px;
            display: block;
            margin: 0 auto;
            text-align: center;
        }

        .nota-cancelado {
            font-size: 10px;
            font-style: italic;
            margin-top: 5px;
            text-align: center;
        }

        .resumen-container {
            border: 2px solid #000;
            padding: 8px;
            margin-bottom: 5mm;
            page-break-inside: avoid;
        }

        .resumen-total {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            padding: 4px;
            border: 2px solid #000;
            margin-top: 4px;
        }

        .bold {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .mb-2 {
            margin-bottom: 5px;
        }

        .mt-2 {
            margin-top: 5px;
        }

        .vencido {
            color: #d32f2f;
            font-weight: bold;
        }

        .line {
            width: 120px;
            background-color: #333;
        }
    </style>
</head>

<body>
    <!-- Recibo Principal (Copia Cliente) -->
    <div class="recibo-container">
        <!-- Header con 3 columnas: Recibo/Cliente - Logo - Empresa -->
        <table class="header-table">
            <tr>
                <td class="recibo-cliente-cell">
                    <div class="recibo-number">
                        RECIBO N°:
                        {{ $data['planDocument']->document_series ?? '' }}-{{ $data['planDocument']->document_number ?? '00000' }}
                    </div>
                    <div>CÓDIGO: {{ $data['supply']->old_code }} | {{ $data['person']->number ?? 'Sin documento' }}</div>
                    <div>{{ strtoupper($data['person']->name ?? 'Cliente') }}</div>
                    <div>{{ strtoupper($data['supply']->optional_address ?? ($data['zone']->name ?? '')) }}</div>
                    <div>
                        FACTURACIÓN: {{ strtoupper($data['planDocument']->period) }}
                    </div>
                </td>
                <td class="logo-cell">
                    <img src="{{ resource_path('views/tenant/supplies/documents/logo.png') }}" class="logo"
                        alt="Logo">
                </td>
                <td class="company-cell">
                    <div class="company-name">{{ strtoupper($company->name) }}</div>
                    <div class="company-details">
                        {{ $establishment->address }}<br>
                        @if (isset($establishment->district->description))
                            {{ $establishment->district->description }} -
                        @endif
                        @if (isset($establishment->province->description))
                            {{ $establishment->province->description }} -
                        @endif
                        @if (isset($establishment->department->description))
                            {{ $establishment->department->description }}
                        @endif
                        <br>
                        PARTIDA REGISTRAL N°: 11001106<br>
                        R.U.C.: {{ $company->number }}
                    </div>
                </td>
            </tr>
        </table>


        <!-- Contenedor principal con dos tablas: 75% importes + 25% fechas -->
        <table width="100%" style="margin-bottom: 0px; border-collapse: collapse;">
            <tr>
                <td width="75%" style="vertical-align: top; padding: 0; ">
                    <!-- Tabla de importes (75%) con altura del 60% -->
                    <table class="tabla-importes" style="margin: 0; height: 42%;">
                        <thead>
                            <tr>
                                <th width="50%">IMPORTES FACTURADO</th>
                                <th width="25%">MES</th>
                                <th width="25%">DEUDA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>AGUA POTABLE Y ALCANTARILLADO</td>
                                <td class="center">{{ strtoupper($data['planDocument']->period) }}</td>
                                <td>
                                    {{ number_format($data['planDocument']->amount ?: $data['plan']->total, 2) }}</td>
                            </tr>
                            @php
                                $totalRows = 14;
                                $contentRows = 2; // Solo tenemos 1 fila con contenido
                                $emptyRows = $totalRows - $contentRows;
                            @endphp
                            @for ($i = 0; $i < $emptyRows; $i++)
                                <tr>
                                    <td>&nbsp;</td>
                                    <td class="center">&nbsp;</td>
                                    <td class="right">&nbsp;</td>
                                </tr>
                            @endfor
                        <tfoot>
                            <tr>
                                <td class="left bold">
                                    TOTAL:
                                </td>
                                <td></td>
                                <td class="left bold">
                                    S/ {{ number_format($data['planDocument']->amount ?: $data['plan']->total, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                        </tbody>
                    </table>
                    @php
                        $amount = $data['planDocument']->amount ?: $data['plan']->total;
                        $integerPart = floor($amount);
                        $decimalPart = round(($amount - $integerPart) * 100);

                        // Convertir número a palabras de forma simplificada
                        if ($integerPart == 0) {
                            $amountInWords = 'CERO CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 1) {
                            $amountInWords = 'UNO CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 2) {
                            $amountInWords = 'DOS CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 3) {
                            $amountInWords = 'TRES CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 4) {
                            $amountInWords = 'CUATRO CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 5) {
                            $amountInWords = 'CINCO CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 6) {
                            $amountInWords = 'SEIS CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 7) {
                            $amountInWords = 'SIETE CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 8) {
                            $amountInWords = 'OCHO CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 9) {
                            $amountInWords = 'NUEVE CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 10) {
                            $amountInWords = 'DIEZ CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 11) {
                            $amountInWords = 'ONCE CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 12) {
                            $amountInWords = 'DOCE CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 13) {
                            $amountInWords = 'TRECE CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 14) {
                            $amountInWords =
                                'CATORCE CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 15) {
                            $amountInWords = 'QUINCE CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 20) {
                            $amountInWords = 'VEINTE CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 30) {
                            $amountInWords =
                                'TREINTA CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 50) {
                            $amountInWords =
                                'CINCUENTA CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } elseif ($integerPart == 100) {
                            $amountInWords = 'CIEN CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
                        } else {
                            $amountInWords =
                                strtoupper(number_format($integerPart, 0)) .
                                ' CON ' .
                                str_pad($decimalPart, 2, '0', STR_PAD_LEFT) .
                                '/100 SOLES';
                        }
                    @endphp
                    <!-- SON alineado exactamente con la tabla de arriba -->
                    <div class="son-section" style="margin: 0;">
                        SON: {{ $amountInWords }}
                    </div>
                </td>
                <td width="25%" style="vertical-align: top;">
                    <!-- Tabla de fechas (25%) -->
                    <table class="tabla-fechas">
                        <tr>
                            <td class="info-box">
                                <div class="info-box-title">FECHA DE EMISIÓN:</div>
                                <div>
                                    {{ $data['planDocument']->generation_date ? $data['planDocument']->generation_date->format('d-m-Y') : $data['date'] }}
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-box">
                                <div class="info-box-title">CATEGORÍA AGUA:</div>
                                <div>{{ strtoupper($data['plan']->description) }}</div>
                                <div class="info-box-title mt-2">CONSUMO AGUA: DE</div>
                                <div>{{ strtoupper($data['planDocument']->period) }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-box">
                                <div class="info-box-title">FECHA VENCIMIENTO:</div>
                                <div class="vencido">
                                    {{ $data['planDocument']->due_date ? $data['planDocument']->due_date->format('d-m-Y') : 'N/A' }}
                                </div>


                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="margin-top: 10px;"></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <div style="text-align: center; display: flex; justify-content: center; align-items: center;">
                                    @php
                                        $isOverdue =
                                            $data['planDocument']->due_date &&
                                            $data['planDocument']->due_date->isPast();
                                    @endphp
                                    @if ($isOverdue)
                                        <img src="{{ resource_path('views/tenant/supplies/documents/sad.png') }}"
                                            class="emoji" alt="Emoji">
                                    @else
                                        <img src="{{ resource_path('views/tenant/supplies/documents/happy.png') }}"
                                            class="emoji" alt="Emoji">
                                    @endif
                                </div>
                                <div class="nota-cancelado">
                                    En caso de haber cancelado la deuda, sírvase omitir este recibo.
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Son -->

        <!-- Contenedor para que SON tenga el mismo ancho que la tabla 75% -->


        <!-- Messages footer usando tabla -->
        <table class="footer-messages-table">
            <tr>
                <td class="message-left">
                    <div style="text-align: center;">
                        "Sin agua no hay vida, Sin vida no hay nada".<br>
                        ¡PROTÉGELA!<br><br>
                        "Tomar agua nos da vida, pero tomar conciencia nos dará agua"
                    </div>
                </td>
                <td class="message-right">
                    "Si sufres cualquier forma de violencia familiar, no la justifiques. Da el primer paso y busca
                    apoyo."
                    <img src="{{ resource_path('views/tenant/supplies/documents/linea.png') }}" class="line"
                        alt="Logo">
                </td>
            </tr>
        </table>
    </div>

    <!-- Segunda copia - Resumen -->
    <div class="resumen-container">
        <table class="header-table">
            <tr>
                <td class="recibo-cliente-cell">
                    <div class="recibo-number">
                        RECIBO N°:
                        {{ $data['planDocument']->document_series ?? '' }}-{{ $data['planDocument']->document_number ?? '00000' }}
                    </div>
                    <div>CÓDIGO: {{ $data['supply']->code }} | {{ $data['person']->number ?? 'Sin documento' }}</div>
                    <div><strong>CLIENTE:</strong> {{ strtoupper($data['person']->name ?? 'Cliente') }}</div>
                    <div><strong>DIRECCIÓN:</strong>
                        {{ strtoupper($data['supply']->optional_address ?? ($data['zone']->name ?? '')) }}</div>
                    <div><strong>FACTURACIÓN:</strong> {{ strtoupper($data['planDocument']->period) }}</div>
                    <div><strong>CONSUMO:</strong> DE {{ strtoupper($data['planDocument']->period) }}</div>
                </td>
                <td class="logo-cell">
                    <img src="{{ resource_path('views/tenant/supplies/documents/logo.png') }}" class="logo"
                        alt="Logo">
                </td>
                <td class="company-cell">
                    <div class="company-name">{{ strtoupper($company->name) }}</div>
                    <div class="company-details">
                        {{ $establishment->address }}<br>
                        @if (isset($establishment->district->description))
                            {{ $establishment->district->description }} -
                        @endif
                        @if (isset($establishment->province->description))
                            {{ $establishment->province->description }} -
                        @endif
                        @if (isset($establishment->department->description))
                            {{ $establishment->department->description }}
                        @endif
                        <br>
                        PARTIDA REGISTRAL N° 11001106<br>
                        R.U.C. {{ $company->number }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Información de fecha vencimiento para el resumen -->
        <table width="100%">
            <tr>
                <td width="100%">
                    <div
                        style="text-align: center; width: 40%; border: 1px solid #333; padding: 5px; margin-left: auto;">
                        <div class="info-box-title">FECHA VENCIMIENTO:</div>
                        <div class="vencido">
                            {{ $data['planDocument']->due_date ? $data['planDocument']->due_date->format('d-m-Y') : 'N/A' }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Total a pagar destacado -->
        <div class="resumen-total">
            TOTAL A PAGAR S/: {{ number_format($data['planDocument']->amount ?: $data['plan']->total, 2) }}
        </div>
    </div>


</body>

</html>
