<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Formato vehicular</title>

    <style>
        body {
            font-size: 12px;
            font-family: Arial, sans-serif;
        }
        @page{
            margin-top:10px;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        .font-md {
            font-size: 12px;
        }

        .font-lg {
            font-size: 14px;
        }

        .font-xlg {
            font-size: 16px;
        }

        .font-bold {
            font-weight: bold;
        }

        .company_logo {
            max-height: 100px;
        }

        .company_logo_box {
            height: 100px;
        }

        .company_logo_ticket {
            max-width: 200px;
            max-height: 150px
        }

        .contain {
            object-fit: cover;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .centered {
            z-index: -1;
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 120%;
            transform: translate(-25%, -25%);
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .text-lowercase {
            text-transform: lowercase;
        }

        .text-left {
            text-align: left !important;
        }

        .align-top {
            vertical-align: top !important;
        }

        .full-width {
            width: 100%;
        }

        .m-10 {
            margin: 10px;
        }

        .mt-10 {
            margin-top: 10px;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .m-20 {
            margin: 20px;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .p-20 {
            padding: 20px;
        }

        .pt-20 {
            padding-top: 20px;
        }

        .pb-20 {
            padding-bottom: 20px;
        }

        .p-10 {
            padding: 10px;
        }

        .pt-10 {
            padding-top: 10px;
        }

        .pb-10 {
            padding-bottom: 10px;
        }

        .border-box {
            border: thin solid #333;
        }

        .border-top {
            border-top: thin solid #333;
        }

        .border-bottom {
            border-bottom: thin solid #333;
        }

        .border-top-bottom {
            border-top: thin solid #333;
            border-bottom: thin solid #333;
        }

        .bg-grey {
            background-color: #F8F8F8;
        }

        .logo {}

        /* Headings */
        p,
        p,
        p,
        p,
        p,
        p {
            font-weight: 200;
            letter-spacing: -1px;
        }

        p {
            font-size: 32px;
            line-height: 44px;
            font-weight: 500;
        }

        p {
            font-size: 24px;
            font-weight: 500;
            line-height: 42px;
        }

        p {
            font-size: 18px;
            font-weight: 400;
            letter-spacing: normal;
            line-height: 24px;
        }

        p {
            font-size: 16px;
            font-weight: 400;
            letter-spacing: normal;
            line-height: 27px;
        }

        p {
            font-size: 13px;
            font-weight: 300;
            letter-spacing: normal;
            line-height: 18px;
        }

        p {
            font-size: 10px;
            font-weight: 300;
            letter-spacing: normal;
            line-height: 18px;
        }

        table,
        tr,
        td,
        th {
            font-size: 12px !important;
            /* border: 1px solid #000; */
        }

        p {
            font-size: 12px !important;
        }

        .desc {
            font-size: 10px !important;
        }

        .desc-ticket {
            font-size: 1rem !important;
        }

        .desc-9 {
            font-size: 9px !important;
        }

        .m-0 {
            margin: 0 !important;
        }

        .mt-0,
        .my-0 {
            margin-top: 0 !important;
        }

        .mr-0,
        .mx-0 {
            margin-right: 0 !important;
        }

        .mb-0,
        .my-0 {
            margin-bottom: 0 !important;
        }

        .ml-0,
        .mx-0 {
            margin-left: 0 !important;
        }

        .m-1 {
            margin: 0.25rem !important;
        }

        .mt-1,
        .my-1 {
            margin-top: 0.25rem !important;
        }

        .mr-1,
        .mx-1 {
            margin-right: 0.25rem !important;
        }

        .mb-1,
        .my-1 {
            margin-bottom: 0.25rem !important;
        }

        .ml-1,
        .mx-1 {
            margin-left: 0.25rem !important;
        }

        .m-2 {
            margin: 0.5rem !important;
        }

        .mt-2,
        .my-2 {
            margin-top: 0.5rem !important;
        }

        .mr-2,
        .mx-2 {
            margin-right: 0.5rem !important;
        }

        .mb-2,
        .my-2 {
            margin-bottom: 0.5rem !important;
        }

        .ml-2,
        .mx-2 {
            margin-left: 0.5rem !important;
        }

        .m-3 {
            margin: 1rem !important;
        }

        .mt-3,
        .my-3 {
            margin-top: 1rem !important;
        }

        .mr-3,
        .mx-3 {
            margin-right: 1rem !important;
        }

        .mb-3,
        .my-3 {
            margin-bottom: 1rem !important;
        }

        .ml-3,
        .mx-3 {
            margin-left: 1rem !important;
        }

        .m-4 {
            margin: 1.5rem !important;
        }

        .mt-4,
        .my-4 {
            margin-top: 1.5rem !important;
        }

        .mr-4,
        .mx-4 {
            margin-right: 1.5rem !important;
        }

        .mb-4,
        .my-4 {
            margin-bottom: 1.5rem !important;
        }

        .ml-4,
        .mx-4 {
            margin-left: 1.5rem !important;
        }

        .m-5 {
            margin: 3rem !important;
        }

        .mt-5,
        .my-5 {
            margin-top: 3rem !important;
        }

        .mr-5,
        .mx-5 {
            margin-right: 3rem !important;
        }

        .mb-5,
        .my-5 {
            margin-bottom: 3rem !important;
        }

        .ml-5,
        .mx-5 {
            margin-left: 3rem !important;
        }

        .p-0 {
            padding: 0 !important;
        }

        .pt-0,
        .py-0 {
            padding-top: 0 !important;
        }

        .pr-0,
        .px-0 {
            padding-right: 0 !important;
        }

        .pb-0,
        .py-0 {
            padding-bottom: 0 !important;
        }

        .pl-0,
        .px-0 {
            padding-left: 0 !important;
        }

        .p-1 {
            padding: 0.25rem !important;
        }

        .pt-1,
        .py-1 {
            padding-top: 0.25rem !important;
        }

        .pr-1,
        .px-1 {
            padding-right: 0.25rem !important;
        }

        .pb-1,
        .py-1 {
            padding-bottom: 0.25rem !important;
        }

        .pl-1,
        .px-1 {
            padding-left: 0.25rem !important;
        }

        .p-2 {
            padding: 0.5rem !important;
        }

        .pt-2,
        .py-2 {
            padding-top: 0.5rem !important;
        }

        .pr-2,
        .px-2 {
            padding-right: 0.5rem !important;
        }

        .pb-2,
        .py-2 {
            padding-bottom: 0.5rem !important;
        }

        .pl-2,
        .px-2 {
            padding-left: 0.5rem !important;
        }

        .p-3 {
            padding: 1rem !important;
        }

        .pt-3,
        .py-3 {
            padding-top: 1rem !important;
        }

        .pr-3,
        .px-3 {
            padding-right: 1rem !important;
        }

        .pb-3,
        .py-3 {
            padding-bottom: 1rem !important;
        }

        .pl-3,
        .px-3 {
            padding-left: 1rem !important;
        }

        .p-4 {
            padding: 1.5rem !important;
        }

        .pt-4,
        .py-4 {
            padding-top: 1.5rem !important;
        }

        .pr-4,
        .px-4 {
            padding-right: 1.5rem !important;
        }

        .pb-4,
        .py-4 {
            padding-bottom: 1.5rem !important;
        }

        .pl-4,
        .px-4 {
            padding-left: 1.5rem !important;
        }

        .p-5 {
            padding: 3rem !important;
        }

        .pt-5,
        .py-5 {
            padding-top: 3rem !important;
        }

        .pr-5,
        .px-5 {
            padding-right: 3rem !important;
        }

        .pb-5,
        .py-5 {
            padding-bottom: 3rem !important;
        }

        .pl-5,
        .px-5 {
            padding-left: 3rem !important;
        }

        .m-n1 {
            margin: -0.25rem !important;
        }

        .mt-n1,
        .my-n1 {
            margin-top: -0.25rem !important;
        }

        .mr-n1,
        .mx-n1 {
            margin-right: -0.25rem !important;
        }

        .mb-n1,
        .my-n1 {
            margin-bottom: -0.25rem !important;
        }

        .ml-n1,
        .mx-n1 {
            margin-left: -0.25rem !important;
        }

        .m-n2 {
            margin: -0.5rem !important;
        }

        .mt-n2,
        .my-n2 {
            margin-top: -0.5rem !important;
        }

        .mr-n2,
        .mx-n2 {
            margin-right: -0.5rem !important;
        }

        .mb-n2,
        .my-n2 {
            margin-bottom: -0.5rem !important;
        }

        .ml-n2,
        .mx-n2 {
            margin-left: -0.5rem !important;
        }

        .m-n3 {
            margin: -1rem !important;
        }

        .mt-n3,
        .my-n3 {
            margin-top: -1rem !important;
        }

        .mr-n3,
        .mx-n3 {
            margin-right: -1rem !important;
        }

        .mb-n3,
        .my-n3 {
            margin-bottom: -1rem !important;
        }

        .ml-n3,
        .mx-n3 {
            margin-left: -1rem !important;
        }

        .m-n4 {
            margin: -1.5rem !important;
        }

        .mt-n4,
        .my-n4 {
            margin-top: -1.5rem !important;
        }

        .mr-n4,
        .mx-n4 {
            margin-right: -1.5rem !important;
        }

        .mb-n4,
        .my-n4 {
            margin-bottom: -1.5rem !important;
        }

        .ml-n4,
        .mx-n4 {
            margin-left: -1.5rem !important;
        }

        .m-n5 {
            margin: -3rem !important;
        }

        .mt-n5,
        .my-n5 {
            margin-top: -3rem !important;
        }

        .mr-n5,
        .mx-n5 {
            margin-right: -3rem !important;
        }

        .mb-n5,
        .my-n5 {
            margin-bottom: -3rem !important;
        }

        .ml-n5,
        .mx-n5 {
            margin-left: -3rem !important;
        }

        .m-auto {
            margin: auto !important;
        }

        .mt-auto,
        .my-auto {
            margin-top: auto !important;
        }

        .mr-auto,
        .mx-auto {
            margin-right: auto !important;
        }

        .mb-auto,
        .my-auto {
            margin-bottom: auto !important;
        }

        .ml-auto,
        .mx-auto {
            margin-left: auto !important;
        }

        p {
            padding: 0 1px;
            margin: 2px;
        }
    </style>
</head>

<body>
    @php
        $customer = $technical_service->customer;
        $states = ['Bueno', 'Regular', 'Malo'];
    @endphp
    <div class="text-center">
        <strong style="font-size: 20px;
        text-decoration: underline;
        ">
            FORMATO DE RECEPCIÓN DE VEHÍCULO
        </strong>
    </div>
    <div class="text-left">
        <p style="font-size: 18px !important;">{{ $company->name }}</p>
        <p style="font-size: 15px !important;">{{ 'RUC ' . $company->number }}</p>
        <p style="text-transform: uppercase;font-size: 14px !important;">
            {{ $establishment->address !== '-' ? $establishment->address : '' }}
            {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
            {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
            {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
        </p>

        @isset($establishment->trade_address)
            <p>{{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
            </p>
        @endisset

        <p>{{ $establishment->telephone !== '-' ? 'Central telefónica: ' . $establishment->telephone : '' }}
        </p>

        <p>{{ $establishment->email !== '-' ? 'Email: ' . $establishment->email : '' }}</p>

        @isset($establishment->web_address)
            <p>{{ $establishment->web_address !== '-' ? 'Web: ' . $establishment->web_address : '' }}</p>
        @endisset

        @isset($establishment->aditional_information)
            <p>{{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
            </p>
        @endisset
    </div>
    <table class="full-width border-box">
        <thead>
            <tr>
                <th class="border-box bg-grey">DATOS GENERALES</th>
                <th class="border-box bg-grey">DATOS DEL VEHICULO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td valign="top" width="50%" class="border-box">
                    <p>
                        <strong>Raz. Social:</strong>
                        {{ $customer->name }}
                    </p>
                    <p>
                        <strong>
                            Dirección:
                        </strong>
                        {{ $customer->address }}
                    </p>
                    <p>
                        <strong>Placa:</strong>
                        {{ $technical_service_car->plate_number }}
                    </p>
                    <p>
                        <strong>
                            Ases. Comercial:
                        </strong>
                        {{ $technical_service->user->name }}
                    </p>
                    <p>
                        <strong>
                            N° Flota / Kilometraje:
                        </strong>
                        {{ $technical_service_car->km }}
                    </p>
                </td>
                <td valign="top">
                    <p>
                        <strong>Color:</strong>
                        {{ $technical_service_car->color }}
                        /
                        <strong>
                            Año:
                        </strong>
                        {{ $technical_service_car->year }}
                    </p>
                    <p>
                        <strong>Chasis / VIN:</strong>
                        {{ $technical_service_car->chasis }}
                    </p>
                    <p>
                        <strong>Venc. SOAT:</strong>
                        {{ $technical_service_car->date_soat_due }}
                    </p>
                    <p>
                        <strong>
                            Venc. Rev. Tec.:
                        </strong>
                        {{ $technical_service_car->date_check_due }}
                    </p>
                    <p>
                        <strong>
                            Cert. Anual:
                        </strong>
                        {{ $technical_service_car->certificate }}
                    </p>
                    <p>
                        <strong>
                            Marca:
                        </strong>
                        {{ $technical_service_car->brand ?? "-"}}
                        <strong>
                            Model:
                        </strong>
                        {{ $technical_service_car->model ?? "-" }}
                    </p>
                    <p>
                        <strong>
                            Fecha:
                        </strong>
                        {{ $technical_service->date_of_issue->format('Y-m-d') }}

                        <strong>Hora:</strong>
                        {{ $technical_service->time_of_issue }}
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="full-width">
        <tbody>
            <td width="49%" valign="top">
                <table class="full-width border-box">
                    <thead>
                        <tr>
                            <th class="border-box" colspan="3">ESTADOS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td width="33%" class="border-box">
                                <strong>Bueno</strong>
                            </td>
                            <td class="border-box">
                                <strong>Regular</strong>
                            </td>
                            <td class="border-box">
                                <strong>Malo</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="height: 15px;"></div>
                            </td>
                            <td style="background-color: #333"></td>
                            <td style="background-color: red"></td>
                        </tr>
                    </tbody>
                </table>
                <table class="full-width mt-10">
                    <thead>
                        <tr>
                            <th class="border-box" colspan="5">ACCESORIOS EXTERNOS</th>
                        </tr>
                        <tr>
                            <th class="border-box" width="55%">Descripción</th>
                            <th class="border-box" width="25%">Cant</th>
                            <th class="border-box">
                                <div style="height: 15px;"></div>
                            </th>
                            <th class="border-box" style="background-color: #333"></th>
                            <th class="border-box" style="background-color: red"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($list1 as $item)
                            <tr>
                                <td class="border-box">
                                    {{ $item['label'] }}
                                </td>
                                <td class="text-center border-box">
                                    {{ $item['quantity'] }}
                                </td>
                                @foreach ($states as $state)
                                    <td class="text-center border-box">
                                        @if ($item['state'] == $state)
                                            X
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>

                </table>
                <table class="full-width mt-10">
                    <thead>
                        <tr>
                            <th class="border-box" colspan="2">
                                AUTORIZACIONES
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border-box " width="70%">
                                Autorizo conducir mi vehículo para pruebas en exteriores del Taller
                            </td>
                            <td class="border-box text-center">
                                {{ $technical_service_car->auth_drive ? 'X' : '' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="border-box " width="70%">
                                Autorizo enviar mi vehículo para trabajos de terceros en Talleres de su elección
                            </td>
                            <td class="border-box text-center">
                                {{ $technical_service_car->move_on ? 'X' : '' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="border-box " width="70%">
                                Declaro que no existen elementos de valor dentro del vehiculo
                            </td>
                            <td class="border-box text-center">
                                {{ $technical_service_car->no_value_things ? 'X' : '' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="border-box " width="70%">
                                Acepto retirar mi vehículo en un máximo de 3 dias, luego de finalizado el servicio; caso
                                contrario asumiré un costo de 5/7.00 diarios por cochera (interna y/o externa)
                            </td>
                            <td class="border-box text-center">
                                {{ $technical_service_car->cost_for_days ? 'X' : '' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="full-width mt-10">
                    <thead>
                        <tr>
                            <th class="border-box text-center">Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border-box">
                                {{ $technical_service_car->observations }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td></td>
            <td width="49%" valign="top">
                <table class="full-width">
                    <thead>
                        <tr>
                            <th class="border-box" colspan="5">ACCESORIOS INTERNOS</th>
                        </tr>
                        <tr>
                            <th class="border-box" width="55%">Descripción</th>
                            <th class="border-box" width="25%">Cant</th>
                            <th class="border-box">
                                <div style="height: 15px;"></div>
                            </th>
                            <th class="border-box" style="background-color: #333"></th>
                            <th class="border-box" style="background-color: red"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($list2 as $item)
                            <tr>
                                <td class="border-box">
                                    {{ $item['label'] }}
                                </td>
                                <td class="text-center border-box">
                                    {{ $item['quantity'] }}
                                </td>
                                @foreach ($states as $state)
                                    <td class="text-center border-box">
                                        @if ($item['state'] == $state)
                                            X
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>

                </table>
                <table class="full-width mt-10">
                    <thead>
                        <tr>
                            <th class="border-box" colspan="5">HERRAMIENTAS</th>
                        </tr>
                        <tr>
                            <th class="border-box" width="55%">Descripción</th>
                            <th class="border-box" width="25%">Cant</th>
                            <th class="border-box">
                                <div style="height: 15px;"></div>
                            </th>
                            <th class="border-box" style="background-color: #333"></th>
                            <th class="border-box" style="background-color: red"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($list3 as $item)
                            <tr>
                                <td class="border-box">
                                    {{ $item['label'] }}
                                </td>
                                <td class="text-center border-box">
                                    {{ $item['quantity'] }}
                                </td>
                                @foreach ($states as $state)
                                    <td class="text-center border-box">
                                        @if ($item['state'] == $state)
                                            X
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>

                </table>
                <table class="full-width mt-10">
                    <thead>
                        <tr>
                            <th class="border-box" colspan="5">DOCUMENTOS DE VEHICULO</th>
                        </tr>
                        <tr>
                            <th class="border-box" width="55%">Descripción</th>
                            <th class="border-box" width="25%">Cant</th>
                            <th class="border-box">
                                <div style="height: 15px;"></div>
                            </th>
                            <th class="border-box" style="background-color: #333"></th>
                            <th class="border-box" style="background-color: red"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($list4 as $item)
                            <tr>
                                <td class="border-box">
                                    {{ $item['label'] }}
                                </td>
                                <td class="text-center border-box">
                                    {{ $item['quantity'] }}
                                </td>
                                @foreach ($states as $state)
                                    <td class="text-center border-box">
                                        @if ($item['state'] == $state)
                                            X
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>

                </table>
                <div class="full-width">
                    <div class="border-box
                    mt-10
                    text-center
                    "
                        style="position: relative;height:30px; ">
                        <div
                            style="position: absolute;
                        width: {{ $technical_service_car->gasoline_level }}%;
                        background-color: #82a1b1;
                        height: 30px;
                        z-index: -1;
                        ">
                        </div>
                        <span style="margin-top: 10px;position: absolute; z-index: 1; width: 100%;">
                            <strong>
                                Gasolina en el tanque: {{ $technical_service_car->gasoline_level }}%
                            </strong>
                        </span>
                        
                    </div>
                </div>
                <div class="full-width mt-10 text-center">
                    <img src="{{ module_path('Sale') . '/Resources/views/technical-services/vehicle3.jpg' }}" style="width: 100%; max-width: 350px; display: block; margin: 0 auto;">
                </div>
            </td>
        </tbody>
    </table>
</body>

</html>
