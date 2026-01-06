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
        }
        @page {
            margin: 0cm 0.3cm;
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
        <h3 align="center" class="title"><strong>LIQUIDACION DE DOCUMENTOS POR TRANSPORTISTA DEL DIA {{$date_of_issue}}</strong></h3>

    </div>
    <br>
    <div style="margin-top:20px; margin-bottom:15px;">
        <table>
            <tr>
                <td>
                    <p><b>Empresa: </b></p>
                </td>
                <td align="center">
                    <p><strong>{{ $company->name }}</strong></p>
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
                <td align="center">{{ $company->number }}</td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>
    <br>
    @php
        $total_documents = 0;
        $total_amount = 0;
        $sellers = $records
            ->map(function ($record) {
                return $record->seller_number ?: $record->seller_name;
            })
            ->unique()
            ->toArray();
    @endphp
    @if (!empty($records))
        <div class="">
            <div class=" ">
                <div>
                    <strong>TRANSPORTISTA: </strong> {{ $dispatcher->name }} - {{ $dispatcher->number }}
                </div>
                <div>
                    <strong>VENDEDORES: </strong> {{ implode(', ', $sellers) }}
                </div>
                <table class="">
                    <thead>
                        <tr>
                            <th class="">#</th>
                            <th class="">Tipo Documento</th>
                            <th class="">Comprobante</th>
                            <th class="">Código Cliente</th>
                            <th class="">Nombre / Razón Social</th>
                            <th class="">Precio Venta</th>
                            <th class="">Forma de pago</th>
                            <th class="">Contado</th>
                            <th class="">Crédito</th>
                            <th class="">Devolución</th>
                            <th class="">Deuda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $value)
                            @php

                                $payment_method_type_id = $value->payment_method_type_id;
                                $customer_id = $value->customer_id;
                                $total_due = App\Models\Tenant\Person::getAccumulatedDue($customer_id);
                                $payment_method_explode = explode(',', $payment_method_type_id);
                                $payment_method_description = '';
                                foreach ($payment_method_explode as $item) {
                                    $payment_method = App\Models\Tenant\PaymentMethodType::find($item);
                                    $payment_method_description .= $payment_method
                                        ? $payment_method->description . ', '
                                        : '';
                                }
                                $document_type_id = $value->document_type_id;
                                $document_number = $value->number_full;
                                $customer_internal_id = $value->customer_internal_id;
                                $customer_name = $value->customer_name;
                                $sale_price = $value->total;
                                $payment_method = null;
                                $document_type = App\Models\Tenant\Catalogs\DocumentType::find($document_type_id);
                                $document_type_description = $document_type ? $document_type->description : '';
                                if (strlen($payment_method_description) > 0) {
                                    $payment_method_description = substr($payment_method_description, 0, -2);

                                }
                                if($payment_method_description == null){
                                    $payment_method_description = $value->payment_condition_id == '01' ? 'Contado' : 'Crédito';
                                }
                                $seller_code = null;
                                if ($value->seller_number) {
                                    $seller_code = $value->seller_number;
                                } else {
                                    $seller_code = $value->seller_name;
                                }
                                $sellers[] = $seller_code;
                                $record = (object) [
                                    'payment_condition' => $value->payment_condition_id == '01' ? 'Contado' : 'Crédito',
                                    'seller_code' => $seller_code,
                                    'payment_method_description' => $payment_method_description, // 'Método de pago
                                    'document_type_description' => $document_type_description, // 'Tipo de documento
                                    'document_type_id' => $document_type_id,
                                    'document_number' => $document_number,
                                    'customer_internal_id' => $customer_internal_id,
                                    'customer_name' => $customer_name,
                                    'sale_price' => $sale_price,
                                    'payment_method' => $payment_method,
                                    'due' => $total_due,
                                ];
                            @endphp
                            <tr>
                                <td class="celda">{{ $key + 1 }}</td>
                                <td class="celda">
                                    {{ $record->document_type_description }}
                                </td>
                                <td class="celda">
                                    {{ $record->document_number }}
                                </td>
                                <td class="celda">
                                    {{ $record->customer_internal_id }}
                                </td>
                                <td class="celda">
                                    {{ $record->customer_name }}
                                </td>
                                <td class="celda">
                                    {{ $record->sale_price }}
                                </td>
                                <td class="celda">
                                    {{ $record->payment_method_description }}
                                </td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda"></td>
                                <td class="celda">
                                    {{ $record->due }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">
                                <strong>TOTAL DOCUMENTOS:</strong> {{ count($records) }}
                            </td>
                            <td colspan="2"></td>
                            <td colspan="2" style="text-align: end;">
                                <strong>
                                    TOTAL VENTAS: {{ number_format($records->sum('total'), 2) }}
                                </strong>
                            </td>
                            <td></td>
                            <td></td>
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
