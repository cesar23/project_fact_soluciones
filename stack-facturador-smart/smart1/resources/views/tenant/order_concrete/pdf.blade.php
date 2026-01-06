<!DOCTYPE html>
<html>

<head>
    <title>Orden de Vaciado de Concreto</title>
    <style>
        @page {
            margin: 15px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td {
            padding: 3px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .border-table {
            border-collapse: collapse;
            width: 100%;
        }

        .border-table td,
        .border-table th {
            border: 1px solid black;
        }

        .header {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            border: 1px solid black;
            vertical-align: middle;
        }

        .logo-container {
            text-align: center;
        }

        .logo {
            width: 70px;
            margin: 0 auto;
        }

        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            padding-top: 5px;
        }

        .code {
            text-align: right;
            font-size: 9px;
        }

        .section-title {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .signature-table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        .signature-table td {
            border: 1px solid black;
            height: 60px;
            vertical-align: top;
            padding: 3px;
            font-size: 9px;
        }

        .signature-title {
            text-align: center;
            font-weight: bold;
        }

        .inner-table {
            border-collapse: collapse;
            width: 100%;
        }

        .inner-table td {
            border: 1px solid black;
        }

        .data-label {
            width: 40%;
            font-weight: bold;
        }

        .total-mark {
            position: absolute;
            right: 30px;
            top: 300px;
            font-size: 16px;
            color: blue;
            font-weight: bold;
        }

        .main-container {
            position: relative;
        }

        .text-center {
            text-align: center;
        }

        p {
            padding: 0px;
            margin: 0px;
        }
    </style>
</head>
@php
    $logo = "{$company->logo}";
    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }
@endphp

<body>
    <table class="border-table" cellspacing="0" cellpadding="3">
        <tbody>
            <tr>
                <td colspan="3" class="text-center">
                    @if ($company->logo)
                        <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                            alt="{{ $company->name }}" class="logo" style="max-width: 100%;">
                    @endif
                </td>
                <td colspan="2" class="text-center">
                    <h3>ORDEN DE VACIADO DE CONCRETO PREMEZCLADO</h3>
                </td>
                <td colspan="5" style="padding: 0px;">
                    <table style="padding: 0px;border: none;">
                        <tbody>
                            <tr>
                                <td style="border: none;border-bottom: 1px solid black;">
                                    <p>
                                        <span class="data-label">Fecha:</span>
                                        <span class="data-value">{{ $document->date }}</span>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="border: none;">
                                    <p>
                                        <span class="data-label">Número de Orden:</span>
                                        <span class="data-value">{{ $document->series }}-{{ $document->number }}</span>
                                    </p>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>SEDE</strong>
                </td>
                <td colspan="4">
                    {{ $document->establishment_code }}
                </td>
                <td>
                    <strong>MAESTRO</strong>
                </td>
                <td colspan="4">
                    {{ $document->master->name }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>DIRECCIÓN</strong>
                </td>
                <td colspan="4">
                    {{ $document->address }}
                </td>
                <td>
                    <strong>CELULAR</strong>
                </td>
                <td colspan="4">
                    {{ $document->master->telephone }}
                </td>
            </tr>
            <tr>
                <td colspan="5" class="text-center">
                    <strong>DATOS DEL CLIENTE</strong>
                </td>
                <td colspan="5" class="text-center">
                    <strong>REQUERIMIENTO</strong>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>NOMBRE</strong>
                </td>
                <td colspan="4">
                    {{ $document->customer->name }}
                </td>
                <td>
                    <strong>FECHA</strong>
                </td>
                <td colspan="4">
                    {{ $document->date }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>CELULAR</strong>
                </td>
                <td colspan="4">
                    {{ $document->customer->telephone }}
                </td>
                <td>
                    <strong>HORA</strong>
                </td>
                <td colspan="4">
                    {{ $document->hour }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>DIRECCIÓN</strong>
                </td>
                <td colspan="4">
                    {{ $document->customer->address }}
                </td>
                <td>
                    <strong>ELECTRO</strong>
                </td>
                <td colspan="4">
                    {{ $document->electro }}
                </td>
            </tr>
            <tr>

                <td>
                <strong>DOCUMENTO</strong>
                </td>
                <td colspan="4">
                    @if($document->document_id)
                        {{ $document->document->series }}-{{ $document->document->number }}
                    @else
                        {{ $document->sale_note->series }}-{{ $document->sale_note->number }}
                    @endif
                     
                </td>
                <td>
                    <strong>VOLUMEN</strong>
                </td>
                <td colspan="4">
                    {{ $document->volume }}
                </td>
            </tr>
            <tr>

                <td colspan="5" style="border-bottom: none;border-top: none;">
                </td>
                <td>
                    <strong>MEZCLA (KG/CM2)</strong>
                </td>
                <td colspan="4">
                    {{ $document->mix_kg_cm2 }}
                </td>
            </tr>
            <tr>

                <td colspan="5" style="border-bottom: none;border-top: none;">
                </td>
                <td>
                    <strong>T. CEMENTO</strong>
                </td>
                <td colspan="4">
                    {{ $document->type_cement }}
                </td>
            </tr>
            <tr>

                <td colspan="5" style="border-bottom: none;border-top: none;">
                </td>
                <td>
                    <strong>BOMBA</strong>
                </td>
                <td colspan="4">
                    {{ $document->pump }}
                </td>
            </tr>
            <tr>

                <td colspan="5" style="border-bottom: none;border-top: none;">
                </td>
                <td>
                    <strong>OTROS</strong>
                </td>
                <td colspan="4">
                    {{ $document->other }}
                </td>
            </tr>
            <tr>
                <td colspan="5" class="text-center">
                    <strong>INSUMOS</strong>
                </td>
                <td colspan="5" class="text-center">
                    <strong>ATENCIÓN</strong>
                </td>
            </tr>
            @php
                $supplies = $document->supplies;
                $attentions = $document->attentions;
                $max = max(count($supplies), count($attentions));
            @endphp
            <tr>
                <td colspan="2">
                    <strong>DESCRIPCIÓN</strong>
                </td>
                <td>
                    <strong>TIPO</strong>
                </td>
                <td>
                    <strong>CANT. XM3</strong>
                </td>
                <td>
                    <strong>TOTAL</strong>
                </td>
                <td colspan="3">
                    <strong>GUIA O NOTA</strong>
                </td>
                <td colspan="2">
                    <strong>CANT.</strong>
                </td>
            </tr>
            @for ($i = 0; $i < $max; $i++)
                <tr>
                    @if (isset($supplies[$i]))
                        @php
                            $supply = $supplies[$i];
                        @endphp
                        <td colspan="2">
                            {{ $supply->description }}
                        </td>
                        <td>
                            {{ $supply->type }}
                        </td>
                        <td>
                            {{ $supply->quantity }}
                        </td>
                        <td>
                            {{ $supply->total }}
                        </td>
                    @else
                        <td colspan="5"></td>
                    @endif
                    @if (isset($attentions[$i]))
                        @php
                            $attention = $attentions[$i];
                        @endphp
                        <td colspan="3">
                            {{ $attention->dispatch_note }}
                        </td>
                        <td colspan="2">
                            {{ $attention->quantity }}
                        </td>
                    @else
                        <td colspan="5"></td>
                    @endif
                </tr>
            @endfor
            <tr>
                <td colspan="10" class="text-center">
                    <strong>OBSERVACIONES</strong>
                </td>
            </tr>
            <tr>
                <td colspan="10">
                    {{ $document->observations }}
                </td>
            </tr>
            <tr>
                <td class="text-center" colspan="3">
                    <strong>
                        REVISADO - TESORERIA
                    </strong>
                </td>
                <td class="text-center">
                    <strong>
                        REVISADO - JEFE DE PLANTA
                    </strong>
                </td>
                <td class="text-center">
                    <strong>
                        REVISADO - OPERADOR DE PLANTA
                    </strong>
                </td>
                <td class="text-center" colspan="5">
                    <strong>
                        REVISADO - GERENTE GENERAL
                    </strong>
                </td>
            </tr>
            <tr>

                <td colspan="3">
                    <div>
                        <strong>
                            NOMBRE: {{ $document->treasury_reviewed_name }}
                        </strong>
                    </div>
                    <div>
                        <strong>
                            FECHA:
                        </strong>
                    </div>
                    <div>
                        <strong>
                            FIRMA:
                        </strong>
                    </div>
                </td>
                <td>
                    <div>
                        <strong>
                            NOMBRE: {{ $document->plant_manager_reviewed_name }}
                        </strong>
                    </div>
                    <div>
                        <strong>
                            FECHA:
                        </strong>
                    </div>
                    <div>
                        <strong>
                            FIRMA:
                        </strong>
                    </div>
                </td>
                <td >
                    <div>
                        <strong>
                            NOMBRE: {{ $document->plant_operator_reviewed_name }}
                        </strong>
                    </div>
                    <div>
                        <strong>
                            FECHA:
                        </strong>
                    </div>
                    <div>
                        <strong>
                            FIRMA:
                        </strong>
                    </div>
                </td>
                <td colspan="5">
                    <div>
                        <strong>
                            NOMBRE: {{ $document->manager_approved_name }}
                        </strong>
                    </div>
                    <div>
                        <strong>
                            FECHA:
                        </strong>
                    </div>
                    <div>
                        <strong>
                            FIRMA:
                        </strong>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>


</body>

</html>
