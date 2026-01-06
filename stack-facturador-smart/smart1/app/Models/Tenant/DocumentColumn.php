<?php

namespace App\Models\Tenant;

use App\Services\ItemLotsGroupService;
use Hyn\Tenancy\Abstracts\TenantModel;

class DocumentColumn extends TenantModel
{
    protected $table = 'document_columns';
    public $timestamps = false;
    protected $fillable = [
        'column_align',
        'column_order',
        'name',
        'value',
        'width',
        'order',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function getValudDocumentItem($document_item, $value)
    {

        $configuration = Configuration::select(['decimal_quantity', 'decimal_quantity_unit_price_pdf', 'change_decimal_quantity_unit_price_pdf'])->first();
        // if ($configuration->change_decimal_quantity_unit_price_pdf) {
        //     if (
        //         $configuration->decimal_quantity_unit_price_pdf > 6 &&
        //         $configuration->decimal_quantity_unit_price_pdf <= 8
        //     ) {
        //         $width_column = 13;
        //     } elseif ($configuration->decimal_quantity_unit_price_pdf > 8) {
        //         $width_column = 15;
        //     } else {
        //         $width_column = 12;
        //     }
        // }
        $percentage_igv = $document_item->percentage_igv;
        $item = Item::find($document_item->item_id);
        $digemid = $item->getCatDigemid();
        switch ($value) {
            case  'image':
                $imagen = ($item->image_medium !== 'imagen-no-disponible.jpg') ? asset('storage'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'items'.DIRECTORY_SEPARATOR.$item->image_medium) : asset("/logo/{$item->image_medium}");
                return $imagen;
            case  'second_name':
                return $item->second_name;
            case  'description':
                return $item->name;
            case  'category':
                return optional($item->category)->name;
            case  'model':
                return $item->model;
            case  'brand':
                return optional($item->brand)->name;
            case  'lot':
                $itemLotGroup = new ItemLotsGroupService;
                if(isset($document_item->item->IdLoteSelected)){
                    return $itemLotGroup->getLote($document_item->item->IdLoteSelected);
                }
                if(isset($document_item->item->lots_group)){
                    return $itemLotGroup->getLote($document_item->item->lots_group);
                }
                return "";
            case  'serie':
                $series = "";
                if (isset($document_item->item->lots)) {
                    foreach ($document_item->item->lots as $index => $lot) {
                        if (isset($lot->has_sale) && $lot->has_sale) {
                            $series .= $lot->series;
                        }
                        // return $lot->series;
                        if ($index != count($document_item->item->lots) - 1) {
                            $series .= "|";
                        }
                    }
                }
                return $series;
            case  'date_of_due':
                $itemLotGroup = new ItemLotsGroupService;
                if(isset($document_item->item->IdLoteSelected)){
                    return $itemLotGroup->getLotDateOfDue($document_item->item->IdLoteSelected);
                }
                if(isset($document_item->item->lots_group)){
                    return $itemLotGroup->getLotDateOfDue($document_item->item->lots_group);
                }
                return "";
                return $item->date_of_due;
            case  'barcode':
                return $item->barcode;
            case  'internal_code':

                return $item->internal_id ?? "-";
            case  'factory_code':
                return $item->factory_code;
            case  'cod_digemid':
                return $document_item->item->cod_digemid;
            case  'nom_prod':
                if (!empty($digemid)) {
                    return $digemid->getNomProd();
                }
            case  'num_reg_san':
                if (!empty($digemid)) {
                    return $digemid->getNumRegSan();
                }
            case  'nom_titular':
                if (!empty($digemid)) {
                    return $digemid->getNomTitular();
                }
            case  'info_link':
                if($item->info_link){
                    return $item->info_link;
                }
                return '';
            case  'discount':
                $type = null;
                $is_split = false;
                if ($document_item->discounts) {
                    $total_discount_line = 0;
                    foreach ($document_item->discounts as $disto) {
                        $type = $disto->discount_type_id;
                        $amount = $disto->amount;
                        if (isset($disto->is_split) && $disto->is_split) {
                            $amount = $amount * 1.18;
                            $is_split = true;
                        }
                        $total_discount_line = $total_discount_line + $amount;
                    }
                    if (!$is_split) {
                        return number_format($total_discount_line  * (1 + ($percentage_igv / 100)), 2);
                    }
                    return number_format($total_discount_line, 2);
                } else {
                    return 0;
                }
            case  'unit_value':
                if ($configuration->change_decimal_quantity_unit_price_pdf) {
                    return  $document_item->generalApplyNumberFormat($document_item->unit_value, $configuration->decimal_quantity_unit_price_pdf);
                } else {
                    return number_format($document_item->unit_value, 2);
                }
            case  'total_value':
                return $document_item->total_value;
            case  'unit_price':
                if ($configuration->change_decimal_quantity_unit_price_pdf) {
                    return    $document_item->generalApplyNumberFormat($document_item->unit_price, $configuration->decimal_quantity_unit_price_pdf);
                } else {
                    return number_format($document_item->unit_price, 2);
                }
            case  'total_price':
                if (isDacta()) {
                    return $document_item->total_value + $document_item->total_igv + $document_item->total_isc;
                } else {
                    return $document_item->total;
                }
            case  'item_value':
                return number_format($item->sale_unit_price / (1 + ($percentage_igv / 100)), 2);
            case  'item_price':
                return number_format($item->sale_unit_price, 2);
            case 'discount_value':
                $type = null;
                if ($document_item->discounts) {
                    $total_discount_line = 0;
                    $is_split = false;
                    foreach ($document_item->discounts as $disto) {
                        $amount = $disto->amount;
                        $type = $disto->discount_type_id;
                    
                        $total_discount_line = $total_discount_line + $amount;
                    }
                    return number_format($total_discount_line, 2);
                } else {
                    return 0;
                }
        }
    }
}
