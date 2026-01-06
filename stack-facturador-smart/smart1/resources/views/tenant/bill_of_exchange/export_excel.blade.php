<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Letras de Cambio</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 5px;
        }

        th {
            background-color: #f8f9fa;
        }

        @page {
            size: auto;
            margin: 5px;
        }

        .text-danger {
            color: red;
        }
    </style>
</head>

<body>
    <h2 class="text-center">{{ $company->name }}</h2>
    <h3 class="text-center">Letras por cobrar por vencimiento</h3>
    <p>Fecha de emisión: {{ $date }}</p>
    @php
        $now = \Carbon\Carbon::now();
    @endphp


    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Doc Relacionado</th>
                <th>Serie-Número</th>
                <th>N° Unico</th>
                <th class="text-center">Fecha Emisión</th>
                <th class="text-center">Fecha Vencimiento</th>
                <th class="text-center">Moneda</th>
                <th class="text-right">Total</th>
                <th class="text-right">Cobrado</th>
                <th class="text-right">Saldo</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Días de atraso</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $row)
                @php
                    $doc_related = $row->items->first();
                    $payment = $row->payments->sum('payment');
                    $date_of_due = \Carbon\Carbon::parse($row->date_of_due);
                    $days_of_delay = 0;
                    if ($date_of_due < $now) {
                        $days_of_delay = $now->diffInDays($date_of_due);
                    }
                    $total = $row->total;
                    $number_full = null;
                    if ($doc_related) {
                        $number_full = $doc_related->document->series . '-' . $doc_related->document->number;
                    }

                @endphp
                <tr>
                    <td class="text-center {{ $days_of_delay > 0 ? 'text-danger' : '' }}"
                        style="color: {{ $days_of_delay > 0 ? 'red' : 'black' }}">{{ $row->customer->name }}
                    </td>
                    <td class="text-center {{ $days_of_delay > 0 ? 'text-danger' : '' }}"
                        style="color: {{ $days_of_delay > 0 ? 'red' : 'black' }}">
                        @foreach ($row->items as $doc)
                            {{ $doc->document->series }}-{{ $doc->document->number }}{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </td>
                    <td class="text-center {{ $days_of_delay > 0 ? 'text-danger' : '' }}"
                        style="color: {{ $days_of_delay > 0 ? 'red' : 'black' }}">
                        {{ $row->series }}-{{ $row->number }}
                    </td>
                    <td class="text-center {{ $days_of_delay > 0 ? 'text-danger' : '' }}"
                        style="color: {{ $days_of_delay > 0 ? 'red' : 'black' }}">
                        {{ $row->code }}
                    </td>
                    <td class="text-center {{ $days_of_delay > 0 ? 'text-danger' : '' }}"
                        style="color: {{ $days_of_delay > 0 ? 'red' : 'black' }}">
                        {{ $row->created_at ? $row->created_at->format('d/m/Y') : '' }}
                    </td>
                    <td class="text-center {{ $days_of_delay > 0 ? 'text-danger' : '' }}"
                        style="color: {{ $days_of_delay > 0 ? 'red' : 'black' }}">
                        {{ $row->date_of_due ? $row->date_of_due->format('d/m/Y') : '' }}
                    </td>
                    <td class="text-center {{ $days_of_delay > 0 ? 'text-danger' : '' }}"
                        style="color: {{ $days_of_delay > 0 ? 'red' : 'black' }}">
                        {{ $row->currency_type->id }}
                    </td>
                    <td class="text-right {{ $days_of_delay > 0 ? 'text-danger' : '' }}"
                        style="color: {{ $days_of_delay > 0 ? 'red' : 'black' }}">
                        {{ number_format($row->total, 2) }}
                    </td>
                    <td class="text-right {{ $days_of_delay > 0 ? 'text-danger' : '' }}"
                        style="color: {{ $days_of_delay > 0 ? 'red' : 'black' }}">
                        {{ number_format($payment, 2) }}
                    </td>
                    <td class="text-right {{ $days_of_delay > 0 ? 'text-danger' : '' }}"
                        style="color: {{ $days_of_delay > 0 ? 'red' : 'black' }}">
                        {{ number_format($total - $payment, 2) }}
                    </td>
                    <td class="text-center {{ $days_of_delay > 0 ? 'text-danger' : '' }}"
                        style="color: {{ $days_of_delay > 0 ? 'red' : 'black' }}">
                        {{ $row->total_canceled ? 'Pagado' : 'Pendiente' }}
                    </td>
                    <td class="text-center {{ $days_of_delay > 0 ? 'text-danger' : '' }}"
                        style="color: {{ $days_of_delay > 0 ? 'red' : 'black' }}">
                        {{ $days_of_delay }}
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
