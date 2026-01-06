@php
    $all_documents = collect($data['all_documents'])->sortBy('order_number_key');
    $income_records = $all_documents->where('type_transaction_prefix', 'income');
    $egress_records = $all_documents->where('type_transaction_prefix', 'egress');
    $credit_note_records = collect([]);
    if(isset($split_notes)){
        $credit_note_records = $all_documents->where('type_transaction_prefix', 'egress')
        ->where('type_transaction', 'Nota de crédito');
        $egress_records = $all_documents->where('type_transaction_prefix', 'egress')
        ->where('type_transaction', '!=', 'Nota de crédito');
    }
    $income_methods_payment = $income_records->pluck('payment_method_description')->unique();

    // Iterar sobre los métodos de pago y crear un array con los documentos que tienen ese método de pago
    $income_methods_payment = $income_methods_payment->map(function ($item, $key) use ($income_records) {
        $documents = $income_records->where('payment_method_description', $item)->values();
        if ($item == null) {
            $item = 'Otros';
        }
        return [
            'payment_method' => $item,
            'documents' => $documents,
        ];
    });
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte de Ingresos y Egresos</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-spacing: 0;
            border: 1px solid black;
        }

        .celda {
            text-align: center;
            padding: 5px;
            border: 0.1px solid black;
        }

        th {
            padding: 5px;
            text-align: center;
            border: 0.1px solid #0088cc;
        }

        .title {
            font-weight: bold;
            padding: 5px;
            font-size: 20px !important;
            text-decoration: underline;
        }

        .section-separator {
            height: 8px;
        }

        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .table-0-padding td {
            padding-left: 0.5px;
            padding-right: 0.5px;
            font-size: 10px;
        }
        .table-0-padding th {
            padding-left: 0.5px;
            padding-right: 0.5px;
            font-size: 10px;
        }
    </style>
</head>

<body>
    @if (count($income_records) > 0)
        <div class="section-separator"></div>
        <p align="center" class="title">Ingresos</p>
        <table >
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tipo</th>
                    <th>TD</th>
                    <th>DOC</th>
                    @isset($detail)
                        <th>N° Op.</th>
                    @endisset
                    <th>Fecha emisión</th>
                    <th>Cliente/Proveedor</th>
                    <th>N° Documento</th>
                    <th>MON</th>
                    <th>Pagado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($income_methods_payment as $key => $value)
                    <tr>
                        <td colspan="10">
                            <strong>{{ $value['payment_method'] }}</strong>
                        </td>
                    </tr>
                    @php
                        $total_acum = 0;
                    @endphp
                    @foreach ($value['documents'] as $document)
                        <tr>
                            <td class="celda">{{ $loop->iteration }}</td>
                            <td class="celda">
                                @if(strpos($document['type_transaction'], 'cuenta') !== false)
                                Venta (cuenta)
                                @else
                                {{ $document['type_transaction'] }}
                                @endif
                            </td>
                            <td class="celda">
                                @if ($document['document_type_description'] == '01')
                                    {{-- FACTURA ELECTRÓNICA --}}
                                    01
                                @elseif ($document['document_type_description'] == '03')
                                    {{-- BOLETA DE VENTA ELECTRÓNICA --}}
                                    03
                                @else
                                    @if ($document['document_type_description'] == 'FACTURA ELECTRÓNICA')
                                        01
                                    @elseif ($document['document_type_description'] == 'BOLETA DE VENTA ELECTRÓNICA')
                                        03
                                    @elseif ($document['document_type_description'] == 'NOTA DE VENTA')
                                        80
                                    @elseif (strpos($document['document_type_description'], 'COTIZACION') !== false)
                                        COT
                                    @else
                                        {{ $document['document_type_description'] }}
                                    @endif
                                @endif
                            </td>
                            <td class="celda">{{ $document['number'] }}</td>
                            @isset($detail)
                                <td class="celda">{{ $document['reference'] }}</td>
                            @endisset
                            <td class="celda">
                                {{ \Carbon\Carbon::parse($document['date_of_issue'])->format('d-m-Y') }}
                            </td>
                            <td class="celda">{{ $document['customer_name'] }}</td>
                            <td class="celda">{{ $document['customer_number'] }}</td>
                            <td class="celda">{{ $document['currency_type_id'] }}</td>
                            <td class="celda">{{ $document['total_payments'] ?? '0.00' }}</td>
                        </tr>
                        @php
                            $total_acum += $document['total_payments'];
                        @endphp
                    @endforeach
                    <tr class="total-row">
                        <td colspan="8" class="celda"></td>
                        <td class="celda">Total</td>
                        <td class="celda">{{ number_format($total_acum, 2, '.', '') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif



    @if (count($egress_records) > 0)
        <div class="section-separator"></div>
        <p align="center" class="title">Egresos</p>
        <table >
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tipo</th>
                    <th>TD</th>
                    <th>DOC</th>
                    <th>Fecha emisión</th>
                    <th>Cliente/Proveedor</th>
                    <th>N° Documento</th>
                    <th>MON</th>
                    <th>Pagado</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($egress_records as $key => $value)
                    <tr>
                        <td class="celda">{{ $loop->iteration }}</td>
                        <td class="celda">{{ $value['type_transaction'] }}</td>
                        <td class="celda">
                            @if ($value['document_type_description'] == '01')
                                {{-- FACTURA ELECTRÓNICA --}}
                                01
                            @elseif ($value['document_type_description'] == '03')
                                {{-- BOLETA DE VENTA ELECTRÓNICA --}}
                                03
                            @else
                                @if ($value['document_type_description'] == 'FACTURA ELECTRÓNICA')
                                    01
                                @elseif ($value['document_type_description'] == 'BOLETA DE VENTA ELECTRÓNICA')
                                    03
                                @elseif ($value['document_type_description'] == 'NOTA DE VENTA')
                                    80
                                @elseif (strpos($value['document_type_description'], 'COTIZACION') !== false)
                                    COT
                                @else
                                    {{ $value['document_type_description'] }}
                                @endif
                            @endif
                        </td>
                        <td class="celda">{{ $value['number'] }}</td>
                        <td class="celda">{{ \Carbon\Carbon::parse($value['date_of_issue'])->format('d-m-Y') }}</td>
                        <td class="celda">{{ $value['customer_name'] }}</td>
                        <td class="celda">{{ $value['customer_number'] }}</td>
                        <td class="celda">{{ $value['currency_type_id'] }}</td>
                        <td class="celda">{{ $value['total_payments'] ?? '0.00' }}</td>
                        <td class="celda">{{ str_replace('-', '', $value['total_string']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (count($credit_note_records) > 0)
    <div class="section-separator"></div>
    <p align="center" class="title">Notas de crédito</p>
    <table >
        <thead>
            <tr>
                <th>#</th>
                <th>NC</th>
                <th>F. Emisión</th>
                <th>Total</th>
                <th>FT/BV</th>
                <th>F. Emisión2</th>
                <th>Total</th>
                <th>Cliente</th>
                <th>Doc</th>
                <th>Mon</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_acum_credit = 0;
            @endphp
            @foreach ($credit_note_records as $key => $value)
                <tr>
                    <td class="celda">{{ $loop->iteration }}</td>
                    <td class="celda">{{ $value['number'] }}</td>
                    <td class="celda">{{ \Carbon\Carbon::parse($value['date_of_issue'])->format('d-m-Y') }}</td>
                    <td class="celda">{{ str_replace('-', '', $value['total']) }}</td>
                    <td class="celda">{{ $value['number_2'] }}</td>
                    <td class="celda">{{ \Carbon\Carbon::parse($value['date_of_issue_2'])->format('d-m-Y') }}</td>
                    <td class="celda">{{ $value['total_2'] }}</td>
                    <td class="celda">{{ $value['customer_name'] }}</td>
                    <td class="celda">{{ $value['customer_number'] }}</td>
                    <td class="celda">{{ $value['currency_type_id'] }}</td>
                    <td class="celda">{{ $value['total_final'] }}</td>
                    @php
                        $total_acum_credit += $value['total'];
                    @endphp
                </tr>
            @endforeach
            
        </tbody>

        <tfoot>
            <tr>
                <td colspan="5" class="celda"></td>
                <td class="celda">Total</td>
                <td class="celda">{{ number_format($total_acum_credit, 2, '.', '') }}</td>
                <td class="celda" colspan="4"></td>
            </tr>
        </tfoot>
    </table>

    @endif
</body>

</html>
