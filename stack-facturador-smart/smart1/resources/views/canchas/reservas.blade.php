<!DOCTYPE html>
<html>

<head>
    <title>Reserva</title>
    <style>
        @page {

            margin: 5px;

        }

        html {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-spacing: 0;
        }

        .mp-0 {
            margin: 0;
            padding: 0;

        }

        .celda {
            text-align: center;
            padding: 5px;
            border: 0.1px solid black;
        }

        th {
            padding: 5px;
            text-align: center;
        }

        .border-bottom {
            border-bottom: 1px dashed black;
        }

        .border-top {
            border-top: 1px dashed black;
        }

        .title {
            font-weight: bold;
            /*padding: 5px;*/
            font-size: 13px !important;
            text-decoration: underline;
        }

        p>strong {
            margin-left: 5px;
            font-size: 12px;
        }

        thead {
            font-weight: bold;
            text-align: center;
        }

        .td-custom {
            line-height: 0.1em;
        }

        .width-custom {
            width: 50%
        }

        .font-bold {
            font-weight: bold;
        }

        .full-width {
            width: 100%;
        }

        .desc-9 {
            font-size: 9px;
        }

        .desc {
            font-size: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .mt-3 {
            margin-top: 2.5rem;
        }

        .mb {
            margin-bottom: 0.5rem;
        }

        .mt {
            margin-top: 0.5rem;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        table,
        tr,
        td,
        th {
            /*font-size: 10px !important;*/
            padding: 0px;
            margin: 0px;
        }

        p {
            margin: 0.3rem;
        }
        body {
            position: relative;
        }
    </style>
</head>
@php
    use App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate;
    if ($cancha->customer_id) {
        $name = $cancha->customer->name;
        $phone = $cancha->customer->telephone;
    } else {
        $name = $cancha->reservante_nombre." ".$cancha->reservante_apellidos;
        $phone = $cancha->numero;
    }
    if ($cancha->type_id) {
        $name_location = $cancha->type->nombre;
        $location = $cancha->type->ubicacion;
        $description = $cancha->type->description;
    } else {
        $name_location = $cancha->nombre;
        $location = $cancha->ubicacion;
        $description = $cancha->description;
    }
@endphp

<body>

    <div style="margin-top:10px">
        <p align="center" class="title"><strong>{{ strtoupper($company->name) }}</strong></p>
        <p align="center" class="mp-0 title"><strong>{{ $establishment->address }}</strong></p>
        @if (isset($establishment->district->description) && $establishment->district->description != '')
            <p align="center" class="mp-0 title"><strong>{{ $establishment->district->description }}</strong></p>
        @endif
        @if (isset($establishment->province->description) && isset($establishment->department->description))
            <p align="center" class="mp-0 title"><strong>{{ $establishment->province->description }} -
                    {{ $establishment->department->description }}</strong></p>
        @endif

        <p class="desc ">
            <strong>RUC:</strong> {{ $company->number }}
            <strong>
                TLF:
            </strong>{{ $establishment->telephone }}
        </p class="desc">
        <p>
            ---------------------------------------------------------------
        </p>



        <p class="desc">
            <strong>Cliente:</strong> {{ strtoupper($name) }}
        </p>

        <p class="desc">
            <strong>Fecha de Reserva:</strong> {{ $cancha->fecha_reserva }}
        </p>
        <p class="desc">
            <strong>Hora de Reserva:</strong> {{ $cancha->hora_reserva }}
        </p>

        <p class="desc" style="text-decoration: underline;">
                <strong>
                    LUGAR DE RESERVA
                </strong>
        </p>
        <p class="desc">
            <strong>Nombre:</strong> {{ $name_location }}
        </p>
        <p class="desc">
            <strong>Ubicación:</strong> {{ $location }}
        </p>
        <p class="desc">
            <strong>Descripción:</strong> {{$description}}
        </p>


    </div>
    <table class="full-width">

        <tbody>

            <tr>
                <td style="text-align: center;">
                    @php
                        $qrCode = new QrCodeGenerate();
                        $qr = $qrCode->displayPNGBase64($cancha->ticket);
                    @endphp
                    <img src="data:image/png;base64,{{ $qr }}">
                </td>
            </tr>


        </tbody>
    </table>
    @if ($cancha->anulado == 1)
    <div style="position: absolute; text-align: center; top:180px;left:-10px;"
    >
        <img src="data:{{ mime_content_type(public_path('status_images' . DIRECTORY_SEPARATOR . 'anulado.png')) }};base64, {{ base64_encode(file_get_contents(public_path('status_images' . DIRECTORY_SEPARATOR . 'anulado.png'))) }}"
            alt="anulado" class="" style="opacity: 0.6;width:250px;">
    </div>
@endif
</body>

</html>
