<!DOCTYPE html>
<html lang="es">

<head>
</head>

<body style="margin:0px; padding:0px;">
    @if (!empty($record))
        <div style="width: 100%; overflow: hidden; margin: 0; padding: 0;">
            @php
                function withoutRounding($number, $total_decimals)
                {
                    $number = (string) $number;
                    if ($number === '') {
                        $number = '0';
                    }
                    if (strpos($number, '.') === false) {
                        $number .= '.';
                    }
                    $number_arr = explode('.', $number);

                    $decimals = substr($number_arr[1], 0, $total_decimals);
                    if ($decimals === false) {
                        $decimals = '0';
                    }

                    $return = '';
                    if ($total_decimals == 0) {
                        $return = $number_arr[0];
                    } else {
                        if (strlen($decimals) < $total_decimals) {
                            $decimals = str_pad($decimals, $total_decimals, '0', STR_PAD_RIGHT);
                        }
                        $return = $number_arr[0] . '.' . $decimals;
                    }
                    return $return;
                }
                $show_price = \App\Models\Tenant\Configuration::first()->isShowPriceBarcodeTicket();
            @endphp
            @for ($i = 0; $i < $stock; $i += 2)
                <div style="width: 100%; overflow: hidden;">
                    @for ($j = 0; $j < 2; $j++)
                        <div style="width: 50%; float: left;height: 100px; text-align: center;">
                            <div style="margin:0; padding:0; height: 5px; ">
                            </div>
                            <div style="margin:0; padding:0; height: 17px; ">
                                <strong style="font-size: 10px;">
                                    {{ strlen($record->description) > 50 ? substr($record->description, 0, 50) . '...' : $record->description }}
                                </strong>
                            </div>
                            <div style="margin:0; padding:0;">
                                <span style="font-size: 9px;">
                                    COD: {{ strtoupper($record->internal_id) }}
                                </span>
                            </div>
                            <p style="margin:0; padding:0;">
                                <span style="font-size: 9px;">
                                    {{ $record->currency_type->symbol }}
                                    {{ number_format($record->sale_unit_price, 2) }}
                                </span>
                            </p>
                            <p style="margin:0; padding:0;">
                                @php
                                    $colour = [0, 0, 0];
                                    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                                    echo '<img style="width:250px; max-height: 20px;" src="data:image/png;base64,' .
                                        base64_encode(
                                            $generator->getBarcode(
                                                $record->barcode,
                                                $generator::TYPE_CODE_128,
                                                2,
                                                80,
                                                $colour,
                                            ),
                                        ) .
                                        '">';
                                @endphp
                            </p>
                            <p style="margin:0; padding:0;">
                                <span style="font-size: 9px;">
                                    {{ $record->barcode }}
                                </span>
                            </p>
                        </div>
                    @endfor

                </div>
            @endfor
        </div>
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
