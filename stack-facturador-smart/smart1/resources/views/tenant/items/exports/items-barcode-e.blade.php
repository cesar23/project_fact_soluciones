<!DOCTYPE html>
<html lang="es">

<head>

</head>
@php
    $company = \App\Models\Tenant\Company::active();
@endphp
{{--  --}}

<body style="margin: 0; padding: 0; font-family: Arial, sans-serif;">
    @if (!empty($record))
        @for ($i = 0; $i < $stock; $i++)
            <!-- Contenedor principal con bordes redondeados y fondo blanco -->
            <div style="width: 100%; padding: 3px; box-sizing: border-box; background: white; border-radius: 4px;">
                <!-- Nombre de la empresa y descripción del producto -->
                <div style="font-size: 9px; font-weight: bold; margin-bottom: 1px;">
                    {{ $company->name }}
                </div>
                <div style="font-size: 8px; margin-bottom: 2px;">
                    {{ strtoupper($record->description) }}
                </div>

                <!-- Sección código de barras y detalles -->
                <div style="width: 100%; overflow: hidden;">
                    <!-- Código de barras -->
                    <div style="width: 65%; float: left;">
                        @php
                            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                            echo '<img style="height: 30px; width: 100%;" src="data:image/png;base64,' .
                                base64_encode($generator->getBarcode($record->barcode, $generator::TYPE_CODE_128, 1, 60)) .
                                '">';
                        @endphp
                    </div>

                    <!-- Información a la derecha -->
                    <div style="width: 25%; float: right; font-size: 7px; padding-left: 2px;">
                        <div>Depart: {{ $record->category->name }}</div>
                        <div style="margin-top: 2px;">Marca: {{ $record->brand->name }}</div>
                    </div>
                </div>

                <!-- SKU/Código en dos columnas -->
                <div style="width: 100%; overflow: hidden;">
                    <div style="width: 50%; float: left; font-size: 8px;">
                        SKU:{{ $record->internal_id }}
                    </div>
                    <div style="width: 50%; float: right; font-size: 8px; text-align: right;">
                        Código:{{ $record->internal_id }}
                    </div>
                </div>
            </div>

            <!-- Separador entre etiquetas -->
            @if($i < $stock - 1)
                <div style="page-break-after: always;"></div>
            @endif
        @endfor
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>


