<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<style>
    @page :first {
        margin-top: 5px;
    }

    @page :not(:first) {
        margin-top: 150px;
    }

    html {
        font-family: sans-serif;
        font-size: 12px;
    }

    h4,
    h3 {
        margin: 0;
        padding: 0;
    }

    .text-center {
        text-align: center;
    }

    .mt-2 {
        margin-top: 2px;
    }

    .mt-5 {
        margin-top: 5px;
    }

    .mt-10 {
        margin-top: 10px;
    }

    .mb-5 {
        margin-bottom: 5px;
    }

    .w-100 {
        width: 100%;
    }

    .text-end {
        text-align: right;
    }

    .text-left {
        text-align: left;
    }

    .text-red {
        color: red;
    }

    .text-blue {
        color: blue;
    }

    .border-dotted {
        border-bottom: 1px dotted black;
    }

    .border-top-dashed {
        border-top: 1px dashed black;
    }

    .border-top {
        border-top: 1px solid black;
    }
</style>

<body>
    <div>
        <h3 align="center" class="title"><strong>RELACIÓN DE VENTAS</strong></h3>
    </div>
    <div class="text-center mt-10">
        <h4>
            {{ $company->name }}
        </h4>
    </div>
    @if ($establishment)
        <div class="text-center mt-5">
            <h4>
                {{ $establishment->description }}
            </h4>
        </div>
    @endif
    <table class="w-100 mt-5">
        <tbody>
            <tr>
                <td width="50%">
                    Del {{ $period['d_start'] }} al {{ $period['d_end'] }}
                </td>
                <td width="50%" class="text-end">
                    Expresado en S/
                </td>
            </tr>
        </tbody>
    </table>
    @php
        $total_general = 0;
        $total_general_paid = 0;
        $total_general_pending = 0;
    @endphp

    <!-- TABLA ÚNICA PARA TODO EL REPORTE -->
    <table class="w-100 mt-2" style="border-collapse: collapse;">
        <thead>
            <tr>
                <th width="120px" class="border-top">DOCUMENTO</th>
                <th class="border-top">FECHA</th>
                <th class="border-top">VENDEDOR</th>
                <th class="border-top" colspan="8"></th>
            </tr>
            <tr>
                <th style="border-bottom: 1px solid;font-size:12px"></th>
                <th style="border-bottom: 1px solid;font-size:12px">Código</th>
                <th style="border-bottom: 1px solid;font-size:12px" colspan="2">Descripción</th>
                <th style="border-bottom: 1px solid;font-size:12px">Und</th>
                <th style="border-bottom: 1px solid;font-size:12px">Cant.</th>
                <th style="border-bottom: 1px solid;font-size:12px">P.Venta</th>
                <th style="border-bottom: 1px solid;font-size:12px">TOTAL</th>
                <th style="border-bottom: 1px solid;font-size:12px">A cuenta:</th>
                <th style="border-bottom: 1px solid;font-size:12px">Importe</th>
                <th style="border-bottom: 1px solid;font-size:12px;width:70px;">x Cobrar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($formattedRecords as $idx => $clientData)
                <tr>
                    <td colspan="7" class="text-left text-blue">
                        CLIENTE: {{ $clientData['client_name'] }}
                    </td>
                    <td colspan="4" class="text-left text-blue">
                        CONTACTO: {{ $clientData['client_contact_name'] }}
                    </td>
                </tr>
                <tr>
                    <td colspan="7" class="text-left text-blue">
                        DIRECCIÓN: {{ $clientData['client_address'] }}
                    </td>
                    <td colspan="4" class="text-left text-blue">
                        TELEFONO: {{ $clientData['client_contact_phone'] }}
                    </td>
                </tr>
                @foreach ($clientData['records_by_date'] as $dateData)
                    <tr>
                        <td colspan="11" style=" font-weight: bold; " class="text-red">
                            {{ $dateData['date'] }}
                        </td>
                    </tr>
                    @php
                        $total_payment = 0;
                        $total = 0;
                    @endphp
                    @foreach ($dateData['records'] as $record)
                        <tr>
                            <td class="text-left">
                                {{ $record->number }}
                            </td>
                            <td>{{ $record->date_of_issue }}</td>
                            <td>
                                {{ $record->seller_name }}
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        @php
                            $items = $record->items;
                            $payments = $record->payments;

                            $total_general += floatval($record->total ?? 0);
                            $total_general_paid += floatval($record->total_paid ?? 0);
                            $total_general_pending += floatval($record->total_pending ?? 0);
                        @endphp
                        @foreach ($items as $item)
                            <tr>
                                <td></td>
                                <td>
                                    {{ $item['internal_id'] }}
                                </td>
                                <td colspan="2">{{ $item['description'] }}</td>
                                <td>{{ $item['unit_type_id'] }}</td>
                                <td class="text-center">{{ number_format($item['quantity'], 0) }}</td>
                                <td class="text-center">{{ number_format($item['unit_price'], 2) }}</td>
                                <td class="text-center">{{ number_format($item['total'], 2) ?? '' }}</td>
                                <td colspan="3"></td>
                            </tr>
                        @endforeach
                        @foreach ($payments as $payment)
                            @php
                                $payment_date = \Carbon\Carbon::parse($payment['date_of_payment'])->format(
                                    'd/m/Y',
                                );
                            @endphp
                            <tr>
                                <td colspan="5"></td>
                                <td class="text-red">-CANCELACION {{ $payment_date }}</td>
                                <td class="text-red" colspan="2">
                                    {{ $payment['payment_method_type']['description'] }}</td>
                                <td class="text-red">{{ $payment_date }}</td>
                                <td class="text-red text-center">{{ number_format($payment['payment'], 2) }}
                                </td>
                                <td></td>
                            </tr>
                        @endforeach

                        <!-- Total por documento individual -->
                        <tr style=" font-weight: bold;">
                            <td colspan="6" style="text-align: right;">

                            </td>
                            <td class="border-top-dashed"></td>
                            <td class="border-top-dashed text-center">{{ $record->formatted_total ?? '' }}</td>
                            <td class="border-top-dashed"></td>
                            <td class="border-top-dashed text-center">{{ $record->formatted_total_paid ?? '' }}
                            </td>
                            <td class="border-top-dashed text-center">
                                {{ $record->formatted_total_pending ?? '' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach

            <!-- TOTAL GENERAL DEL DOCUMENTO -->
            <tr style="font-weight: bold; font-size: 13px;">
                <td colspan="6" style="text-align: right; padding-top: 10px;">
                    TOTAL GENERAL:
                </td>
                <td class="border-top" style="padding-top: 10px;"></td>
                <td class="border-top text-center" style="padding-top: 10px;">
                    {{ number_format($total_general, 2) }}
                </td>
                <td class="border-top" style="padding-top: 10px;"></td>
                <td class="border-top text-center" style="padding-top: 10px;">
                    {{ number_format($total_general_paid, 2) }}
                </td>
                <td class="border-top text-center" style="width: 70px; padding-top: 10px;">
                    {{ number_format($total_general_pending, 2) }}
                </td>
            </tr>
        </tbody>
    </table>
    
    <script type="text/php">
            if (isset($pdf)) {
                $date = date('d/m/Y');
                $time = date('H:i:s');
                $pdf->page_text($pdf->get_width() - 75, 10, "Página {PAGE_NUM}", null, 7);
                $pdf->page_text($pdf->get_width() - 75, 16, "Fecha: $date", null, 7);
                $pdf->page_text($pdf->get_width() - 75, 22, "Hora: $time", null, 7);    
                $pdf->page_text($pdf->get_width() - 75, $pdf->get_height() - 20, "Página N°{PAGE_NUM} de {PAGE_COUNT}", null, 7);
                
            }
        </script>
</body>

</html>
