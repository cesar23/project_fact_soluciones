<?php

namespace Modules\Inventory\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StockHistoryExport implements FromView, ShouldAutoSize
{
    use Exportable;

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

    public function additionalData($additionalData)
    {
        $this->additionalData = $additionalData;
        return $this;
    }

    public function view(): View
    {

        return view('inventory::reports.valued_kardex.stock_date_excel', [
            'records' => $this->records,
            'company' => $this->company,
            'additionalData' => $this->additionalData
        ]);
    }
}
