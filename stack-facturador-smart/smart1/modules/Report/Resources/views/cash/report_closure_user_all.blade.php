<!DOCTYPE html>
<html>

<head>
    <title>Reporte General de Ventas por Cajas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #00b0f0;
            color: white;
            text-align: center;
            padding: 5px 0;
            font-weight: bold;
            font-size: 14px;
        }

        .subheader {
            background-color: #c6e2ff;
            color: #000;
            text-align: center;
            padding: 3px 0;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td {
            border: 1px solid #000;
            padding: 4px 6px;
            overflow: hidden;
        }

        .date {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            font-weight: bold;
        }

        .page-break {
            page-break-after: always;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Cabecera principal -->
    <div class="header">REPORTE GENERAL DE VENTAS POR CAJAS</div>
    <div style="text-align: center; margin: 10px 0;">
        <strong>Periodo:</strong> {{ $date_start }} al {{ $date_end }}
    </div>

    @foreach ($all_combined_data as $index => $data)
        <!-- Subtítulo para cada usuario -->
        <div class="subheader">REPORTE DE CAJA: {{ $data['cash_user_name'] }}</div>

        <!-- Tabla principal -->
        <table>
            <tr>
                <td colspan="2"></td>
                <td class="total-row">Fecha de Inicio</td>
                <td class="total-row">Fecha de final</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td class="total-row">USUARIO</td>
                <td>{{ $data['cash_user_name'] }}</td>
                <td class="date">{{ $data['cash_general_info']['date_start'] }}</td>
                <td class="date">{{ $data['cash_general_info']['date_end'] }}</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td class="total-row">INFORMACION</td>
                <td></td>
                <td class="total-row">MONTOS DE OPERACION</td>
                <td></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td class="total-row" width="16.6%">Empresa:</td>
                <td width="16.6%">{{ $company_name }}</td>
                <td class="total-row" width="16.6%">Saldo inicial:</td>
                <td width="16.6%" class="text-right">{{ $data['cash_beginning_balance'] }}</td>
                <td class="total-row" width="16.6%">Por cobrar:</td>
                <td width="16.6%" class="text-right">{{ $data['document_credit_total'] }}</td>
            </tr>
            <tr>
                <td class="total-row">Ruc:</td>
                <td>{{ $company_number }}</td>
                <td class="total-row">Ingreso efectivo:</td>
                <td class="text-right">{{ $data['total_cash_efectivo'] }}</td>
                <td class="total-row">Notas de Débito:</td>
                <td class="text-right">{{ $data['nota_debito'] }}</td>
            </tr>
            <tr>
                <td class="total-row">Establecimiento:</td>
                <td>{{ $establishment_description }}</td>
                <td class="total-row">Egreso efectivo:</td>
                <td class="text-right">{{ $data['cash_egress'] }}</td>
                <td class="total-row">Notas de Crédito:</td>
                <td class="text-right">{{ $data['nota_credito'] }}</td>
            </tr>
            <tr>
                <td class="total-row">Usuario:</td>
                <td>{{ $data['cash_user_name'] }}</td>
                <td class="total-row">Total efectivo:</td>
                <td class="text-right">{{ $data['total_cash_efectivo'] - $data['cash_egress'] + $data['cash_beginning_balance'] }}</td>
                <td class="total-row">Total propinas:</td>
                <td class="text-right">{{ $data['total_tips'] }}</td>
            </tr>
            <tr>
                <td class="total-row">Estado de caja:</td>
                <td></td>
                <td class="total-row">Billetera digital:</td>
                <td class="text-right">{{ $data['total_virtual'] }}</td>
                <td class="total-row">Total efectivo CPE:</td>
                <td class="text-right">{{ $data['cpe_total_cash'] }}</td>
            </tr>
            <tr>
                <td class="total-row">Fecha y hora apertura:</td>
                <td>{{ $data['cash_general_info']['date_start'] }} {{ $data['cash_general_info']['time_start'] }}</td>
                <td class="total-row">Dinero en bancos:</td>
                <td class="text-right">{{ $data['total_bank'] }}</td>
                <td class="total-row">Total efectivo NOTA DE VENTA:</td>
                <td class="text-right">{{ $data['sale_notes_total_cash'] }}</td>
            </tr>
            <tr>
                <td class="total-row">Fecha y hora cierre:</td>
                <td>{{ $data['cash_general_info']['date_end'] }} {{ $data['cash_general_info']['time_end'] }}</td>
                <td class="total-row">Ingreso total:</td>
                <td class="text-right">{{ $data['cash_income'] }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td class="total-row">Saldo final:</td>
                <td class="text-right">{{ $data['cash_income'] + $data['cash_beginning_balance'] - $data['cash_egress'] }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="text-center total-row">DESCRIPCION</td>
                <td class="text-center total-row" colspan="2">SUMA DE INGRESO A CAJA y BANCOS</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

            @php
                $rightData = [
                    ['title' => 'VENTAS A CREDITO', 'value' => $data['document_credit_total']],
                    ['title' => 'VENTAS AL CONTADO', 'value' => $data['cash_income'] ],
                    ['title' => 'EGRESO GENERAL', 'value' => $data['cash_egress']],
                    ['title' => 'TOTAL', 'value' => $data['cash_income'] - $data['cash_egress'], 'is_total' => true],
                ];
                $payment_methods = collect($data['methods_payment'])
                    ->sortByDesc('sum')
                    ->values()
                    ->all();
            @endphp

            @foreach ($payment_methods as $key => $method)
                <tr>
                    <td class="total-row">{{ strtoupper($method['name']) }}:</td>
                    <td>S/</td>
                    <td class="text-right">{{ number_format((float) str_replace(',', '', $method['sum']), 2) }}</td>

                    @if ($key < 4)
                        <td class="total-row">{{ $rightData[$key]['title'] }}</td>
                        <td @if (isset($rightData[$key]['is_total'])) class="total-row" @endif>S/</td>
                        <td @if (isset($rightData[$key]['is_total'])) class="total-row" @endif class="text-right">
                            {{ number_format((float) str_replace(',', '', $rightData[$key]['value']), 2) }}
                        </td>
                    @else
                        <td></td>
                        <td></td>
                        <td></td>
                    @endif
                </tr>
            @endforeach

            @if (count($payment_methods) < 4)
                @for ($i = count($payment_methods); $i < 4; $i++)
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="total-row">{{ $rightData[$i]['title'] }}</td>
                        <td @if (isset($rightData[$i]['is_total'])) class="total-row" @endif>S/</td>
                        <td @if (isset($rightData[$i]['is_total'])) class="total-row" @endif class="text-right">
                            {{ number_format((float) str_replace(',', '', $rightData[$i]['value']), 2) }}
                        </td>
                    </tr>
                @endfor
            @endif

            {{-- @foreach ($payment_methods as $key => $method)
                @if ($key >= 4)
                    <tr>
                        <td class="total-row">{{ strtoupper($method['name']) }}:</td>
                        <td>S/</td>
                        <td class="text-right">{{ number_format((float) str_replace(',', '', $method['sum']), 2) }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endif
            @endforeach --}}

            <!-- Totales -->
            <tr>
                <td class="total-row">SUMA TOTAL</td>
                <td class="total-row">S/</td>
                <td class="text-right total-row">
                    {{ number_format((float) str_replace(',', '', $data['cash_income']), 2) }}
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <!-- Agregar salto de página entre reportes excepto el último -->
        @if ($index < count($all_combined_data) - 1)
            <div class="page-break"></div>
        @endif
    @endforeach

    <!-- Página nueva para el resumen consolidado -->
    <div class="page-break"></div>

    <!-- SUMA TOTAL DE VENTAS -->
    <div class="header" style="background-color: #00bfff;">SUMA TOTAL DE VENTAS</div>

    <table style="margin-bottom: 10px;">
        <tr>
            <td width="25%">Fecha de Inicio</td>
            <td width="25%">Fecha de final</td>
            <td width="50%"></td>
        </tr>
        <tr>
            <td class="date">{{ $date_start }}</td>
            <td class="date">{{ $date_end }}</td>
            <td></td>
        </tr>
    </table>

    <!-- Preparar datos -->
    @php
        // Métodos de pago
        $payment_method_totals = [];
        $grand_total = 0;

        foreach ($all_combined_data as $data) {
            foreach ($data['methods_payment'] as $method) {
                $method_name = strtoupper($method['name']);
                $amount = (float) str_replace(',', '', $method['sum']);

                if (!isset($payment_method_totals[$method_name])) {
                    $payment_method_totals[$method_name] = 0;
                }

                $payment_method_totals[$method_name] += $amount;
                $grand_total += $amount;
            }
        }

        // Diagnóstico: verificar los documentos disponibles
        $has_documents = false;
        $documents_count = 0;
        $sellers_count = 0;
        
    

        // Recopilación de todos los vendedores
        $all_sellers = collect();
        
        // Crear un registro de documentos procesados para evitar duplicados
        $processed_document_keys = [];
        
        $tmp_dtd = [];

        
        // Consultar nombres de vendedores
    
        // Agrupar por vendedor
        $vendors = [];
        $total_all_users = 0;
        $total_credit_all = 0;
        $total_cash_all = 0;
        $total_sale_notes_all = 0;
        $total_documents_all = 0;
        $total_gain_items_all = 0;
        $total_comission_all = 0;
        
        $all_cpe_total = 0;
        $all_sale_notes_total = 0;
    
    

        foreach ($all_combined_data as $data) {
            $all_cpe_total += $data['cpe_amount'];
            $all_sale_notes_total += $data['sale_note_amount'];
            foreach ($data['sellers'] as $seller) {

            $name = $seller['name'];
            $id = $seller['id'];
            
            
            $vendor_exists = false;
            foreach($vendors as &$vendor) {
                if($vendor['id'] == $id) {
                    $vendor['total'] += $seller['payment'];
                    $vendor['gain_items'] += $seller['gain']; 
                    $vendor['comission'] += $seller['comission'];
                    $vendor_exists = true;
                    break;
                }
            }
            
            if(!$vendor_exists) {
                $vendors[] = [
                    'id' => $id,
                    'name' => $name, 
                    'total' => $seller['payment'],
                    'credit' => 0,
                    'gain_items' => $seller['gain'],
                    'comission' => $seller['comission'],
                ];
            }
        
            
            $total_all_users += $seller['payment'];
            $total_credit_all += 0;
            $total_gain_items_all += $seller['gain'];
            $total_comission_all += $seller['comission'];
        }
    }
        // Determinar cuál array es más largo
        $payment_count = count($payment_method_totals);
        $vendor_count = count($vendors);
        $max_rows = max($payment_count, $vendor_count) + 1; // +1 para la fila de totales
    @endphp


    <!-- Tabla combinada -->
    <table>
        <tr>
            <td colspan="3" class="bold text-center">SUMA DE INGRESOS A CAJA y BANCOS</td>
            <td colspan="5" class="bold text-center">INFORMACIÓN DE VENDEDORES</td>
        </tr>
        <tr>
            <td class="bold">DESCRIPCION</td>
            <td>S/</td>
            <td class="bold">MONTO</td>
            <td></td>
            <td></td>
            <td class="bold" width="15%">VENDEDOR</td>
            <td class="bold">TOTAL</td>
            <td class="bold">CRÉDITO</td>
            <td class="bold">COMISIÓN</td>
            <td class="bold">GANANCIA</td>
        </tr>

        @for ($i = 0; $i < $max_rows - 1; $i++)
            <tr>
                @if ($i < $payment_count)
                    @php
                        $method_name = array_keys($payment_method_totals)[$i];
                        $method_total = $payment_method_totals[$method_name];
                    @endphp
                    <td>{{ $method_name }}:</td>
                    <td>S/</td>
                    <td class="text-right">{{ number_format($method_total, 2) }}</td>
                    <td></td>
                    <td></td>
                @else
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endif

                @if ($i < $vendor_count)
                    <td>{{ $vendors[$i]['name'] }}</td>
                    <td class="text-right">S/ {{ number_format($vendors[$i]['total'], 2) }}</td>
                    <td class="text-right">S/ {{ number_format($vendors[$i]['credit'], 2) }}</td>
                    <td class="text-right">S/ {{ number_format($vendors[$i]['comission'], 2) }}</td>
                    <td class="text-right">S/ {{ number_format($vendors[$i]['gain_items'], 2) }}</td>
                @else
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endif
            </tr>
        @endfor

        <!-- Fila de totales -->
        <tr>
            <td>SUMA TOTAL</td>
            <td>S/</td>
            <td class="text-right">{{ number_format($grand_total, 2) }}</td>
            <td></td>
            <td></td>
            <td>TOTAL</td>
            <td class="text-right">S/ {{ number_format($total_all_users, 2) }}</td>
            <td class="text-right">S/ {{ number_format($total_credit_all, 2) }}</td>
            <td class="text-right">S/ {{ number_format($total_comission_all, 2) }}</td>
            <td class="text-right">S/ {{ number_format($total_gain_items_all, 2) }}</td>
        </tr>
    </table>

    <!-- INFORMACIÓN GENERAL -->
    <div class="header" style="margin-top: 20px;">INFORMACIÓN GENERAL</div>

    <table>
        <tr>
            <td style="font-weight: bold; width: 25%;">VENTAS (CPE)</td>
            <td style="width: 5%;">S/</td>
            <td style="width: 12%;" class="text-right">
                @php
                    $total_cpe = 0;
                    $total_notes = 0;
                    $total_quotations = 0;
                    foreach ($all_combined_data as $data) {
                        $total_cpe += isset($data['cpe_total']) ? (float) str_replace(',', '', $data['cpe_total']) : 0;
                        $total_notes += isset($data['sale_notes_total'])
                            ? (float) str_replace(',', '', $data['sale_notes_total'])
                            : 0;
                        $total_quotations += isset($data['quotations_total'])
                            ? (float) str_replace(',', '', $data['quotations_total'])
                            : 0;
                    }
                @endphp
                {{ number_format($total_cpe, 2) }}
            </td>
            <td style="width: 8%;"></td>
            <td style="font-weight: bold; width: 25%;">INGRESOS TOTALES</td>
            <td style="width: 5%;">S/</td>
            <td style="width: 20%;" class="text-right">{{ number_format($grand_total, 2) }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;   ">VENTAS TOTALES NOTA DE VENTA</td>
            <td>S/</td>
            <td class="text-right">{{ number_format($total_notes, 2) }}</td>
            <td></td>
            <td style="font-weight: bold; ">EGRESO TOTALES</td>
            <td>S/</td>
            <td class="text-right">
                @php
                    $total_egress = 0;
                    foreach ($all_combined_data as $data) {
                        $total_egress += (float) str_replace(',', '', $data['cash_egress']);
                    }
                @endphp
                {{ number_format($total_egress, 2) }}
            </td>
        </tr>
        <tr>
            <td
             colspan="3"></td>
            {{-- <td style="font-weight: bold;">COTIZACIONES</td>
            <td>S/</td>
            <td class="text-right">{{ number_format($total_quotations, 2) }}</td>
            --}}
            <td></td> 
            <td style="font-weight: bold;">TOTAL</td>
            <td>S/</td>
            <td class="text-right">{{ number_format($grand_total - $total_egress, 2) }}</td>
        </tr>
    </table>
</body>

</html>
