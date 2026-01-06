<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Pedido</title>
    <style>
        html {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            background: #fff;
        }
        body {
            background: #fff;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 5px;
        }
        #register {
            max-width: 340px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: none;
            padding: 18px 12px 24px 12px;
        }
        .company_logo_ticket {
            max-width: 120px;
            max-height: 60px;
            margin-bottom: 8px;
        }
        h3.title {
            font-size: 18px !important;
            font-weight: bold;
            color: #000;
            margin: 10px 0 6px 0;
            text-align: center;
            letter-spacing: 1px;
        }
        .info {
            text-align: center;
            color: #000;
            margin-bottom: 8px;
        }
        .info h5 {
            margin: 2px 0;
            font-size: 12px;
            font-weight: 400;
        }
        .mesa-fecha {
            text-align: center;
            text-transform: uppercase;
            font-size: 13px;
            margin-bottom: 2px;
            color: #000;
        }
        .fecha {
            text-align: center;
            font-size: 11px;
            margin-bottom: 2px;
            color: #000;
        }
        .to-carry {
            text-align: center;
            font-size: 13px;
            color: #000;
            font-weight: bold;
            margin-bottom: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        thead th {
            background: #fff;
            color: #000;
            font-weight: 600;
            padding: 7px 4px;
            border: none;
            font-size: 12px;
            border-bottom: 1px solid #000;
        }
        tbody td {
            padding: 7px 4px;
            border-bottom: 1px solid #000;
            font-size: 12px;
            color: #000;
        }
        tbody tr:last-child td {
            border-bottom: none;
        }
        .total-row th {
            background: #fff;
            color: #000;
            font-size: 14px;
            font-weight: bold;
            padding: 8px 4px;
            border-top: 2px solid #000;
        }
        .total-row th:last-child {
            color: #000;
            font-size: 15px;
        }
        .descripcion {
            text-transform: uppercase;
            font-weight: 500;
            color: #000;
        }
        .cantidad, .importe {
            text-align: center;
        }
        .footer {
            text-align: center;
            color: #000;
            font-size: 11px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div id="register">
    <div class="info">
        <div>
            @if($company->logo!=null)
                <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo_ticket contain">
            @else
                <img src="data:{{mime_content_type("logo/logo.jpg")}};base64, {{base64_encode(file_get_contents("logo/logo.jpg"))}}"  class="company_logo_ticket contain">
            @endif
        </div>
        @isset($establishment->aditional_information)
            <h5><b>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</b></h5>
        @endisset
        <h5>{{ ($establishment->address !== '-')? $establishment->address : '' }}{{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}{{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}{{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}</h5>
        @isset($establishment->trade_address)
            <h5>{{ ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : '' }}</h5>
        @endisset
        <h5>Teléfonos:{{ ($establishment->telephone !== '-')? $establishment->telephone : '' }}</h5>
        <h5>{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</h5>
        @isset($establishment->web_address)
            <h5>{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</h5>
        @endisset
    </div>
    <h3 class="title">COMANDA <br>N° ORDEN {{ $orden->id }}</h3>
    @if ($batch_number > 0)
        <h4 style="text-align: center;font-size: 14px;font-weight: bold;color: #000; margin:0;">
            Atención N°: {{ $batch_number }}
        </h4>
    @endif
    @if($orden->table_id)
        <div class="text-center">
            <h2 style="margin: 0px;text-transform: uppercase;text-align: center;">Nº MESA {{ str_pad($orden->mesa->number, 2, "0", STR_PAD_LEFT)}} </h2>
        </div>
        
    @endif
    <div class="fecha">
        FECHA {{ $date }}
    </div>
    @if ($orden->to_carry==1)
        <div class="to-carry">PEDIDO PARA LLEVAR</div>
        <div style="text-align: center;font-size: 14px;font-weight: bold;color: #000; margin:0;">
            {{ $orden->reference }}
        </div>
    @endif
    <table>
        <thead>
            <tr>
                <th class="cantidad">Cant.</th>
                <th class="descripcion">Descripción</th>
                @if(!$category_id)
                    <th class="importe" style="text-align: right;width: 65px;">Importe</th>
                @endif
            </tr>
        </thead>
        <tbody>
            <?php $total=0; ?>
            @foreach($orden_items as $row)
                <?php $total=$total+$row->price*$row->quantity; ?>
                <tr>
                    <td class="cantidad">{{$row->quantity}}</td>
                    <td class="descripcion">{{ strtoupper($row->item->description)}}

                        @if($row->observations && $row->observations != '-')
                            <div style="font-size: 10px;color: #000;">Obs: {{ $row->observations }}</div>
                        @endif
                    </td>
                    @if(!$category_id)
                        <td class="importe" style="text-align: right;">S/ {{ number_format($row->price * $row->quantity,2) }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                @if(!$category_id)
                    <th colspan="2" style="text-align: right">Total S/ </th>
                    <th style="text-align: right;">{{ number_format($total,2) }}</th>
                @else
                    <th colspan="3" style="text-align: right"></th>
                
                @endif
            </tr>
        </tfoot>
    </table>
    <div class="footer">
        ¡Gracias por su preferencia!
    </div>
</div>
</body>
</html>


