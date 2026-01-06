@php
    $enabled_sales_agents = App\Models\Tenant\Configuration::getRecordIndividualColumn('enabled_sales_agents');
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Compras</title>
</head>

<body>
    <div>
        <h3 align="center" class="title"><strong>Reporte Nota de Venta</strong></h3>
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

                @inject('reportService', 'Modules\Report\Services\ReportService')
                @if (isset($filters['seller_id']) && !empty($filters['seller_id']))
                    <td>
                        <p><strong>Usuario: </strong></p>
                    </td>
                    <td align="center">
                        {{ $reportService->getUserName($filters['seller_id']) }}
                    </td>
                @endif
            </tr>
        </table>
    </div>
    <br>
    @if (!empty($records))
        <div class="">
            <div class=" ">

                @php
                    $acum_total_taxed = 0;
                    $acum_total_igv = 0;
                    $acum_total = 0;

                    $acum_total_taxed_usd = 0;
                    $acum_total_igv_usd = 0;
                    $acum_total_usd = 0;
                @endphp

                <table class="">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-center">Fecha Emisión</th>
                            <th class="text-center">Hora Emisión</th>
                            <th class="text-center">Fecha de pago</th>
                            <th class="">Usuario/Vendedor</th>
                            <th>Primera compra</th>
                            <th>Cliente</th>
                            <th>Tipo de documento</th>
                            <th>N° documento</th>
                            <th>Nota de Venta</th>
                            <th class="text-center">Estado pago</th>
                            <th class="text-center">Condición pago</th>
                            <th class="text-center">Método pago</th>
                            <th class="text-center">Destino</th>
                            <th class="text-center">Referencia/N° Operación</th>
                            <th class="text-center">Confirmación de pago</th>
                            <th class="text-center">Monto</th>
                            <th>Estado</th>
                            <th class="text-center">Moneda</th>
                            <th class="text-center">Plataforma</th>
                            <th class="text-center">Orden de compra</th>
                            <th class="text-center">Region</th>
                            <th class="text-center">Departamento</th>
                            <th class="text-center">Provincia</th>
                            <th class="text-center">Distrito</th>
                            <th class="text-center">Comprobantes</th>
                            <th class="text-center">Fecha comprobante</th>
                            <th class="text-center">Guias</th>
                            <th>Cotización</th>
                            <th>Caso</th>

                            <th class="text-center">Productos</th>
                            <th class="text-right">Descuento</th>

                            <th class="text-right">T.Exportación</th>
                            <th class="text-right">T.Inafecta</th>
                            <th class="text-right">T.Exonerado</th>
                            <th class="text-right">T.Gravado</th>
                            <th class="text-right">T.Igv</th>
                            <th class="text-right">Total</th>
                            @if ($enabled_sales_agents)
                                <th>Agente</th>
                                <th>Datos de referencia</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $value)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $value->date_of_issue->format('Y-m-d') }}</td>
                                <td class="celda">{{ $value->time_of_issue }}</td>

                                @php
                                    $date_of_payment = $value->date_of_issue->format('Y-m-d');
                                    $payments = $value->payments;
                                    if (count($payments) > 0) {
                                        $date_of_payment = $payments[0]->date_of_payment->format('Y-m-d');
                                    }
                                @endphp
                                <td>{{ $date_of_payment }}</td>
                                <td class="celda">{{ $value->user->name }}</td>
                                @php
                                    $count_register = numberDocumentByClient($value->customer_id,\App\Models\Tenant\SaleNote::class);
                                @endphp

                                <td>{{ $count_register > 1 ? 'No' : 'Si' }}</td>
                            
                                <td>{{ $value->customer->name }}</td>
                                @php
                                    $identity_document_type_description = '';
                                    $identity_document_type_id = $value->customer->identity_document_type_id;
                                    $db_identity_document_type = \App\Models\Tenant\Catalogs\IdentityDocumentType::where(
                                        'id',
                                        $identity_document_type_id,
                                    )->first();
                                    if ($db_identity_document_type) {
                                        $identity_document_type_description = $db_identity_document_type->description;
                                    }
                                @endphp
                                <td>{{ $identity_document_type_description }}</td>
                                <td>{{ $value->customer->number }}</td>
                                <td>{{ $value->number_full }}</td>
                                <td>
                                    {{ $value->total_canceled ? 'Pagado' : 'Pendiente' }}
                                </td>
                                <td>
                                    {{ $value->payment_condition_id == '01' ? 'Contado' : 'Crédito' }}
                                </td>
                                <td>
                                    @php
                                        $payments_methods = [];
                                        if ($value->payment_condition_id == '01') {
                                            $payments_methods = $value->payments->map(function ($row) {
                                                return $row['payment_method_type']['description'];
                                            });
                                        } else {
                                            $payments_methods = $value->fee->map(function ($row) {
                                                if ($row->payment_method_type) {
                                                    return $row->payment_method_type->description;
                                                } else {
                                                    return 'Credito';
                                                }
                                            });
                                        }
                                    @endphp
                                    {{ implode(', ', $payments_methods->toArray()) }}
                                </td>
                                <td>
                                    @php
                                        $payment_destinations = [];
                                        foreach ($value->payments as $payment) {
                                            $destination = getPaymentDestination(
                                                $payment->id,
                                                \App\Models\Tenant\SaleNotePayment::class,
                                            );
                                            if (strlen($destination) > 0) {
                                                $payment_destinations[] = $destination;
                                            }
                                        }
                                    @endphp
                                    {{ implode(', ', $payment_destinations) }}
                                </td>
                                <td>
                                    @php
                                        $reference_payment = '';
                                        foreach ($value->payments as $payment) {
                                            $reference_payment .= $payment->reference . ' ';
                                        }
                                    @endphp
                                    {{ $reference_payment }}
                                </td>
                                <td>
                                    @if ($value->state_payment_id == '01')
                                        En espera
                                    @elseif ($value->state_payment_id == '02')
                                        Aprobado
                                    @else
                                        Rechazado
                                    @endif

                                </td>
                                <td>
                                    {{$value->total}}
                                </td>
                                <td>{{ $value->state_type->description }}</td>
                                <td>{{ $value->currency_type_id }}</td>
                                <td class="celda">
                                    @foreach ($value->getPlatformThroughItems() as $platform)
                                        <label class="d-block">{{ $platform->name }}</label>
                                    @endforeach
                                </td>
                                <td>{{ $value->purchase_order }}</td>
                                <td>{{ $value->customer->department->description }}</td>
                                <td>{{ $value->customer->department->description }}</td>
                                <td>{{ $value->customer->province->description }}</td>
                                <td>{{ $value->customer->district->description }}</td>
                                @php
                                    $documents = $value->documents;
                                @endphp
                                <td>
                                    @foreach ($documents as $doc)
                                        <p class="d-block">{{ $doc->number_full }}</p>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach ($documents as $doc)
                                        <p class="d-block">{{ $doc->date_of_issue->format('Y-m-d') }}</p>
                                    @endforeach
                                </td>
                                @php
                                $dispatches_order = [];
                                $dispatch_order = $value->dispatch_order;                                
                                if ($dispatch_order) {
                                    $dispatches = $dispatch_order->dispatches;
                                    foreach ($dispatches as $dispatch) {
                                        $dispatches_order[] = $dispatch->series.'-'.$dispatch->number_full;
                                    }
                                }
                                @endphp
                                 {{
                                    implode(', ', $dispatches_order)
                                 }}
                                <td>

                                </td>
                                <td class="celda">{{ $value->quotation ? $value->quotation->number_full : '' }}</td>
                                <td class="celda">
                                    {{ isset($value->quotation->sale_opportunity) ? $value->quotation->sale_opportunity->number_full : '' }}
                                </td>

                                <td>
                                    @foreach ($value->getItemsforReport() as $key => $item)
                                        - {{ $item['description'] }} / Cantidad: {{ $item['quantity'] }}
                                        @if ($key < count($value->getItemsforReport()) - 1)
                                            <br />
                                        @endif
                                    @endforeach
                                </td>

                                @if ($value->state_type_id == '11')
                                    <td class="celda">0</td>
                                    <td class="celda">0</td>
                                    <td class="celda">0</td>
                                    <td class="celda">0</td>
                                    <td class="celda">0</td>
                                    <td class="celda">0</td>
                                    <td class="celda">0</td>
                                @else
                                    <td class="celda">{{ $value->total_discount }}</td>
                                    <td class="celda">{{ $value->total_exportation }}</td>
                                    <td class="celda">{{ $value->total_unaffected }}</td>
                                    <td class="celda">{{ $value->total_exonerated }}</td>
                                    <td class="celda">{{ $value->total_taxed }}</td>
                                    <td class="celda">{{ $value->total_igv }}</td>
                                    <td class="celda">{{ $value->total }}</td>
                                @endif

                                @if ($enabled_sales_agents)
                                    <td>{{ optional($value->agent)->search_description }}</td>
                                    <td>{{ $value->reference_data }}</td>
                                @endif
                            </tr>

                            @php

                                if ($value->currency_type_id == 'PEN') {
                                    if ($value->state_type_id == '11') {
                                        $acum_total += 0;
                                        $acum_total_taxed += 0;
                                        $acum_total_igv += 0;
                                    } else {
                                        $acum_total += $value->total;
                                        $acum_total_taxed += $value->total_taxed;
                                        $acum_total_igv += $value->total_igv;
                                    }
                                } elseif ($value->currency_type_id == 'USD') {
                                    if ($value->state_type_id == '11') {
                                        $acum_total_usd += 0;
                                        $acum_total_taxed_usd += 0;
                                        $acum_total_igv_usd += 0;
                                    } else {
                                        $acum_total_usd += $value->total;
                                        $acum_total_taxed_usd += $value->total_taxed;
                                        $acum_total_igv_usd += $value->total_igv;
                                    }
                                }
                            @endphp
                        @endforeach
                        <tr>
                            <td class="celda" colspan="34"></td>
                            <td class="celda">Totales PEN</td>
                            <td class="celda">{{ $acum_total_taxed }}</td>
                            <td class="celda">{{ $acum_total_igv }}</td>
                            <td class="celda">{{ $acum_total }}</td>
                        </tr>
                        <tr>
                            <td class="celda" colspan="34"></td>
                            <td class="celda">Totales USD</td>
                            <td class="celda">{{ $acum_total_taxed_usd }}</td>
                            <td class="celda">{{ $acum_total_igv_usd }}</td>
                            <td class="celda">{{ $acum_total_usd }}</td>
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
