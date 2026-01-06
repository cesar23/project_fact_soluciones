@php
    use App\CoreFacturalo\Helpers\Template\TemplateHelper;
    use App\Models\Tenant\Document;
	use App\Models\Tenant\SaleNote;
    if ($document != null) {
        $establishment = $document->establishment;
        $customer = $document->customer;
        $invoice = $document->invoice;
        $document_base = ($document->note) ? $document->note : null;

        //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
        $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);

        if($document_base) {

            $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

        } else {

            $affected_document_number = null;
        }

        $payments = $document->payments;

        // $document->load('reference_guides');

        if ($document->payments) {
            $total_payment = $document->payments->sum('payment');
            $balance = ($document->total - $total_payment) - $document->payments->sum('change');
        }


    }

    $accounts = \App\Models\Tenant\BankAccount::all();

    $path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');


    // Pago/Coutas detalladas
    $paymentDetailed= [];
	if(
		$document != null  && (
		get_class($document) == Document::class ||
		get_class($document) == SaleNote::class
        )
	){
        $paymentDetailed = TemplateHelper::getDetailedPayment($document);
	}
    $document_type_ids = ["01", "03", "80","07","08"];

@endphp
<head>
    <link href="{{ $path_style }}" rel="stylesheet" />
</head>
<body>

@if($document != null && in_array($document->document_type_id, $document_type_ids))
    <table class="full-width border-box my-2">

        <tr>
            <td class="text-upp p-2">SON:
                @foreach(array_reverse( (array) $document->legends) as $row)
                    @if ($row->code == "1000")
                        {{ $row->value }} {{ $document->currency_type->description }}
                    @else
                        {{$row->code}}: {{ $row->value }}
                    @endif
                @endforeach
            </td>
        </tr>
       
    </table>
    
    <table class="full-width border-box my-2">
        @if ($document->retention)
        <tr>
            <td>
                <strong>Información de la retención:</strong>
            </td>
        </tr>
        <tr>
            <td>Base imponible de la retención:
                S/ {{ round($document->retention->amount / $document->retention->percentage, 2) }}</td>
        </tr>
        <tr>
            <td>Porcentaje de la retención {{ $document->retention->percentage * 100 }}%</td>
        </tr>
        <tr>
            <td>Monto de la retención S/ {{ $document->retention->amount_pen }}</td>
        </tr>
        @endif
        <tr>
            <td class="text-upp p-2">OBSERVACIONES:
                @if($document->additional_information)
                    @foreach($document->additional_information as $information)
                        @if ($information)
                            {{ $information }}
                        @endif
                    @endforeach
                @endif
            </td>
        </tr>
    </table>




@endif
<table class="full-width">
    <tr>
        <td class="text-center desc">Representación Impresa de {{ isset($document->document_type) ? $document->document_type->description : 'Comprobante Electrónico'  }} {{ isset($document->hash) ? 'Código Hash: '.$document->hash : '' }} <br>Para consultar el comprobante ingresar a {!! searchUrl() !!}</td>
    </tr>
</table>
</body>
