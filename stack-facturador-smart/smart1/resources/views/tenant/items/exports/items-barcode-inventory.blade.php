<!DOCTYPE html>
<html lang="es">

<head>
    <style>

    </style>
</head>

<body style="margin:0px; padding:0px;">
    @if (!empty($record))
        <div class="">
            <div class=" ">
                <div class="table" style="margin:0px; padding:0px;overflow: wrap;" width="100%" autosize="1">
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
                        $count_format = 1;

                    @endphp
                    @for ($i = 0; $i < $stock; $i += 3)
                        <div style="margin:0px; padding:0px;">
                            @for ($j = 0; $j < $count_format; $j++)
                                <div class="celda" width="100%"
                                    style="text-align: center; padding-top: 0px; padding-bottom: 10px; font-size: 12px; vertical-align: top; width: 100%;">

                                    <div style="text-align: center;">
                                            @php
                                                $colour = [0, 0, 0];
                                                $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                                                echo '<img style="width:160px; height: 63px;" src="data:image/png;base64,' .
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
                                        <p style="font-size: 12px;margin:0px; padding:0px;">{{ $record->barcode }}</p>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
