@php
    $configuration = \App\Models\Tenant\Configuration::first();
    $configurations = \App\Models\Tenant\Configuration::first();
    $establishment = $document->user->establishment;
    $customer = $document->customer;
    $tittle = "TS-".str_pad($document->id, 8, '0', STR_PAD_LEFT);
    $company_name = $company->name;
    $company_owner = null;
    if ($configurations->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }    
    $detail = [];
    if($document->repair) {
        $detail[] = 'REPARACIÓN';
    }
    if($document->warranty) {
        $detail[] = 'GARANTÍA';
    }
    if($document->maintenance) {
        $detail[] = 'MANTENIMIENTO';
    }
    if($document->diagnosis) {
        $detail[] = 'DIAGNÓSTICO';
    }
    if($document->ironing_and_painting) {
        $detail[] = 'PLANCHADO Y PINTURA';
    }
    if($document->equipments) {
        $detail[] = 'EQUIPAMIENTO';
    }
    if($document->preventive_maintenance) {
        $detail[] = 'MANTENIMIENTO PREVENTIVO';
    }
    if($document->corrective_maintenance) {
        $detail[] = 'MANTENIMIENTO CORRECTIVO';
    }
    $detail = !empty($detail) ? implode(' / ', $detail) : null;

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
                <h5 class="text-center">{{ get_document_name('technical_service', 'SERVICIO TÉCNICO') }}</h5>
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
            <td>
                {{ $document->cellphone }}
            </td>
            @if ($document->web_platform_id)
            <td class="align-top">Plataforma:</td>
            <td>
                {{ $document->web_platform->name }}
                </td>
            @endif
        </tr>
        <tr>
            <td class="align-top">N° Serie:</td>
            <td>
                {{ $document->serial_number }}
            </td>
            @if ($document->purchase_order)
                <td class="align-top">N° Orden de Compra:</td>
                <td>
                    {{ $document->purchase_order }}
                </td>
            @endif
        </tr>
    </table>


    <table class="full-width mt-4 mb-5">
        <tr>
            <td><b>Descripción:</b></td>
        </tr>
        <tr>
            <td>{{ $document->description }}</td>
        </tr>
        <tr>
            <td><b> Estado:</b></td>
        </tr>
        <tr>
            <td>{{ $document->state }}</td>
        </tr>

        <tr>
            <td><b>Motivo:</b></td>
        </tr>
        <tr>
            <td>{{ $document->reason }}</td>
        </tr>
        @if ($document->activities)
            <tr>
                <td><b>Actividades realizadas:</b></td>
            </tr>
            <tr>
                <td>{{ $document->activities }}</td>
            </tr>
        @endif
        @if ($detail)
        <tr>
            
            <td>
                <br>
                <h5><strong>
                    {{ $detail }}</strong></h5>
            </td>
        </tr>
        @endif
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
                <td class="text-right font-bold">
                    {{ number_format($document->total + $document->cost - $total_payment, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
