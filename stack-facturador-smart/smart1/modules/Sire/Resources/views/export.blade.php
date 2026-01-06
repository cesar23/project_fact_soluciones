<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>SIRE - {{ $type == 'sale' ? 'VENTAS' : 'COMPRAS' }}</title>
    <style>
        .text-center {
            text-align: center;
        }

        .text-danger {
            color: red;
        }

        .text-strong {
            font-weight: bold;
        }

        .text-end {
            text-align: end;
        }

        .text-left {
            text-align: left;
        }
    </style>

</head>

<body>

    <h4>SIRE - - {{ $type == 'sale' ? 'VENTAS' : 'COMPRAS' }}</h4>
    {{-- fecha --}}
    @php
        $date = date('d-m-Y');
        
    @endphp
    <h5>Fecha: {{ $date }}</h5>
    {{-- tabla --}}
    <table>
        <thead>
            <tr>
                <th>Servicio</th>
                <th>F. Emisión</th>
                <th>
                    @if ($type == 'sale')
                        Cliente
                    @else
                        Proveedor
                    @endif

                </th>
                <th>Tipo Documento</th>
                <th>Serie</th>
                <th>Número</th>
                <th>Total</th>

            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
                @php
                    $label = array_key_exists('label', $record) ? $record['label'] : '';
                    $class = '';
                    $style = '';
                    if ($label == 'DANGER') {
                        $style = 'color: red;';
                        $class = 'text-danger';
                    } elseif ($label == 'STRONG') {
                        $style = 'font-weight: bold;';
                        $class = 'text-strong';
                    }
                @endphp

                <tr>
                    <td style="{{ $style }}" {{-- class="{{ ($record["label"] == 'DANGER' ? 'text-danger' : $record["label"] == 'STRONG') ? 'text-strong' : '' }} text-center" --}}>
                        {{ $record['service'] }}
                    </td>
                    <td style="{{ $style }}" {{-- class="{{ ($record["label"] == 'DANGER' ? 'text-danger' : $record["label"] == 'STRONG') ? 'text-strong' : '' }} text-center" --}} {{-- class="{{ ($record["label"] == 'DANGER' ? 'text-danger' : $record["label"] == 'STRONG') ? 'text-strong' : '' }} text-center" --}}>
                        {{ $record['date'] }}
                    </td>
                    <td style="{{ $style }}" {{-- class="{{ ($record["label"] == 'DANGER' ? 'text-danger' : $record["label"] == 'STRONG') ? 'text-strong' : '' }} text-left" --}}>
                        {{ $record['name_company'] }}
                        <br />
                        <small>{{ $record['number_company'] }}</small>
                    </td>
                    <td style="{{ $style }}" {{-- class="{{ ($record["label"] == 'DANGER' ? 'text-danger' : $record["label"] == 'STRONG') ? 'text-strong' : '' }} text-center" --}}>
                        {{ $record['document_type'] }}
                    </td>
                    <td style="{{ $style }}" {{-- class="{{ ($record["label"] == 'DANGER' ? 'text-danger' : $record["label"] == 'STRONG') ? 'text-strong' : '' }} text-center" --}} {{-- class="{{ ($record["label"] == 'DANGER' ? 'text-danger' : $record["label"] == 'STRONG') ? 'text-strong' : '' }} text-center" --}}>
                        {{ $record['serie'] }}
                    </td>
                    <td style="{{ $style }}" {{-- class="{{ ($record["label"] == 'DANGER' ? 'text-danger' : $record["label"] == 'STRONG') ? 'text-strong' : '' }} text-center" --}}>
                        {{ $record['number'] }}
                    </td>
                    <td style="{{ $style }}" {{-- class="{{ ($record["label"] == 'DANGER' ? 'text-danger' : $record["label"] == 'STRONG') ? 'text-strong' : '' }} text-end" --}}>
                        {{ $record['total'] }}
                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>
</body>

</html>
