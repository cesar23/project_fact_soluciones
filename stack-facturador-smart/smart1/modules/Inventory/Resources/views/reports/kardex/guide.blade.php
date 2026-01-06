<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Guia</title>
    <style>
        @page {
            margin: 25px;
        }

        html {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-spacing: 0;
            border-collapse: collapse;
        }

        table.no-border {
            border: 0px solid white;
        }

        th {
            padding: 5px;
            text-align: center;
            border: thin solid black;
        }

        thead {
            font-weight: bold;
            background: #0088cc;
            color: white;
            text-align: center;
        }

        .border-box {
            border: 1px solid black;
        }

        .strong {
            font-weight: bold;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .company_logo_ticket.contain {
            object-fit: contain;
        }

        .full-width {
            width: 100%;
        }

        .half-width {
            width: 50%;
        }

        .fourteen-width {
            width: 40%;
        }

        .ten-width {
            width: 10%;
        }

        .five-width {
            width: 5%;
        }

        .threeten-width {
            width: 30%;
        }

        .celda {
            text-align: center;
            padding: 5px;
            border: 0.1px solid black;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>
@php
    $company = \App\Models\Tenant\Company::first();
@endphp

<body>
    <div>
        <table class="no-border">
            <tr>
                <td colspan="4" align="center" style="max-width: 300px; height: auto;">
                    @if (!empty($company->logo))
                        <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                            alt="{{ $company->name }}" class="company_logo_ticket contain"
                            style="width: auto; max-height: 100px;">
                    @endif
                </td>

                <td colspan="4" align="left" style="max-width: 300px; height: auto;">
                    <table style="border:2px solid black; max-width: 150px;">
                        <tr>
                            <td align="center">
                                <h3 class="font-bold">{{ 'R.U.C. ' . ($company_number ?? '') }}</h3>
                                <h3 class="text-center font-bold">{{ $document_type_name ?? 'DOCUMENTO' }}</h3>
                                <h3 class="text-center font-bold">{{ $document_number ?? '' }}</h3>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
        </table>
    </div>
    <div>
        <table class="no-border">
            <tr>
                <td>
                    <table class="no-border">
                        <tr>
                            <td>
                                <strong>ALMACÉN:</strong>
                            </td>
                            <td>
                                {{ $warehouse_name ?? '' }}
                            </td>
                            <td>
                                <strong>FECHA DE DOCUMENTO:</strong>
                            </td>
                            <td>
                                {{ $document_date_of_issue ?? '' }} {{ $document_time_of_issue ?? '' }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>MOTIVO:</strong>
                            </td>
                            <td>
                                {{ $transaction_name ?? '' }}
                            </td>
                            @if (!empty($reference))
                                <td>
                                    <strong>REFERENCIA:</strong>
                                </td>
                                <td>
                                    {{ $reference }}
                                </td>
                            @else
                                <td></td>
                                <td></td>
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div class="" style="margin-top: 20px;">
        <div class=" ">
            <table class="full-width">
                <thead>
                    <tr>
                        <th class="five-width text-center">ITEM</th>
                        <th class="five-width text-left">CODIGO INTERNO</th>
                        <th class="threeten-width text-left">DESCRIPCIÓN PRODUCTO</th>
                        <th class="ten-width">UNIDAD</th>
                        <th class="ten-width">CANTIDAD</th>
                        <th class="ten-width">LOTE/SERIE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $row)
                        <tr>
                            <td class="celda text-center" style="font-size: 9px !important">{{ $index + 1 }}</td>
                            <td class="celda text-left"
                                style="font-size: 9px !important;max-width: 120px;word-wrap: break-word; word-break: break-all; white-space: normal;">
                                {{ $row['item_internal_id'] ?? '' }}</td>
                            <td class="celda text-left" style="font-size: 9px !important">{{ $row['item_name'] ?? '' }}
                            </td>
                            <td class="celda" style="font-size: 9px !important">{{ $row['unit_type_symbol'] ?? '' }}</td>
                            <td class="celda" style="font-size: 9px !important">{{ $row['quantity'] ?? '' }}</td>
                            <td class="celda" style="font-size: 9px !important">{{ $row['lot'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <table style="width: 100%; border: none; margin-top: 20px;">
                <tr>
                    <td colspan="6" style="border: none; padding-top: 30px;">
                        <table style="width: 100%; border: none;">
                            <tr>
                                <td style="text-align: center; border: none; padding: 10px;">
                                    <div
                                        style="border-bottom: 1px solid black; width: 150px; margin: 0 auto; margin-bottom: 5px;">
                                    </div>
                                    <strong>Autorizado por</strong>
                                </td>
                                <td style="text-align: center; border: none; padding: 10px;">
                                    <div
                                        style="border-bottom: 1px solid black; width: 150px; margin: 0 auto; margin-bottom: 5px;">
                                    </div>
                                    <strong>Fecha</strong>
                                </td>
                                <td style="text-align: center; border: none; padding: 10px;">
                                    <div
                                        style="border-bottom: 1px solid black; width: 150px; margin: 0 auto; margin-bottom: 5px;">
                                    </div>
                                    <strong>Recibido por</strong>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
