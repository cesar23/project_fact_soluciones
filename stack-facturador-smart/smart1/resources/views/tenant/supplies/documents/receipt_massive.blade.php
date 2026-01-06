<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Recibos Masivos</title>
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

        .receipt-wrapper {
            page-break-after: always;
        }

        .recibo-container {
            border: 2px solid #000;
            margin-bottom: 2mm;
            padding: 3mm;
            page-break-inside: avoid;
        }

        .header-table {
            width: 100%;
        }

        .recibo-cliente-cell {
            width: 40%;
            vertical-align: top;
            font-size: 10px;
        }

        .client-name {
            font-size: 10px;
            line-height: 1.1;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: pre-line;
            max-width: 100%;
            display: block;
        }

        .client-name.long {
            font-size: 10px;
            line-height: 1.0;
            max-height: 20px;
            overflow: hidden;
        }

        .client-name.very-long {
            font-size: 10px;
            line-height: 0.9;
            max-height: 18px;
            overflow: hidden;
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
            padding: 1px 5px;
            text-align: left;
            font-weight: bold;
            vertical-align: top;
        }

        .tabla-importes td {
            padding: 4px 5px;
            text-align: left;
            font-size: 9px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            vertical-align: top;
            line-height: 1.0;
        }

        .tabla-importes td.center {
            text-align: center;
            vertical-align: top;
        }

        .tabla-importes td.right {
            text-align: right;
            vertical-align: top;
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

        .status-paid {
            color: #28a745;
            font-weight: bold;
        }

        .status-pending {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>

<body>
    @foreach ($receiptsData as $index => $data)
        <!-- Wrapper para forzar salto de página después de ambos bloques (excepto el último) -->
        <div class="receipt-wrapper" style="@if ($index === count($receiptsData) - 1) page-break-after: avoid; @endif">
            <!-- Recibo Principal (Copia Cliente) -->
            <div class="recibo-container">
                <!-- Header con 3 columnas: Recibo/Cliente - Logo - Empresa -->
                <table class="header-table">
                    <tr>
                        <td class="recibo-cliente-cell">
                            <div class="recibo-number">
                                RECIBO N°:
                                {{ $data['debt']->serie_receipt }}-{{ str_pad($data['debt']->correlative_receipt, 6, '0', STR_PAD_LEFT) }}
                            </div>
                            <div>CÓDIGO: {{ $data['supply']->old_code ?? null }} </div>
                            @php
                                $clientName = strtoupper($data['person']->name ?? 'Cliente');
                            @endphp
                            <div class="{{ $data['clientNameClass'] }}">{{ $clientName }}</div>
                            @php
                                $supply_via_type = $data['supplyVia']->supplyTypeVia ?? null;
                                $short = $supply_via_type->short ?? '';
                            @endphp
                            <div>MAZAMARI - {{ $data['sector']->name ?? '' }} - {{ $short }}
                                {{ $data['supplyVia']->name ?? '' }}
                                @if (isset($data['supply']->mz))
                                    - Mz {{ $data['supply']->mz }}
                                @endif
                                @if (isset($data['supply']->lte))
                                    - Lte {{ $data['supply']->lte }}
                                @endif
                        


                            </div>
                            <div>
                                @if ($data['debt']->month && $data['debt']->year)
                                    PERÍODO: {{ $data['monthName'] }} {{ $data['debt']->year }}
                                @else
                                    FECHA:
                                    {{ $data['debt']->generation_date ? $data['debt']->generation_date->format('d/m/Y') : date('d/m/Y') }}
                                @endif
                            </div>
                            <div class="{{ $data['debt']->active ? 'status-paid' : 'status-pending' }}">
                                ESTADO: {{ $data['debt']->active ? 'PAGADO' : 'PENDIENTE' }}
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
                        <td width="75%" style="vertical-align: top; padding: 0;">
                            <!-- Tabla de importes (75%) -->
                            <table class="tabla-importes" style="margin: 0;">
                                <thead>
                                    <tr>
                                        <th width="50%">CONCEPTOS</th>
                                        <th width="25%">PERÍODO</th>
                                        <th width="25%">MONTO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Deudas anteriores agrupadas --}}
                                    @if (isset($data['previousDebtsGrouped']) && count($data['previousDebtsGrouped']) > 0)
                                        @foreach ($data['previousDebtsGrouped'] as $group)
                                            <tr>
                                                <td valign="top">
                                                    {{ $group['description'] }}:
                                                    @if ($group['isSingleDebt'])
                                                        {{ $group['firstMonth'] }}-{{ $group['firstYear'] }}
                                                    @else
                                                        {{ $group['firstMonth'] }}-{{ $group['firstYear'] }} al
                                                        {{ $group['lastMonth'] }}-{{ $group['lastYear'] }}
                                                    @endif
                                                </td>
                                                <td class="center" valign="top">
                                                    {{ $group['count'] }} {{ $group['count'] == 1 ? 'mes' : 'meses' }}
                                                </td>
                                                <td class="right" valign="top">
                                                    {{ number_format($group['totalAmount'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @endif

                                    {{-- Deuda actual --}}
                                    <tr style="background-color: #f0f8ff;">
                                        <td><strong>{{ $data['description'] }}</strong></td>
                                        <td class="center">
                                            @if ($data['debt']->month && $data['debt']->year)
                                                {{ $data['monthName'] }} {{ $data['debt']->year }}
                                            @else
                                                {{ $data['debt']->generation_date ? $data['debt']->generation_date->format('m/Y') : date('m/Y') }}
                                            @endif
                                        </td>
                                        <td class="right">
                                            <strong>{{ number_format($data['debt']->amount, 2) }}</strong></td>
                                    </tr>

                                    @php
                                        $contentRows =
                                            1 +
                                            (isset($data['previousDebtsGrouped'])
                                                ? count($data['previousDebtsGrouped'])
                                                : 0);
                                        // Cálculo dinámico: reducir drásticamente filas vacías con más contenido
                                        if ($contentRows <= 2) {
                                            $emptyRows = 4;
                                        } elseif ($contentRows <= 4) {
                                            $emptyRows = 2;
                                        } elseif ($contentRows <= 6) {
                                            $emptyRows = 1;
                                        } else {
                                            $emptyRows = 0; // Mínimo 1 fila para mantener formato
                                        }
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
                                        <td class="left bold">TOTAL:</td>
                                        <td></td>
                                        <td class="right bold">S/
                                            {{ number_format($data['totalAmount'] ?? $data['debt']->amount, 2) }}</td>
                                    </tr>
                                </tfoot>
                                </tbody>
                            </table>

                            @php
                                $amount = $data['totalAmount'] ?? $data['debt']->amount;
                                $integerPart = floor($amount);
                                $decimalPart = round(($amount - $integerPart) * 100);

                                // Función simplificada para convertir números a palabras
                                $numbers = [
                                    0 => 'CERO',
                                    1 => 'UNO',
                                    2 => 'DOS',
                                    3 => 'TRES',
                                    4 => 'CUATRO',
                                    5 => 'CINCO',
                                    6 => 'SEIS',
                                    7 => 'SIETE',
                                    8 => 'OCHO',
                                    9 => 'NUEVE',
                                    10 => 'DIEZ',
                                    11 => 'ONCE',
                                    12 => 'DOCE',
                                    13 => 'TRECE',
                                    14 => 'CATORCE',
                                    15 => 'QUINCE',
                                    20 => 'VEINTE',
                                    30 => 'TREINTA',
                                    50 => 'CINCUENTA',
                                    100 => 'CIEN',
                                ];

                                $amountInWords = isset($numbers[$integerPart])
                                    ? $numbers[$integerPart]
                                    : strtoupper(number_format($integerPart, 0));

                                $amountInWords .= ' CON ' . str_pad($decimalPart, 2, '0', STR_PAD_LEFT) . '/100 SOLES';
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
                                            {{ $data['debt']->generation_date ? $data['debt']->generation_date->format('d-m-Y') : $data['date'] }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="info-box">
                                        <div class="info-box-title">TIPO DE DEUDA:</div>
                                        <div>{{ $data['debtType'] }}</div>

                                    </td>
                                </tr>
                                <tr>
                                    <td class="info-box">
                                        <div class="info-box-title">FECHA VENCIMIENTO:</div>
                                        <div class="vencido">
                                            {{ $data['debt']->due_date ? $data['debt']->due_date->format('d-m-Y') : 'N/A' }}
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
                                        <div
                                            style="text-align: center; display: flex; justify-content: center; align-items: center;">
                                            @php
                                                $isOverdue =
                                                    $data['debt']->due_date &&
                                                    $data['debt']->due_date->isPast() &&
                                                    !$data['debt']->active;
                                                $isPaid = $data['debt']->active;
                                            @endphp
                                            @if ($isPaid)
                                                <img src="{{ resource_path('views/tenant/supplies/documents/happy.png') }}"
                                                    class="emoji" alt="Pagado">
                                            @elseif ($isOverdue)
                                                <img src="{{ resource_path('views/tenant/supplies/documents/sad.png') }}"
                                                    class="emoji" alt="Vencido">
                                            @else
                                                <img src="{{ resource_path('views/tenant/supplies/documents/happy.png') }}"
                                                    class="emoji" alt="Al día">
                                            @endif
                                        </div>
                                        <div class="nota-cancelado">
                                            @if ($data['debt']->active)
                                                DEUDA PAGADA - RECIBO INFORMATIVO
                                            @else
                                                En caso de haber cancelado la deuda, sírvase omitir este recibo.
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Messages footer -->
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
                            "Si sufres cualquier forma de violencia familiar, no la justifiques. Da el primer paso y
                            busca apoyo."
                            <img src="{{ resource_path('views/tenant/supplies/documents/linea.png') }}" class="line"
                                alt="Línea">
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
                                {{ $data['debt']->serie_receipt }}-{{ str_pad($data['debt']->correlative_receipt, 6, '0', STR_PAD_LEFT) }}
                            </div>
                            <div>CÓDIGO: {{ $data['supply']->old_code ?? null }} </div>
                            @php
                                $clientNameResumen = strtoupper($data['person']->name ?? 'Cliente');
                            @endphp
                            <div><strong>CLIENTE:</strong> <span
                                    class="{{ $data['clientNameClass'] }}">{{ $clientNameResumen }}</span></div>
                            <div><strong>DIRECCIÓN:</strong>
                                {{ strtoupper($data['supply']->optional_address ?? ($data['sector']->name ?? '')) }}
                            </div>
                            <div><strong>CONCEPTO:</strong> {{ $data['description'] }}</div>
                            @if ($data['debt']->month && $data['debt']->year)
                                <div><strong>PERÍODO:</strong> {{ $data['monthName'] }} {{ $data['debt']->year }}
                                </div>
                            @endif
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
                                style="text-align: center; width: 40%; border: 1px solid #333; padding: 3px; margin-left: auto;">
                                <div class="info-box-title">FECHA VENCIMIENTO:</div>
                                <div class="vencido">
                                    {{ $data['debt']->due_date ? $data['debt']->due_date->format('d-m-Y') : 'N/A' }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Total a pagar destacado -->
                <div class="resumen-total">
                    @if ($data['debt']->active)
                        PAGADO S/: {{ number_format($data['totalAmount'] ?? $data['debt']->amount, 2) }}
                    @else
                        TOTAL A PAGAR S/: {{ number_format($data['totalAmount'] ?? $data['debt']->amount, 2) }}
                    @endif
                </div>
            </div>
        </div><!-- Cierre de receipt-wrapper -->
    @endforeach
</body>

</html>
