<html>
<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        margin: 5px;
    }

    @page {
        margin: 5px;
        margin-top: 20px; /* Margen normal ya que el header está integrado en la tabla */
    }
    
    .header-fixed {
        position: fixed;
        top: -15px;
        left: -5px;
        right: 5px;
        width: 100%;
        z-index: 1000;
        padding: 10px;
        height: 50px; /* Altura fija del header */
    }
    
    .content-wrapper {
        margin-top: 0; /* Sin margen adicional ya que el header está integrado */
        position: relative;
    }

    .text-bold {
        font-weight: bold;
    }

    .text-left {
        text-align: left;
    }

    .text-end {
        text-align: right;
    }

    .float {
        float: left;
    }

    .w-100 {
        width: 100%;
    }

    .text-center {
        text-align: center;
    }

    .text-danger {
        color: red;
    }

    .v-align-top {
        vertical-align: top;
    }

    .b-b {
        border-bottom: 1px solid black;
    }

    .b-y {
        border-top: 1px solid black;
        border-bottom: 1px solid black;
    }

    .w-70 {
        width: 70%;
    }

    .w-50 {
        width: 50%;
    }

    .t-xs {
        font-size: 9px;
    }

    h4 {
        padding: 0;
        margin: 0;
    }

    .w-40 {
        width: 40%;
    }

    .w-45 {
        width: 45%;
    }

    th {
        font-size: 11px;
    }

    td {
        font-size: 10px;
        vertical-align: top;
    }

    table {
        border-collapse: collapse;
    }
    
    thead {
        display: table-header-group;
    }

    /* Asegurar que el thead se repita en cada página */
    .main-table thead {
    }
    
    /* Asegurar que el thead no aparezca debajo del header fijo */
    .table-bordered thead th {
        padding-top: 5px;
    }

    .mb-2 {
        margin-bottom: 4px;
    }

    .width-customer {
        width: 30%;

    }
</style>
@php
    $logo = $company->logo;

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

@endphp

<head>
    <title>Reporte de Caja</title>
</head>

<body>
    
    <div class="content-wrapper">
        @php
        $total_cash = array_sum(array_column($products_with_documents, 'total_cash'));
            $data_general = [
                'RECAUDACIÓN POR VENTAS EFECTIVO' => [
                    'records' => $products_with_documents,
                    'type' => 'income',
                    'total' => array_sum(array_column($products_with_documents, 'total')),
                    'total_cash' => array_sum(array_column($products_with_documents, 'total_cash')),
                ],
                // 'RECAUDACIÓN DE VENTAS EN BANCOS' => [
                //     'records' => $products_with_documents_bank,
                //     'type' => 'income',
                //     'total' => array_sum(array_column($products_with_documents_bank, 'total')),
                //     'total_bank' => array_sum(array_column($products_with_documents_bank, 'total_bank')),
                // ],
                'RECAUDACIÓN DE VENTAS EN BANCOS' => [
                    'records' => $products_with_documents_bank,
                    'type' => 'income',
                    'total' => array_sum(array_column($products_with_documents_bank, 'total')),
                    'total_cash' => 0,
                    'total_bank' => array_sum(array_column($products_with_documents_bank, 'total_payment')),
                ],
            
                'CREDITOS EMITIDOS' => [
                    'records' => $credits,
                    'type' => 'income',
                    'total' => array_sum(array_column($credits, 'total')),
                    'total_cash' => 0,
                ],
                'COBRANZA EFECTUADA EFECTIVO' => [
                    'records' => array_filter($document_credit_payment, function($item) {
                        return $item['total_cash'] > 0;
                    }),
                    'type' => 'income',
                    'total' => array_sum(array_column($document_credit_payment, 'total')),
                    'total_cash' => array_sum(array_column($document_credit_payment, 'total_cash')),
                    'total_bank' => array_sum(array_column($document_credit_payment, 'total_bank')),
                ],
                'COBRANZA EFECTUADA EN BANCOS' => [
                    'records' => array_filter($products_with_documents_bank_credit, function($item) {
                        return $item['total_bank'] > 0;
                    }),
                    'type' => 'income',
                    'total' => array_sum(array_column($products_with_documents_bank_credit, 'total')),
                    'total_cash' => 0,
                    'total_bank' => array_sum(array_column($products_with_documents_bank_credit, 'total_payment')),
                ],

                'GASTOS - EGRESOS' => [
                    'records' => $expenses,
                    'type' => 'expense',
                    'total' => array_sum(array_column($expenses, 'total')),
                    'total_cash' => 0,
                ],
                'TRANSFERENCIAS DE STOCKS' => [
                    'records' => $transfers,
                    'type' => 'income',
                    'total' => array_sum(array_column($transfers, 'total')),
                    'total_cash' => 0,
                ],
                
            ];

        @endphp
        <div class="w-100">
        <table class="table table-bordered w-100 ">
            <thead>
                <!-- Header con logo y título que se repetirá en cada página -->
                <tr>
                    <th colspan="12" style="border: none; padding: 15px; position: relative;text-align: left;">
                        <!-- Logo (33.33%) -->
                        <div style="float: left; width: 33.33%;">
                            @if ($company->logo)
                                <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                                    alt="{{ $company->name }}" style="max-height: 60px;">
                            @endif
                        </div>

                        <!-- Título centrado (33.33%) -->
                        <div style="float: left; width: 33.33%; text-align: center;">
                            <h2 style="margin: 0; font-size: 16px; font-weight: bold;">REPORTE DE CAJA DIARIA</h2>
                            @if ($establishment_description)
                                <div style="font-size: 11px; margin-top: 5px;">SUCURSAL: {{ $establishment_description }}</div>
                            @endif
                            @if ($user_name)
                                <div style="font-size: 11px;">CAJERO: {{ $user_name }}</div>
                            @endif
                            <div style="font-size: 11px;">Del: {{ $date_opening }}</div>
                        </div>

                        <!-- Espacio para paginación (33.33%) -->
                        <div style="float: left; width: 33.33%;">
                            <!-- Espacio reservado para paginación del script -->
                        </div>

                        <!-- Clear float -->
                        <div style="clear: both;"></div>
                    </th>
                </tr>
            
                <!-- Headers de columnas -->
                <tr>
                    <th class="b-y" width="7%">F. PAGO</th>
                    <th class="b-y" width="7%">F. EMISION</th>
                    <th class="b-y" width="7%">DOCUMENTO</th>
                    <th class="b-y width-customer">CLIENTE</th>
                    <th class="b-y">CANT.</th>
                    <th class="b-y">PRODUCTO</th>
                    <th class="b-y text-center">PRE.UNIT.</th>
                    <th class="b-y text-center">F.VCTO.</th>
                    <th class="b-y text-center">TOTAL</th>
                    <th class="b-y text-center">REFERENCIA</th>
                    <th class="b-y text-center">IMPORTE</th>
                    <th class="b-y">VENDEDOR</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data_general as $key => $value)
                    <tr>
                        <td colspan="12" class="text-danger">
                            {{ $key }}
                        </td>
                    </tr>

                    @foreach ($value['records'] as $doc)
                        @php
                            $count_items = count($doc['items']);
                            $first_item = [
                                'description' => '',
                                'quantity' => 0,
                                'unit_price' => 0,
                            ];
                            if ($count_items > 0) {
                                $first_item = $doc['items'][0];
                            }
                        @endphp
                        <tr>
                            <td>
                                {{ $doc['date_of_payment'] }}
                            </td>
                            <td>
                                {{ $doc['date_of_issue'] }}
                            </td>
                            <td>
                                {{ $doc['number_full'] }}
                            </td>
                            <td>
                                {{ $doc['customer_name'] }}
                            </td>
                            <td class="text-center">
                                {{ number_format($first_item['quantity'], 0) }}
                            </td>
                            <td>
                                {{ $first_item['description'] }}
                            </td>
                            <td class="text-center">
                                {{ number_format($first_item['unit_price'], 2) }}
                            </td>
                            <td class="text-center">
                                {{ $doc['date_of_due'] }}
                            </td>
                            <td class="text-center">
                                {{ number_format($doc['total'], 2) }}
                            </td>
                            <td class="text-center">
                                {{ $doc['reference'] }}
                            </td>
                            <td class="text-center">
                                {{ number_format($doc['total_payment'], 2) }}
                            </td>
                            <td>
                                {{ $doc['seller_name'] }}
                            </td>

                        </tr>
                        @if ($count_items > 1)
                            @php
                                $rest_items = $doc['items']->slice(1);
                            @endphp
                            @foreach ($rest_items as $item)
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-center">
                                        {{ number_format($item['quantity'], 0) }}
                                    </td>
                                    <td>
                                        {{ $item['description'] }}
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($item['unit_price'], 2) }}
                                    </td>
                                    <td colspan="5"></td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                    <tr>
                        <td colspan="7"></td>
                        <td class="text-bold text-danger text-center">TOTAL</td>
                        <td class="text-bold text-danger text-center">
                            {{ number_format($value['total'], 2) }}
                        </td>
                        <td></td>
                        <td class="text-bold text-danger text-center">
                            @if (isset($value['total_cash']) && $value['total_cash'] > 0)
                                {{ number_format($value['total_cash'], 2) }}
                            @elseif(isset($value['total_bank']) && $value['total_bank'] > 0)
                                {{ number_format($value['total_bank'], 2) }}
                            @else
                                0.00
                            @endif
                        </td>
                        <td></td>
                    </tr>
                @endforeach

            </tbody>
        </table>
        @php
            $total_expense = $data_general['GASTOS - EGRESOS']['total'];
            $total_sales =
                $data_general['RECAUDACIÓN POR VENTAS EFECTIVO']['total_cash'] +
                $data_general['RECAUDACIÓN DE VENTAS EN BANCOS']['total'];
            $total_credit_payment = $data_general['COBRANZA EFECTUADA EFECTIVO']['total_cash'];
            $total_credit_bank = $data_general['COBRANZA EFECTUADA EN BANCOS']['total_bank'];
            // $total_bank = $data_general['INGRESOS BANCO']['total'];
            $total_bank = 0;
            $total_debt = $total_sales + $total_credit_payment + $total_bank + $total_credit_bank + $beginning_balance;
            $total_credit = $total_expense +$data_general['RECAUDACIÓN DE VENTAS EN BANCOS']['total'] + $total_credit_bank;

        @endphp
        <table class="w-40">
            <tr>
                <td width="45%">
                    <span class="text-danger">
                        DEBE:
                    </span>
                </td>
                <td width="10%"></td>
                <td width="45%">
                    <span class="text-danger">
                        HABER:
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <table width="100%">
                        <tr>
                            <td class="t-xs">SALDO INICIAL</td>
                            <td class="t-xs text-end">{{ number_format($beginning_balance, 2) }}</td>
                        </tr>
                    </table>
                </td>
                <td></td>
                <td>
                    <table width="100%">
                        <tr>
                            <td class="t-xs">TOTAL GASTO EG</td>
                            <td class="t-xs text-end">
                                {{ number_format($data_general['GASTOS - EGRESOS']['total'], 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <table width="100%">
                        <tr>
                            <td class="t-xs">TOTAL VT. EF:</td>
                            <td class="t-xs text-end">
                                {{ number_format($total_cash, 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td></td>
                <td>
                    <table width="100%">
                        <tr>
                            <td class="t-xs">TOTAL VT. BANCO</td>
                            <td class="t-xs text-end">
                                {{ number_format($data_general['RECAUDACIÓN DE VENTAS EN BANCOS']['total_bank'], 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table width="100%">
                        <tr>
                            <td class="t-xs">TOTAL VT. BANCO:</td>
                            <td class="t-xs text-end">
                                {{ number_format($data_general['RECAUDACIÓN DE VENTAS EN BANCOS']['total_bank'], 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td></td>
                <td>
                    <table width="100%">
                        <tr>
                            <td class="t-xs">
                                TOTAL COBRANZA BANCO
                            </td>
                            <td class="t-xs text-end">
                                {{ number_format($data_general['COBRANZA EFECTUADA EN BANCOS']['total_bank'], 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        
            <tr>
                <td>
                    <table width="100%">
                        <tr>
                            <td class="t-xs">
                                TOTAL COBRANZA EF
                            </td>
                            <td class="t-xs text-end">
                                {{ number_format($data_general['COBRANZA EFECTUADA EFECTIVO']['total_cash'], 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td></td>
                <td class="">
                    <table width="100%">
                        <tr>
                            <td class="t-xs"></td>
                            <td class="t-xs text-end"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="b-b">
                    <table width="100%">
                        <tr>
                            <td class="t-xs">
                                TOTAL COBRANZA BANCO
                            </td>
                            <td class="t-xs text-end">
                                {{ number_format($data_general['COBRANZA EFECTUADA EN BANCOS']['total_bank'], 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td></td>
                <td class="b-b">
                    <table width="100%">
                        <tr>
                            <td class="t-xs"></td>
                            <td class="t-xs text-end"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table width="100%">
                        <tr>
                            <td class="t-xs">TOTAL</td>
                            <td class="t-xs text-end">
                                {{ number_format($total_debt, 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td></td>
                <td>
                    <table width="100%">
                        <tr>
                            <td class="t-xs">TOTAL</td>
                            <td class="t-xs text-end">
                                {{ number_format($total_credit, 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <table width="100%">
                        <tr>
                            <td class="t-xs">DEFICIT</td>
                            <td class="t-xs text-end">
                                @if ($total_debt < $total_credit)
                                    -{{ number_format($total_credit - $total_debt, 2) }}
                                @else
                                    0.00
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td></td>
                <td>
                    <table width="100%">
                        <tr>
                            <td class="t-xs">SALDO FINAL</td>
                            <td class="t-xs text-end">
                                {{ number_format($total_debt - $total_credit, 2) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        </table>
        </div>
    </div> <!-- Cierre del content-wrapper -->

    <script type="text/php">
        if (isset($pdf)) {
            $date = date('d/m/Y');
            $time = date('H:i:s');
            $width = $pdf->get_width();
            $height = $pdf->get_height();
            
            // FOOTER - información de página (esquina superior derecha) - Sin color para evitar errores
            $pdf->page_text($width - 85, 10, "Página {PAGE_NUM}", null, 8);
            $pdf->page_text($width - 85, 20, "Fecha: $date", null, 8);
            $pdf->page_text($width - 85, 30, "Hora: $time", null, 8);
            
            // FOOTER - información de página (parte inferior)
            $pdf->page_text($width - 100, $height - 12, "Página N°{PAGE_NUM} de {PAGE_COUNT}", null, 8);
            
        }
    </script>
</body>


</html>
