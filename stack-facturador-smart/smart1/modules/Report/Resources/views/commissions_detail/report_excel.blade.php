<!DOCTYPE html>
<html lang="es">
    <?php
    use App\Models\Tenant\Item;
    use App\Models\Tenant\ItemUnitType;
    ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Comisión vendores</title>
</head>

<body>
    <div>
        <h3 align="center" class="title"><strong>Reporte de comisión de vendedores - utilidades detallado</strong></h3>
    </div>
    <br>
    <div style="margin-top:20px; margin-bottom:15px;">
        <table>
            <tr>
                <td>
                    <p><b>Empresa: </b></p>
                </td>
                <td align="center">
                    <p><strong>{{ $company['name'] ?? '' }}</strong></p>
                </td>
                <td>
                    <p><strong>Fecha: </strong></p>
                </td>
                <td align="center">
                    <p><strong>{{ date('Y-m-d') }}</strong></p>
                </td>
            </tr>
            <tr>
                <td>
                    <p><strong>Ruc: </strong></p>
                </td>
                <td align="center">{{ $company['number'] ?? '' }}</td>
            </tr>
        </table>
    </div>
    <br>
    @if (!empty($records))
        @php
            $acum_unit_gain = 0;
            $acum_overall_profit = 0;
        @endphp
        <div class="">
            <div class=" ">
                <table class="">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th class="text-center">Comprobante</th>
                            <th class="text-center">Serie</th>
                            <th class="text-center">Ruc/Dni</th>

                            <th class="text-center">Comercial</th>
                            <th class="text-center">Detalle</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Precio compra</th>
                            <th class="text-center">Precio venta</th>

                            <th class="text-center">Ganancia unidad</th>
                            <th class="text-center">Ganancia total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $row)
                            @php
                                // Obtener datos básicos
                                $quantity = $row['quantity'] ?? 0;
                                $unit_price = isset($row['unit_price']) ? $row['unit_price'] : 0;
                                
                            
                                // Obtener el item relacionado
                                $item_id = $row['item_id'] ?? null;
                                $items = null;
                                $purchase_unit_price = $row['purchase_unit_price'] ?? 0;
                            
                                
                                // Tipo de documento y presentación
                                $type_document = $row['type_document'] ?? '';
                                $presentation_name = null;
                                
                                // Datos del documento o nota de venta
                                $relation = null;
                                $date_of_issue = $row['date_of_issue'] ?? '';
                                $customer_number = $row['customer_number'] ?? '';
                                $customer_name = $row['customer_name'] ?? '';
                                $serie = $row['serie'] ?? '';
                                
                                // Determinar tipo de documento
                                // if (isset($row['document_id'])) {
                                //     $type_document = isset($row['type_document']) ? $row['type_document'] : 'FACTURA/BOLETA';
                                // } elseif (isset($row['sale_note_id'])) {
                                //     $type_document = 'NOTA DE VENTA';
                                // }
                                
                                // Calcular ganancias
                                $unit_gain = $row['unit_gain'] ?? 0;    
                                $overall_profit = $row['overall_profit'] ?? 0;
                                
                                $acum_unit_gain += (float) $unit_gain;
                                $acum_overall_profit += (float) $overall_profit;
                            @endphp

                            <tr>
                                <td class="celda">{{ $loop->iteration }}</td>
                                <td class="celda">{{ $date_of_issue }}</td>
                                <td class="celda">{{ $type_document }}</td>
                                <td class="celda">{{ $serie }}</td>
                                <td class="celda">{{ $customer_number }}</td>

                                <td class="celda">{{ $customer_name }}</td>
                                <td class="celda">{{ $row['name'] ?? 'Sin descripción' }}
                                    @if ($presentation_name)
                                        <br>
                                        <small>
                                            PRES:{{ $presentation_name }}
                                        </small>
                                    @endif
                                </td>

                                <td class="celda">{{ $quantity }}</td>
                                <td class="celda">{{ $purchase_unit_price }}</td>
                                <td class="celda">{{ $unit_price }}</td>

                                <td class="celda">{{ number_format($unit_gain,2) }}</td>
                                <td class="celda">{{ $overall_profit }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="celda" style="text-align:right;" colspan="10">TOTAL:</td>
                            <td class="celda">{{ number_format($acum_unit_gain, 2, '.', '') }}</td>
                            <td class="celda">{{ number_format($acum_overall_profit, 2, '.', '') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
