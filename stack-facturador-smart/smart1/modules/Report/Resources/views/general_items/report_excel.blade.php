<?php
use App\Models\Tenant\Configuration;
$configuration = Configuration::first();
$multi_companies = $configuration->multi_companies;
$qty_general = 0;
$total_general = 0;
$purchase_total_general = 0;
$gain_general = 0;
//if exists function in the controller
if (!function_exists('getLocationData')) {
    function getLocationData($value, $type = 'sale')
    {
        $customer = null;
        $district = '';
        $department = '';
        $province = '';
        $type_doc = null;
        if ($type == 'sale') {
            if (isset($value->document->customer)) {
                $type_doc = $value->document;
            } elseif (isset($value->sale_note->customer)) {
                $type_doc = $value->sale_note;
            }
        }
        if ($value && $type_doc && $type_doc->customer) {
            $customer = $type_doc->customer;
        }
        if ($customer != null) {
            if ($customer->district && $customer->district->description) {
                $district = $customer->district->description;
            }
            if ($customer->department && $customer->department->description) {
                $department = $customer->department->description;
            }
            if ($customer->province && $customer->province->description) {
                $province = $customer->province->description;
            }
        }
        return [
            'district' => $district,
            'department' => $department,
            'province' => $province,
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
        content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REPORTE PRODUCTOS</title>
</head>

<body>
    @if (!empty($records))
        <div>
            <div class=" ">
                <table>
                    <thead>
                        <tr>
                            @include('report::general_items.partials.report_excel_header', [
                                'document_type_id' => $document_type_id,
                                'type' => $type,
                            ])
                        </tr>
                    </thead>
                    <tbody>
                        @if ($type == 'sale')

                            @foreach ($records as $key => &$value)
                                @if ($value->sale_note && $value->sale_note->date_of_issue)
                                    <?php
                                    $value = \App\Models\Tenant\SaleNoteItem::find($value->id);
                                    if (isset($qty)) {
                                        unset($qty);
                                    }
                                    $series = '';
                                    if (isset($value->item->lots)) {
                                        $series_data = collect($value->item->lots)
                                            ->where('has_sale', 1)
                                            ->pluck('series')
                                            ->toArray();
                                        $series = implode(' - ', $series_data);
                                    }
                                    
                            
                                    $total_item_purchase = \Modules\Report\Http\Resources\GeneralItemCollection::getPurchaseUnitPrice($value);
                                    
                                    if (isset($row->item->presentation)) {
                                        if (is_object($row->item->presentation)) {
                                            $quantity_unit = $value->item->presentation->quantity_unit;
                                            $total_item_purchase *= $quantity_unit * $value->quantity;
                                        }
                                    }
                                    $apply_conversion_to_pen = $request_apply_conversion_to_pen == 'true';
                                    $purchase_total_general += round($total_item_purchase, 2);
                                    if($value->isCurrencyTypeUsd()){
                                        $gain_general += round($value->getConvertTotalToPen() - $total_item_purchase, 2);
                                    }else{
                                        $gain_general += round($value->total - $total_item_purchase, 2);
                                    }
                                    
                                    if (!$apply_conversion_to_pen && $value->isCurrencyTypeUsd()) {
                                        $total_item_purchase /= $value->getExchangeRateSale() == 0 ? 1 : $value->getExchangeRateSale();
                                    }
                                    $utility_item = $value->total - $total_item_purchase;
                                    $qty_general += $value->quantity;
                                    if($value->isCurrencyTypeUsd()){
                                        $total_general += round($value->getConvertTotalToPen(), 2);
                                    }else{
                                        $total_general += round($value->total, 2);
                                    }
                                    /** @var \App\Models\Tenant\Item $item */
                                    $item = $value->getModelItem();
                                    $model = $item->model;
                                    $document = $value->sale_note;
                                    $platform = $item->getWebPlatformModel();
                                    if ($platform !== null) {
                                        $platform = $platform->name;
                                    }
                                    $pack = $item->getSetItems();
                                    $stablihsment = getLocationData($value, $type);
                                    ?>
                                    @include('report::general_items.partials.report_excel_body_sale', [
                                        'document_type_id' => $document_type_id,
                                        'document' => $document,
                                        'type' => $type,
                                        'value' => $value,
                                        'key' => $key,
                                        'item' => $item,
                                        'stablihsment' => $stablihsment,
                                        'configuration' => $configuration,
                                        'multi_companies' => $multi_companies,
                                    ])
                                    @if ($pack !== null)
                                        @foreach ($pack as $item_pack)
                                            <?php
                                            /** @var \App\Models\Tenant\ItemSet $item_pack */
                                            $value->item = $item_pack->individual_item;
                                            /** @var \App\Models\Tenant\Item $item */
                                            $item = $value->item;
                                            $qty = $item_pack->quantity;
                                            $qty_general += $qty;
                                            ?>
                                            @include(
                                                'report::general_items.partials.report_excel_body_sale',
                                                [
                                                    'document_type_id' => $document_type_id,
                                                    'document' => $document,
                                                    'type' => $type,
                                                    'value' => $value,
                                                    'key' => $key,
                                                    'item' => $item,
                                                    'qty' => $qty,
                                                ]
                                            )
                                        @endforeach
                                    @endif
                                @endif
                            @endforeach

                            @foreach ($records as $key => $value)
                                @if ($value->document && $value->document->date_of_issue)
                                    <?php
                                    if (isset($qty)) {
                                        unset($qty);
                                    }
                                    /** @var \App\Models\Tenant\DocumentItem $value */
                                    $series = '';
                                    if (isset($value->item->lots)) {
                                        $series_data = collect($value->item->lots)
                                            ->where('has_sale', 1)
                                            ->pluck('series')
                                            ->toArray();
                                        $series = implode(' - ', $series_data);
                                    }
                                    $total_item_purchase = \Modules\Report\Http\Resources\GeneralItemCollection::getPurchaseUnitPrice($value);
                                    
                                    if (isset($row->item->presentation)) {
                                        if (is_object($row->item->presentation)) {
                                            $quantity_unit = $value->item->presentation->quantity_unit;
                                    
                                            $total_item_purchase *= $quantity_unit * $value->quantity;
                                        }
                                    }
                                    $purchase_total_general += round($total_item_purchase, 2);
                                    if($value->isCurrencyTypeUsd()){
                                        $gain_general += round($value->getConvertTotalToPen() - $total_item_purchase, 2);
                                    }else{
                                        $gain_general += round($value->total - $total_item_purchase, 2);
                                    }
                                    
                                    $apply_conversion_to_pen = $request_apply_conversion_to_pen == 'true';
                                    if (!$apply_conversion_to_pen && $value->isCurrencyTypeUsd()) {
                                        $total_item_purchase /= $value->getExchangeRateSale() == 0 ? 1 : $value->getExchangeRateSale();
                                    }
                                    $utility_item = $value->total - $total_item_purchase;
                                    $item = $value->getModelItem();
                                    $qty_general += $value->quantity;
                                    if($value->isCurrencyTypeUsd()){
                                        $total_general += round($value->getConvertTotalToPen(), 2);
                                    }else{
                                        $total_general += round($value->total, 2);
                                    }
                                    $model = $item->model;
                                    /** @var  \App\Models\Tenant\Document $document */
                                    $document = $value->document;
                                    $purchseOrder = $document->purchase_order;
                                    $platform = $item->getWebPlatformModel();
                                    if ($platform !== null) {
                                        $platform = $platform->name;
                                    }
                                    $pack = $item->getSetItems();
                                    $item = $value->item;
                                    $stablihsment = getLocationData($value, $type);
                                    ?>
                                    @include('report::general_items.partials.report_excel_body_sale', [
                                        'document_type_id' => $document_type_id,
                                        'document' => $document,
                                        'type' => $type,
                                        'value' => $value,
                                        'key' => $key,
                                        'item' => $item,
                                        'stablihsment' => $stablihsment,
                                        'configuration' => $configuration,
                                        'multi_companies' => $multi_companies,
                                    ])
                                    @if ($pack !== null)
                                        @foreach ($pack as $item_pack)
                                            <?php
                                            /** @var \App\Models\Tenant\ItemSet $item_pack */
                                            $value->item = $item_pack->individual_item;
                                            /** @var \App\Models\Tenant\Item $item */
                                            $item = $value->item;
                                            $qty = $item_pack->quantity;
                                            $qty_general += $qty;
                                            ?>
                                            @include(
                                                'report::general_items.partials.report_excel_body_sale',
                                                [
                                                    'document_type_id' => $document_type_id,
                                                    'document' => $document,
                                                    'type' => $type,
                                                    'value' => $value,
                                                    'key' => $key,
                                                    'item' => $item,
                                                    'qty' => $qty,
                                                    'stablihsment' => $stablihsment,
                                                    'configuration' => $configuration,
                                                    'multi_companies' => $multi_companies,
                                                ]
                                            )
                                        @endforeach
                                    @endif
                                @endif
                            @endforeach
                            @foreach ($records as $key => $value)
                                @if ($value->quotation && $value->quotation->date_of_issue)
                                    <?php
                                    $value = \App\Models\Tenant\QuotationItem::find($value->id);
                                    if (isset($qty)) {
                                        unset($qty);
                                    }
                                    /** @var \App\Models\Tenant\QuotationItem $value */
                                    $series = '';
                                    if (isset($value->item->lots)) {
                                        $series_data = collect($value->item->lots)
                                            ->where('has_sale', 1)
                                            ->pluck('series')
                                            ->toArray();
                                        $series = implode(' - ', $series_data);
                                    }
                                    $total_item_purchase = \Modules\Report\Http\Resources\GeneralItemCollection::getPurchaseUnitPrice($value);
                                    if (isset($row->item->presentation)) {
                                        if (is_object($row->item->presentation)) {
                                            $quantity_unit = $value->item->presentation->quantity_unit;
                                    
                                            $total_item_purchase *= $quantity_unit * $value->quantity;
                                        }
                                    }
                                    $purchase_total_general += round($total_item_purchase, 2);
                                    if($value->isCurrencyTypeUsd()){
                                        $gain_general += round($value->getConvertTotalToPen() - $total_item_purchase, 2);
                                    }else{
                                        $gain_general += round($value->total - $total_item_purchase, 2);
                                    }
                                    
                                    $apply_conversion_to_pen = $request_apply_conversion_to_pen == 'true';
                                    if (!$apply_conversion_to_pen && $value->isCurrencyTypeUsd()) {
                                        $total_item_purchase /= $value->getExchangeRateSale() == 0 ? 1 : $value->getExchangeRateSale();
                                    }
                                    $utility_item = $value->total - $total_item_purchase;
                                    $item = $value->getModelItem();
                                    $qty_general += $value->quantity;
                                    if($value->isCurrencyTypeUsd()){
                                        $total_general += round($value->getConvertTotalToPen(), 2);
                                    }else{
                                        $total_general += round($value->total, 2);
                                    }
                                    
                                    $model = $item->model;
                                    /** @var  \App\Models\Tenant\Quotation $document */
                                    $document = $value->quotation;
                                    $purchseOrder = $document->purchase_order;
                                    $platform = $item->getWebPlatformModel();
                                    if ($platform !== null) {
                                        $platform = $platform->name;
                                    }
                                    $pack = $item->getSetItems();
                                    $item = $value->item;
                                    $stablihsment = getLocationData($value, $type);
                                    ?>
                                    @include('report::general_items.partials.report_excel_body_sale', [
                                        'document_type_id' => $document_type_id,
                                        'document' => $document,
                                        'type' => $type,
                                        'value' => $value,
                                        'key' => $key,
                                        'item' => $item,
                                        'stablihsment' => $stablihsment,
                                        'configuration' => $configuration,
                                        'multi_companies' => $multi_companies,
                                    ])
                                    @if ($pack !== null)
                                        @foreach ($pack as $item_pack)
                                            <?php
                                            /** @var \App\Models\Tenant\ItemSet $item_pack */
                                            $value->item = $item_pack->individual_item;
                                            /** @var \App\Models\Tenant\Item $item */
                                            $item = $value->item;
                                            $qty = $item_pack->quantity;
                                            $qty_general += $qty;
                                            ?>
                                            @include(
                                                'report::general_items.partials.report_excel_body_sale',
                                                [
                                                    'document_type_id' => $document_type_id,
                                                    'document' => $document,
                                                    'type' => $type,
                                                    'value' => $value,
                                                    'key' => $key,
                                                    'item' => $item,
                                                    'qty' => $qty,
                                                    'stablihsment' => $stablihsment,
                                                    'configuration' => $configuration,
                                                    'multi_companies' => $multi_companies,
                                                ]
                                            )
                                        @endforeach
                                    @endif
                                @endif
                            @endforeach
                        @else
                            @foreach ($records as $value)
                                <?php
                                /** @var \App\Models\Tenant\SaleNoteItem  $value */
                                $purchase = $value->purchase;
                                
                                ?>
                                @if ($purchase !== null)
                                    @include('report::general_items.partials.report_excel_body_purchase', [
                                        'document_type_id' => $document_type_id,
                                        'value' => $value,
                                        'purchase' => $purchase,
                                        'configuration' => $configuration,
                                        'multi_companies' => $multi_companies,
                                    ])
                                @endif
                            @endforeach
                        @endif

                        @if ($type == 'sale')
                            <tr>
                                <td colspan="24"></td>
                                <td>TOTALES</td>
                                <td>{{ $qty_general }}</td>
                                <td colspan="14"></td>
                                <td>{{ $total_general }}</td>
                                <td> {{ $purchase_total_general }} </td>
                                <td>{{ $gain_general }}</td>
                            </tr>
                        @endif
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
