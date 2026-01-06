<!DOCTYPE html>
<html>

<head>
    <title>Recibo de Pago Múltiple</title>
    <style>
        @page {

            margin: 5px;

        }

        html {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-spacing: 0;
        }

        .mp-0 {
            margin: 0;
            padding: 0;

        }

        .celda {
            text-align: center;
            padding: 5px;
            border: 0.1px solid black;
        }

        th {
            padding: 5px;
            text-align: center;
        }

        .border-bottom {
            border-bottom: 1px dashed black;
        }

        .border-top {
            border-top: 1px dashed black;
        }

        .title {
            font-weight: bold;
            /*padding: 5px;*/
            font-size: 13px !important;
            text-decoration: underline;
        }

        p>strong {
            margin-left: 5px;
            font-size: 12px;
        }

        thead {
            font-weight: bold;
            text-align: center;
        }

        .td-custom {
            line-height: 0.1em;
        }

        .width-custom {
            width: 50%
        }

        .font-bold {
            font-weight: bold;
        }

        /*.full-width{
            width: 100%;
        }*/
        .desc-9 {
            font-size: 9px;
        }

        .desc {
            font-size: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .mt-3 {
            margin-top: 2.5rem;
        }

        .mb {
            margin-bottom: 0.5rem;
        }

        .mt {
            margin-top: 0.5rem;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        table,
        tr,
        td,
        th {
            /*font-size: 10px !important;*/
            padding: 0px;
            margin: 0px;
        }
        p{
            margin: 0.3rem;
        }
    </style>
</head>

<body>
    <div style="margin-top:10px">
        <p align="center" class="title"><strong>{{ strtoupper($company->name) }}</strong></p>
        <p align="center" class="mp-0 title"><strong>{{ $establishment->address }}</strong></p>
        @if (isset($establishment->district->description) && $establishment->district->description != '')
            <p align="center" class="mp-0 title"><strong>{{ $establishment->district->description }}</strong></p>
        @endif
        @if (isset($establishment->province->description) && isset($establishment->department->description))
            <p align="center" class="mp-0 title"><strong>{{ $establishment->province->description }} -
                    {{ $establishment->department->description }}</strong></p>
        @endif

        <p class="desc ">
            <strong>RUC:</strong> {{ $company->number }}
            <strong>
                TLF:
            </strong>{{ $establishment->telephone }}
        </p class="desc">
        <p>
            ---------------------------------------------------------------
        </p>
        <p align="center" class="mp-0 desc"><strong>CONSTANCIA DE PAGO
                {{ str_pad($multipayment->id, 8, '0', STR_PAD_LEFT) }}
            </strong></p>
        <p>
            ---------------------------------------------------------------
        </p>

        @php
        $user = $multipayment->user;
            $items = $multipayment->items;
            $first_item = $items->first();
            $document_payment = null;
            $document = null;
            if ($first_item->document_payment) {
                $document_payment = $first_item->document_payment;
                $document = $document_payment->document;
            } else {
                $document_payment = $first_item->sale_note_payment;
                $document = $document_payment->sale_note;
            }
            $customer = $document->customer;
            $payment_method_type = $document_payment->payment_method_type->description;

        @endphp
        <p class="desc">
            <strong>Cliente:</strong> {{ strtoupper($customer->name) }}
        </p>
        
        <p class="desc">
            <strong>Fecha de Emisión:</strong> {{ $multipayment->created_at->format('d/m/Y H:i:s') }}
        </p>

        <p class="desc">
            <strong>Usuario:</strong> {{ $user->name }}
        </p>

        <p class="desc">
            <strong>Método de Pago:</strong> {{ $payment_method_type }}
        </p>

        @if($document_payment->reference)
        <p class="desc">
            <strong>Referencia:</strong> {{ $document_payment->reference }}
        </p>
        @endif


        <p class="desc">
            <strong>Cta x cobrar:</strong> {{$multipayment->remaining}}
        </p>

        <p class="desc text-center">
            <strong>Documentos pagados</strong>
        </p>
    </div>
    <table>
        <thead>
            <tr>
                <th class="celda">Doc</th>
                <th class="celda">X cobrar</th>
                <th class="celda">Pagado</th>
                </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            @php
            $document_payment = null;
            $document = null;
            if ($item->document_payment) {
                $document_payment = $item->document_payment;
                $document = $document_payment->document;
            } else {
                $document_payment = $item->sale_note_payment;
                $document = $document_payment->sale_note;
            }
            @endphp
            <tr>
                <td class="celda">{{ $document->series }}-{{ $document->number }}</td>
                <td class="celda">{{ $item->remaining }}</td>
                <td class="celda">{{ $item->total }}</td>
            </tr>

            @endforeach
            <tr>
                <td
                colspan="3"
                >
            <strong>TOTAL PAGADO:</strong>{{$multipayment->payment}}
            <strong>SALDO:</strong>{{$multipayment->remaining}}
            </td>
            </tr>
</body>

</html>
