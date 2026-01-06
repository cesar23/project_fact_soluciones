<?php

namespace App\Exports;

use App\Models\Tenant\PersonType;
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
class ItemPriceUpdatePersonTypeTemplate implements FromView, ShouldAutoSize
{
    use Exportable;




  

    public function view(): View {
        $types = PersonType::all();
        return view('tenant.items.exports.update_person_type_prices_template', [
            'types'=> $types,
        ]);
    }


}
