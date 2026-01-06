<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InventoryReviewProductionOrdenExport implements  FromView, ShouldAutoSize
{
    use Exportable;

    protected $records;
    protected $company;
    protected $establishment;

    public function records($records) {
        $this->records = $records;
        return $this;
    }

    public function company($company) {
        $this->company = $company;

        return $this;
    }

    public function establishment($establishment) {
        $this->establishment = $establishment;

        return $this;
    }


    public function view(): View {

        return view('tenant.inventory_review_production_orden.exports.report_excel', [
            'records' => $this->records,
            'company' => $this->company,
            'establishment' => $this->establishment,
        ]);
    }
}
