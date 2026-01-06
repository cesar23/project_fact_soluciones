<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Clientes</title>
</head>
@php
    $configuration = \App\Models\Tenant\Configuration::first();
    $show_line_credit = $configuration->bill_of_exchange_special;
@endphp

<body>
    @if (!empty($records))
        <div class="">
            <div class=" ">
                <table class="">
                    <thead>
                        <tr>
                            <td>Código documento de identidad</td>
                            <td>Número de documento</td>
                            <td>Nombre/Razón Social</td>
                            <td>Nombre Comercial</td>
                            @if ($show_line_credit)
                                <td>Línea crédito</td>
                                <td>Línea utilizada</td>
                                <td>Línea disponible</td>
                            @endif
                            <td>Código del Páis</td>
                            <td>Código de Ubigeo</td>
                            <td>Dirección</td>
                            <td>Correo electrónico</td>
                            <td>Teléfono</td>
                            <td>Tipo Cliente</td>
                            <td>Código interno</td>
                            <td>Zona</td>
                            <td>Web</td>
                            <td>Observación</td>
                            <td>Vendedor</td>
                            <td>Código de barras</td>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $key => $value)
                            <?php
                            /** @var \App\Models\Tenant\Person $value*/
                            $line_credit = floatval($value->line_credit);
                            $total_used = 0.00;
                            $amount_available = 0.00;
                            $data_credit = null;
                            if ($show_line_credit) {
                                $data_credit = \Modules\Dashboard\Helpers\DashboardView::getUnpaidByCustomerJustTotalAndTotalPayment($value->id)->get();

                                if ($data_credit) {
                                    $total_used = floatval($data_credit->sum('total'));
                                    $amount_available = $line_credit - $total_used;
                                }
                            }
                            $ubigeo = $value->district_id;
                            $department = $value->department->description ?? '';
                            $province = $value->province->description ?? '';
                            $district = $value->district->description ?? '';
                            $zone = $value->zone_id ? $value->getZone()->name : '';
                            $seller = $value->seller ? $value->seller->getName() : '';
                            $observation = $value->observation ?: '';
                            ?>
                            <tr>
                                <td class="celda">{{ $value->identity_document_type_id }}</td>
                                <td class="celda">{{ $value->number }}</td>
                                <td class="celda">{{ $value->name }}</td>
                                <td class="celda">{{ $value->trade_name }}</td>
                                @if ($show_line_credit)
                                    <td class="celda" style="text-align: right;">{{ number_format($line_credit, 2) }}</td>
                                    <td class="celda" style="text-align: right;">{{ number_format($total_used, 2) }}</td>
                                    <td class="celda" style="text-align: right;">{{ number_format($amount_available, 2) }}</td>
                                @endif
                                <td class="celda">{{ $value->country_id }}</td>
                                <td class="celda">{{ $ubigeo }}</td>
                                <td class="celda">{{ $value->address }}</td>
                                <td class="celda">{{ $value->email }}</td>
                                <td class="celda">{{ $value->telephone }}</td>
                                <td class="celda">{{ $value->person_type_id == 1 ? 'INTERNO' : 'DISTRIBUIDOR' }}</td>
                                <td class="celda">{{ $value->internal_code }}</td>
                                <td class="celda">{{ $zone }}</td>
                                <td class="celda">{{ $value->website }}</td>
                                <td class="celda">{{ $observation }}</td>
                                <td class="celda">{{ $seller }}</td>
                                <td class="celda">{{ $value->barcode }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div>
            <p>No se encontraron registros.</p>
        </div>
    @endif
</body>

</html>
