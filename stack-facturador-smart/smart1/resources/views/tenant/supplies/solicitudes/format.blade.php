<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Solicitud de Acceso al Servicio</title>
    <style>
        @page {
            margin: 8mm 10mm;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            font-size: 9pt;
            line-height: 1.2;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo {
            max-width: 40px;
            height: auto;
            float: left;
            margin-right: 10px;
        }

        .company-info {
            font-weight: bold;
            color: #203965;
            font-size: 12pt;
            margin-bottom: 5px;
        }

        .company-subtitle {
            color: #78d2ff;
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .title {
            font-size: 11pt;
            font-weight: bold;
            color: #1179c4;
            text-align: left;
            margin: 15px 0;
        }

        .section-title {
            font-weight: bold;
            font-size: 8pt;
            color: #3d3f42;
            margin: 10px 0 5px 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }

        .info-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-size: 7pt;
            font-weight: bold;
        }

        .header-cell {
            background-color: #95b3d7;
            color: #000;
        }

        .data-cell {
            background-color: #fff;
        }

        .checkbox-container {
            margin: 5px 0;
        }

        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            background-color: #e1e9f3;
            margin-right: 5px;
            text-align: center;
            font-size: 8pt;
            vertical-align: top;
        }

        .checkbox-label {
            font-size: 8pt;
            margin-left: 5px;
        }

        .location-info {
            width: 250px;
            font-size: 8pt;
        }

        .location-table {
            border-collapse: collapse;
            width: 100%;
        }

        .location-table td {
            border: 1px solid #000;
            padding: 2px;
            font-size: 8pt;
        }

        .signature-section {
            margin-top: 30px;
        }

        .signature-box {
            width: 55mm;
            height: 25mm;
            border: 1px solid #000;
            display: inline-block;
            margin: 0 5px;
        }

        .signature-label {
            text-align: center;
            margin-top: 5px;
            font-size: 8pt;
        }

        .note {
            font-size: 8pt;
            margin-top: 10px;
        }

        .clear {
            clear: both;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    @php
        $company = \App\Models\Tenant\Company::first();
        $logo = $company->logo;
        if ($logo) {
            $logo = "storage/uploads/logos/{$logo}";
            $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
        }
    @endphp

    <!-- Encabezado -->
    <div class="header">
        <table width="100%">
            <tr>
                <td>
                    @if ($logo && file_exists(public_path($logo)))
                        <img src="data:{{ mime_content_type(public_path($logo)) }};base64, {{ base64_encode(file_get_contents(public_path($logo))) }}"
                            alt="{{ $company->name }}" class="logo">
                    @endif

                    <div class="company-info text-center">
                        ASOCIACION "JUNTA ADMINISTRADORA DE AGUA POTABLE MAZAMARI"
                    </div>
                    <div class="company-subtitle text-center">
                        OPERADOR ESPECIALIZADO EN SANEAMIENTO
                    </div>
                </td>

            </tr>
        </table>
        <div class="clear"></div>
    </div>

    <table width="100%">
        <tr>
            <td>
                <div class="title">SOLICITUD DE ACCESO AL SERVICIO</div>

                <div style="margin: 10px 0;">
                    <strong>SEÑOR:</strong><br>
                    <strong>EDGAR DANIEL PEREZ ALVAREZ</strong><br>
                    <strong>PRESIDENTE DE LA ASOC. JAAP MAZAMARI - OES</strong>
                </div>
            </td>
            <td style="text-align: right;">
                <div class="location-info">
                    <table class="location-table" width="100%">
                        <tr>
                            <td style="border:none;">Lugar</td>
                            <td width="5px" style="border:none;"></td>
                            <td width="250px" style="text-align: center;">{{ 'MAZAMARI' }}</td>
                        </tr>
                        <tr>
                            <td style="border:none;">Fecha</td>
                            <td width="5px" style="border:none;"></td>
                            <td style="text-align: center;">{{ $solicitude->created_at->format('Y-m-d') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none;">ASOC</td>
                            <td width="5px" style="border:none;"></td>
                            <td style="text-align: center;">JAAP - OES</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- I.- DATOS DEL SOLICITANTE -->
    <div class="section-title">I.- DATOS DEL SOLICITANTE</div>

    <table class="info-table">
        <tr>
            <td class="data-cell" style="width: 56%;">{{ $solicitude->person->name ?? 'N/A' }}</td>
            <td class="data-cell" style="width: 17%;">{{ $solicitude->person->number ?? 'N/A' }}</td>
            <td class="data-cell" style="width: 27%;">{{ $solicitude->person->telephone ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="header-cell">Nombres y Apellidos</td>
            <td class="header-cell">DNI N°</td>
            <td class="header-cell">Celular N°</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="data-cell" style="width: 56%;">
                {{ $solicitude->person->address ?? 'N/A' }}
            </td>
            <td class="data-cell" style="width: 27%;">{{ 'MAZAMARI' }}</td>
            <td class="data-cell" style="width: 8.5%;">{{ $solicitude->supply->mz ?? 'N/A' }}</td>
            <td class="data-cell" style="width: 8.5%;">{{ $solicitude->supply->lte ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="header-cell">Domicilio(Calle, Jirón, Avenida, Pasaje)</td>
            <td class="header-cell">Distrito</td>
            <td class="header-cell">Manzana</td>
            <td class="header-cell">Lote</td>
        </tr>
    </table>

    <!-- II.- DATOS DEL PREDIO -->
    <div class="section-title">II.- DATOS DEL PREDIO (Marcar con "X")</div>

    <div class="checkbox-container">
        <span class="checkbox"></span><span class="checkbox-label">En construcción</span><br>
        <span class="checkbox"></span><span class="checkbox-label">Habilitado</span><br>
        <span class="checkbox"></span><span class="checkbox-label">Terreno libre</span>
    </div>

    <table class="info-table">
        <tr>
            <td class="data-cell" style="width: 56%;">
                {{ $solicitude->supply->sector->name ?? '' }}
                {{ $solicitude->supply->supplyVia->name ?? '' }}
            </td>
            <td class="data-cell" style="width: 27%;">N/A</td>
            <td class="data-cell" style="width: 8.5%;">{{ $solicitude->supply->mz ?? 'N/A' }}</td>
            <td class="data-cell" style="width: 8.5%;">{{ $solicitude->supply->lte ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="header-cell">Ubicación (Calle, Jirón, Avenida, Pasaje)</td>
            <td class="header-cell">N° de puerta central</td>
            <td class="header-cell">Manzana</td>
            <td class="header-cell">Lote</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="data-cell" style="width: 56%;">{{ $solicitude->supply->zone_type ?? 'URBANIZACIÓN' }}</td>
            <td class="data-cell" style="width: 28%;">MAZAMARI</td>
            <td class="data-cell" style="width: 16%;">SATIPO</td>
        </tr>
        <tr>
            <td class="header-cell">(Urbanización, Habilitación Urbana, AA.HH, Barrio, AA.VV.)</td>
            <td class="header-cell">Distrito</td>
            <td class="header-cell">Provincia</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="data-cell">{{ $solicitude->observation ?? 'Referencia del predio' }}</td>
        </tr>
        <tr>
            <td class="header-cell">Referencia</td>
        </tr>
    </table>

    <!-- Servicios solicitados -->
    <div style="margin: 10px 0; font-size: 8pt; font-weight: bold;">
        Mediante la presente solicitud el solicitante manifiesta su voluntad de acceder a la prestación de los
        siguientes servicios:
    </div>

    <table width="100%">
        <tr>
            <td style="width: 40%; vertical-align: top;">
                <table class="info-table">
                    <tr>
                        <td class="header-cell" colspan="2">TIPO DE SERVICIO:</td>
                    </tr>
                    <tr>
                        <td class="data-cell" style="width: 20%;">
                            @php
                                $serviceName = strtolower($solicitude->supplyService->name ?? '');
                                $hasWater = str_contains($serviceName, 'agua') || str_contains($serviceName, 'water');
                            @endphp
                            {{ $hasWater ? '' : '' }}
                        </td>
                        <td class="data-cell">SERVICIO DE AGUA POTABLE</td>
                    </tr>
                    <tr>
                        <td class="data-cell">
                            @php
                                $hasSewerage =
                                    str_contains($serviceName, 'desague') ||
                                    str_contains($serviceName, 'alcantarillado');
                            @endphp
                            {{ $hasSewerage ? '' : '' }}
                        </td>
                        <td class="data-cell">SERVICIO DE DESAGUE</td>
                    </tr>
                </table>
            </td>
            <td style="width: 40%; vertical-align: top;">
                <table class="info-table">
                    <tr>
                        <td class="header-cell" colspan="2">Servicios Extras</td>
                    </tr>
                    <tr>
                        <td class="data-cell" style="width: 20%;"></td>
                        <td class="data-cell">Conexión Nueva</td>
                    </tr>
                    <tr>
                        <td class="data-cell"></td>
                        <td class="data-cell">Independización/Subdivisión</td>
                    </tr>
                    <tr>
                        <td class="data-cell"></td>
                        <td class="data-cell">Regularización</td>
                    </tr>
                    <tr>
                        <td class="data-cell"></td>
                        <td class="data-cell">Reubicación</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Uso del servicio -->
    <table class="info-table" style="margin-top: 15px;">
        <tr>
            <td colspan="2" class="data-cell" style="width: 73%; text-align: left;">USO DEL SERVICIO:</td>
            <td class="data-cell" style="width: 27%; text-align: left;">N° de conexiones</td>
        </tr>
        @php
            $uso = strtoupper($solicitude->use ?? 'DOMESTICO');
        @endphp
        <tr>
            <td class="data-cell">{{ $uso == 'DOMESTICO' ? '' : '' }}</td>
            <td class="data-cell">DOMÉSTICO</td>
            <td class="data-cell"></td>
        </tr>
        <tr>
            <td class="data-cell">{{ $uso == 'COMERCIAL' ? '' : '' }}</td>
            <td class="data-cell">COMERCIAL</td>
            <td class="data-cell"></td>
        </tr>
        <tr>
            <td class="data-cell">{{ $uso == 'INDUSTRIAL' ? '' : '' }}</td>
            <td class="data-cell">INDUSTRIAL</td>
            <td class="data-cell"></td>
        </tr>
        <tr>
            <td class="data-cell">{{ $uso == 'TEMPORAL' ? '' : '' }}</td>
            <td class="data-cell">TEMPORAL</td>
            <td class="data-cell"></td>
        </tr>
    </table>

    <!-- Documentos adjuntos -->
    <div style="margin: 15px 0; font-size: 8pt; font-weight: bold;">
        Adjunto los siguientes documentos:
    </div>

    <table class="info-table">
        <tr>
            <td class="data-cell" style="width: 6%;"></td>
            <td class="data-cell">Documento que acredita la propiedad o posesión del predio.</td>
        </tr>
        <tr>
            <td class="data-cell" style="width: 6%;"></td>
            <td class="data-cell">Copia simple del documento de identidad del solicitante o los documentos que
                acrediten la representación.</td>
        </tr>
        <tr>
            <td class="data-cell" style="width: 6%;"></td>
            <td class="data-cell">Plano de ubicación o croquis del predio.</td>
        </tr>
        <tr>
            <td class="data-cell" style="width: 6%;"></td>
            <td class="data-cell">Otros (Especifique).......................................................</td>
        </tr>
    </table>

    <div style="margin: 15px 0; font-size: 8pt; font-weight: bold;">
        Atentamente,
    </div>

    <!-- Firmas -->
    <div class="signature-section">
        <table width="100%">
            <tr>
                <td style="width: 45%; text-align: center;">
                    <div class="signature-box"></div>
                    <div class="signature-label">FIRMA DEL SOLICITANTE O SU REPRESENTANTE</div>
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 45%; text-align: center;">
                    <div class="signature-box"></div>
                    <div class="signature-label">SELLO DE RECEPCIÓN</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="note">
        <strong>Nota:</strong> Este formato tiene carácter de Declaración Jurada
    </div>

</body>

</html>
