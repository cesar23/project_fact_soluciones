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
    $logo = "storage/uploads/logos/{$company->logo}";
    if ($establishment->logo) {
        $logo = "{$establishment->logo}";
    }
    $customer = $document->customer;
    $invoice = $document->invoice;
    $accounts = \App\Models\Tenant\BankAccount::all();
    $tittle = $document->prefix . '-' . str_pad($document->number ?? $document->id, 8, '0', STR_PAD_LEFT);
    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();
    $documment_columns = \App\Models\Tenant\DocumentColumn::where('is_visible', true)->where('type','COT')
        ->orderBy('column_order', 'asc')
        ->get();
    $total_discount_items = 0;
    $plate_number_info = $document->plate_number_info;

    // Fix para imagen de logo
    $logo_path = public_path("{$logo}");
    $logo_base64 = '';
    if(file_exists($logo_path)) {
        $logo_base64 = base64_encode(file_get_contents($logo_path));
    }

    // Anchos para 58mm (aproximados, pueden necesitar ajuste)
    // Ancho total disponible aproximado: 210px para un POS de 58mm (considerando algunos márgenes)
    // Ajustar estos valores según sea necesario.
    $item_name_max_width = 100; // Ancho máximo en px para la descripción del item.
    $item_font_size = '8px';
    $header_font_size = '10px';
    $subheader_font_size = '8px';
    $footer_font_size = '7px';
    $table_font_size = '8px';
    $line_height_compact = '0.9em'; // Para reducir espacio entre líneas

@endphp
<html>
<head>

</head>
<body>
    @if ($company->logo && $logo_base64)
        <div class="text-center company_logo_box">
            <img src="data:{{ mime_content_type($logo_path) }};base64, {{ $logo_base64 }}"
                alt="{{ $company->name }}" class="company_logo_ticket">
        </div>
    @endif
    <table class="full-width">
        <tr>
            <td class="text-center">
                <h5 style="font-size: {{ $header_font_size }};">{{ $company_name }}</h5>
            </td>
        </tr>
        @if ($company_owner)
            <tr>
                <td class="text-center">
                    <p style="font-size: {{ $subheader_font_size }};">De: {{ $company->name }}</p>
                </td>
            </tr>
        @endif
        <tr>
            <td class="text-center">
                <p style="font-size: {{ $subheader_font_size }};">{{ 'RUC ' . $company->number }}</p>
            </td>
        </tr>
        <tr>
            <td class="text-center desc">
                @if ($configuration->show_company_address)
                    {{ $establishment->address !== '-' ? $establishment->address : '' }}
                    {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                    {{-- <br> --}} {{-- Considerar saltos de línea para mejor ajuste --}}
                    {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                    {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                @endif
                @isset($establishment->trade_address)
                    <br><span >{{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}</span>
                @endisset
                <br><span >{{ $establishment->telephone !== '-' ? 'Telf: ' . $establishment->telephone : '' }}</span>
                @if ($configuration->show_email)
                    <br><span >{{ $establishment->email !== '-' ? 'Email: ' . $establishment->email : '' }}</span>
                @endif
                @isset($establishment->web_address)
                    <br><span >{{ $establishment->web_address !== '-' ? 'Web: ' . $establishment->web_address : '' }}</span>
                @endisset
                @isset($establishment->aditional_information)
                    <br><span >{{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}</span>
                @endisset
            </td>
        </tr>
        <tr>
            <td class="text-center py-1 border-top">
                <h4 style="font-size: {{ $header_font_size }};">{{ get_document_name('quotation', 'Cotización') }}</h4>
            </td>
        </tr>
        <tr>
            <td class="text-center pb-1 border-bottom">
                <h3 style="font-size: {{ $header_font_size }};">{{ $tittle }}</h3>
            </td>
        </tr>
    </table>
    <table class="full-width mt-1">
        <tr>
            <td width="30%" >F. Emisión:</td>
            <td width="70%" >{{ $document->date_of_issue->format('Y-m-d') }}</td>
        </tr>

        @if ($document->date_of_due)
            <tr>
                <td >T. Validez:</td>
                <td >{{ $document->date_of_due }}</td>
            </tr>
        @endif

        @if ($document->delivery_date)
            <tr>
                <td >T. Entrega:</td>
                <td >{{ $document->delivery_date }}</td>
            </tr>
        @endif
        @if ($configuration->info_customer_pdf)
            <tr>
                <td >Cliente:</td>
                <td >{{ $customer->name }}</td>
            </tr>
            <tr>
                <td >{{ $customer->identity_document_type->description }}:</td>
                <td >{{ $customer->number }}</td>
            </tr>
            @if ($customer->address !== '')
                <tr>
                    <td >Dirección:</td>
                    <td >
                        {{ $customer->address }}
                        {{ $customer->district_id !== '-' ? ', ' . $customer->district->description : '' }}
                        {{-- <br> --}}
                        {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                        {{ $customer->department_id !== '-' ? '- ' . $customer->department->description : '' }}
                    </td>
                </tr>
            @endif
        @endif
        @if (isset($customer->location) && $customer->location)
            <tr>
                <td >Ubicación:</td>
                <td >{{ $customer->location }}</td>
            </tr>
        @endif
        @if ($document->shipping_address)
            <tr>
                <td >Dir. Envío:</td>
                <td >{{ $document->shipping_address }}</td>
            </tr>
        @endif

        @if ($customer->telephone)
            <tr>
                <td >Teléfono:</td>
                <td >{{ $customer->telephone }}</td>
            </tr>
        @endif
        @if ($document->payment_method_type)
            <tr>
                <td >T. Pago:</td>
                <td >{{ $document->payment_method_type->description }}</td>
            </tr>
        @endif

        @if ($document->account_number)
            <tr>
                <td >N° Cuenta:</td>
                <td >{{ $document->account_number }}</td>
            </tr>
        @endif
        @if ($document->sale_opportunity)
            <tr>
                <td >O. Venta:</td>
                <td >{{ $document->sale_opportunity->number_full }}</td>
            </tr>
        @endif
        <tr>
            <td >Vendedor:</td>
            <td >
                @if ($document->seller && $document->seller->name)
                    {{ $document->seller->name }}
                @else
                    {{ $document->user->name }}
                @endif
            </td>
        </tr>
        @if ($document->description && !is_integrate_system())
            <tr>
                <td >Observación:</td>
                <td >{!! str_replace("\n", '<br/>', $document->description) !!}</td>
            </tr>
        @endif
        @if ($document->contact)
            <tr>
                <td >Contacto:</td>
                <td >{{ $document->contact }}</td>
            </tr>
        @endif
        @if ($document->phone)
            <tr>
                <td >Telf. Contacto:</td>
                <td >{{ $document->phone }}</td>
            </tr>
        @endif
        @if ($document->purchase_order)
            <tr>
                <td >O. Compra:</td>
                <td >{{ $document->purchase_order }}</td>
            </tr>
        @endif
        @if ($document->quotation_id)
            <tr>
                <td >Cotización:</td>
                <td >{{ $document->quotation->identifier }}</td>
            </tr>
        @endif
        @if ($plate_number_info)
        <tr>
            <td >N° Placa:</td>
            <td >{{ $plate_number_info['description'] }}</td>
        </tr>
        {{-- Se omite resto de info de placa para ahorrar espacio, considerar si es crucial --}}
        @endif
    </table>

    <div class="border-box mt-1 mb-1">
        <table class="full-width">
            <thead>
                <tr>
                    <th class="border-bottom text-center py-1 px-1" style="font-size: {{ $table_font_size }};" width="15%">Cant</th>
                    <th class="border-bottom border-left text-left py-1 px-1" style="font-size: {{ $table_font_size }};" width="40%">Descripción</th>
                    <th class="border-bottom border-left text-right py-1 px-1" style="font-size: {{ $table_font_size }};" width="20%">P.U.</th>
                    <th class="border-bottom border-left text-right py-1 px-1" style="font-size: {{ $table_font_size }};" width="25%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($document->items as $row)
                    <tr>
                        <td class="text-center align-top px-1" style="font-size: {{ $table_font_size }};">
                            @if ((int) $row->quantity != $row->quantity)
                                {{ $row->quantity }}
                            @else
                                {{ number_format($row->quantity, 0) }}
                            @endif
                        </td>
                
                        <td class="text-left align-top border-left px-1 desc">
                            @php
                                $description = $row->name_product_pdf ?? $row->item->description;
                                $description = trim($description);
                                $symbols = ['&nbsp;', '&amp;', '&quot;', '&lt;', '&gt;'];
                                $replacements = [' ', '&', '"', '<', '>'];
                                $description = str_replace($symbols, $replacements, $description);
                                $description = removePTag($description);
                            @endphp
                    {{$description}} 

                            @if ($configurations->name_pdf)
                                @php
                                    $item_name = \App\Models\Tenant\Item::select('name')
                                        ->where('id', $row->item_id)
                                        ->first();
                                @endphp
                                @if ($item_name && $item_name->name)
                                    <br><span style="font-size: {{ $footer_font_size }}">{{ $item_name->name }}</span>
                                @endif
                            @endif
                             @if ($row->attributes)
                                @foreach ($row->attributes as $attr)
                                    <br /><span style="font-size: {{ $footer_font_size }}">{!! $attr->description !!} : {{ $attr->value }}</span>
                                @endforeach
                            @endif
                            @if ($row->discounts)
                                @foreach ($row->discounts as $dtos)
                                    @if ($dtos->is_amount == false)
                                        <br /><span style="font-size: {{ $footer_font_size }}">{{ $dtos->factor * 100 }}% {{ $dtos->description }}</span>
                                    @endif
                                @endforeach
                            @endif
                        </td>
                        <td class="text-right align-top border-left px-1" style="font-size: {{ $table_font_size }};">
                            @if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf)
                                {{ $row->generalApplyNumberFormat($row->unit_price, $configuration_decimal_quantity->decimal_quantity_unit_price_pdf) }}
                            @else
                                {{ number_format($row->unit_price, $configuration->decimal_quantity) }}
                            @endif
                        </td>
                    
                        <td class="text-right align-top border-left px-1" style="font-size: {{ $table_font_size }};">
                             {{ number_format($row->total, $configuration->decimal_quantity) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <table class="full-width mt-1 totals-table">
        @php
            $col_label = 'colspan="2"'; // Ajustar colspan basado en columnas visibles (Cant, Desc, P.U., Total -> 4 cols. Leyenda usa 2, valor 2)
            $col_value = 'colspan="2"';
        @endphp

        @if ($document->total_exportation > 0)
            <tr>
                <td {{ $col_label }} class="text-right font-bold desc">Op. Exportación: {{ $document->currency_type->symbol }}</td>
                <td {{ $col_value }} class="text-right font-bold desc">{{ number_format($document->total_exportation, $configuration->decimal_quantity) }}</td>
            </tr>
        @endif
        @if ($document->total_free > 0)
            <tr>
                <td {{ $col_label }} class="text-right font-bold desc">Op. Gratuitas: {{ $document->currency_type->symbol }}</td>
                <td {{ $col_value }} class="text-right font-bold desc">{{ number_format($document->total_free, $configuration->decimal_quantity) }}</td>
            </tr>
        @endif
        @if ($document->total_unaffected > 0)
            <tr>
                <td {{ $col_label }} class="text-right font-bold desc">Op. Inafectas: {{ $document->currency_type->symbol }}</td>
                <td {{ $col_value }} class="text-right font-bold desc">{{ number_format($document->total_unaffected, $configuration->decimal_quantity) }}</td>
            </tr>
        @endif
        @if ($document->total_exonerated > 0)
            <tr>
                <td {{ $col_label }} class="text-right font-bold desc">Op. Exoneradas: {{ $document->currency_type->symbol }}</td>
                <td {{ $col_value }} class="text-right font-bold desc">{{ number_format($document->total_exonerated, $configuration->decimal_quantity) }}</td>
            </tr>
        @endif

        @if ($document->total_taxed > 0 || $document->document_type_id === '07')
            <tr>
                <td {{ $col_label }} class="text-right font-bold desc">Op. Gravadas: {{ $document->currency_type->symbol }}</td>
                <td {{ $col_value }} class="text-right font-bold desc">{{ number_format($document->total_taxed, $configuration->decimal_quantity) }}</td>
            </tr>
        @endif

        @if ($document->total_plastic_bag_taxes > 0)
            <tr>
                <td {{ $col_label }} class="text-right font-bold desc">Icbper: {{ $document->currency_type->symbol }}</td>
                <td {{ $col_value }} class="text-right font-bold desc">{{ number_format($document->total_plastic_bag_taxes, $configuration->decimal_quantity) }}</td>
            </tr>
        @endif
        <tr>
            <td {{ $col_label }} class="text-right font-bold desc">IGV: {{ $document->currency_type->symbol }}</td>
            <td {{ $col_value }} class="text-right font-bold desc">{{ number_format($document->total_igv, $configuration->decimal_quantity) }}</td>
        </tr>

        @if ($document->total_isc > 0)
        <tr>
            <td {{ $col_label }} class="text-right font-bold desc">ISC: {{ $document->currency_type->symbol }}</td>
            <td {{ $col_value }} class="text-right font-bold desc">{{ number_format($document->total_isc, $configuration->decimal_quantity) }}</td>
        </tr>
        @endif

        @if($total_discount_items > 0)
        <tr>
            <td {{ $col_label }} class="text-right font-bold desc">Total Dctos.: {{ $document->currency_type->symbol }}</td>
            <td {{ $col_value }} class="text-right font-bold desc">{{ number_format($total_discount_items, $configuration->decimal_quantity) }}</td>
        </tr>
        @endif


        <tr>
            <td {{ $col_label }} class="text-right font-bold border-top desc">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
            <td {{ $col_value }} class="text-right font-bold border-top desc">{{ number_format($document->total, $configuration->decimal_quantity) }}</td>
        </tr>
        @if ($document->fee->count() > 0 && $configurations->show_fee_info_pdf)
            <tr>
                <td colspan="4" class="border-bottom"></td>
            </tr>
            @foreach ($document->fee as $fee)
                <tr>
                    <td colspan="4" class="text-left"><b>{{ $loop->iteration }}° Cuota:</b> {{ $fee->date->format('d/m/Y') }} - {{ $document->currency_type->symbol }} {{ $fee->amount }}</td>
                </tr>
            @endforeach
        @endif
    </table>

    <table class="full-width mt-1">
        @if ($document->additional_information)
            <tr>
                <td class="text-left footer-text"><b>Información Adicional</b></td>
            </tr>
            @foreach ($document->additional_information as $information)
                <tr>
                    <td class="text-left footer-text">{{ $information }}</td>
                </tr>
            @endforeach
        @endif
    </table>

    <table class="full-width mt-1">
        @if (in_array($document->document_type_id, ['01', '03']))
            @foreach ($accounts as $account)
                <tr>
                    <td class="text-left footer-text desc" colspan="2">
                        @if ($account->bank_name)
                            <span class="font-bold">{{ $account->bank_name }}:</span>
                        @endif
                        @if ($account->cci)
                            CCI: {{ $account->cci }}
                        @endif
                        @if ($account->account_number)
                            N°: {{ $account->account_number }}
                        @endif
                    </td>
                </tr>
            @endforeach
        @endif
    </table>

    @if ($document->terms_condition)
        <table class="full-width mt-1">
            <tr>
                <td class="text-left footer-text desc">
                    <span class="font-bold">Términos y Condiciones</span><br>
                    {!! str_replace("\n", '<br/>', $document->terms_condition) !!}
                </td>
            </tr>
        </table>
    @endif
    
    


    @if ($configurations->show_qr_code_ticket_quotation && $document->qr)
        <table class="full-width mt-1">
            <tr>
                <td class="text-center">
                    <img src="data:image/png;base64, {{ $document->qr }}" style="width:80px;" />
                </td>
            </tr>
        </table>
    @endif

    


</body>
</html> 