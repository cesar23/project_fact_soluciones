<?php

namespace App\Exports;

use App\Models\Tenant\ItemUnitType;
use App\Models\Tenant\Warehouse;
use Illuminate\Contracts\View\View;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Class ItemExport
 *
 * @package App\Exports
 */
class ItemPriceUpdatePresentationTemplate implements FromView, ShouldAutoSize
{
    use Exportable;

    public function view(): View {
        $presentations = ItemUnitType::where('active', true)->with('item')->get()->transform(function ($presentation) {
                $item = $presentation->item;
                $item_presentation = $item->item_presentation;
                $item_internal_id = $item->internal_id;
            return [
                'id' => $presentation->id,
                'item_presentation' => $item_presentation->description,
                'item_internal_id' => $item_internal_id,
                'description' => $item->description,
                'quantity_unit_type' => $presentation->quantity,
            ];
        });
        return view('tenant.items.exports.update_presentation_price_template', [
            'presentations'=> $presentations,
        ]);
    }


}
