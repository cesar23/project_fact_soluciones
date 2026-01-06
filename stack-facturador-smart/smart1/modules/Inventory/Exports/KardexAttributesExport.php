<?php

namespace Modules\Inventory\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class KardexAttributesExport implements FromView, ShouldAutoSize
{
    use Exportable;
    public function init_balance($init_balance)
    {
        $this->init_balance = $init_balance;

        return $this;
    }
 
    public function records($records)
    {
        $this->records = $records;

        return $this;
    }

    public function company($company)
    {
        $this->company = $company;

        return $this;
    }

    public function establishment($establishment)
    {
        $this->establishment = $establishment;

        return $this;
    }

    public function item($item)
    {
        $this->item = $item;

        return $this;
    }

    public function view(): View
    {
        $userWarehouse = auth()->user()->establishment_id;
        return view('inventory::reports.kardex.report_attributes_pdf', [
             'records' => $this->records,
             'company' => $this->company,
            'init_balance' => $this->init_balance ?? 0,
            'establishment' => $this->establishment,
            'item' => $this->item,
            'userWarehouse' => $userWarehouse
        ]);
    }
}
