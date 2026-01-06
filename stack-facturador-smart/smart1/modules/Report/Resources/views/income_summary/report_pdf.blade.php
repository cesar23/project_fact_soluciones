@php

$establishment = $cash->user->establishment;
$state_types_accepted = ['01', '03', '05', '07', '13'];
// $cash_documents = $cash->cash_documents;
$cash_documents = \App\Models\Tenant\DocumentPayment::whereHas('document', function ($query) use ($cash, $state_types_accepted) {
            $query->where('cash_id', $cash->id)
                ->whereIn('state_type_id', $state_types_accepted);
        })->get();
$cash_sales_note = \App\Models\Tenant\SaleNotePayment::whereHas('sale_note', function ($query) use ($cash, $state_types_accepted) {
            $query->where('cash_id', $cash->id)
                ->whereIn('state_type_id', $state_types_accepted)
                ->where(function ($query) {
                    $query->whereNull('quotation_id') // No tiene cotización
                        ->orWhereHas('quotation', function ($q2) {
                            $q2->doesntHave('payments'); // Tiene cotización, pero sin pagos
                        });
                });
        })->get();

        $quotation_payments = \Modules\Sale\Models\QuotationPayment::whereHas('quotation', function ($query) use ($cash, $state_types_accepted) {
            $query->where('cash_id', $cash->id)
                ->whereIn('state_type_id', $state_types_accepted);
        })
            ->whereDoesntHave('global_payment')
            ->get();

        $quotation_payments_global_payment = \Modules\Sale\Models\QuotationPayment::whereHas('global_payment', function ($query) use ($cash, $state_types_accepted) {
            $query->where('destination_id', $cash->id)
                ->where('destination_type', Cash::class);
        })->get();

        $cash_quotations = $quotation_payments->merge($quotation_payments_global_payment);


$cash_documents_credit = $cash->cash_documents_credit;

$totals_income_summary = $cash->getTotalsIncomeSummary();

@endphp
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Reporte Resúmen de ingresos - {{$cash->user->name}} - {{$cash->date_opening}} {{$cash->time_opening}}</title>
        <style>
            html {
                font-family: sans-serif;
                font-size: 12px;
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
                font-size: 12px;
            }
            
            thead tr th {
                font-weight: bold;
                background: #0088cc;
                color: white;
                text-align: center;
            }
            .width-custom {
                width: 50%
            }
        </style>
    </head>
    <body>
        <div>
            <p align="center" class="title"><strong>Resúmen de ingresos por métodos de pago</strong></p>
        </div>
        <div style="margin-top:20px; margin-bottom:20px;">
            <table> 
                <tr>
                    <td class="td-custom width-custom">
                        <p><strong>Empresa: </strong>{{$company->name}}</p>
                    </td>
                    <td class="td-custom">
                        <p><strong>Fecha reporte: </strong>{{date('Y-m-d')}}</p>
                    </td>
                </tr>
                <tr>
                    <td class="td-custom">
                        <p><strong>Ruc: </strong>{{$company->number}}</p>
                    </td>
                    <td class="td-custom width-custom">
                        <p><strong>Establecimiento: </strong>{{$establishment->address}} - {{$establishment->department->description}} - {{$establishment->district->description}}</p>
                    </td>
                </tr>
                
                <tr>
                    <td class="td-custom">
                        <p><strong>Vendedor: </strong>{{$cash->user->name}}</p>
                    </td>
                    <td class="td-custom">
                        <p><strong>Fecha y hora apertura: </strong>{{$cash->date_opening}} {{$cash->time_opening}}</p>
                    </td>
                </tr>
                <tr>
                    <td class="td-custom">
                        <p><strong>Estado de caja: </strong>{{($cash->state) ? 'Aperturada':'Cerrada'}}</p>
                    </td>
                    @if(!$cash->state)
                    <td class="td-custom">
                        <p><strong>Fecha y hora cierre: </strong>{{$cash->date_closed}} {{$cash->time_closed}}</p>
                    </td>
                    @endif
                </tr> 
                
                <tr>
                    <td class="td-custom">
                        <p><strong>Total comprobantes: </strong>S/ {{ $totals_income_summary['document_total_payments'] }}</p>
                    </td>
                    <td class="td-custom">
                        <p><strong>Total notas de venta: </strong>S/ {{ $totals_income_summary['sale_note_total_payments'] }}</p>
                    </td>
                </tr> 
            </table> 
        </div>

        @php
            // dd($order_cash_income);
        @endphp

        @if (isset($order_cash_income))
            @include('report::income_summary.partials.table_payments')
        @else
            
            @if($cash_documents->count())
                <div class="">
                    <div class=" ">
                        <table class="">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha y hora emisión</th>
                                    <th>Tipo documento</th>
                                    <th>Documento</th>
                                    <th>Método de pago</th> 
                                    <th>Moneda</th>
                                    <th>Importe</th>
                                    <th>Vuelto</th>
                                    <th>Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                    @php
                                        
                                        $type_transaction =  null;
                                        $document_type_description = null;
                                        $number = null;
                                        $date_time_of_issue = null;
                                        $payment_method_description = null;
                                        $total = null;  
                                        $currency_type_id = null;

                                    @endphp

    
                                        @foreach($cash_sales_note as $payment)
                                        <tr>
                                            
                                            @php
                                                $type_transaction =  'Venta';
                                                $document_type_description =  'NOTA DE VENTA';
                                                $number = $value->sale_note->number_full;
                                                $date_time_of_issue = "{$value->sale_note->date_of_issue->format('Y-m-d')} {$value->sale_note->time_of_issue}";
                                                $payment_method_description = $payment->payment_method_type->description;
                                                $total = $payment->payment;
                                                
                                                if(!in_array($payment->associated_record_payment->state_type_id, ['01','03','05','07','13'])){
                                                    $total = 0;
                                                }

                                                $currency_type_id = $value->sale_note->currency_type_id;

                                            @endphp


                                            <td class="celda">{{ $loop->iteration }}</td>
                                            <td class="celda">{{ $date_time_of_issue}}</td>
                                            <td class="celda">{{ $document_type_description }}</td>
                                            <td class="celda">{{ $number }}</td>
                                            <td class="celda">{{$payment_method_description }}</td>  
                                            <td class="celda">{{$currency_type_id }}</td>  
                                            <td class="celda">{{ number_format($value->sale_note->total,2) }}</td>
                                            <td class="celda">{{ number_format($payment->change,2) }}</td>
                                            <td class="celda">{{ number_format($total,2) }}</td>

                                        </tr>
                                        @endforeach

                                    
                                        @foreach($cash_documents as $payment)
                                        <tr>
                                            @php
                                                $type_transaction =  'Venta';
                                                $document_type_description =  $value->document->document_type->description;
                                                $number = $value->document->number_full;
                                                $date_time_of_issue = "{$value->document->date_of_issue->format('Y-m-d')} {$value->document->time_of_issue}";
                                                $payment_method_description = $payment->payment_method_type->description;
                                                $total = $payment->payment;
                                                
                                                if(!in_array($payment->associated_record_payment->state_type_id, ['01','03','05','07','13'])){
                                                    $total = 0;
                                                }
                                                
                                                $currency_type_id = $value->document->currency_type_id;

                                            @endphp


                                            <td class="celda">{{ $loop->iteration }}</td>
                                            <td class="celda">{{ $date_time_of_issue}}</td>
                                            <td class="celda">{{ $document_type_description }}</td>
                                            <td class="celda">{{ $number }}</td>
                                            <td class="celda">{{$payment_method_description }}</td>  
                                            <td class="celda">{{$currency_type_id }}</td>  
                                            <td class="celda">{{ number_format($value->document->total,2) }}</td>
                                            <td class="celda">{{ number_format($payment->change,2) }}</td>
                                            <td class="celda">{{ number_format($total,2) }}</td>

                                        </tr>
                                        @endforeach
                                        @foreach($cash_quotations as $payment)
                                        <tr>
                                            @php
                                                $type_transaction =  'Venta';
                                                $document_type_description =  'COTIZACIÓN';
                                                $number = $payment->quotation->number_full;
                                                $date_time_of_issue = "{$payment->quotation->date_of_issue->format('Y-m-d')} {$payment->quotation->time_of_issue}";
                                                $payment_method_description = $payment->payment_method_type->description;
                                                $total = $payment->payment;
                                                
                                                if(!in_array($payment->associated_record_payment->state_type_id, ['01','03','05','07','13'])){
                                                    $total = 0;
                                                }
                                                
                                                $currency_type_id = $payment->quotation->currency_type_id;

                                            @endphp


                                            <td class="celda">{{ $loop->iteration }}</td>
                                            <td class="celda">{{ $date_time_of_issue}}</td>
                                            <td class="celda">{{ $document_type_description }}</td>
                                            <td class="celda">{{ $number }}</td>
                                            <td class="celda">{{$payment_method_description }}</td>  
                                            <td class="celda">{{$currency_type_id }}</td>  
                                            <td class="celda">{{ number_format($payment->quotation->total,2) }}</td>
                                            <td class="celda">{{ number_format($payment->change,2) }}</td>
                                            <td class="celda">{{ number_format($total,2) }}</td>

                                        </tr>
                                        @endforeach


                                @foreach($cash_documents_credit as $value)
                                
                                    @php
                                        
                                        $type_transaction =  null;
                                        $document_type_description = null;
                                        $number = null;
                                        $date_time_of_issue = null;
                                    
                                        $total = null;  
                                        $currency_type_id = null;

                                    @endphp

                                    @if($value->sale_note)
    
                                    
                                        <tr>
                                            
                                            @php
                                                $document = $value->sale_note;
                                                $type_transaction =  'Venta';
                                                $document_type_description =  'NOTA DE VENTA';
                                                $number = $document->number_full;
                                                $date_time_of_issue = "{$document->date_of_issue->format('Y-m-d')} {$document->time_of_issue}";
                                                $payment_method_description = 'Crédito';
                                                $total = 0;
                                                
                                                $currency_type_id = $document->currency_type_id;

                                            @endphp


                                            <td class="celda">{{ $loop->iteration }}</td>
                                            <td class="celda">{{ $date_time_of_issue}}</td>
                                            <td class="celda">{{ $document_type_description }}</td>
                                            <td class="celda">{{ $number }}</td>
                                            <td class="celda">{{$payment_method_description }}</td>  
                                            <td class="celda">{{$currency_type_id }}</td>  
                                            <td class="celda">{{ number_format($document->total,2) }}</td>
                                            <td class="celda">0</td>
                                            <td class="celda">0</td>

                                        </tr>
                                        

                                    @elseif($value->document)
                                    
                                    
                                        <tr>
                                            @php
                                                $document = $value->document;
                                                $type_transaction =  'Venta';
                                                $document_type_description =  $document->document_type->description;
                                                $number = $document->number_full;
                                                $date_time_of_issue = "{$document->date_of_issue->format('Y-m-d')} {$document->time_of_issue}";
                                                $payment_method_description = 'Crédito';
                                            
                                                
                                            
                                                
                                                $currency_type_id = $document->currency_type_id;

                                            @endphp


                                            <td class="celda">{{ $loop->iteration }}</td>
                                            <td class="celda">{{ $date_time_of_issue}}</td>
                                            <td class="celda">{{ $document_type_description }}</td>
                                            <td class="celda">{{ $number }}</td>
                                            <td class="celda">{{$payment_method_description }}</td>  
                                            <td class="celda">{{$currency_type_id }}</td>  
                                            <td class="celda">{{ number_format($document->total,2) }}</td>
                                            <td class="celda">0</td>
                                            <td class="celda">0</td>

                                        </tr>
                                        
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="callout callout-info">
                    <p>No se encontraron registros.</p>
                </div>
            @endif

        @endif

    </body>
</html>
