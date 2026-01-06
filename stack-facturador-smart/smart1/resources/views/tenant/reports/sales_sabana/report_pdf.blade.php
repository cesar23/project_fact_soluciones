<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte</title>
    <style>
        html {
            font-family: sans-serif;
            font-size: 12px;
            margin: 0 15px;
            padding: 0;
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
            border-color: #0088cc;
            border: 0.1px solid black;
        }

        .title {
            font-weight: bold;
            padding: 5px;
            font-size: 20px !important;
            text-decoration: underline;
        }

        p>strong {
            margin-left: 5px;
            font-size: 13px;
        }

        thead {
            font-weight: bold;
            background: #0088cc;
            color: white;
            text-align: center;
        }
    </style>
</head>

<body>
    <div>
        <h3 align="center" class="title"><strong>Reporte ventas sabana</strong></h3>
    </div>
    <br>
    <div style="margin-top:20px; margin-bottom:15px;">
        <table>
            <tr>
                <td class="celda">
                    <p><b>Empresa: </b></p>
                </td>
                <td align="center">
                    <p><strong>{{ $company->name }}</strong></p>
                </td>
                <td class="celda">
                    <p><strong>Fecha: </strong></p>
                </td>
                <td align="center">
                    <p><strong>{{ date('Y-m-d') }}</strong></p>
                </td>
            </tr>
            <tr>
                <td class="celda">
                    <p><strong>Ruc: </strong></p>
                </td>
                <td align="center">{{ $company->number }}</td>
                <td class="celda"></td>
                <td class="celda"></td>
            </tr>
        </table>
    </div>
    <br>
    @php
        $total_general = 0; 
    @endphp

    @if (!empty($records))
        <div class="">
            <div class=" ">

                <table class="">
                    <thead>
                        <tr>
                            <th class="">#</th>
                            <th class="">Vendedor</th>
                            <th class="">Tipo documento</th>
                            <th class="">Número documento</th>
                            <th class="">Proveedor</th>
                            <th class="">Fecha de documento</th>
                            <th class="">Cod producto</th>
                            <th class="">Producto</th>
                            <th class="">Cantidad</th>
                            <th class="">Unidad de medida</th>
                            <th class="">Cod cliente</th>
                            <th class="">Cliente</th>
                            <th class="">Forma de pago</th>
                            <th class="">Importe sin igv</th>
                            <th class="">Importe con igv</th>
                            <th class="">Importe total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $value)
                            @php
                                $item_id = $value->item_id;
                                $seller_name = null;
                                $document_type = null;
                                $document_number = null;
                                $supplier_name = null;
                                $date_of_issue = null;
                                $item_internal_id = null;
                                $item_description = null;
                                $unit_type_id = null;
                                $customer_internal_code = null;
                                $customer_name = null;
                                $payment_condition = null;
                                $unit_value = null;
                                $unit_price = null;
                                $total = null;
                                $quantity = null;
                                $seller_name = isset($value->seller_id)
                                    ? App\Models\Tenant\User::find($value->seller_id)->name
                                    : null;
                                $document_type = App\Models\Tenant\Catalogs\DocumentType::find($value->document_type_id)
                                    ->description;
                                $document_number = $value->number_full;
                                $last_purchase_item = App\Models\Tenant\PurchaseItem::where('item_id', $item_id)
                                    ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                                    ->orderBy('purchases.date_of_issue', 'desc')
                                    ->select('purchase_items.*')
                                    ->first();
                                $last_purchase_document = $last_purchase_item ? $last_purchase_item->purchase : null;
                                $supplier_name = $last_purchase_document
                                    ? $last_purchase_document->supplier->name
                                    : null;
                                $date_of_issue = $value->date_of_issue;
                                $item = json_decode($value->item);
                                $item_internal_id = isset($item->internal_id) ? $item->internal_id : null;
                                if ($item_internal_id == null) {
                                    $item_db = App\Models\Tenant\Item::find($item_id);
                                    if ($item_db) {
                                        $item_internal_id = $item_db->internal_id;
                                    }
                                }
                                $item_description = $item->description;
                                $unit_type_id = $item->unit_type_id;
                                $customer = App\Models\Tenant\Person::find($value->customer_id);
                                $customer_internal_code = $customer->internal_code;
                                $customer_name = $customer->name;
                                $payment_condition = $value->payment_condition_id == '01' ? 'Contado' : 'Crédito';
                                if ($value->payment_condition_id == '02' && $value->order_note_id) {
                                    $order_note = \Modules\Order\Models\OrderNote::find($value->order_note_id);
                                    $payment_method = \App\Models\Tenant\PaymentMethodType::find(
                                        $order_note->payment_method_type_id,
                                    );
                                    if ($payment_method->is_credit) {
                                        $payment_condition = $payment_method->description;
                                    }
                                }
                                $unit_value = $value->unit_value;
                                $unit_price = $value->unit_price;
                                $total = $value->unit_price * $value->quantity;
                                $quantity = $value->quantity;
                                $record = (object) [
                                    'seller_name' => $seller_name,
                                    'document_type' => $document_type,
                                    'document_number' => $document_number,
                                    'supplier_name' => $supplier_name,
                                    'date_of_issue' => $date_of_issue,
                                    'item_internal_id' => $item_internal_id,
                                    'item_description' => $item_description,
                                    'unit_type_id' => $unit_type_id,
                                    'customer_internal_code' => $customer_internal_code,
                                    'customer_name' => $customer_name,
                                    'payment_condition' => $payment_condition,
                                    'unit_value' => number_format($unit_value, 2),
                                    'unit_price' => number_format($unit_price, 2),
                                    'total' => $total,
                                    'quantity' => $quantity,
                                ];
                                $total_general += $record->total;
                            @endphp
                            <tr>
                                <td class="celda">{{ $key + 1 }}</td>
                                <td class="celda">
                                    {{ $record->seller_name }}
                                </td>
                                <td class="celda">
                                    {{ $record->document_type }}
                                </td>
                                <td class="celda">
                                    {{ $record->document_number }}
                                </td>
                                <td class="celda">
                                    {{ $record->supplier_name }}
                                </td>
                                <td class="celda">
                                    {{ $record->date_of_issue }}
                                </td>
                                <td class="celda">
                                    {{ $record->item_internal_id }}
                                </td>
                                <td class="celda">
                                    {{ $record->item_description }}
                                </td>
                                <td class="celda">
                                    {{ $record->quantity }}
                                </td>
                                <td class="celda">
                                    {{ $record->unit_type_id }}
                                </td>
                                <td class="celda">
                                    {{ $record->customer_internal_code }}
                                </td>
                                <td class="celda">
                                    {{ $record->customer_name }}
                                </td>
                                <td class="celda">
                                    {{ $record->payment_condition }}
                                </td>
                                <td class="celda">
                                    {{ $record->unit_value }}
                                </td>
                                <td class="celda">
                                    {{ $record->unit_price }}
                                </td>
                                <td class="celda">
                                    {{ $record->total }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="12" class="celda"></td>
                            <td colspan="2" class="celda">
                                <strong>Total</strong>
                            </td>
                            <td class="celda">
                                <strong>{{ number_format($total_general, 2) }}</strong>
                            </td>
                        </tr>
                    </tfoot>
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
