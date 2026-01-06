{{-- resources/views/pdf/estados_financieros.blade.php --}}
<!DOCTYPE html>
<html lang="es">
@php
    if (!function_exists('format_number')) {
        function format_number($number, $decimals = 0, $negative = false)
        {
            if ($negative) {
                return '(' . number_format(abs($number), $decimals) . ')';
            }
            return number_format($number, $decimals);
        }
    }
@endphp

<head>
    <meta charset="utf-8">
    <title>Estados Financieros 0710</title>
    <style type="text/css">
        @page {
            margin: 5mm 2mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            text-align: center;
        }

        .w-100 {
            width: 100%;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        .small {
            font-size: 10px;
        }

        .mt-4 {
            margin-top: 4mm;
        }

        .mt-6 {
            margin-top: 6mm;
        }

        .mb-2 {
            margin-bottom: 2mm;
        }

        .mb-4 {
            margin-bottom: 4mm;
        }

        .pt-2 {
            padding-top: 2mm;
        }

        .pb-2 {
            padding-bottom: 2mm;
        }

        .p-1 {
            padding: 1mm;
        }

        .p-2 {
            padding: 2mm;
        }

        .border {
            border: 1px solid #000;
        }

        .bt {
            border-top: 1px solid #000;
        }

        .bb {
            border-bottom: 1px solid #000;
        }

        .bl {
            border-left: 1px solid #000;
        }

        .br {
            border-right: 1px solid #000;
        }

        .table {
            border-collapse: collapse;
        }



        .table-plain td,
        .table-plain th {
            padding: 1mm;
        }

        .thead {
            background: #f2f2f2;
        }

        .nowrap {
            white-space: nowrap;
        }

        .code {
            width: 16mm;
        }

        .amount {
            width: 26mm;
        }

        .desc {
            /* se adapta al ancho restante */
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: none;
        }

        .page-break {
            page-break-before: always;
        }

        .logo-box {
            width: 40mm;
            height: 12mm;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
        }

        .logo-box span {
            line-height: 12mm;
            display: inline-block;
            font-size: 10px;
        }

        .logo {
            width: 150px;
        }

        table td {
            padding: 0.15mm;
            margin: 0px;
        }

        table {
            border-collapse: collapse;
        }

        .cell {
            font-weight: bold;
            background-color: #969696;
        }

        .cell-md {
            font-size: 14px;
        }

        table.table1 th,
        table.table1 td,
        table.table1 tr {
            border: 1px solid #000;
        }

        table.table1 tbody td {
            font-size: 12px;
            padding: 0.7mm;
        }

        table.table2 th,
        table.table2 td,
        table.table2 tr {
            border: 1px solid #000;
        }

        table.table2 tbody td {
            padding: 0.7mm;
        }
    </style>
</head>

<body>

    <div style="width: 100%;text-align: left;">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(base_path('modules/Account/Resources/views/account_ledger/sunat.png'))) }}"
            alt="Logo" class="logo">
    </div>
    <div class="text-center" style="font-size:15px;margin-top: 8px;">
        REPORTE DEFINITVO
    </div>
    <div class="text-center" style="font-size:15px;font-weight: bold;margin-top: 18px;">
        FORMULARIO 0710 RENTA ANUAL {{$period}}
    </div>
    <div class="text-center" style="font-size:15px;font-weight: bold;margin-top: 18px;">
        TERCERA CATEGORÍA E ITF
    </div>
    <div class="text-center" style="font-size:15px;margin-top: 18px;">
        ESTADOS FINANCIEROS
    </div>

    <div style="width: 98%;border: 1px solid #000;padding: 7px;border-radius: 10px;margin: 0 auto;margin-top: 15px;">
        <table>
            <tr>
                <td width="200px" valign="top">
                    <strong>Número de RUC:</strong>
                </td>
                <td valign="top">{{ $company->number }}</td>
                <td width="35px"></td>
                <td width="150px" valign="top">
                    <strong>
                        Razón Social:
                    </strong>
                </td>
                <td valign="top">
                    {{ $company->name }}
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <strong>Periodo tributario:</strong>
                </td>
                <td valign="top">{{$period}}</td>
                <td></td>
                <td valign="top">
                    <strong>
                        Número de orden:
                    </strong>
                </td>
                <td valign="top">
                    {{$period +1}}
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <strong>Número de formulario:</strong>
                </td>
                <td valign="top">0710</td>
                <td></td>
                <td valign="top">
                    <strong>
                        Fecha de presentación:
                    </strong>
                </td>
                <td valign="top">
                    {{date('d/m/Y H:i:s')}}
                </td>
            </tr>
        </table>
    </div>

    <table style="width: 100%;border-collapse: collapse;border: 1px solid #000; margin-top: 15px;" class="table1">
        <thead>

            <tr>
                <th colspan="6" class="cell text-center">
                    <div style="font-size: 16px;">
                        Estado de Situación Financiera
                    </div>
                    <div style="font-size: 16px;">
                        (Valor Histórico al 31 de Dic. {{$period}})
                    </div>
                </th>
            </tr>
            <tr>
                <th class="text-center cell" colspan="3" style="font-size: 16px;">
                    ACTIVO
                </th>
                <th class="text-center cell" colspan="3" style="font-size: 16px;">
                    PASIVO
                </th>

            </tr>

        </thead>
        <tbody>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Efectivo y equivalentes de efectivo</td>
                <td class="cell text-center">359</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 10 --}}
                    @php
                        $account_10 = $sum_accounts->where('account_group', '10')->sum('balance_debit');
                        $a_10 = $account_10;
                    @endphp
                    {{ format_number($a_10, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Sobregiros Bancarios</td>
                <td class="cell text-center">401</td>
                <td class="text-right" style="padding-right: 5px;">0</td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Inversiones Financieras</td>
                <td class="cell text-center">360</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 11 --}}
                    @php
                        $account_11 = $sum_accounts->where('account_group', '11')->sum('balance_debit');
                        $a_11 = $account_11;
                    @endphp
                    {{ format_number($a_11, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Trib. y aport. sist. pens. y salud por pagar</td>
                <td class="cell text-center">402</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 40 --}}
                    @php
                        $account_40 = $sum_accounts->where('account_group', '40')->sum('balance_credit');
                        $a_40 = $account_40;
                    @endphp
                    {{ format_number($a_40, 0) }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Cuentas por cobrar comerciales – terceros</td>
                <td class="cell text-center">361</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 12 --}}
                    @php
                        $account_12 = $sum_accounts->where('account_group', '12')->sum('balance_debit');
                        $a_12 = $account_12;
                    @endphp
                    {{ format_number($a_12, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Remuneraciones y participaciones por pagar</td>
                <td class="cell text-center">403</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 41 --}}
                    @php
                        $account_41 = $sum_accounts->where('account_group', '41')->sum('balance_credit');
                        $a_41 = $account_41;
                    @endphp
                    {{ format_number($a_41, 0) }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Cuentas por cobrar comerciales - relacionadas</td>
                <td class="cell text-center">362</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 13 --}}
                    @php
                        $account_13 = $sum_accounts->where('account_group', '13')->sum('balance_debit');
                        $a_13 = $account_13;
                    @endphp
                    {{ format_number($a_13, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Ctas por pagar comerciales - terceros</td>
                <td class="cell text-center">404</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 42 --}}
                    @php
                        $account_42 = $sum_accounts->where('account_group', '42')->sum('balance_credit');
                        $a_42 = $account_42;
                    @endphp
                    {{ format_number($a_42, 0) }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Cuentas por cobrar al personal, acc (socios) y
                    directores</td>
                <td class="cell text-center">363</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 14 --}}
                    @php
                        $account_14 = $sum_accounts->where('account_group', '14')->sum('balance_debit');
                        $a_14 = $account_14;
                    @endphp
                    {{ format_number($a_14, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Ctas por pagar comerciales - relacionadas</td>
                <td class="cell text-center">405</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 43 --}}
                    @php
                        $account_43 = $sum_accounts->where('account_group', '43')->sum('balance_credit');
                        $a_43 = $account_43;
                    @endphp
                    {{ format_number($a_43, 0) }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Cuentas por cobrar diversas - terceros</td>
                <td class="cell text-center">364</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 16 --}}
                    @php
                        $account_16 = $sum_accounts->where('account_group', '16')->sum('balance_debit');
                        $a_16 = $account_16;
                    @endphp
                    {{ format_number($a_16, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Ctas por pagar accionist (soc, partic) y direct.</td>
                <td class="cell text-center">406</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 44 --}}
                    @php
                        $account_44 = $sum_accounts->where('account_group', '44')->sum('balance_credit');
                        $a_44 = $account_44;
                    @endphp
                    {{ format_number($a_44, 0) }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Cuentas por cobrar diversas - relacionadas</td>
                <td class="cell text-center">365</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 17 --}}
                    @php
                        $account_17 = $sum_accounts->where('account_group', '17')->sum('balance_debit');
                        $a_17 = $account_17;
                    @endphp
                    {{ format_number($a_17, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Ctas por pagar diversas - terceros</td>
                <td class="cell text-center">407</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 46 --}}
                    @php
                        $account_46 = $sum_accounts->where('account_group', '46')->sum('balance_credit');
                        $a_46 = $account_46;
                    @endphp
                    {{ format_number($a_46, 0) }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Servicios y otros contratados por anticipado</td>
                <td class="cell text-center">366</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 18 --}}
                    @php
                        $account_18 = $sum_accounts->where('account_group', '18')->sum('balance_debit');
                        $a_18 = $account_18;
                    @endphp
                    {{ format_number($a_18, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Ctas por pagar diversas - relacionadas</td>
                <td class="cell text-center">408</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 47 --}}
                    @php
                        $account_47 = $sum_accounts->where('account_group', '47')->sum('balance_credit');
                        $a_47 = $account_47;
                    @endphp
                    {{ format_number($a_47, 0) }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Estimación de cuentas de cobranza dudosa</td>
                <td class="cell text-center">367</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 19 --}}
                    @php
                        $account_19 = $sum_accounts->where('account_group', '19')->sum('balance_credit');
                        $a_19 = $account_19;
                    @endphp
                    {{ format_number($a_19, 0, true) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Obligaciones financieras</td>
                <td class="cell text-center">409</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 45 --}}
                    @php
                        $account_45 = $sum_accounts->where('account_group', '45')->sum('balance_credit');
                        $a_45 = $account_45;
                    @endphp
                    {{ format_number($a_45, 0) }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Mercaderías</td>
                <td class="cell text-center">368</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 20 --}}
                    @php
                        $account_20 = $sum_accounts->where('account_group', '20')->sum('balance_debit');
                        $a_20 = $account_20;
                    @endphp
                    {{ format_number($a_20, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Provisiones</td>
                <td class="cell text-center">410</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 48 --}}
                    @php
                        $account_48 = $sum_accounts->where('account_group', '48')->sum('balance_credit');
                        $a_48 = $account_48;
                    @endphp
                    {{ format_number($a_48, 0) }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Productos terminados</td>
                <td class="cell text-center">369</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 21 --}}
                    @php
                        $account_21 = $sum_accounts->where('account_group', '21')->sum('balance_debit');
                        $a_21 = $account_21;
                    @endphp
                    {{ format_number($a_21, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Pasivo Diferido</td>
                <td class="cell text-center">411</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 49 --}}
                    @php
                        $account_49 = $sum_accounts->where('account_group', '49')->sum('balance_credit');
                        $a_49 = $account_49;
                    @endphp
                    {{ format_number($a_49, 0) }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Subproductos, desechos y desperdicios</td>
                <td class="cell text-center">370</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 22 --}}
                    @php
                        $account_22 = $sum_accounts->where('account_group', '22')->sum('balance_debit');
                        $a_22 = $account_22;
                    @endphp
                    {{ format_number($a_22, 0) }}
                </td>
                <td rowspan="2" class="text-center bold" style="padding-left: 5px;">TOTAL PASIVO</td>
                <td rowspan="2" class="cell text-center bold">412</td>
                <td rowspan="2" class="text-right bold" style="padding-right: 5px;">
                    {{-- total pas --}}
                    @php
                        $total_pas = $a_40 + $a_41 + $a_42 + $a_43 + $a_44 + $a_45 + $a_46 + $a_47 + $a_48 + $a_49;
                    @endphp
                    {{ format_number($total_pas, 0) }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Productos en proceso</td>
                <td class="cell text-center">371</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 23 --}}
                    @php
                        $account_23 = $sum_accounts->where('account_group', '23')->sum('balance_debit');
                        $a_23 = $account_23;
                    @endphp
                    {{ format_number($a_23, 0) }}
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Materias primas</td>
                <td class="cell text-center">372</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 24 --}}
                    @php
                        $account_24 = $sum_accounts->where('account_group', '24')->sum('balance_debit');
                        $a_24 = $account_24;
                    @endphp
                    {{ format_number($a_24, 0) }}
                </td>
                <td rowspan="3" colspan="3" class="text-center bold" style="padding-left: 5px;">PATRIMONIO
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Materiales auxiliares, suministros y repuestos</td>
                <td class="cell text-center">373</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 25 --}}
                    @php
                        $account_25 = $sum_accounts->where('account_group', '25')->sum('balance_debit');
                        $a_25 = $account_25;
                    @endphp
                    {{ format_number($a_25, 0) }}
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Envases y embalajes</td>
                <td class="cell text-center">374</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 26 --}}
                    @php
                        $account_26 = $sum_accounts->where('account_group', '26')->sum('balance_debit');
                        $a_26 = $account_26;
                    @endphp
                    {{ format_number($a_26, 0) }}
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Inventarios por recibir</td>
                <td class="cell text-center">375</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 28 --}}
                    @php
                        $account_28 = $sum_accounts->where('account_group', '28')->sum('balance_debit');
                        $a_28 = $account_28;
                    @endphp
                    {{ format_number($a_28, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Capital</td>
                <td class="cell text-center">414</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 50 --}}
                    @php
                        $account_50 = $sum_accounts->where('account_group', '50')->sum('balance_credit');
                        $a_50 = $account_50;
                    @endphp
                    {{ format_number($a_50, 0) }}
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Desvalorización de Inventarios</td>
                <td class="cell text-center">376</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 29 --}}
                    @php
                        $account_29 = $sum_accounts->where('account_group', '29')->sum('balance_credit');
                        $a_29 = $account_29;
                    @endphp
                    {{ format_number($a_29, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Acciones de inversión</td>
                <td class="cell text-center">415</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 51 --}}
                    @php
                        $account_51 = $sum_accounts->where('account_group', '51')->sum('balance_credit');
                        $a_51 = $account_51;
                    @endphp
                    {{ format_number($a_51, 0) }}
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Activos no corrientes mantenidos para la venta</td>
                <td class="cell text-center">377</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 27 --}}
                    @php
                        $account_27 = $sum_accounts->where('account_group', '27')->sum('balance_debit');
                        $a_27 = $account_27;
                    @endphp
                    {{ format_number($a_27, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Capital Adicional positivo</td>
                <td class="cell text-center">416</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 52 --}}
                    @php
                        $account_52 = $sum_accounts->where('account_group', '52')->sum('balance_credit');
                        $account_529 = $sum_accounts->where('code', 'like', '529%')->sum('balance_debit');
                        $a_52 = $account_52 - $account_529;
                    @endphp
                    {{ format_number($a_52, 0) }}
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Otros activos corrientes</td>
                <td class="cell text-center">378</td>
                <td class="text-right" style="padding-right: 5px;">0</td>
                <td class="text-left" style="padding-left: 5px;">Capital Adicional negativo</td>
                <td class="cell text-center">
                    417

                </td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 529 --}}
                    @php
                        $account_529 = $sum_accounts->where('code', 'like', '529%')->sum('balance_debit');
                        $a_529 = $account_529;
                    @endphp
                    {{ format_number($a_529, 0, true) }}
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Inversiones mobiliarias</td>
                <td class="cell text-center">379</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 30 --}}
                    @php
                        $account_30 = $sum_accounts->where('account_group', '30')->sum('balance_debit');
                        $a_30 = $account_30;
                    @endphp
                    {{ format_number($a_30, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Resultados no realizados</td>
                <td class="cell text-center">418</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 56 --}}
                    @php
                        $account_56 = $sum_accounts->where('account_group', '56')->sum('balance_credit');
                        $a_56 = $account_56;
                    @endphp
                    {{ format_number($a_56, 0) }}
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Propiedades de Inversión (1)</td>
                <td class="cell text-center">380</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 31 --}}
                    @php
                        $account_31 = $sum_accounts->where('account_group', '31')->sum('balance_debit');
                        $a_31 = $account_31;
                    @endphp
                    {{ format_number($a_31, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Excedente de revaluación</td>
                <td class="cell text-center">419</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 57 --}}
                    @php
                        $account_57 = $sum_accounts->where('account_group', '57')->sum('balance_credit');
                        $a_57 = $account_57;
                    @endphp
                    {{ format_number($a_57, 0) }}
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Activos por derecho de uso (2)</td>
                <td class="cell text-center">381</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 32 --}}
                    @php
                        $account_32 = $sum_accounts->where('account_group', '32')->sum('balance_debit');
                        $a_32 = $account_32;
                    @endphp
                    {{ format_number($a_32, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Reservas</td>
                <td class="cell text-center">420</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 58 --}}
                    @php
                        $account_58 = $sum_accounts->where('account_group', '58')->sum('balance_credit');
                        $a_58 = $account_58;
                    @endphp
                    {{ format_number($a_58, 0) }}
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Propiedades, Planta y Equipo</td>
                <td class="cell text-center">382</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 33 --}}
                    @php
                        $account_33 = $sum_accounts->where('account_group', '33')->sum('balance_debit');
                        $a_33 = $account_33;
                    @endphp
                    {{ format_number($a_33, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Resultados Acumulados Positivos</td>
                <td class="cell text-center">421</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 591101 --}}
                    @php
                        $account_591101 = $sum_accounts->where('code', 'like', '591101%')->sum('balance_credit');
                        $a_591101 = $account_591101;
                    @endphp
                    {{ format_number($a_591101, 0) }}
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Depreciación de (1), (2) y PPE acumulados</td>
                <td class="cell text-center">383</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 39 --}}
                    @php
                        $account_39 = $sum_accounts->where('account_group', '39')->sum('balance_credit');
                        $account_398 = $sum_accounts->where('code', 'like', '398%')->sum('balance_debit');
                        $a_39 = $account_39 - $account_398;
                    @endphp
                    {{ format_number($a_39, 0, true) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Resultados Acumulados Negativos</td>
                <td class="cell text-center">422</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 592101 --}}
                    @php
                        $account_592101 = $sum_accounts->where('code', 'like', '592101%')->sum('balance_debit');
                        $a_592101 = $account_592101;
                    @endphp
                    {{ format_number($a_592101, 0,true) }}
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Intangibles</td>
                <td class="cell text-center">384</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 34 --}}
                    @php
                        $account_34 = $sum_accounts->where('account_group', '34')->sum('balance_debit');
                        $a_34 = $account_34;
                    @endphp
                    {{ format_number($a_34, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Utilidad del ejercicio</td>
                <td class="cell text-center">423</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 591102 --}}
                    @php
                        $account_591102 = $sum_accounts->where('code', '591102')->sum('balance_credit');
                        $a_591102 = $account_591102;
                    @endphp
                    {{ format_number($a_591102, 0) }}
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Activos biológicos</td>
                <td class="cell text-center">385</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 35 --}}
                    @php
                        $account_35 = $sum_accounts->where('account_group', '35')->sum('balance_debit');
                        $a_35 = $account_35;
                    @endphp
                    {{ format_number($a_35, 0) }}
                </td>
                <td class="text-left" style="padding-left: 5px;">Pérdida del ejercicio</td>
                <td class="cell text-center">424</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 592102 --}}
                    @php
                        $account_592102 = $sum_accounts->where('code', 'like', '592102')->sum('balance_credit');
                        $a_592102 = $account_592102;
                    @endphp
                    {{ format_number($a_592102, 0,true) }}
                </td>

            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Deprec. Act. biológico y amortiz. Acumulada</td>
                <td class="cell text-center">386</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 398 --}}
                    @php
                        $account_398 = $sum_accounts->where('code', 'like', '398%')->sum('balance_debit');
                        $a_398 = $account_398;
                    @endphp
                    {{ format_number($a_398, 0, true) }}
                </td>
                <td rowspan="2" class="text-center bold" style="padding-left: 5px;">TOTAL PATRIMONIO</td>
                <td rowspan="2" class="cell text-center bold">
                    425
                </td>
                <td rowspan="2" class="text-right bold" style="padding-right: 5px;">
                    
                    @php
                        $total_patrimonio =
                            $a_50 + $a_51 + $a_52 + $a_529 + $a_56 + $a_57 + $a_58 + $a_591101 + $a_591102 + $a_592101 - $a_592102;
                    @endphp
                    {{ format_number($total_patrimonio, 0) }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Desvalorización de activo inmovilizado</td>
                <td class="cell text-center">387</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 36 --}}
                    @php
                        $account_36 = $sum_accounts->where('account_group', '36')->sum('balance_debit');
                        $a_36 = $account_36;
                    @endphp
                    {{ format_number($a_36, 0, true) }}
                </td>

            <tr>
                <td class="text-left" style="padding-left: 5px;">Activo diferido</td>
                <td class="cell text-center">388</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 37 --}}
                    @php
                        $account_37 = $sum_accounts->where('account_group', '37')->sum('balance_debit');
                        $a_37 = $account_37;
                    @endphp
                    {{ format_number($a_37, 0) }}
                </td>
                <td rowspan="3" class="text-center bold" style="padding-left: 5px;">TOTAL PASIVO Y PATRIMONIO</td>
                <td rowspan="3" class="cell text-center bold">426</td>
                <td rowspan="3" class="text-right bold" style="padding-right: 5px;">
                    {{-- total pasivo y patrimonio --}}
                    @php
                        $total_pasivo_patrimonio = $total_patrimonio + $total_pas;
                    @endphp
                    {{ format_number($total_pasivo_patrimonio, 0) }}
                </td>
            </tr>
            <tr>
                <td class="text-left" style="padding-left: 5px;">Otros activos no corrientes</td>
                <td class="cell text-center">389</td>
                <td class="text-right" style="padding-right: 5px;">
                    {{-- 38 --}}
                    @php
                        $account_38 = $sum_accounts->where('account_group', '38')->sum('balance_debit');
                        $a_38 = $account_38;
                    @endphp
                    {{ format_number($a_38, 0) }}
                </td>

            </tr>
            <tr>
                <td class="text-left bold" style="padding-left: 5px;">TOTAL ACTIVO NETO</td>
                <td class="cell text-center bold">390</td>
                <td class="text-right bold" style="padding-right: 5px;">
                    {{-- total activo neto --}}
                    @php
                        $total_activo_neto =
                            $a_10 +
                            $a_11 +
                            $a_12 +
                            $a_13 +
                            $a_14 +
                            $a_16 +
                            $a_17 +
                            $a_18 +
                            $a_19 +
                            $a_20 +
                            $a_21 +
                            $a_22 +
                            $a_23 +
                            $a_24 +
                            $a_25 +
                            $a_26 +
                            $a_27 +
                            $a_28 +
                            $a_29 +
                            $a_30 +
                            $a_31 +
                            $a_32 +
                            $a_33 +
                            $a_34 +
                            $a_35 +
                            $a_36 +
                            $a_37 +
                            $a_38 -
                            $a_39;
                    @endphp
                    {{ format_number($total_activo_neto, 0) }}
                </td>

            </tr>
        </tbody>

    </table>
    {{-- ======================= PÁGINA 2 ======================= --}}
    <div class="page-break"></div>

    <div style="width: 100%;">
        <table style="width: 80%;border-collapse: collapse;border: 1px solid #000;margin: 0 auto;" class="table2">
            <thead>
                <tr>
                    <th colspan="3" class="cell text-center">
                        <div style="font-size: 16px;">Estado de Resultados</div>
                        <div style="font-size: 16px;">Del 01/01 al 31/12 de {{$period}}</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="75%" class="cell-md text-left" style="padding-left: 5px;">Ventas Netas o Ing. por
                        Servicios</td>
                    <td width="10%" class="cell-md cell text-center">461</td>
                    <td width="15%" class="cell-md text-right" style="padding-right: 5px;">
                        {{-- 70 --}}
                        @php
                            $account_70 = $sum_accounts->where('account_group', '70')->first();
                            $a_70 = $account_70 ? $account_70->balance_credit : 0;
                        @endphp
                        {{ format_number($a_70, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Desc. rebajas y bonif. concedidas</td>
                    <td class="cell-md cell text-center">462</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        @php
                            $account_74 = $sum_accounts->where('account_group', '74')->first();
                            $a_74 = $account_74 ? $account_74->balance_debit : 0;
                            $account_709 = $sum_accounts->where('code', 'like', '709%')->first();
                            $a_709 = $account_709 ? $account_709->balance_credit : 0;
                            $a_709_74 = $a_709 + $a_74;
                        @endphp
                        {{ format_number($a_709_74, 0, true) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Ventas Netas</td>
                    <td class="cell-md cell text-center">463</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        @php
                            $sale_net = $a_70 - $a_709_74;
                        @endphp
                        {{ format_number($sale_net, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Costo de Ventas</td>
                    <td class="cell-md cell text-center">464</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{-- 69 --}}
                        @php
                            $account_69 = $sum_accounts->where('account_group', '69')->first();
                            $a_69 = $account_69 ? $account_69->balance_debit : 0;
                        @endphp
                        {{ format_number($a_69, 0) }}
                    </td>
                </tr>
                @php
                    $result_gross = $sale_net - $a_69;
                @endphp
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Resultado bruto Utilidad</td>
                    <td class="cell-md cell text-center">466</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{ format_number($result_gross > 0 ? $result_gross : 0, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Resultado bruto Pérdida</td>
                    <td class="cell-md cell text-center">467</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                            {{ format_number($result_gross < 0 ? $result_gross : 0, 0, true) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Gastos de Ventas</td>
                    <td class="cell-md cell text-center">468</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{-- 94 --}}
                        @php
                            $account_94 = $sum_accounts->where('account_group', '94')->first();
                            $a_94 = $account_94 ? $account_94->balance_debit : 0;
                        @endphp
                        {{ format_number($a_94, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Gastos de Administración</td>
                    <td class="cell-md cell text-center">469</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{-- 95 --}}
                        @php
                            $account_95 = $sum_accounts->where('account_group', '95')->first();
                            $a_95 = $account_95 ? $account_95->balance_debit : 0;
                        @endphp
                        {{ format_number($a_95, 0) }}
                    </td>
                </tr>
                @php
                    $result_operation =$result_gross -  $a_94 - $a_95;
                @endphp
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Resultado de operación Utilidad</td>
                    <td class="cell-md cell text-center">470</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{ format_number($result_operation > 0 ? $result_operation : 0, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Resultado de operación Pérdida</td>
                    <td class="cell-md cell text-center">471</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{ format_number($result_operation < 0 ? $result_operation : 0, 0, true) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Gastos Financieros</td>
                    <td class="cell-md cell text-center">472</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{-- 97 --}}
                        @php
                            $account_97 = $sum_accounts->where('account_group', '97')->first();
                            $a_97 = $account_97 ? $account_97->balance_debit : 0;
                        @endphp
                        {{ format_number($a_97, 0, true) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Ingresos Financieros Gravados</td>
                    <td class="cell-md cell text-center">473</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{-- 77 --}}
                        @php
                            $account_77 = $sum_accounts->where('account_group', '77')->first();
                            $a_77 = $account_77 ? $account_77->balance_credit : 0;
                        @endphp
                        {{ format_number($a_77, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Otros Ingresos gravados</td>
                    <td class="cell-md cell text-center">475</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{-- 75 --}}
                        @php
                            $account_75 = $sum_accounts->where('account_group', '75')->first();
                            $a_75 = $account_75 ? $account_75->balance_credit : 0;
                        @endphp
                        {{ format_number($a_75, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Otros Ingresos no gravados</td>
                    <td class="cell-md cell text-center">476</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{-- 759 --}}
                        @php
                            $account_759 = $sum_accounts->where('code', 'like', '759%')->first();
                            $a_759 = $account_759 ? $account_759->balance_credit : 0;
                        @endphp
                        {{ format_number($a_759, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Enajen. de val. y bienes del Act. F.</td>
                    <td class="cell-md cell text-center">477</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{-- 756 --}}
                        @php
                            $account_756 = $sum_accounts->where('code', 'like', '756%')->first();
                            $a_756 = $account_756 ? $account_756->balance_credit : 0;
                        @endphp
                        {{ format_number($a_756, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Costo enajen. de val. y bienes A.F.</td>
                    <td class="cell-md cell text-center">478</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{-- 655 --}}
                        @php
                            $account_655 = $sum_accounts->where('code', 'like', '655%')->first();
                            $a_655 = $account_655 ? $account_655->balance_debit : 0;
                        @endphp
                        {{ format_number($a_655, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Gastos diversos</td>
                    <td class="cell-md cell text-center">480</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{-- 65 & 66 --}}
                        @php
                            $account_65 = $sum_accounts->where('account_group', '65')->first();
                            $a_65 = $account_65 ? $account_65->balance_debit : 0;
                            $account_66 = $sum_accounts->where('account_group', '66')->first();
                            $a_66 = $account_66 ? $account_66->balance_debit : 0;
                            $a_65_66 = $a_65 + $a_66;
                        @endphp
                        {{ format_number($a_65_66, 0) }}
                    </td>
                </tr>
                @php
                    $sum_all = $result_operation - $a_97 + $a_77 + $a_75 + $a_759 + $a_756 + $a_655 + $a_65_66;
                @endphp
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Resultado antes de part. Utilidad</td>
                    <td class="cell-md cell text-center">484</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{ format_number($sum_all > 0 ? $sum_all : 0, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Resultado antes de part. Pérdida</td>
                    <td class="cell-md cell text-center">485</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{ format_number($sum_all < 0 ? $sum_all : 0, 0, true) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Distribución legal de la renta</td>
                    <td class="cell-md cell text-center">486</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{-- 87 --}}
                        @php
                            $account_87 = $sum_accounts->where('account_group', '87')->first();
                            $a_87 = $account_87 ? $account_87->balance_debit : 0;
                        @endphp
                        {{ format_number($a_87, 0) }}
                    </td>
                </tr>
                @php
                    $result_before_tax = $sum_all - $a_87;
                @endphp
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Resultado antes del imp. Utilidad</td>
                    <td class="cell-md cell text-center">487</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{ format_number($result_before_tax > 0 ? $result_before_tax : 0, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Resultado antes del imp. Pérdida</td>
                    <td class="cell-md cell text-center">489</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{ format_number($result_before_tax < 0 ? $result_before_tax : 0, 0, true) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Impuesto a la Renta</td>
                    <td class="cell-md cell text-center">490</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{-- 88 --}}
                        @php
                            $account_88 = $sum_accounts->where('account_group', '88')->first();
                            $a_88 = $account_88 ? $account_88->balance_debit : 0;
                        @endphp
                        {{ format_number($a_88, 0) }}
                    </td>
                </tr>
                @php
                    $result_exercise = $result_before_tax - $a_88;
                @endphp
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Resultado del ejercicio Utilidad</td>
                    <td class="cell-md cell text-center">492</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{ format_number($result_exercise > 0 ? $result_exercise : 0, 0) }}
                    </td>
                </tr>
                <tr>
                    <td class="cell-md text-left" style="padding-left: 5px;">Resultado del ejercicio Pérdida</td>
                    <td class="cell-md cell text-center">493</td>
                    <td class="cell-md text-right" style="padding-right: 5px;">
                        {{ format_number($result_exercise < 0 ? $result_exercise : 0, 0, true) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>


</body>

</html>
