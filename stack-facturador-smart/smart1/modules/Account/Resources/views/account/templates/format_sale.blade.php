<style>
    .text-center {
        text-align: center;
    }

    .font-weight {
        font-weight: bold;
    }
</style>
@php
    $total_general = 0;
    $total_tax = 0;
    $total_value = 0;
    $col_span = 25;
    $add_state_type = $params['add_state_type'] ?? 'false' === 'true';
    $show_export_system = $show_export_system ?? false;
@endphp
<table>
    <tr>
        <td colspan="{{ $col_span }}">{{ $company['name'] }}</td>
    </tr>
    <tr>
        <td colspan="{{ $col_span }}">{{ $company['number'] }}</td>
    </tr>
    {{--
    <tr>
        <td colspan="{{ $col_span }}">Moneda: {{$currency->description}}</td>
    </tr>
    --}}
    <tr>
        <td colspan="{{ $col_span }}" class="text-center font-weight">FORMATO 14.1 : "REGISTRO DE VENTAS E INGRESOS
            DEL
            PERIODO {{ $period }}"
        </td>
    </tr>
    <tr>
        <td colspan="2">
            NUMERO CORRELATIVO DEL REGISTRO O CUO.
        </td>
        <td>
            FECHA DE EMISION DEL COMPROBANTE DE PAGO O EMISION DEL DOCUMENTO
        </td>
        <td>
            FECHA VENC.
        </td>
        <td colspan="3">
            COMPROBANTE DE PAGO
        </td>
        <td colspan="3">
            INFORMACION DE CLIENTE
        </td>
        <td>
            VALOR<br />FACTURADO<br />EXPORTACION
        </td>
        <td>
            BASE<br />IMPONIBLE<br />GRAVADA
        </td>
        <td colspan="2">
            IMPORTE TOTAL
        </td>
        <td>
            ISC
        </td>
        <td>VENTA DIFERIDA</td>
        <td>
            IGV Y/O<br />IMP.
        </td>
        <td>
            IMPUESTO AL<br />
            CONSUMO DE<br />
            LAS<br />
            BOLSAS<br />
            DE PLÁSTICO
        </td>
        <td>
            OTROS<br />TRIBUTOS
        </td>
        <td>
            IMPORTE TOTAL
        </td>
        <td>
            TIPO DE<br />CAMBIO
        </td>
        <td>
            MONEDA
        </td>
        <td colspan="4">
            REFERENCIA DEL COMPROBANTE O<br />
            DOC. ORIGINAL QUE SE MODIFICA
        </td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td></td>
        <td></td>
        <td>TIPO</td>
        <td>SERIE</td>
        <td>NUMERO</td>
        <td>TIPO</td>
        <td>R.U.C.</td>
        <td>APELLIDOS Y NOMBRES</td>
        <td></td>
        <td></td>
        <td>EXONERADA</td>
        <td>INAFECTA</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>FECHA</td>
        <td>TIPO</td>
        <td>SERIE</td>
        <td>Nro.COMP.</td>
        @if ($add_state_type)
            <td>ESTADO</td>
        @endif
        <td>OBSERVACION</td>
        @if ($add_reference)
            @if (!$show_export_system)
                <td style="width: 150px;">MET. PAGO</td>
                <td style="width: 150px;">DESTINO</td>
                <td style="width: 150px;">REFERENCIA</td>
                <td style="width: 150px;">GLOSA</td>
                <td style="width: 150px;">PAGO</td>
            @else
                <td style="width: 150px;">PRODUCTO</td>
                <td style="width: 150px;">GALONES</td>
                <td style="width: 150px;">FECHA DE PAGO</td>
                <td style="width: 150px;">METODO DE PAGO</td>
                <td style="width: 150px;">DESTINO</td>
                <td style="width: 150px;">IMPORTE DE PAGO</td>
                <td style="width: 150px;">N° OPERACION O REFERENCIA</td>
            @endif
        @endif
    </tr>
    @foreach ($records as $row)
        <tr>
            <?php
            $obs = '';
            $date_of_issue = $row['date_of_issue'];
            $document_type_id = $row['document_type_id'];
            $state_type_id = $row['state_type_id'];
            $total_exportation = 0;
            $total_taxed = 0;
            $total_exonerated = 0;
            $total_unaffected = 0;
            $total_plastic_bag_taxes = 0;
            $total_igv = 0;
            $total = 0;
            $ok = 0;
            $total_isc = 0;
            
            $obs = isset($row['observation'][0]) ? $row['observation'][0] : '';
            
            $series = $row['series'];
            $number = $row['number'];
            $customer_identity_document_type_id = $row['customer_identity_document_type_id'];
            $customer_number = $row['customer_number'];
            $customer_name = $row['customer_name'];
            $exchange_rate_sale = $row['exchange_rate_sale'];
            $currency_type_symbol = $row['currency_type_symbol'];
            if (in_array($document_type_id, ['01', '03']) && in_array($state_type_id, ['09', '11'])) {
                // do nothing
            } else {
                $total_exportation = $row['total_exportation'];
                $total_taxed = $row['total_taxed'];
                $total_exonerated = $row['total_exonerated'];
                $total_unaffected = $row['total_unaffected'];
                $total_plastic_bag_taxes = $row['total_plastic_bag_taxes'];
                $total_igv = $row['total_igv'];
                $total = $row['total'];
                $total_isc = $row['total_isc'];
                $ok = 1;
            }
            ?>
            <td>06</td>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $date_of_issue }}</td>
            <td></td>
            <td>{{ $document_type_id }}</td>
            <td>{{ $series }}</td>
            <td>{{ $number }}</td>
            <td>{{ $customer_identity_document_type_id }}</td>
            <td>{{ $customer_number }}</td>
            <td>{{ $customer_name }}</td>

            <td>{{ $total_exportation }}</td>

            <td>{{ $total_taxed }}</td>
            <td>{{ $total_exonerated }}</td>
            <td>{{ $total_unaffected }}</td>
            {{-- Aqui deberia ir $total_isc --}}
            <td>{{ $total_isc }} </td>
            <td></td>
            <td>{{ $total_igv }}</td>
            <td>{{ $total_plastic_bag_taxes }}</td>
            <td></td>
            <td>{{ $total }}</td>

            <td>{{ $exchange_rate_sale }}</td>
            <td>{{ $row['format_currency_type_id'] }}</td>
            {{-- <td>{{ $currency_type_symbol }}</td> --}}
            @if ($row['affected_document'])
                <td>{{ $row['affected_document']['date_of_issue'] }}</td>
                <td>{{ $row['affected_document']['document_type_id'] }}</td>
                <td>{{ $row['affected_document']['series'] }}</td>
                <td>{{ $row['affected_document']['number'] }}</td>
            @else
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            @endif

            @if ($add_state_type)
                <td>{{ $row['state_type_description'] }}</td>
            @endif
            <td>{{ $obs }}</td>
            @if ($add_reference)
                @if (!$show_export_system)
                    <td>
                        @foreach ($row['payments'] as $payment)
                            {{ $payment['method'] }} <br>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($row['payments'] as $payment)
                            {{ $payment['destination'] }} <br>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($row['payments'] as $payment)
                            {{ $payment['reference'] }} <br>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($row['payments'] as $payment)
                            {{ $payment['glosa'] }} <br>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($row['payments'] as $payment)
                            {{ $payment['payment'] }} <br>
                        @endforeach
                    </td>
                @else
                    <td>
                        @foreach ($row['items'] as $item)
                            {{ $item['product'] }} <br>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($row['items'] as $item)
                            {{ $item['quantity'] }} <br>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($row['payments'] as $payment)
                            {{ $payment['date_of_payment'] }} <br>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($row['payments'] as $payment)
                            {{ $payment['method'] }} <br>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($row['payments'] as $payment)
                            {{ $payment['destination'] }} <br>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($row['payments'] as $payment)
                            {{ $payment['payment'] }} <br>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($row['payments'] as $payment)
                            {{ $payment['reference'] }} <br>
                        @endforeach
                    </td>
                @endif
            @endif
            @php
                $total_tax += $total_igv;
                $total_value += $total_taxed;
                $total_general += $total;
            @endphp
        </tr>
    @endforeach
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>{{ $total_value }}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>{{ $total_tax }}</td>
        <td></td>
        <td></td>
        <td>{{ $total_general }}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
</table>
