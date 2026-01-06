<?php
use App\Models\Tenant\Document;
use App\CoreFacturalo\Helpers\Template\TemplateHelper;
use App\Models\Tenant\SaleNote;

$enabled_sales_agents = App\Models\Tenant\Configuration::getRecordIndividualColumn('enabled_sales_agents');
$show_products = $filters['products'] === 'true';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
@php
    $apply_conversion_to_pen = apply_conversion_to_pen('reports/sales');
    $config = \App\Models\Tenant\Configuration::getConfig();
@endphp
<body>

    <div>
        <h3 align="center" class="title"><strong>Reporte Documentos</strong></h3>
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
                <td>
                    <p><strong>Establecimiento: </strong></p>
                </td>
                <td align="center">{{ $establishment->address }} - {{ $establishment->department->description }}
                    - {{ $establishment->district->description }}</td>
            </tr>
            @inject('reportService', 'Modules\Report\Services\ReportService')
            <tr>
                @if ($filters['seller_id'])
                    <td>
                        <p><strong>Usuario: </strong></p>
                    </td>
                    <td align="center">
                        {{ $reportService->getUserName($filters['seller_id']) }}
                    </td>
                @endif
                @if ($filters['person_id'])
                    <td>
                        <p><strong>Cliente: </strong></p>
                    </td>
                    <td align="center">
                        {{ $reportService->getPersonName($filters['person_id']) }}
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
                    $acum_total_charges = 0;
                    $acum_total_taxed = 0;
                    $acum_total_igv = 0;
                    $acum_total = 0;

                    $serie_affec = '';
                    $acum_total_exonerado = 0;
                    $acum_total_inafecto = 0;

                    $acum_total_free = 0;

                    $acum_total_taxed_usd = 0;
                    $acum_total_igv_usd = 0;
                    $acum_total_usd = 0;
                @endphp
                <table class="">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th class="">Usuario/Vendedor</th>
                            <th>Tipo Doc</th>
                            <th>Número</th>
                            <th>Fecha emisión</th>
                            <th>Fecha Vencimiento</th>
                            @if ($config->show_channels_documents)
                            <th>Canal</th>
                            @endif
                            <th>Doc. Afectado</th>
                            <th># Guía</th>
                            <th>Cotización</th>
                            <th>Caso</th>

                            <th>DIST</th>
                            <th>DPTO</th>
                            <th>PROV</th>

                            <th>Direccion de cliente</th>
                            <th>Cliente</th>
                            <th>RUC</th>
                            @if ($show_products)
                                <th>Producto</th>
                                <th>Cantidad</th>
                            @endif
                            <th>Estado</th>
                            <th class="">Moneda</th>
                            <th>Plataforma</th>
                            <th>Orden de compra</th>

                            <th>Nota de venta</th>
                            <th>Fecha N. Venta</th>

                            <th class="">Forma de pago</th>
                            <th> MÉTODO DE PAGO </th>
                            <th>TC</th>
                            <th>Total Cargos</th>
                            <th>Total Exonerado</th>
                            <th>Total Inafecto</th>
                            <th>Total Gratuito</th>
                            <th>Total Gravado</th>
                            <th>Descuento total</th>
                            <th>Total IGV</th>
                            <th>Total ISC</th>
                            <th>Total</th>
                            <th>Total de productos</th>

                            @foreach ($categories as $category)
                                <th>{{ $category->name }}</th>
                            @endforeach

                            @foreach ($categories_services as $category)
                                <th>{{ $category->name }}</th>
                            @endforeach

                            @if ($enabled_sales_agents)
                                <th>Agente</th>
                                <th>Datos de referencia</th>
                            @endif

                            <th>Placa</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $value)
                            <?php
                            /** @var \App\Models\Tenant\Document|App\Models\Tenant\SaleNote  $value */
                            $iteration = $loop->iteration;
                            $userCreator = $value->user->name;
                            $channel_name = null;
                            if ($value->channel) {
                                $channel_name = $value->channel->channel->channel_name;
                            }
                            $document_type = $value->getDocumentType();
                            $seller = \App\CoreFacturalo\Helpers\Template\ReportHelper::getSellerData($value);
                            try {
                                $user = $seller->name;
                            } catch (ErrorException $e) {
                                $user = '';
                            }
                            if ($show_products) {
                                $items = $value->items;
                                $first_item = $items->first();
                                $rest_items = $items->slice(1);
                            }

                            $total_taxed = $value->total_taxed;
                            $total_igv = $value->total_igv;
                            $total = $value->total;
                            $total_charge = $value->total_charge;
                            $total_exonerated = $value->total_exonerated;
                            $total_unaffected = $value->total_unaffected;
                            $total_free = $value->total_free;
                            $total_isc = $value->total_isc;
                            $total_discount = $value->total_discount;
                            $currency_type_id = $value->currency_type_id;
                            $exchange_rate_sale = $value->exchange_rate_sale;
                            if($apply_conversion_to_pen && $currency_type_id !== 'PEN'){
                                $total_taxed = $total_taxed * $exchange_rate_sale;
                                $total_igv = $total_igv * $exchange_rate_sale;
                                $total = $total * $exchange_rate_sale;
                                $total_charge = $total_charge * $exchange_rate_sale;
                                $total_exonerated = $total_exonerated * $exchange_rate_sale;
                                $total_unaffected = $total_unaffected * $exchange_rate_sale;
                                $total_free = $total_free * $exchange_rate_sale;
                                $total_isc = $total_isc * $exchange_rate_sale;
                                $total_discount = $total_discount * $exchange_rate_sale;
                                $currency_type_id = 'PEN';
                            }
                            ?>
                            <tr>
                                <td class="celda">{{ $iteration }}</td>
                                <td class="celda">
                                    @if ($filters['user_type'] === 'CREADOR')
                                        {{ $userCreator }}
                                    @else
                                        {{ $user }}
                                    @endif
                                </td>
                                <td class="celda">{{ $document_type->id }}</td>
                                <td class="celda">{{ $value->series }}-{{ $value->number }}</td>
                                @php
                                    $date_of_issue = $value->date_of_issue;
                                    if (is_string($date_of_issue)) {
                                        $date_of_issue = \Carbon\Carbon::parse($date_of_issue);
                                    }
                                @endphp
                                <td class="celda">{{ $date_of_issue->format('Y-m-d') }}</td>
                                @php
                                    if (isset($value->invoice)) {
                                        $date_of_due_in = $value->invoice->date_of_due;
                                        if (is_string($date_of_due_in)) {
                                            $date_of_due_in = \Carbon\Carbon::parse($date_of_due_in);
                                        }
                                    }
                                @endphp
                                <td class="celda">{{ isset($value->invoice) ? $date_of_due_in->format('Y-m-d') : '' }}
                                </td>
                                @if ($config->show_channels_documents)
                                <td class="celda">{{ $channel_name }}</td>
                                @endif
                                @if (in_array($document_type->id, ['07', '08']) && $value->note)
                                    @php
                                        $serie = $value->note->affected_document
                                            ? $value->note->affected_document->series
                                            : $value->note->data_affected_document->series;
                                        $number = $value->note->affected_document
                                            ? $value->note->affected_document->number
                                            : $value->note->data_affected_document->number;
                                        $serie_affec = $serie . ' - ' . $number;

                                    @endphp
                                @endif
                                <td class="celda">{{ $serie_affec }} </td>
                                <td class="celda">
                                    @if (!empty($value->guides))
                                        @foreach ($value->guides as $guide)
                                            {{ $guide->number }}<br>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="celda">{{ $value->quotation ? $value->quotation->number_full : '' }}</td>
                                <td class="celda">
                                    {{ isset($value->quotation->sale_opportunity) ? $value->quotation->sale_opportunity->number_full : '' }}
                                </td>

                                <?php $stablihsment = \App\CoreFacturalo\Helpers\Template\ReportHelper::getLocationData($value); ?>
                                <td class="celda">{{ $stablihsment['district'] }}</td>
                                <td class="celda">{{ $stablihsment['department'] }}</td>
                                <td class="celda">{{ $stablihsment['province'] }}</td>

                                <td class="celda">{{ $value->customer->address }}</td>
                                <td class="celda">{{ $value->customer->name }}</td>
                                <td class="celda">{{ $value->customer->number }}</td>
                                @if ($show_products)
                                    @if (isset($first_item->item->description))
                                        <td class="celda">{{ $first_item->item->description }}</td>
                                        <td class="celda">{{ $first_item->quantity }}</td>
                                    @else
                                        <td class="celda"></td>
                                        <td class="celda"></td>
                                    @endif
                                @endif
                                <td class="celda">{{ $value->state_type->description }}</td>

                                @php
                                    $signal = $document_type->id;
                                    $state = $value->state_type_id;
                                @endphp

                                <td class="celda">{{ $currency_type_id }}</td>
                                <td class="celda">
                                    @foreach ($value->getPlatformThroughItems() as $platform)
                                        <label class="d-block">{{ $platform->name }}</label>
                                    @endforeach
                                </td>
                                <td class="celda">{{ $value->purchase_order }}</td>

                                @if ($value->sale_note)
                                    <td class="celda">{{ $value->sale_note->number_full }}</td>
                                    @php
                                        $date_of_issue_nv = $value->sale_note->date_of_issue;
                                        if (is_string($date_of_issue_nv)) {
                                            $date_of_issue_nv = \Carbon\Carbon::parse($date_of_issue_nv);
                                        }
                                    @endphp
                                    <td class="celda">{{ $date_of_issue_nv->format('Y-m-d') }}</td>
                                @else
                                    <td class="celda"></td>
                                    <td class="celda"></td>
                                @endif

                                <td class="celda">
                                    {{ $value->payments()->count() > 0 ? $value->payments()->first()->payment_method_type->description : '' }}
                                </td>
                                <td class="celda">
                                    @php
                                        $payments = [];
                                        if (
                                            get_class($value) == Document::class ||
                                            get_class($value) == SaleNote::class
                                        ) {
                                            $payments = TemplateHelper::getDetailedPayment($value);
                                        }
                                    @endphp

                                    @foreach ($payments as $payment)
                                        @foreach ($payment as $pay)
                                            {{ $pay['description'] }}
                                            @if ($loop->count > 1 && !$loop->last)
                                                <br>
                                            @endif
                                        @endforeach
                                    @endforeach

                                </td>
                                <td>{{ $value->exchange_rate_sale }}</td>

                            
                                @if ($signal == '07')
                                    @if (in_array($value->state_type_id, ['09', '11']))
                                        <td class="celda">0</td>
                                        <td class="celda">0</td>
                                        <td class="celda">0</td>
                                        <td class="celda">0</td>
                                        <td class="celda">0</td>
                                        <td class="celda">0</td>
                                        <td class="celda">0</td>
                                        <td class="celda">0</td>
                                        <td class="celda">0</td>
                                    @else
                                        <td class="celda">
                                            {{ $signal == '07' ? '-' : '' }}{{ number_format($total_charge, 2) }}
                                        </td>
                                        <td class="celda">
                                            {{ $signal == '07' ? '-' : '' }}{{ number_format($total_exonerated, 2) }}</td>
                                        <td class="celda">
                                            {{ $signal == '07' ? '-' : '' }}{{ number_format($total_unaffected, 2) }}</td>
                                        <td class="celda">
                                            {{ $signal == '07' ? '-' : '' }}{{ number_format($total_free, 2) }}
                                        </td>
                                        <td class="celda">
                                            {{ $signal == '07' ? '-' : '' }}{{ number_format($total_taxed, 2) }}
                                        </td>
                                        <td class="celda">{{ $value->total_discount }}</td>
                                        <td class="celda">{{ $signal == '07' ? '-' : '' }}{{ number_format($total_igv, 2) }}
                                        </td>
                                        <td class="celda">{{ $signal == '07' ? '-' : '' }}{{ number_format($total_isc, 2) }}
                                        </td>
                                        <td class="celda">{{ $signal == '07' ? '-' : '' }}{{ number_format($total, 2) }}
                                        </td>
                                    @endif
                                @else
                                    <td class="celda">
                                        {{ in_array($document_type->id, ['01', '03']) && in_array($value->state_type_id, ['09', '11']) ? 0 : number_format($total_charge, 2) }}
                                    </td>
                                    <td class="celda">
                                        {{ in_array($document_type->id, ['01', '03']) && in_array($value->state_type_id, ['09', '11']) ? 0 : number_format($total_exonerated, 2) }}
                                    </td>
                                    <td class="celda">
                                        {{ in_array($document_type->id, ['01', '03']) && in_array($value->state_type_id, ['09', '11']) ? 0 : number_format($total_unaffected, 2) }}
                                    </td>
                                    <td class="celda">
                                        {{ in_array($document_type->id, ['01', '03']) && in_array($value->state_type_id, ['09', '11']) ? 0 : number_format($total_free, 2) }}
                                    </td>

                                    <td class="celda">
                                        {{ in_array($document_type->id, ['01', '03']) && in_array($value->state_type_id, ['09', '11']) ? 0 : number_format($total_taxed, 2) }}
                                    </td>
                                    <td class="celda">
                                        {{ in_array($document_type->id, ['01', '03']) && in_array($value->state_type_id, ['09', '11']) ? 0 : number_format($total_discount, 2) }}
                                    </td>
                                    <td class="celda">
                                        {{ in_array($document_type->id, ['01', '03']) && in_array($value->state_type_id, ['09', '11']) ? 0 : number_format($total_igv, 2) }}
                                    </td>
                                    <td class="celda">
                                        {{ in_array($document_type->id, ['01', '03']) && in_array($value->state_type_id, ['09', '11']) ? 0 : number_format($total_isc, 2) }}
                                    </td>
                                    <td class="celda">
                                        {{ in_array($document_type->id, ['01', '03']) && in_array($value->state_type_id, ['09', '11']) ? 0 : number_format($total, 2) }}
                                    </td>
                                @endif

                                @foreach ($categories as $category)
                                    @php
                                        $amount = 0;

                                        foreach ($value->items as $item) {
                                            if ($item->relation_item->category_id == $category->id) {
                                                $amount += $item->total;
                                            }
                                        }
                                    @endphp

                                    <td>{{ $amount }}</td>
                                @endforeach


                                @foreach ($categories_services as $category)
                                    @php
                                        $quantity = 0;

                                        foreach ($value->items as $item) {
                                            if ($item->relation_item->category_id == $category->id) {
                                                $quantity += $item->quantity;
                                            }
                                        }
                                    @endphp

                                    <td>{{ $quantity }}</td>
                                @endforeach

                                @php

                                    $total_exonerated =
                                        in_array($document_type->id, ['01', '03', '07']) &&
                                        in_array($value->state_type_id, ['09', '11'])
                                            ? 0
                                            : $total_exonerated;
                                    $total_unaffected =
                                        in_array($document_type->id, ['01', '03', '07']) &&
                                        in_array($value->state_type_id, ['09', '11'])
                                            ? 0
                                            : $total_unaffected;
                                    $total_free =
                                        in_array($document_type->id, ['01', '03', '07']) &&
                                        in_array($value->state_type_id, ['09', '11'])
                                            ? 0
                                            : $total_free;

                                    $total_taxed =
                                        in_array($document_type->id, ['01', '03', '07']) &&
                                        in_array($value->state_type_id, ['09', '11'])
                                            ? 0
                                            : $total_taxed;
                                    $total_igv =
                                        in_array($document_type->id, ['01', '03', '07']) &&
                                        in_array($value->state_type_id, ['09', '11'])
                                            ? 0
                                            : $total_igv;
                                    $total =
                                        in_array($document_type->id, ['01', '03', '07']) &&
                                        in_array($value->state_type_id, ['09', '11'])
                                            ? 0
                                            : $total;
                                @endphp

                                @php

                                    $serie_affec = '';

                                    $quality_item = 0;
                                    foreach ($value->items as $itm) {
                                        $quality_item += $itm->quantity;
                                    }

                                @endphp
                                <td>{{ $quality_item }}</td>

                                @if ($enabled_sales_agents)
                                    <td>{{ optional($value->agent)->search_description }}</td>
                                    <td>{{ $value->reference_data }}</td>
                                @endif

                                <td>{{ $value->getPlateNumberSaleReport() }}</td>

                            </tr>
                            @php
                                if ($currency_type_id == 'PEN') {


                                    if ($signal == '07' && $state !== '11') {
                                        $acum_total += -$total;
                                        $acum_total_taxed += -$total_taxed;
                                        $acum_total_igv += -$total_igv;

                                        $acum_total_charges += -$total_charge;
                                        $acum_total_exonerado += -$total_exonerated;
                                        $acum_total_inafecto += -$total_unaffected;
                                        $acum_total_free += -$total_free;
                                    } elseif ($signal != '07' && $state == '11') {
                                        $acum_total += 0;
                                        $acum_total_taxed += 0;
                                        $acum_total_igv += 0;

                                        $acum_total_charges += 0;
                                        $acum_total_exonerado += 0;
                                        $acum_total_inafecto += 0;
                                        $acum_total_free += 0;
                                    } else {
                                        $acum_total += $total;
                                        $acum_total_taxed += $total_taxed;
                                        $acum_total_igv += $total_igv;

                                        $acum_total_charges += $total_charge;
                                        $acum_total_exonerado += $total_exonerated;
                                        $acum_total_inafecto += $total_unaffected;
                                        $acum_total_free += $total_free;
                                    }
                                } elseif ($currency_type_id == 'USD') {
                                    if ($signal == '07' && $state !== '11') {
                                        $acum_total_usd += -$total;
                                        $acum_total_taxed_usd += -$total_taxed;
                                        $acum_total_igv_usd += -$total_igv;
                                    } elseif ($signal != '07' && $state == '11') {
                                        $acum_total_usd += 0;
                                        $acum_total_taxed_usd += 0;
                                        $acum_total_igv_usd += 0;
                                    } else {
                                        $acum_total_usd += $total;
                                        $acum_total_taxed_usd += $total_taxed;
                                        $acum_total_igv_usd += $total_igv;
                                    }
                                }
                                $colspan = 23;
                                if ($config->show_channels_documents) {
                                    $colspan += 1;
                                }
                                if ($show_products) {
                                    $colspan += 2;
                                }
                            @endphp
                            @if ($show_products)
                                @foreach ($rest_items as $item)
                                    <tr>
                                        <td colspan="16"></td>
                                        @if (isset($item->item->description))
                                            <td class="celda">{{ $item->item->description }}</td>
                                            <td class="celda">{{ $item->quantity }}</td>
                                        @else
                                            <td class="celda"></td>
                                            <td class="celda"></td>
                                        @endif
                                        <td colspan="20"></td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach

                        <tr>
                            <td colspan="{{ $colspan }}"></td>
                            <td colspan="2">Totales PEN</td>
                            <td>{{ number_format($acum_total_charges, 2) }}</td>
                            <td>{{ number_format($acum_total_exonerado, 2) }}</td>
                            <td>{{ number_format($acum_total_inafecto, 2) }}</td>
                            <td>{{ number_format($acum_total_free, 2) }}</td>
                            <td>{{ number_format($acum_total_taxed, 2) }}</td>
                            <td></td>
                            <td>{{ number_format($acum_total_igv, 2) }}</td>
                            <td></td>
                            <td>{{ number_format($acum_total, 2) }}</td>
                        </tr>
                        @if(!$apply_conversion_to_pen)
                        <tr>
                            <td colspan="{{ $colspan }}"></td>
                            <td colspan="2">Totales USD</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ number_format($acum_total_taxed_usd, 2) }}</td>
                            <td></td>
                            <td>{{ number_format($acum_total_igv_usd, 2) }}</td>
                            <td></td>
                            <td>{{ number_format($acum_total_usd, 2) }}</td>
                        </tr>
                        @endif

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
