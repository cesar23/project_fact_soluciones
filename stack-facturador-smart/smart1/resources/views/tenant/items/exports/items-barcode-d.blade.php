<!DOCTYPE html>
<html lang="es">

<head>

</head>
@php
    $company = \App\Models\Tenant\Company::active();
@endphp
{{--  --}}

<body style="margin:0px; padding:0px;">
    @if (!empty($record))

        @for ($i = 0; $i < $stock; $i++)
            <div style="width: 100%; height: 50px; text-align: center;">
                <div style="height: 13x;">
                    <span style="font-size: 8px; margin: 0px;">{{ $company->name }}</span>
                </div>
                <div style="height: 25px;">
                    <p style="font-size: 7px; margin: 0px; display: table-cell; vertical-align: middle;">
                        {{ strtoupper($record->description) }}</p>
                </div>
                <div style="">
                    @php
                        $colour = [0, 0, 0];
                        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                        echo '<img style="height: 32px; width: 155px;" src="data:image/png;base64,' .
                            base64_encode(
                                $generator->getBarcode($record->barcode, $generator::TYPE_CODE_128, 2, 80, $colour),
                            ) .
                            '">';
                    @endphp
                </div>
                <div style="">
                    <p style="font-size: 7px; margin: 0px;">{{ $record->barcode }}</p>


                    <p style="font-size: 8px; margin: 0px;">
                        {{ $record->currency_type->symbol }}
                        {{ $record->sale_unit_price }}</p>

                </div>
                <div style="">

                </div>
            </div>
        @endfor
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
