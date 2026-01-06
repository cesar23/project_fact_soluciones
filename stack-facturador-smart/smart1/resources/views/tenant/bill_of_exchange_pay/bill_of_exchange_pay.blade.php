@php
    $establishment = \App\Models\Tenant\Establishment::where('id', $document->establishment_id)->first();
    $supplier = $document->supplier;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');

    $left = $document->series ? $document->series : $document->prefix;
    $tittle = $left . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $payments = $document->payments;
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();
    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

@endphp
<html>

<head>
    {{-- <title>{{ $tittle }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
    <style>
        body {
            font-size: 12px;
            font-family: Arial, sans-serif;
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
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 200;
            letter-spacing: -1px;
            margin: 0px;
            padding: 0px;
        }

        h1 {
            font-size: 32px;
            line-height: 44px;
            font-weight: 500;
        }

        h2 {
            font-size: 24px;
            font-weight: 500;
            line-height: 42px;
        }

        h3 {
            font-size: 18px;
            font-weight: 400;
            letter-spacing: normal;
            line-height: 24px;
        }

        h4 {
            font-size: 16px;
            font-weight: 400;
            letter-spacing: normal;
            line-height: 27px;
        }

        h5 {
            font-size: 13px;
            font-weight: 300;
            letter-spacing: normal;
            line-height: 18px;
        }

        h6 {
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

        .float-left {
            float: left;
        }

        .float-right {
            float: right;
        }

        .text-end {
            text-align: right;
        }

        .transform-rotate-90 {
            transform: rotate(-90deg);
        }

        .bg-red {
            /* background-color: red; */
        }

        .bg-blue {
            /* background-color: blue; */
        }

        .bg-green {
            background-color: green;
        }

        .radius-5 {
            border-radius: 5px;
        }

        .bl {
            border-left: 1px solid #333;
        }

        .bt {
            border-top: 1px solid #333;
        }

        @page {
            margin: 0.5cm 0.5cm;
        }
    </style>
</head>

<body>

    <div style="
      ">
        <div class="float-left bg-blue" style="width:20%;height: 100%;">
            <div class="transform-rotate-90 bg-red"
                style="width: 450px;
                margin-top: 160px;
                margin-left: -160px;
                font-size: 8px;
                height: 120px;
                text-align: left; ">

                <strong>Clausulas especiales:</strong>
                <div>
                    (1) En caso de mora, el Valor de Cambio generará los intereses compensatorios y moratorios más altos
                    que
                    la ley permita a su última tenedora.
                </div>
                <div>
                    (2) El plazo de su vencimiento podrá ser prorrogado por el tenedor, por el plazo que este
                    establezca,
                    sin que sea necesaria la intervención del aceptante Principal ni de los Subscriptores.
                </div>
                <div>
                    (3) Esta letra de Cambio no requiere de protestación por falta de pago.
                </div>
                <div>
                    (4) Su importe debe ser aplicado solo en la misma moneda en que fue emitida esta letra de valor.
                </div>
                <br><br>
                <div style="font-size: 8px;">
                    <table style="font-size: 8px;" class="full-width">
                        <tr>
                            <td width="50%">
                                <div class="text-center" style="font-size: 8px;">
                                    _________________________ <br>
                                    Aceptante
                                </div>
                                <div style="font-size: 8px;">
                                    Representante:
                                </div>
                                <div style="font-size: 8px;">
                                    RUC:
                                </div>
                            </td>
                            <td>
                                <div class="text-center" style="font-size: 8px;">
                                    _________________________ <br>
                                    Aceptante
                                </div>
                                <div style="font-size: 8px;">
                                    Representante:
                                </div>
                                <div style="font-size: 8px;">
                                    RUC:
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
        <div class="float-left" style="width: 77%;">
            <table class="full-width">
                <tr>
                    <td width="60%">
                        <div class="company_logo_box">
                            <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                                alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                        </div>
                    </td>
                    <td class="text-end">
                        <div style="text-transform: uppercase;font-size:10px;">
                            {{ $establishment->address !== '-' ? $establishment->address : '' }}
                            {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                            {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                            {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                        </div>
                        <div style="text-transform: uppercase;font-size:10px;">
                            {{ $establishment->email !== '-' ? $establishment->email : '' }}</div>
                        <div style="text-transform: uppercase;font-size:10px;">
                            {{ $establishment->telephone !== '-' ? $establishment->telephone : '' }}</div>
                    </td>
                </tr>
            </table>

            <div class="full-width border-box radius-5">
                <table class="full-width">
                    <tr>
                        <td class="text-center">
                            <strong>
                                NÚMERO
                            </strong>
                        </td>
                        <td class="text-center bl">
                            <strong>
                                REF. DEL GIRADOR
                            </strong>
                        </td>
                        <td class="text-center bl">
                            <strong>
                                FECHA DE GIRO
                            </strong>
                        </td>
                        <td class="text-center bl">
                            <strong>
                                LUGAR DE GIRO
                            </strong>
                        </td>
                        <td class="text-center bl">
                            <strong>
                                FECHA DE VENCIMIENTO
                            </strong>
                        </td>
                        <td class="text-center bl">
                            <strong>
                                MONEDA E IMPORTE
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td class="bt  text-center">
                            {{ $document->series }}-{{ $document->number }}
                        </td>
                        <td class="bl bt text-center">
                            {{ $document->supplier->number }}
                        </td>
                        <td class="bl bt text-center">
                            {{ $document->date_of_issue ? $document->date_of_issue->format('d/m/Y') : '-' }}
                        </td>
                        <td class="bl bt text-center">
                            {{ $establishment->department->description }}
                        </td>
                        <td class="bl bt text-center">
                            {{ $document->date_of_due->format('d/m/Y') }}
                        </td>
                        <td class="bl bt text-end">
                            {{ $document->currency_type->symbol }} {{ $document->total }}
                        </td>
                    </tr>
                </table>
            </div>
            <div class="mt-1">
                Por esta LETRA DE CAMBIO se servirá(n), pagar incondicionalmente a la orden de: <strong>
                  {{ $document->supplier->name }}
                </strong>
            </div>
            <div class="mt-1 border-box radius-5 p-2">
                La cantidad de: <strong>{{ $document->total_text() }}</strong>
                </strong>
            </div>
            <div class="mt-1">
                En el siguiente lugar de pago, o con cargo en la cuenta del Banco:
            </div>
            <div class="mt-1 border-box radius-5 " style="height: 65px; overflow:hidden;">
                <table class="full-width">
                    <tr>
                        <td width="50%">
                            <table>
                                <tr>
                                    <td colspan="2">
                                        <strong>Aceptante:</strong>
                                    {{$company->name}}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <strong>Dirección:</strong>
                                        {{$establishment->address}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>RUC:</strong>
                                        {{$company->number}}
                                    </td>
                                    <td>
                                        <strong>Teléfono:</strong>
                                        {{$establishment->telephone}}
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td valign="top">
                            <table  style="width:101%;">
                                <tr>
                                    <td valign="top">
                                        <span
                                        style="font-size: 8px;"
                                        >Importe a debitar en la siguiente cuenta del Banco que se indica:................</span>
                                    </td>
                                </tr>
                                <tr>
                                    <table class="full-width border-box"
                                    style="border-bottom: none;border-right: none;margin:0px;"
                                    >
                                        <thead>
                                            <tr>
                                                <th class="border-box">BANCO</th>
                                                <th class="border-box">OFICINA</th>
                                                <th class="border-box">NÚMERO DE CUENTA</th>
                                                <th class="border-box">D.C.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="border-box"><br>
                                                <br>
                                                </td>
                                                <td class="border-box"></td>
                                                <td class="border-box"></td>
                                                <td class="border-box"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>
            </div>
            <div class="" style="margin-top:21px; "></div>
            <div class="full-width mt-2">

                <span style=" display:inline-block; width: 45%;height:100px;" class="border-box radius-5 p-1">
                    <table class="full-width">
                        <tr>
                            <td colspan="2">
                                <strong>
                                    AVAL PERMANENTE:
                                </strong>
                                
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <br>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>
                                <strong>DOMICILIO:</strong>
                            </td>
                            <td>
                                <strong>TELÉFONO:</strong>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <br>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>
                                <strong>RUC:</strong>
                            </td>
                            <td>
                                <strong>FIRMA:</strong>
                            </td>
                        </tr>
                    </table>

                </span>
                <span style=" display:inline-block; width: 5.2%;">

                    <br>
                </span>
                <span style=" display:inline-block; width: 45%;height:100px;" class="border-box radius-5 p-1">
                    <table class="full-width">
                        <tr>
                            <td colspan="2">
                                <strong>
                                    <h5>
                                        {{ $company->name }}
                                        <br>
                                        {{ $company->number }}
                                    </h5>
                                </strong>

                            </td>
                        </tr>

                        <tr>
                            <td class="text-center">
                                <strong>
                                    __________________
                                    <br>
                                    FIRMA
                                </strong>

                            </td>
                            <td class="text-center">

                                <strong>
                                    __________________
                                    <br>
                                    FIRMA
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">

                                Representante: <br>
                                RUC:
                            </td>
                        </tr>
                    </table>
                </span>
            </div>
        </div>
    </div>








</body>

</html>
