<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Suministro de Agua Potable</title>
    <style>
        @page {
            margin: 8mm 10mm;
        }
        
        body {
            font-family: 'Times New Roman', sans-serif;
            margin: 0;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
        }
        
        .header {
            text-align: center;
        }
        
        .logo-text {
            font-size: 7pt;
            text-align: left;
        }
        
        .contract-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 15px 0;
            text-decoration: underline;
        }
        p{
            margin: 0;
        }
        
        .content {
            text-align: justify;
        }
        
        .user-data {
            margin: 10px 0;
        }
        
        .user-data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            font-weight: bold;
        }
        
        .user-data-table td {
            padding: 2px 5px;
            vertical-align: top;
        }
        
    
        .clear {
            clear: both;
        }
        
        .services-section {
            margin: 15px 0;
        }
        
        .service-item {
            margin: 3px 0 3px 20px;
            font-weight: bold;
            font-size: 9pt;
        }
        
        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            text-align: center;
            margin-right: 5px;
            font-size: 8pt;
            vertical-align: top;
        }
        
        .clauses-title {
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
            margin: 20px 0 15px 0;
            padding: 0 10px;
        }
        
        .clause {
            margin-bottom: 8px;
            text-align: justify;
        }
        
        .clause-number {
            font-weight: bold;
        }
        
        .obligations {
            margin-left: 20px;
        }
        
    
        
        .signatures {
            width: 100%;
        }
        
        .signature-left {
            float: left;
            text-align: center;
            width: 45%;
        }
        
        .signature-right {
            float: right;
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 30px;
            width: 150px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .date-location {
            text-align: right;
            margin: 20px 0 40px 0;
            font-weight: bold;
            color: #3d3f42;
        }
        
        .spacer {
            height: 10px;
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
        <table class="logo-text">
            <tr>
                <td style="width: 30%; text-align: left;">
                    @if($logo && file_exists(public_path($logo)))
                    <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                                alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                    @endif
                </td>
                <td style="width: 30%; text-align: center; vertical-align: middle;">
                    <div style="width: 400px; text-align: center;">
                        ASOCIACION JUNTA ADMINISTRADORA DE AGUA POTABLE MAZAMARI ESPECIALIZADO EN SANEAMIENTO EN ALCANTARILLADO
                    </div>
                </td>
            </tr>
        </table>
        
        <div class="contract-title">
            CONTRATO DE SUMINISTROS DE AGUA POTABLE Y/O DESAGUE
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <p>Conste por el presente documento el contrato de suministro de Agua Potable y/o Desague que se celebra de una parte de LA ASOCIACION JUNTA ADMINISTRADORA DE AGUA POTABLE CON RUC N20191889062, con domicilio legal en Av. Republica Suiza Esquina Botto Bernales, quien en adelante se denominara LA ASOCIACION y la otra parte don(a) y sus datos siguientes:</p>
    </div>

    <!-- Datos del usuario -->
    <div class="user-data">
        @php
            $supply = $contract->supply ?? null;
            $via = $supply ? $supply->supplyVia ?? null : null;
            $sector = $supply ? $supply->sector ?? null : null;
            $name_sector = $sector ? $sector->name ?? '' : '';
            $name_via = $via ? $via->name ?? '' : '';
            $address = trim($name_sector . '  ' . $name_via);
            $address = !empty($address) ? $address : 'N/A';
            $userCode = trim(($contract->supply->old_code ?? '') . ' ' . ($contract->supply->cod_route ?? ''));
            $userCode = !empty($userCode) ? $userCode : 'N/A';
        @endphp
        
        <table class="user-data-table">
            <tr>
                <td colspan="4">APELLIDOS Y NOMBRES: {{ $contract->person->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td colspan="2">NUMERO DNI: {{ $contract->person->number ?? 'N/A' }}</td>
                <td colspan="2">CODIGO DE USUARIO: {{ $userCode }}</td>
            </tr>
            <tr>
                <td colspan="4">SECTOR/ZONA: {{ $contract->supply->sector->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td colspan="2">DIRECCION: {{ $address }}</td>
                <td colspan="2">LT.: {{ $contract->supply->lte ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="content">
        <p>Perteneciente a la unidad de Mazamari a quienes en adelante se les denominara EL USUARIO bajo los terminos y condiciones siguientes:</p>
    </div>

    <!-- Servicios -->
    <div class="services-section">
        <p>1.- LA ASOCIACION y EL USUARIO convienen que la primera presta el servicio de:</p>
        
        @php
            // Determinar que servicios estan activos basado en supplyService
            $hasWater = false;
            $hasSewerage = false;
            
            if($contract->supplyService) {
                // Asumir que el nombre del servicio contiene informacion sobre el tipo
                $serviceName = strtolower($contract->supplyService->name ?? '');
                $hasWater = str_contains($serviceName, 'agua') || str_contains($serviceName, 'water');
                $hasSewerage = str_contains($serviceName, 'desague') || str_contains($serviceName, 'alcantarillado') || str_contains($serviceName, 'sewerage');
            }
            
            // Si no se puede determinar, asumir ambos servicios
            if (!$hasWater && !$hasSewerage) {
                $hasWater = true;
                $hasSewerage = true;
            }
        @endphp
        <table style="width: 80%; border-collapse: collapse; margin: 0 auto;">
            <tr>
                <td style="width: 450px; font-weight: bold; font-size: 9pt; padding: 3px 0;">INSTALACION DE AGUA POTABLE</td>
                <td style="font-weight: bold; font-size: 9pt; padding: 3px 0;">{{ $hasWater ? '(X)' : '' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; font-size: 9pt; padding: 3px 0;">INSTALACION DE DESAGUE</td>
                <td style="font-weight: bold; font-size: 9pt; padding: 3px 0;">{{ $hasSewerage ? '(X)' : '' }}</td>
            </tr>
        </table>
    </div>

    <div class="content">
        <p><span class="clause-number">2.-</span> Las caracteristicas tecnicas que requiere la instalacion y demas condiciones del servicio consta en la Ficha catastral del ASOCIADO USUARIO que forma parte del presente contrato.</p>
    </div>

    <div class="content">
        <p><span class="clause-number">3.-</span> La ASOCIACION Y EL ASOCIADO, conviven a someterse de acuerdo a las clausulas generales de los contratos de suministro de Agua Potable y Alcantarillado, el abonado declara conocer al momento de suscribir el presente contrato.</p>
    </div>

    <!-- Clausulas generales -->
    <div class="clauses-title">
        CLAUSULAS GENERALES SEGUN ESTATUTO Y REGLAMENTO PARA LA PRESTACION DEL SERVICIO DE AGUA POTABLE Y DESAGUE A CARGO DE LA ASOCIACION JUNTA ADMINISTRATIVA AGUA DEL DISTRITO DE MAZAMARI, PROVINCIA DE SATIPO, DEPARTAMENTO DE JUNIN
    </div>

    <div class="clause">
        <span class="clause-number">1.-</span> Para la inscripcion en el siguiente contrato el asociado debe cumplir los requisitos exigidos:
    </div>

    <div class="clause">
        <span class="clause-number">2.-</span> Las reinstalaciones de los servicios seran autorizadas solo por la ASOCIACION cuyo costo sera efectuado por el ASOCIADO.
    </div>

    <div class="clause">
        <span class="clause-number">3.-</span> El servicio de AGUA POTABLE comprende la conexion domiciliaria externa desde la tuberia matriz cuyo costo sera ejecutado por el USUARIO.
    </div>

    <div class="clause">
        <span class="clause-number">4.-</span> El INCUMPLIMIENTO de pago de 2 meses consecutivos origina el corte del servicio.
    </div>

    <div class="clause">
        <span class="clause-number">5.-</span> Son obligaciones del usuario:
        
        <div class="obligations" style="margin-left: 80px;">
            <div class="obligation-item">- Pagar puntualmente por los servicios recibidos de acuerdo a la tarifa aprobada en asamblea general del USUARIO.</div>
            <div class="obligation-item">- Hacer uso adecuado de los servicios.</div>
            <div class="obligation-item">- NO manipular las instalaciones sin autorizaciones previas.</div>
        </div>
    </div>

    <div class="content" style="margin-top: 20px;">
        <p>Y para los efectos de ambas partes y estando de acuerdo a las clausulas estipuladas estampamos nuestras firmas en senal de conformidad</p>
    </div>

    <!-- Fecha y lugar -->
    <div class="date-location">
        @php
            $date = $contract->created_at ?? $contract->start_date ?? now();
            $months = [
                1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
                5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
                9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
            ];
            $day = $date->format('d');
            $month = $months[(int)$date->format('m')];
            $year = $date->format('Y');
            $formattedDate = sprintf('%02d DE %s DEL %d', $day, $month, $year);
        @endphp
        MAZAMARI, {{ $formattedDate }}
    </div>

    <!-- Firmas -->
    <div class="signatures">
        <div class="signature-left">
            <div class="signature-line" style="width: 300px;"></div>
            <div>LA ASOCIACION</div>
        </div>
        
        <div class="signature-right">
            <div class="signature-line" style="width: 300px;"></div>
            <div>EL USUARIO</div>
        </div>
        
        <div class="clear"></div>
    </div>

</body>
</html>