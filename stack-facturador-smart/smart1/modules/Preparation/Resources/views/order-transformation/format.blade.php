<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Orden de Transformacion {{ $order->series }}-{{ $order->number }}</title>
    <style>

        @page{
            margin: 35px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 16px;
            margin: 0;
            padding: 5px;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }
        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 8px;
            color: #333;
        }
        .info-section {
            margin-bottom: 12px;
        }
        .info-item {
            margin-bottom: 6px;
            font-size: 16px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 140px;
            font-size: 16px;
        }
        .status-badge {
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0 8px 0;
            padding: 8px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            font-size: 16px;
        }
        .table td {
            font-size: 15px;
        }
        .table td.numeric {
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 15px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            font-size: 14px;
            color: #666;
        }
        .observation-section {
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .observation-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'EMPRESA' }}</div>
        <div class="company-info">
            RUC: {{ $company->number ?? 'N/A' }}<br>
            @if($company->address ?? null)
                {{ $company->address }}<br>
            @endif
            @if($company->telephone ?? null)
                Tel: {{ $company->telephone }}
            @endif
        </div>
        <div class="document-title">ORDEN DE TRANSFORMACION</div>
        <div style="font-size: 14px; margin-top: 5px; font-weight: bold;">
            N° {{ $order->series }}-{{ $order->number }}
        </div>
    </div>

    <!-- Informacion General -->
    <div class="info-section">
        <div class="info-item">
            <span class="info-label">Fecha:</span>
            {{ \Carbon\Carbon::parse($order->date_of_issue)->format('d/m/Y') }}
        </div>
        <div class="info-item">
            <span class="info-label">Usuario:</span>
            {{ $order->user->name ?? 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Almacen Origen:</span>
            {{ $order->warehouse->description ?? 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Almacen Destino:</span>
            {{ $order->destinationWarehouse->description ?? 'N/A' }}
        </div>
        <div class="info-item">
            <span class="info-label">Estado:</span>
            <span class="status-badge status-{{ $order->status }}">
                @switch($order->status)
                    @case('pending')
                        Pendiente
                        @break
                    @case('completed')
                        Completado
                        @break
                    @case('cancelled')
                        Cancelado
                        @break
                    @default
                        {{ $order->status }}
                @endswitch
            </span>
        </div>
        @if($order->person)
        <div class="info-item">
            <span class="info-label">Responsable:</span>
            {{ $order->person->name }}
        </div>
        @endif
    </div>

    <!-- Materia Prima -->
    @if($rawMaterials->count() > 0)
    <div class="section-title">MATERIA PRIMA A TRANSFORMAR</div>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 50%">Descripcion</th>
                <th style="width: 25%">Cantidad</th>
                <th style="width: 25%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rawMaterials as $material)
            <tr>
                <td>
                    @if($material->item->internal_id)
                        <strong>{{ $material->item->internal_id }}</strong><br>
                    @endif
                    {{ $material->item->description ?? 'N/A' }}
                </td>
                <td class="numeric">{{ number_format($material->quantity, 2) }}</td>
                <td class="numeric">{{ number_format($material->quantity * $material->unit_price, 2) }}</td>
            </tr>
            @endforeach
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="2" class="text-right">TOTAL:</td>
                <td class="numeric">
                    {{ number_format($rawMaterials->sum(function($item) { return $item->quantity * $item->unit_price; }), 2) }}
                </td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Productos Finales -->
    @if($finalProducts->count() > 0)
    <div class="section-title">PRODUCTOS FINALES</div>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 50%">Descripcion</th>
                <th style="width: 25%">Cantidad</th>
                <th style="width: 25%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($finalProducts as $product)
            <tr>
                <td>
                    @if($product->item->internal_id)
                        <strong>{{ $product->item->internal_id }}</strong><br>
                    @endif
                    {{ $product->item->description ?? 'N/A' }}
                </td>
                <td class="numeric">{{ number_format($product->quantity, 2) }}</td>
                <td class="numeric">{{ number_format($product->quantity * $product->unit_price, 2) }}</td>
            </tr>
            @endforeach
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="2" class="text-right">TOTAL:</td>
                <td class="numeric">
                    {{ number_format($finalProducts->sum(function($item) { return $item->quantity * $item->unit_price; }), 2) }}
                </td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Informacion de Produccion -->
    @if($order->prod_start_date || $order->prod_responsible || $order->mix_start_date || $order->mix_responsible)
    <div class="section-title">PROCESO</div>
    <div class="info-section">
        @if($order->prod_start_date)
        <div class="info-item">
            <span class="info-label">Inicio Prod.:</span>
            {{ \Carbon\Carbon::parse($order->prod_start_date)->format('d/m/Y') }}
            @if($order->prod_start_time) {{ $order->prod_start_time }} @endif
        </div>
        @endif
        @if($order->prod_end_date)
        <div class="info-item">
            <span class="info-label">Fin Prod.:</span>
            {{ \Carbon\Carbon::parse($order->prod_end_date)->format('d/m/Y') }}
            @if($order->prod_end_time) {{ $order->prod_end_time }} @endif
        </div>
        @endif
        @if($order->prod_responsible)
        <div class="info-item">
            <span class="info-label">Resp. Prod.:</span>
            {{ $order->prod_responsible }}
        </div>
        @endif
        @if($order->mix_start_date)
        <div class="info-item">
            <span class="info-label">Inicio Mezcla:</span>
            {{ \Carbon\Carbon::parse($order->mix_start_date)->format('d/m/Y') }}
            @if($order->mix_start_time) {{ $order->mix_start_time }} @endif
        </div>
        @endif
        @if($order->mix_end_date)
        <div class="info-item">
            <span class="info-label">Fin Mezcla:</span>
            {{ \Carbon\Carbon::parse($order->mix_end_date)->format('d/m/Y') }}
            @if($order->mix_end_time) {{ $order->mix_end_time }} @endif
        </div>
        @endif
        @if($order->mix_responsible)
        <div class="info-item">
            <span class="info-label">Resp. Mezcla:</span>
            {{ $order->mix_responsible }}
        </div>
        @endif
    </div>
    @endif

    <!-- Observaciones -->
    @if($order->observation)
    <div class="observation-section">
        <div class="observation-title">OBSERVACIONES:</div>
        <div>{{ $order->observation }}</div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div style="text-align: center;">
            Documento generado el {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>
</body>
</html>