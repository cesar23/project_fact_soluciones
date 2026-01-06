@php

    $all_documents = collect($data['all_documents'])->sortBy('order_number_key');

    $income_records = $all_documents->where('type_transaction_prefix', 'income');
    $egress_records = $all_documents->where('type_transaction_prefix', 'egress');

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
    @if (count($income_records) > 0)
    <th>
        Referencia
    </th>
    @endif
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
                @if (count($income_records) > 0)
                
                <td class="celda">
                    {{$value['reference']}}
                </td>
            @endisset
                <td class="celda">
                    {{ $value['currency_type_id'] }}
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
                            {{ $value['currency_type_id'] }}
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
