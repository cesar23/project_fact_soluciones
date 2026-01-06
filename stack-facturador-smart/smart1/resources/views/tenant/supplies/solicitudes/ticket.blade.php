<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Solicitud</title>
    <style>
        @page {
            margin: 2mm;
            size: 80mm auto;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 2mm;
            font-size: 8pt;
            line-height: 1.2;
            color: #000;
            width: 76mm;
        }
        
        .header {
            text-align: center;
            margin-bottom: 5mm;
        }
        
        .logo {
            max-width: 30mm;
            height: auto;
            margin-bottom: 2mm;
        }
        
        .company-name {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 1mm;
        }
        
        .company-ruc {
            font-size: 8pt;
            margin-bottom: 1mm;
        }
        
        .company-address {
            font-size: 6pt;
            margin-bottom: 1mm;
        }
        
        .separator {
            border-bottom: 1px solid #000;
            margin: 2mm 0;
        }
        
        .ticket-title {
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            margin: 2mm 0;
        }
        
        .ticket-number {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            margin: 2mm 0;
        }
        
        .info-row {
            margin-bottom: 1mm;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 25mm;
        }
        
        .info-value {
            display: inline-block;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 2mm 0;
        }
        
        .service-table th,
        .service-table td {
            border-bottom: 1px solid #000;
            padding: 1mm;
            text-align: center;
            font-size: 7pt;
        }
        
        .service-table th {
            font-weight: bold;
        }
        
        .total-row {
            border-top: 1px solid #000;
            padding-top: 2mm;
            margin-top: 2mm;
            font-weight: bold;
            text-align: right;
        }
        
        .total-amount {
            text-align: center;
            font-size: 9pt;
            margin: 2mm 0;
        }
        
        .footer-info {
            margin-top: 3mm;
            font-size: 7pt;
        }
        
        .conditions {
            margin-top: 3mm;
            border-top: 1px dashed #000;
            padding-top: 2mm;
        }
        
        .conditions-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 2mm;
        }
        
        .conditions-text {
            font-size: 6pt;
            text-align: justify;
            line-height: 1.3;
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
    
    <!-- Cabecera -->
    <div class="header">
        @if($logo && file_exists(public_path($logo)))
        <img src="data:{{ mime_content_type(public_path($logo)) }};base64, {{ base64_encode(file_get_contents(public_path($logo))) }}"
             alt="{{ $company->name }}" class="logo">
        @endif
        
        <div class="company-name">{{ $company->name ?? 'ASOCIACION JAAP - MAZAMARI' }}</div>
        <div class="company-ruc">RUC: {{ $company->number ?? '-' }}</div>
        <div class="company-address">{{ $company->address ?? '-' }}</div>
        <div class="company-address">
            {{ $company->district ?? 'MAZAMARI' }} - {{ $company->province ?? 'SATIPO' }} - {{ $company->department ?? 'JUNIN' }}
        </div>
    </div>

    <div class="separator"></div>

    <div class="ticket-title">TICKET DE PAGO</div>
    
    <div class="ticket-number">
        SOL-{{ str_pad($solicitude->id, 8, '0', STR_PAD_LEFT) }}
    </div>

    <div class="separator"></div>

    <!-- Informaci�n del cliente -->
    <div class="info-row">
        <span class="info-label">CLIENTE:</span>
        <span class="info-value">{{ $solicitude->person->name ?? 'N/A' }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">TIPO DOC:</span>
        <span class="info-value">{{ $solicitude->person->identity_document_type->description ?? 'DNI' }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">NUMERO DOC:</span>
        <span class="info-value">{{ $solicitude->person->number ?? 'N/A' }}</span>
    </div>

    <!-- Tabla de servicios -->
    <table class="service-table">
        <thead>
            <tr>
                <th style="width: 20mm;">COD.</th>
                <th style="width: 36mm;">DESCRIPCIÓN</th>
                <th style="width: 20mm;">PRECIO</th>
            </tr>
        </thead>
        <tbody>
            @php
                $servicios = [];
                if($solicitude->supplyService) {
                    $servicios[] = [
                        'codigo' => 'SER-' . str_pad($solicitude->supplyService->id, 3, '0', STR_PAD_LEFT),
                        'descripcion' => $solicitude->supplyService->name,
                        'precio' => 50.00
                    ];
                }
                
                if(empty($servicios)) {
                    $servicios[] = [
                        'codigo' => 'SER-001',
                        'descripcion' => 'SOLICITUD DE CONEXIÓN NUEVA',
                        'precio' => 50.00
                    ];
                }
                
                $total = 0;
            @endphp
            
            @foreach($servicios as $servicio)
                @php $total += $servicio['precio']; @endphp
                <tr>
                    <td>{{ $servicio['codigo'] }}</td>
                    <td style="text-align: left;">{{ $servicio['descripcion'] }}</td>
                    <td>{{ number_format($servicio['precio'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total -->
    <div class="total-row">
        <table width="100%">
            <tr>
                <td style="text-align: right; font-weight: bold; border: none; padding: 0;">TOTAL</td>
                <td style="text-align: center; font-weight: bold; border: none; padding: 0; width: 20mm;">S/. {{ number_format($total, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Información adicional -->
    <div class="footer-info">
        <div class="info-row">
            <span class="info-label">Unidad medida:</span>
            <span class="info-value">Servicio</span>
            <span style="margin-left: 10mm; font-weight: bold;">Cantidad:</span>
            <span>1</span>
        </div>
    </div>

    <div class="total-amount">
        <strong>IMPORTE: S/. {{ number_format($total, 2) }}</strong>
    </div>

    <div style="font-size: 7pt; margin: 2mm 0;">
        <strong>SON:</strong> CINCUENTA CON 00/100 SOLES
    </div>

    <!-- Información del sistema -->
    <div class="footer-info">
        <div class="info-row">
            <span class="info-label">Forma de pago:</span>
            <span class="info-value">CONTADO</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">F.H Emisión:</span>
            <span class="info-value">{{ $solicitude->created_at->format('d-m-Y H:i A') }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Operador:</span>
            <span class="info-value">{{ $solicitude->user->name ?? 'SISTEMA' }}</span>
        </div>
    </div>

    <!-- Separador -->
    <div class="separator"></div>

    {{-- <!-- Condiciones -->
    <div class="conditions">
        <div class="conditions-title">CONDICIONES DE SERVICIOS</div>
        <div class="conditions-text">
            Al recibir el presente DOCUMENTO acepto todos los términos y condiciones del contrato del servicio de suministro de agua potable y/o desagüe detallado en el reglamento de la asociación, los cuales se encuentran disponibles en las oficinas administrativas de JAAP - MAZAMARI.
        </div>
    </div> --}}
    <div style="text-align: center; margin-top: 2mm; font-size: 6pt;">
        Este comprobante es de uso interno de la empresa..
    </div>
    <div style="text-align: center; margin-top: 3mm; font-size: 6pt;">
        _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _
    </div>



</body>
</html>