@php
    
    // $total_ = $documents->count()+100;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ticket encomienda</title>
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
    </style>
</head>

<body>
    <div style="margin-top:-15px">
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
                Cel:
            </strong>{{ $establishment->telephone }}
        </p class="desc">

        <p align="center" class="mp-0 desc"><strong>RECIBO DE CAJA</strong></p>
        <p align="center" class="mp-0 desc">{{ $data->series }}-{{ $data->number }}</p>

    </div>
    <div>

        <table class="mb mt">
            <tr>
                <td width="" class="desc font-bold">SRES:</td>
                @php
                    $name = $data->customer->name;
                    $name = explode(' ', $name);
                    $name = $name[0];
                @endphp
                <td width="" class="desc">{{ $name }}</td>
            </tr>
            <tr>
                <td width="" class="desc font-bold">VEH:</td>
                <td width="" class="desc">{{ $data->license_plate }}</td>
            </tr>
            <tr>
                <td width="" class="desc font-bold">FECHA:</td>
                <td width="" class="desc">{{ $data->date_of_issue->format('d/m/Y') }}</td>
            </tr>


        </table>
    </div>
    @php
        $total = 0;
        $subTotal = 0;
    @endphp
    <table width="100%">
        <tbody>
            @foreach ($data->items as $idx => $item)
                <td class="desc-9" width="10%">{{ $idx == 0 ? 'POR:' : '' }}</td>
                <td class="desc-9">
                    @if ($item->name_product_pdf)
                        {!! $item->name_product_pdf !!}
                    @else
                        {{ $item->item->description }}
                    @endif

                </td>
                <td class="desc-9 text-right" width="15%">{{ $item->total }}</td>
            @endforeach
            <tr>
                <td class="desc-9" width="10%"></td>
                <td class="desc-9">TOTAL</td>
                <td class="desc-9 text-right" width="15%">S/ {{ number_format($data->total, 2) }}</td>
            </tr>
        </tbody>
    </table>
    <div class="mt">
        <span class="desc-9">
            <strong>CAJERO:</strong>
            {{ strtoupper($data->seller->name) }}
        </span>
    </div>
    {{-- <table class="" width="100px">
        <thead class="">
            <tr>
                <th class=" border-bottom text-center" width="20px" style="padding-top:4px; padding-bottom:4px; ">
                    Cant.</th>
                <th class=" border-bottom text-center" width="40px" style="padding-top:4px; padding-bottom:4px; ">
                    Descripción</th>
                <th class=" border-bottom text-center" width="40px" style="padding-top:4px; padding-bottom:4px; ">
                    Importe S/</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->items as $item)
                <tr>
                    <td class="desc text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td class="desc">{{ $item->item['description'] }}</td>
                    <td class="desc text-right">{{ $item->total }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="text-center desc align-center" colspan="3">
                    -------------------------------------------------------------</td>
            </tr>

            <tr>
                <td class="text-center desc align-center font-bold" colspan="2">TOTAL S/ </td>
                <td class="text-center desc text-right font-bold">
                    {{ $data->total }}
                </td>
            </tr>
            <tr>
                <td class="text-center desc align-center" colspan="3">
                    -------------------------------------------------------------</td>
            </tr>
        </tbody>
    </table>

    <table width="100%" class="mt-3">
        <tr>
            <td class="desc font-bold">Chofer:</td>
            <td class="desc">{{ $data->driver['name'] }}</td>
        </tr>
    </table>
    <table width="100%" class="mt-3">
        <thead>
            <tr>
                <th class="border-top text-center desc-9" width="33px">FIRMA DEL REMITENTE</th>
                <th class="border-top text-center desc-9" width="33px">RECIBI CONFORME</th>
                <th class="border-top text-center desc-9" width="33px">FIRMA DEL DESTINATARIO</th>
            </tr>
        </thead>
    </table> --}}

</body>

</html>
