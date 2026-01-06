<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ticket de dispatch</title>
    <style>
        html {
            margin: 5px;
        }

        body {
            font-size: 10px;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
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
            max-height: 80px
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
            margin: 0;
        }

        h1 {
            font-size: 32px;
            font-weight: 500;
        }

        h2 {
            font-size: 24px;
            font-weight: 500;
        }

        h3 {
            font-size: 18px;
            font-weight: 400;
            letter-spacing: normal;
        }

        h4 {
            font-size: 16px;
            font-weight: 400;
            letter-spacing: normal;
        }

        h5 {
            font-size: 13px;
            font-weight: 300;
            letter-spacing: normal;
        }

        h6 {
            font-size: 10px;
            font-weight: 300;
            letter-spacing: normal;
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
    </style>
    @php
        $document_number = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
        $document_number_sale_note = str_pad($document->sale_note->number, 8, '0', STR_PAD_LEFT);

        $customer = $document->customer;
    @endphp
</head>

<body>
    @if ($company->logo)
        <div class="text-center company_logo_box">
            <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                alt="{{ $company->name }}" class="company_logo_ticket contain">
        </div>
    @endif

    {{-- @if ($document->state_type->id == '11' || $document->state_type->id == '09')
        <div class="company_logo_box" style="position: absolute; text-align: center; top:500px">
            <img src="data:{{ mime_content_type(public_path('status_images' . DIRECTORY_SEPARATOR . 'anulado.png')) }};base64, {{ base64_encode(file_get_contents(public_path('status_images' . DIRECTORY_SEPARATOR . 'anulado.png'))) }}"
                alt="anulado" class="" style="opacity: 0.6;">
        </div>
    @endif
    @if ($document->soap_type_id == '01')
        <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
            <img src="data:{{ mime_content_type(public_path('status_images' . DIRECTORY_SEPARATOR . 'demo.png')) }};base64, {{ base64_encode(file_get_contents(public_path('status_images' . DIRECTORY_SEPARATOR . 'demo.png'))) }}"
                alt="anulado" class="" style="opacity: 0.6;">
        </div>
    @endif --}}
    <table class="full-width">
        <tr>
            <td class="text-center">{{ $establishment->email !== '-' ? ' ' . $establishment->email : '' }}</td>
        </tr>
        <tr>
            <td class="text-center ">
                {{ $establishment->telephone !== '-' ? $establishment->telephone : '' }}</td>
        </tr>

        <tr>
            <td class="text-center  border-top">
                <h4>
                    TICKET DE ENCOMIENDA
                </h4>
            </td>
        </tr>
        <tr>
            <td class="text-center border-bottom">
                <h3>
                    TE01 -{{ $document_number_sale_note }}</h3>
            </td>
        </tr>
    </table>
    <table class="full-width mt-10 border-box">
        <thead>
            <tr>
                <th class="text-left border-box">
                    DESTINATARIO
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="">
                    <strong>Razón social:</strong>
                    {{ $customer->name }}
                </td>

            </tr>
            <tr>
                <td class="">
                    <strong>
                        {{ $customer->identity_document_type->description }}:
                    </strong>{{ $customer->number }}
                </td>
            </tr>

            <tr>
                <td class="">
                    <strong>
                        Teléfono:
                    </strong>{{ $customer->telephone }}
                </td>
            </tr>
        </tbody>
    </table>
    <table class="full-width mt-10 border-box">
        <thead>
            <tr>
                <th class="text-left border-box">
                    ENVIO
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="">

                    {{ $document->delivery ? $document->delivery->address : null }}
                    <br> {{ $document->delivery ? func_get_location($document->delivery->location_id) : null }}
                </td>

            </tr>
        </tbody>
    </table>
    @isset($document->dispatcher->name)
        @php
            $document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail(
                $document->dispatcher->identity_document_type_id,
            );
        @endphp
        <table class="full-width mt-10 border-box">
            <thead>
                <tr>
                    <th class="text-left border-box">
                        TRANSPORTE
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="">
                        <strong>Nombre y/o razón social:</strong>

                        {{ $document->dispatcher->name }}
                    </td>

                </tr>
                <tr>
                    <td class="">
                        <strong> {{ $document_type_dispatcher->description }}:

                        </strong>

                        {{ $document->dispatcher->number }}
                    </td>

                </tr>
            </tbody>
        </table>

    @endisset
    <table class="full-width mt-10 border-box">

        <tr>
            <td style="text-align: center;">
                @php
                use App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate;
                $qrCode = new QrCodeGenerate();
                $qr = $qrCode->displayPNGBase64("https://www.campograndeperu.com");
                @endphp
            <img src="data:image/png;base64,{{ $qr }}">
        </td>
    </tr>
</table>
</body>

</html>
