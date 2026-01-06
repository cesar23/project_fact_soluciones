<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        @page {
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            text-align: center;
        }

        .border-outer {
            border: 25px solid white;
            /* Borde rojo grueso exterior */
            padding: 0;
            box-sizing: border-box;
            position: absolute;
            width: 95.5%;
            height: 19.6cm;
        }

        .border-inner {
            width: calc(100% - 30px);
            height: calc(100% - 30px);
            margin: 15px;
            padding: 0;
            box-sizing: border-box;
            position: absolute;
            width: 95.3%;
            height: 18.3cm;
        }

        .certificate-content {
            width: 100%;
            max-height: 18cm;
            text-align: center;
            box-sizing: border-box;
        }

        .certificate-header {
            font-family: 'Arial-Bold', sans-serif;
            font-weight: bold;
            width: 60%;
            margin-left: auto;
            margin-right: auto;
            background-color: #000;
            color: #fff;
            text-align: center;
            font-size: 4.5rem;
        }

        .certificate-title {
            font-family: 'certificatefont', sans-serif;
            font-size: 4rem;
            font-weight: bold;
            text-align: center;
        }

        .certificate-description {
            font-size: 1.5rem;
            text-align: center;
            font-family: 'Arial', sans-serif;
            font-weight: bold;
        }

        .person-name {
            font-size: 2rem;
            text-align: center;
            font-weight: bold;
            font-family: 'personfont', sans-serif;
        }

        .person-name-number {
            font-size: 1.5rem;
            text-align: center;
            font-weight: bold;
        }

        .berfore-course {
            font-size: 1.3rem;
            text-align: center;
            font-weight: bold;
            font-family: 'Arial', sans-serif;
        }

        .course {
            font-size: 2rem;
            text-align: center;
            font-weight: bold;
            font-style: italic;
            font-family: 'coursefont', Times, serif;
        }

        .detail-1 {
            font-size: 1rem;
            text-align: center;
            font-family: 'Arial', sans-serif;
        }

        .date-duration {
            width: 80%;
            font-size: 1rem;
            text-align: center;
            font-family: 'Arial', sans-serif;
            margin-left: auto;
            margin-right: auto;
            font-style: italic;
        }

        .title-points {
            width: 80%;
            font-size: 1rem;
            text-align: left;
            font-family: 'Arial', sans-serif;
            margin-left: auto;
            margin-right: auto;
            font-weight: bold;
            font-style: italic;
        }

        .points-list {
            width: 80%;
            font-size: 1rem;
            text-align: left;
            font-family: 'Arial', sans-serif;
            margin-left: auto;
            margin-right: auto;
            height: 150px;
        }

        .points-table {
            width: 100%;
            border-collapse: collapse;
        }

        .points-table td {
            padding: 5px 10px;
            vertical-align: top;
            border: none;
        }

        .points-table .item {
            padding: 3px 0;
        }

        .mt-0.5 {
            margin-top: 0.5rem;
        }

        .mt-1 {
            margin-top: 1rem;
        }

        .mt-2 {
            margin-top: 2rem;
        }

        .date-certificate {
            position: absolute;
            width: 80%;
            font-size: 1rem;
            text-align: right;
            font-family: 'Arial', sans-serif;
            font-style: italic;
            font-weight: bold;
            margin-left: auto;
            margin-right: auto;
        }

        .code-certificate {
            position: absolute;
            width: 80%;
            font-size: 1rem;
            text-align: right;
            font-family: 'Arial', sans-serif;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>

<body
style="background-image: url('{{ public_path('logo/logo_certificate.jpg') }}'); background-size: cover; background-position: center;"
>
    <div class="border-outer">
        <div class="border-inner">


        </div>
    </div>
    <div class="certificate-content">
        <div style="height: 250px;">

        </div>
        {{-- <div class="certificate-header">
            GUZAL PERÚ S.A.C.
        </div>
        <div class="certificate-title">
            CERTIFICADO
        </div>
        <div class="certificate-description">
            Se otorga el presente certificado a:
        </div> --}}
        <div class="person-name">
            {{ $certificate->tag_1 }}
        </div>
        <div class="person-name-number">
            {{ $certificate->tag_2 }}
        </div>

        <div class="berfore-course mt-0.5">
            {{ $certificate->tag_8 }}
        </div>
        <div class="course mt-0.5">
            {{ $certificate->tag_3 }}
        </div>
        <div class="detail-1">
            {{ $certificate->tag_9 }}
        </div>
        @php
            $tag_4 = $certificate->tag_4;
            $tag_4 = preg_replace('/\*(.*?)\*/', '<strong>$1</strong>', $tag_4);
        @endphp
        <div class="date-duration mt-1">
            {!! $tag_4 !!}
        </div>
        <div class="title-points mt-1">
            {{ $certificate->tag_5 }}
        </div>
        @php
        
            $items = $certificate->items;
            $items = (array) $items;
            $items = array_values($items);
            $total_items = count($items);

            // Configuración por defecto: máximo 3 columnas, 4 filas
            $max_columns = 3;
            $max_rows = 4;

            // Calcular columnas y filas dinámicamente
            if ($total_items <= 4) {
                // Para 4 o menos elementos: 1 columna
                $columns = 1;
                $rows = $total_items;
            } elseif ($total_items <= 8) {
                // Para 5-8 elementos: 2 columnas
                $columns = 2;
                $rows = ceil($total_items / 2);
            } else {
                // Para más de 8 elementos: 3 columnas
                $columns = 3;
                $rows = ceil($total_items / 3);
            }

            // Asegurar que no exceda los límites máximos
            $columns = min($columns, $max_columns);
            $rows = min($rows, $max_rows);
        @endphp
        <div class="points-list">
            <table class="points-table">
                @for ($row = 0; $row < $rows; $row++)
                    <tr>
                        @for ($col = 0; $col < $columns; $col++)
                            @php
                                $index = $row + $col * $rows;
                            @endphp
                            <td>
                                @if ($index < $total_items)
                                    <div class="item">
                                        • {{ $items[$index]->description }}
                                    </div>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endfor
            </table>
        </div>
        <div
        style="width: 100%;"
        >
        <div style="width: 48%; float: left;text-align: left;">
            @php
                    use App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate;
                    $qrCode = new QrCodeGenerate();
                    $qr = $qrCode->displayPNGBase64(url('certificate/print/'.$certificate->external_id),250);
            @endphp
            <img src="data:image/png;base64, {{ $qr }}" style="width: 100px;margin-top: -5px;margin-left: 60px;"/>
        </div>
        <div style="width: 48%; float: left;text-align: right;">
            <div class="date-certificate">
                {{ $certificate->tag_6 }}
            </div>
            <div class="code-certificate mt-1">
                @if($certificate->number)
                    {{ $certificate->series ?? "CERT" }}-{{ str_pad($certificate->number, 6, '0', STR_PAD_LEFT) }}
                    
                @endif

            </div>
        </div>
        </div>
    </div>
</body>

</html>
