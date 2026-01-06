@php
    $establishment = $document->establishment;
    $configuration = \App\Models\Tenant\Configuration::first();
    $configurations = \App\Models\Tenant\Configuration::first();
    $company_name = $company->name;
    $company_owner = null;
    if ($configurations->trade_name_pdf) {
        $company_name = $company->trade_name;
        $company_owner = $company->name;
    }
    $establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $logo = $establishment__->logo ?? $company->logo;
    $header_image = $configuration->header_image;
    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

    $customer = $document->customer;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $accounts = \App\Models\Tenant\BankAccount::all();
    $tittle = $document->prefix . '-' . str_pad($document->number ?? $document->id, 8, '0', STR_PAD_LEFT);

    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

@endphp
<html>

<head>
    {{-- <title>{{ $tittle }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>
    <div style="width: 100%;">
        @if ($company->logo && file_exists(public_path("{$logo}")))
            <div style="width: 20%; float: left;">
                <div class="company_logo_box">
                    <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                        alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                </div>
            </div>
        @else
            <div style="width: 20%; float: left;">
            </div>
        @endif
        <div style="width: 45%; float: left; text-align: center;">
            <div style="text-align: center;">
                <h4>{{ $company_name }}</h4>
                @if ($company_owner)
                    De: {{ $company_owner }}
                @endif
                <h6 style="text-transform: uppercase;">
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
        </div>
        <div style="width: 30%; float: left; padding-left: 20px;padding-top: 10px;">
            <div class="border-box rounded-box">
                <div style="text-align: center; font-weight: bold;">{{ 'R.U.C.' }}</div>
                <div style="text-align: center; font-weight: bold;">{{ $company->number }}</div>
                <div style="text-align: center; font-weight: bold; padding: 3px;" class="primary-bg">
                    COTIZACIÓN</div>
                <div style="text-align: center; font-weight: bold;">{{ $tittle }}</div>
            </div>
        </div>
    </div>
    <div class="full-width">
        <table class="full-width">
            <tr>
                <th colspan="3" class="primary-bg text-center">DATOS DEL CLIENTE</th>
                <th colspan="6"></th>
            </tr>
            <tbody>
                <tr>
                    <td width="15%" class="text-bold-italic">CLIENTE:</td>
                    <td width="30%" colspan="4">{{ $customer->name }}</td>
                    <td width="20%" class="text-bold-italic">TIEMPO DE VALIDEZ:</td>
                    <td width="15%" colspan="3">{{ $document->date_of_due }}</td>
                </tr>
                <tr>
                    <td class="text-bold-italic">RUC:</td>
                    <td colspan="4">{{ $customer->number }}</td>
                    <td width="20%" class="text-bold-italic">CONDICION DE PAGO:</td>
                    <td width="20%" colspan="3" style="text-transform: uppercase;">
                        {{ optional($document->payment_condition)->name ?? 'CONTADO' }}</td>
                </tr>
                <tr>
                    <td class="text-bold-italic">DIRECCIÓN:</td>
                    <td colspan="4">{{ $customer->address }}</td>
                    <td width="20%" class="text-bold-italic">FORMA DE PAGO:</td>
                    <td width="20%" colspan="3">
                        {{ optional($document->payment_method_type)->description ?? 'EFECTIVO' }}</td>
                </tr>
                <tr>
                    <td class="text-bold-italic">SOLICITANTE:</td>
                    <td colspan="4">{{ $customer->name }}</td>
                    <td width="25%" class="text-bold-italic">DIRECCIÓN DE ENTREGA:</td>
                    <td colspan="3">{{ $document->shipping_address }}</td>

                </tr>
                <tr>
                    <td class="text-bold-italic">CORREO:</td>
                    <td colspan="4">{{ $customer->email }}</td>
                    <td width="25%" class="text-bold-italic">TIEMPO DE ENTREGA:</td>
                    <td colspan="3">{{ $document->delivery_date }}</td>
                </tr>
                <tr>
                    <td class="text-bold-italic">VENDEDOR:</td>
                    <td colspan="4">{{ optional($document->seller)->name }}

                        <span class="text-bold-italic">CELULAR:</span>
                        <span class="text-bold-italic">{{ optional($document->seller)->telephone }}</span>
                        </span>
                    </td>

                    <td width="25%" class="text-bold-italic">CORREO:</td>
                    <td colspan="3">{{ optional($document->seller)->email }}</td>
                
                </tr>
            </tbody>
        </table>
    </div>
    <div class="full-width mt-2">
        <table class="full-width">
            <tr>
                <td width="40%" class="text-bold-italic text-center h4">COT. N° {{ $tittle }}</td>
                @php
                    $days_spanish = [
                        1 => 'Lunes',
                        2 => 'Martes',
                        3 => 'Miércoles',
                        4 => 'Jueves',
                        5 => 'Viernes',
                        6 => 'Sábado',
                        0 => 'Domingo',
                    ];
                    $month_spanish = [
                        '01' => 'Enero',
                        '02' => 'Febrero',
                        '03' => 'Marzo',
                        '04' => 'Abril',
                        '05' => 'Mayo',
                        '06' => 'Junio',
                        '07' => 'Julio',
                        '08' => 'Agosto',
                        '09' => 'Septiembre',
                        '10' => 'Octubre',
                        '11' => 'Noviembre',
                        '12' => 'Diciembre',
                    ];
                    $date_of_issue = $document->date_of_issue;
                    $format_date =
                        $days_spanish[$date_of_issue->format('w')] .
                        ', ' .
                        $date_of_issue->format('d') .
                        ' de ' .
                        $month_spanish[$date_of_issue->format('m')] .
                        ' del ' .
                        $date_of_issue->format('Y');
                @endphp
                <td width="60%" class="text-bold-italic text-center h4">FECHA DE COTIZACIÓN: {{ $format_date }}
                </td>
            </tr>
        </table>
    </div>
    <table class="full-width mt-10 mb-10 ">
        <thead class="">
            <tr class="primary-bg">
                <th class="text-white border-box text-center py-2" width="8%">ITEM</th>
                <th class="text-white border-box text-center py-2" width="8%">CODIGO</th>
                <th class="text-white border-box text-left py-2">DESCRIPCIÓN</th>
                <th class="text-white border-box text-left py-2">IMAGEN</th>
                <th class="text-white border-box text-left py-2">CANTIDAD</th>
                <th class="text-white border-box text-right py-2" width="12%">P.UNITARIO</th>
                <th class="text-white border-box text-right py-2" width="12%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $index => $row)
                @php
                    $item_db = \App\Models\Tenant\Item::select('internal_id', 'image')->find($row->item_id);
                @endphp
                <tr>
                    <td class="text-center border-box">{{ str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-center border-box">{{ $item_db->internal_id }}</td>
                    <td class="text-center border-box">
                        @if ($row->item->name_product_pdf ?? false)
                            {!! $row->item->name_product_pdf ?? '' !!}
                        @else
                            {!! $row->item->description !!}
                        @endif

                        @if ($row->attributes)
                            @foreach ($row->attributes as $attr)
                                <br /><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                            @endforeach
                        @endif
                        @if ($row->discounts)
                            @foreach ($row->discounts as $dtos)
                                <br /><span style="font-size: 9px">{{ $dtos->factor * 100 }}%
                                    {{ $dtos->description }}</span>
                            @endforeach
                        @endif

                        @if ($row->item !== null && property_exists($row->item, 'extra_attr_value') && $row->item->extra_attr_value != '')
                            <br /><span style="font-size: 9px">{{ $row->item->extra_attr_name }}:
                                {{ $row->item->extra_attr_value }}</span>
                        @endif

                        @if ($row->item->is_set == 1)
                            <br>
                            @inject('itemSet', 'App\Services\ItemSetService')
                            @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                                {{ $item }}<br>
                            @endforeach
                        @endif

                    </td>
                    <td class="text-center align-top border-box">
                        @if ($item_db->image)
                            @php
                                $imagen =
                                    $item_db->image !== 'imagen-no-disponible.jpg'
                                        ? asset(
                                            'storage' .
                                                DIRECTORY_SEPARATOR .
                                                'uploads' .
                                                DIRECTORY_SEPARATOR .
                                                'items' .
                                                DIRECTORY_SEPARATOR .
                                                $item_db->image,
                                        )
                                        : asset("/logo/{$item_db->image}");
                            @endphp
                                <img style="width:65px" height="65px" src="{{ $imagen }}" alt="image"
                                    class="">
                        @endif
                    </td>
                    <td class="text-center align-top border-box">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                    <td class="text-right align-top border-box">{{ number_format($row->unit_value, 2) }}</td>

                    <td class="text-right align-top border-box">{{ number_format($row->total_value, 2) }}</td>
                </tr>
            
            @endforeach
            <tr>
                <td colspan="7">
                    <br>
                </td>
            </tr>
            <tr>
                <td colspan="5" rowspan="3">
                    @if($header_image && file_exists(public_path("storage/uploads/header_images/{$configuration->header_image}")))
                    <img style="width: 550px" height="50px" src="data:{{mime_content_type(public_path("storage/uploads/header_images/{$configuration->header_image}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/header_images/{$configuration->header_image}")))}}" alt="image" class="">
                    @endif
                </td>
                <td class="text-center font-bold border-box">SUB TOTAL:</td>
                <td class="text-right font-bold border-box">{{$document->currency_type->symbol }} {{ number_format($document->total_value, 2) }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold border-box">IGV 18%:</td>
                <td class="text-right font-bold border-box">{{ $document->currency_type->symbol }} {{ number_format($document->total_igv, 2) }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold border-box">TOTAL: {{ $document->currency_type->symbol }}
                </td>
                <td class="text-right font-bold border-box">{{ $document->currency_type->symbol }} {{ number_format($document->total, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @if($document->description)
    <br>

    <table class="full-width">
        <tr>
            <td>
                <strong>Observaciones:</strong>
                {!! $document->description !!}
            </td>
        </tr>
    </table>
    @endif
    <br>
    <table class="full-width">
        <tr>
            <td width="65%" style="text-align: top; vertical-align: top;">
                <br>
                @foreach ($accounts as $account)
                    <p>
                        <span class="font-bold">{{ $account->bank->description }}</span>
                        {{ $account->currency_type->description }}
                        <span class="font-bold">N°:</span> {{ $account->number }}
                        @if ($account->cci)
                            - <span class="font-bold">CCI:</span> {{ $account->cci }}
                        @endif
                    </p>
                @endforeach
            </td>
        </tr>
        <tr>
            {{-- <td width="65%">
            @foreach ($document->legends as $row)
                <p>Son: <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></p>
            @endforeach
            <br/>
            <strong>Información adicional</strong>
            @foreach ($document->additional_information as $information)
                <p>{{ $information }}</p>
            @endforeach
        </td> --}}
        </tr>
    </table>
    
</body>

</html>
