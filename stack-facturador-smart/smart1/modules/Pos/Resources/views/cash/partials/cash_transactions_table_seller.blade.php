@php

    $all_documents = collect($data['all_documents'])->sortBy('order_number_key');

    $income_records = $all_documents->where('type_transaction_prefix', 'income');
    $egress_records = $all_documents->where('type_transaction_prefix', 'egress');
    $data = $income_records->toArray();
    //solo obtengo los registros unicos del array $data en base a la clave "number"
    $uniqueData = array_intersect_key($data, array_unique(array_column($data, 'number')));
    $numbers = [];
    foreach ($uniqueData as $key => $value) {
        $numbers[] = $value['number'];
    }
    $grouped = array_reduce(
        $uniqueData,
        function ($result, $item) {
            $key = isset($item['seller_id']) ? $item['seller_id'] : 'Sin vendedor';
            if (!isset($result[$key])) {
                $result[$key] = [
                    'seller_name' => $item['seller_name'] ?? 'Sin vendedor',
                    'seller_id' => $key,
                    'currency_type_id' => isset($item['currency_type_id'])
                        ? ($item['currency_type_id'] == 'PEN'
                            ? 'S/'
                            : '$')
                        : 'S/',
                    'total' => 0,
                ];
            }

            if (isset($item['total'])) {
                $result[$key]['total'] += $item['total'];
            } else {
                $result[$key]['total'] += 0; // o cualquier valor predeterminado que desees
            }
            return $result;
        },
        [],
    );

    $grouped = array_values($grouped);
    usort($grouped, function ($a, $b) {
        return $b['total'] <=> $a['total'];
    });
@endphp


@if (count($income_records) > 0)

    <p align="center" class="title">Ingresos</p>
    <table>
        <thead>
            <tr>
                <th>
                    #
                </th>
                <th>
                    Tipo transacción
                </th>
                <th>Método de pago</th>
                <th>
                    Tipo documento
                </th>
                <th>
                    Documento
                </th>
                <th>
                    Fecha emisión
                </th>
                <th>
                    Cliente/Proveedor
                </th>
                <th>
                    N° Documento
                </th>
                <th>
                    Vendedor
                </th>
                <th>
                    Moneda
                </th>
                <th>
                    T.Pagado
                </th>
                <th>
                    Total
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($income_records as $key => $value)
                <tr>
                    <td class="celda">
                        {{ $loop->iteration }}
                    </td>
                    <td class="celda">
                        {{ $value['type_transaction'] }}
                    </td>
                    <td class="celda">
                        {{ $value['payment_method_description'] }}
                        @if (isset($value['reference']))
                            <br>
                            <strong>Ref:</strong> {{ $value['reference'] }}
                        @endif
                    </td>
                    <td class="celda">
                        {{ $value['document_type_description'] }}
                    </td>
                    <td class="celda">
                        {{ $value['number'] }}
                    </td>

                    <td class="celda">
                        {{ $value['date_of_issue'] }}
                    </td>
                    <td class="celda">
                        {{ $value['customer_name'] }}
                    </td>
                    <td class="celda">
                        {{ $value['customer_number'] }}
                    </td>

                    <td class="celda">
                        @isset($value['seller_name'])
                            {{ $value['seller_name'] }}
                        @endisset
                        {{-- {{ $value['reference'] }} --}}
                    </td>
                    <td class="celda">
                        {{ isset($value['currency_type_id']) ? $value['currency_type_id'] : 'PEN'}}
                    </td>
                    <td class="celda">
                        {{ $value['total_payments'] ?? '0.00' }}
                    </td>
                    <td class="celda">
                        {{ $value['total_string'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p align="center" class="title">Resumen</p>
    <table>
        <thead>
            <tr>
                <th>Vendedor</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = 0;
            @endphp
            @foreach ($grouped as $item)
                @php
                    $total += $item['total'];
                @endphp
                <tr>
                    <td class="celda">{{ $item['seller_name'] }}</td>
                    <td class="celda">{{ isset($item['currency_type_id']) ? $item['currency_type_id'] : 'PEN'}} {{ number_format($item['total'], 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="celda">Total</td>
                <td class="celda">{{ isset($item['currency_type_id']) ? $item['currency_type_id'] : 'PEN'}} {{ number_format($total, 2) }}</td>
            </tr>
        </tbody>
    </table>
@endif




@if (count($egress_records) > 0)

    <p align="center" class="title">Egresos</p>
    <table>
        <thead>
            <tr>
                <th>
                    #
                </th>
                <th>
                    Tipo transacción
                </th>
                <th>
                    Tipo documento
                </th>
                <th>
                    Documento
                </th>
                <th>
                    Fecha emisión
                </th>
                <th>
                    Cliente/Proveedor
                </th>
                <th>
                    N° Documento
                </th>
                <th>
                    Moneda
                </th>
                <th>
                    T.Pagado
                </th>
                <th>
                    Total
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($egress_records as $key => $value)
                <tr>
                    <td class="celda">
                        {{ $loop->iteration }}
                    </td>
                    <td class="celda">
                        {{ $value['type_transaction'] }}
                    </td>
                    <td class="celda">
                        {{ $value['document_type_description'] }}
                    </td>
                    <td class="celda">
                        {{ $value['number'] }}
                    </td>
                    <td class="celda">
                        {{ $value['date_of_issue'] }}
                    </td>
                    <td class="celda">
                        {{ $value['customer_name'] }}
                    </td>
                    <td class="celda">
                        {{ $value['customer_number'] }}
                    </td>
                    <td class="celda">
                        {{ isset($value['currency_type_id']) ? $value['currency_type_id'] : 'PEN'}}
                    </td>
                    <td class="celda">
                        {{ $value['total_payments'] ?? '0.00' }}
                    </td>
                    <td class="celda">
                        @php
                            $value['total_string'] = str_replace('-', '', $value['total_string']);
                        @endphp
                        {{ $value['total_string'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endif
