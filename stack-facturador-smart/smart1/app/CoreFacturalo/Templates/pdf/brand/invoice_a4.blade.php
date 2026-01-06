@php
$configuration = \App\Models\Tenant\Configuration::first();
$company_name = $company->name;
$company_owner = null;
if ($configuration->trade_name_pdf) {
    $company_name = $company->trade_name;
    $company_owner = $company->name;
}

$establishment = $document->establishment;
$logo = $establishment->logo ?? $company->logo;

if ($logo === null) {
    $logo = $company->logo;
} elseif (!file_exists(public_path($logo))) {
    $logo = $company->logo;
}

if ($logo) {
    $logo = "storage/uploads/logos/{$logo}";
    $logo = str_replace("storage/uploads/logos/storage/uploads/logos/", "storage/uploads/logos/", $logo);
}

$customer = $document->customer;
$invoice = $document->invoice;
$document_base = $document->note ? $document->note : null;
$document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
$accounts = \App\Models\Tenant\BankAccount::all();

if ($document_base) {
    $affected_document_number = $document_base->affected_document
        ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT)
        : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);
} else {
    $affected_document_number = null;
}

$payments = $document->payments;
$total_payment = $payments->sum('payment');
$balance = ($document->total - $total_payment) - $payments->sum('change');
$document->load('reference_guides');
@endphp

<div style="top: -10px;">
<table class="full-width my-0 py-0" border="1">
    <thead >
        <tr class="mt-0">
            <th class="border-bottom text-center py-1 desc" width="10%">Código</th>
            <th class="border-bottom text-center py-1 desc" width="10%">Marca</th>
            <th class="border-bottom text-center py-1 desc" width="">Descripción</th>
            <th class="border-bottom text-center py-1 desc" width="10%">Cant.</th>
            <th class="border-bottom text-center py-1 desc" width="10%">U.M.</th>
            <th class="border-bottom text-center py-1 desc" width="10%">P.U</th>
            <th class="border-bottom text-center py-1 desc" width="10%">Importe</th>
        </tr>
    </thead>
    <tbody class="">
        @foreach($document->items as $row)
            <tr>
                <td class="p-1 text-center align-top desc">{{ $row->item->internal_id }}</div></td>
                <td class="p-1 text-center align-top desc">{{ $row->m_item->brand != null ? $row->m_item->brand->name : '' }}</div></td>
                <td class="p-1 text-left align-top desc text-upp">
                    @if($row->name_product_pdf)
                        {!!$row->name_product_pdf!!}
                    @else
                        {!!$row->item->description!!}
                    @endif

                      

                    @if($row->attributes)
                        @foreach($row->attributes as $attr)
                            <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                        @endforeach
                    @endif
                    {{-- @if($row->discounts)
                        @foreach($row->discounts as $dtos)
                            <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                        @endforeach
                    @endif --}}

                    @if($row->item->is_set == 1)
                     <br>
                     @inject('itemSet', 'App\Services\ItemSetService')
                        {{join( "-", $itemSet->getItemsSet($row->item_id) )}}
                    @endif
                </td>
                <td class="p-1 text-center align-top desc">
                    @if(((int)$row->quantity != $row->quantity))
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                <td class="p-1 text-center align-top desc">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                <td class="p-1 text-right align-top desc">{{ number_format($row->unit_price, 2) }}</td>
                <td class="p-1 text-right align-top desc">{{ number_format($row->total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@if ($document->terms_condition)
        <br>
        <table class="full-width">
            <tr>
                <td>
                    <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                    {!! $document->terms_condition !!}
                </td>
            </tr>
        </table>
    @endif
</div>
