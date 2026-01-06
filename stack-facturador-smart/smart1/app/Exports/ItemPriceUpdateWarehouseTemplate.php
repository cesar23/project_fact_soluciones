<?php

namespace App\Exports;

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
class ItemPriceUpdateWarehouseTemplate implements FromView, ShouldAutoSize
{
    use Exportable;

    public function view(): View {
        $warehouses = Warehouse::where('active', true)->get();
        return view('tenant.items.exports.update_warehouse_prices_template', [
            'warehouses'=> $warehouses,
        ]);
    }


}
