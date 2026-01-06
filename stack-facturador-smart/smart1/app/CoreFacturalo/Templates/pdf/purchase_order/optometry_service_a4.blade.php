@php
    $establishment = $document->user->establishment;
    $customer = $document->customer;
    $tittle = str_pad($document->id, 8, '0', STR_PAD_LEFT);
    $configurations = \App\Models\Tenant\Configuration::first();
    $company_name = $company->name;
    $company_owner = null;
    if ($configurations->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }
@endphp
<html>

<head>
    {{-- <title>{{ $tittle }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>
    <table class="full-width">
        <tr>
            @if ($company->logo)
                <td width="20%">
                    <div class="company_logo_box">
                        <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                            alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                    </div>
                </td>
            @else
                <td width="20%">
                    {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px"> --}}
                </td>
            @endif
            <td width="50%" class="pl-3">
                <div class="text-left">
                    <h4 class="">{{ $company_name }}</h4>
                    @if ($company_owner)
                        De: {{ $company_owner }}
                    @endif
                    <h5>{{ 'RUC ' . $company->number }}</h5>
                    <h6>
                        {{ $establishment->address !== '-' ? $establishment->address : '' }}
                        {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                        {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                        {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                    </h6>

                    @isset($establishment->trade_address)
                        <h6>{{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                        </h6>
                    @endisset
                    <h6>{{ $establishment->telephone !== '-' ? 'Central telefónica: ' . $establishment->telephone : '' }}
                    </h6>

                    <h6>{{ $establishment->email !== '-' ? 'Email: ' . $establishment->email : '' }}</h6>

                    @isset($establishment->web_address)
                        <h6>{{ $establishment->web_address !== '-' ? 'Web: ' . $establishment->web_address : '' }}</h6>
                    @endisset

                    @isset($establishment->aditional_information)
                        <h6>{{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
                        </h6>
                    @endisset
                </div>
            </td>
            <td width="30%" class="border-box py-4 px-2 text-center">
                <h5 class="text-center">SERVICIO DE OPTOMETRIA</h5>
                <h3 class="text-center">{{ $tittle }}</h3>
            </td>
        </tr>
    </table>
    <table class="full-width mt-5">
        <tr>
            <td width="15%">Cliente:</td>
            <td width="45%">{{ $customer->name }}</td>
            <td width="25%">Fecha de emisión:</td>
            <td width="15%">{{ $document->date_of_issue->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td>{{ $customer->identity_document_type->description }}:</td>
            <td>{{ $customer->number }}</td>

        </tr>
        @if ($customer->address !== '')
            <tr>
                <td class="align-top">Dirección:</td>
                <td colspan="">
                    {{ $customer->address }}
                    {{ $customer->district_id !== '-' ? ', ' . $customer->district->description : '' }}
                    {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                    {{ $customer->department_id !== '-' ? '- ' . $customer->department->description : '' }}
                </td>
            </tr>
        @endif
        <tr>
            <td class="align-top">Celular:</td>
            <td colspan="3">
                {{ $document->cellphone }}
            </td>
        </tr>
        {{-- <tr>
        <td class="align-top">N° Serie:</td>
        <td colspan="3">
            {{ $document->serial_number }}
        </td>
    </tr> --}}
    </table>


    <table class="full-width mt-4 mb-5">
        <tr>
            <td><b>Descripción:</b></td>
        </tr>
        <tr>
            <td>{{ $document->description }}</td>
        </tr>

    </table>
    @php
        $data = $document->optometry_service_data;
    @endphp
    <table class="border-box full-width mt-10 mb-10">
        <thead>
            <tr>
                <th class="border-box" colspan="5">
                    PRUEBAS PRELIMINARES
                </th>
            </tr>
            <tr>
                <th class="border-box"></th>
                <th class="border-box">AV s/c</th>
                <th class="border-box">AV c/c</th>
                <th class="border-box">dnp</th>
                <th class="border-box">Kx</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border-box">OD</td>
                <td class="border-box"> {{ $data->od_av_sc }}</td>
                <td class="border-box"> {{ $data->od_av_cc }}</td>
                <td class="border-box"> {{ $data->od_dnp }}</td>
                <td class="border-box"> {{ $data->od_kx }}</td>
            </tr>
            <tr>
                <td class="border-box">OI</td>
                <td class="border-box"> {{ $data->oi_av_sc }}</td>
                <td class="border-box"> {{ $data->oi_av_cc }}</td>
                <td class="border-box"> {{ $data->oi_dnp }}</td>
                <td class="border-box"> {{ $data->oi_kx }}</td>
            </tr>
        </tbody>
    </table>

    <table class="border-box full-width mt-10 mb-10">
        <thead>

            <tr>
                <th class="border-box"></th>
                <th class="border-box">Cover test</th>
                <th class="border-box">PPC</th>
                <th class="border-box">Motilidad</th>
                <th class="border-box">Reacción pupilar</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border-box">Alterado</td>
                <td class="border-box"> {{ $data->cover_test ? 'X' : '' }}</td>
                <td class="border-box"> {{ $data->ppc ? 'X' : '' }}</td>
                <td class="border-box"> {{ $data->motilidad ? 'X' : '' }}</td>
                <td class="border-box"> {{ $data->reaccion_pupilar ? 'X' : '' }}</td>
            </tr>
        </tbody>
    </table>
    <table class="border-box full-width mt-10 mb-10">
        <thead>
            <tr>
                <th class="border-box" colspan="5">
                    REFRACCIÓN
                </th>
            </tr>
            <tr>
                <th class="border-box"></th>
                <th class="border-box">ESF</th>
                <th class="border-box">CIL</th>
                <th class="border-box">EJE</th>
                <th class="border-box">AD</th>
                <th class="border-box">AV</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border-box">OD</td>
                <td class="border-box"> {{ $data->od_esf }}</td>
                <td class="border-box"> {{ $data->od_cil }}</td>
                <td class="border-box"> {{ $data->od_eje }}</td>
                <td class="border-box"> {{ $data->od_ad }}</td>
                <td class="border-box"> {{ $data->od_av }}</td>
            </tr>
            <tr>
                <td class="border-box">OI</td>
                <td class="border-box"> {{ $data->oi_esf }}</td>
                <td class="border-box"> {{ $data->oi_cil }}</td>
                <td class="border-box"> {{ $data->oi_eje }}</td>
                <td class="border-box"> {{ $data->oi_ad }}</td>
                <td class="border-box"> {{ $data->oi_av }}</td>
            </tr>
        </tbody>
    </table>
    <table class="border-box full-width mt-10 mb-10">
        <thead>
            <tr>
                <th class="border-box" colspan="3">
                    PRUEBAS COMPLEMENTARIAS
                </th>
            </tr>
            <tr>
                <th class="border-box"></th>
                <th class="border-box">Alterado</th>
                <th class="border-box">NOTAS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="20%" class="border-box">Test de Worth</td>
                <td width="10%" class="border-box">
                    {{ $data->test_worth ? 'X' : '' }}
                </td>
                <td class="border-box">
                    {{ $data->test_worth_description }}
                </td>
            </tr>
            <tr>
                <td class="border-box">Test de Schober</td>
                <td class="border-box">
                    {{ $data->test_schober ? 'X' : '' }}
                </td>
                <td class="border-box">
                    {{ $data->test_schober_description }}
                </td>
            </tr>
            <tr>
                <td class="border-box">Prueba de color</td>
                <td class="border-box">
                    {{ $data->test_color ? 'X' : '' }}
                </td>
                <td class="border-box">
                    {{ $data->test_color_description }}
                </td>
            </tr>
            <tr>
                <td class="border-box">Rejilla Amster</td>
                <td class="border-box">
                    {{ $data->amster ? 'X' : '' }}
                </td>
                <td class="border-box">
                    {{ $data->amster_description }}
                </td>
            </tr>
            <tr>
                <td class="border-box">T. de Medios</td>
                <td class="border-box">
                    {{ $data->test_medias ? 'X' : '' }}
                </td>
                <td class="border-box">
                    {{ $data->test_medias_description }}
                </td>
            </tr>
            <tr>
                <td class="border-box">Retina</td>
                <td class="border-box">
                    {{ $data->retina ? 'X' : '' }}
                </td>
                <td class="border-box">
                    {{ $data->retina_description }}
                </td>
            </tr>
            <tr>
                <td class="border-box">PIO</td>
                <td class="border-box">
                    {{ $data->pio ? 'X' : '' }}
                </td>
                <td class="border-box">
                    {{ $data->pio_description }}
                </td>
            </tr>
        </tbody>

    </table>
    <table class="full-width mt-10 mb-10">
        <thead>
            <tr>
                <th class="border-box text-center">CANT.</th>
                <th class="border-box text-center">DESCRIPCIÓN</th>
                <th class="border-box text-center">P.UNIT</th>
                <th class="border-box text-center">IMPORTE</th>
                <th class="border-box text-center">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
                <tr>
                    <td class="text-center border-box">{{ $row->quantity }}</td>
                    <td class="border-box">{{ $row->item->description }}</td>
                    <td class="text-right border-box">{{ number_format($row->unit_price, 2) }}</td>
                    <td class="text-right border-box">{{ number_format($row->unit_price * $row->quantity, 2) }}</td>
                    <td class="text-right border-box">{{ number_format($row->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>


    </table>
</body>
<table class="full-width mt-10 mb-10">
    <thead class="">
        <tr class="bg-grey">
        </tr>
    </thead>
    <tbody>
        <tr>
        </tr>
        <tr>
            <td colspan="4" class="text-right font-bold mb-3">COSTO DEL SERVICIO: </td>
            <td class="text-right font-bold">{{ number_format($document->cost + $document->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="4" class="text-right font-bold">PAGOS: </td>
            @php
                $prepayment = $document->prepayment;
                $payments = $document->payments->sum('payment');
                $total_payment = $prepayment + $payments;
            @endphp
            <td class="text-right font-bold">{{ number_format($total_payment, 2) }}</td>
        </tr>
        <tr>
            <td colspan="4" class="text-right font-bold">SALDO A PAGAR: </td>
            <td class="text-right font-bold">{{ number_format($document->total + $document->cost - $total_payment, 2) }}
            </td>
        </tr>
    </tbody>
</table>


</html>
