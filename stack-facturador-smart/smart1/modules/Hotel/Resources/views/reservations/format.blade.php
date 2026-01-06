<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Reserva</title>
    <style>
        /* CSS2 compatible styling */
        body {
            font-family: Arial, sans-serif;
            padding: 0;
            margin: 0
        }

        .container {
            width: 100%;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 5px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header img {
            max-height: 50px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        td {
            padding: 5px;
            border-bottom: 0.5px solid #000;
        }

        .label {
            font-weight: bold;
            width: 150px;
        }

        .input {
            width: calc(100% - 160px);
            padding-left: 5px;
        }

        .checkbox-group {
            width: auto;
        }

        .textarea {
            width: 100%;
            height: 60px;
            border: 1px solid #000;
            padding: 5px;
            resize: none;
        }

        .signature-section {
            margin-top: 20px;
        }

        .signature {
            width: 45%;
            text-align: center;
        }
    </style>
</head>
@php
    use Carbon\Carbon;

    $logo = "{$company->logo}";

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

@endphp

<body>
    <div class="container">
        <div class="header" style="display: flex; align-items: center; justify-content: space-between;">
            <div style="flex: 1; text-align: left;">
                @if($logo && file_exists(public_path($logo)))
                    <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                        alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                @else
                <div style="width: 150px;">
                    <br>
                </div>
                @endif
            </div>
            <div style="flex: 2; text-align: center;">
                <h1>FICHA DE RESERVA</h1>
            </div>
            <div style="flex: 1;"></div>
        </div>

        <table>
            <tr>
                <td class="label">Fecha realizada:</td>
                <td class="input">{{ Carbon::parse($document->reservation_date)->format('d/m/Y') }}</td>
                <td class="label">Nro:</td>
                <td class="input">{{ $document->id ?? '----' }}</td>
            </tr>
            <tr>
                <td class="label">Contacto:</td>
                <td class="input">{{ $document->contact }}</td>
                <td class="label">Medio de reserva:</td>
                <td class="input">{{ $document->reservation_method }}</td>
            </tr>
            <tr>
                <td class="label">Empresa/Agencia:</td>
                <td class="input">{{ $document->agency }}</td>
                <td class="label">Teléfono:</td>
                <td class="input">{{ $document->customer->telephone ?? '----' }}</td>
            </tr>
            <tr>
                <td class="label">Nombre del Pax:</td>
                <td class="input">{{ $document->name }}</td>
                <td class="label">Edad:</td>
                <td class="input">{{ $document->age }}</td>
            </tr>
            <tr>
                <td class="label">Tipo de habitación:</td>
                <td class="input">{{ optional($document->room->category)->description ?? '----' }}</td>
                <td class="label">Nro. de Habit.:</td>
                <td class="input">{{ $document->room->name ?? '----' }}</td>
            </tr>
            <tr>
                <td class="label">Check-in:</td>
                <td class="input">{{ Carbon::parse($document->check_in_date)->format('d/m/Y') }}</td>
                <td class="label">Check-out:</td>
                <td class="input">{{ Carbon::parse($document->check_out_date)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Hora de llegada:</td>
                <td class="input">{{ Carbon::parse($document->arrival_time)->format('H:i') }}</td>
                <td class="checkbox-group" colspan="2">
                    Transfer Inn: <input type="checkbox" {{ $document->transfer_in ? 'checked' : '' }}>
                    Transfer Out: <input type="checkbox" {{ $document->transfer_out ? 'checked' : '' }}>
                </td>
            </tr>
            <tr>
                <td class="label">Tipo de desayuno:</td>
                <td colspan="3">

                    @php
                        switch ($document->breakfast_type) {
                            case 'americano':
                                $breakfast = 'Americano';
                                break;
                            case 'continental':
                                $breakfast = 'Continental';
                                break;
                            case 'vegetariano':
                                $breakfast = 'Vegetariano';
                                break;
                            default:
                                $breakfast = 'No especificado';
                                break;
                        }
                    @endphp
                    {{ $breakfast }}
                </td>

            </tr>
            <tr>
                <td class="label">Nro de noches:</td>
                <td class="input">{{ $document->number_of_nights }}</td>
                <td class="label">Tarifa por noche:</td>
                <td class="input">{{ $document->nightly_rate }}</td>
            </tr>
            <tr>
                <td class="label">Observaciones:</td>
                <td colspan="3">
                    {{ $document->observations }}
                </td>
            </tr>
            <tr>
                <td colspan="2" class="signature">
                    <span>Realizado por:</span>
                    <span class="input">{{ $document->created_by }}</span>
                </td>
                <td colspan="2" class="signature">
                    <span>Hora:</span>
                    <span class="input">{{ Carbon::parse($document->created_at)->format('H:i') }}</span>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
