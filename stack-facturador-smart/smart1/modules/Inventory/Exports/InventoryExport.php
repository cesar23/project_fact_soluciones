<?php

namespace Modules\Inventory\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InventoryExport implements  FromView, ShouldAutoSize
{
    use Exportable;

    protected $records;
    protected $company;
    protected $establishment;
    protected $totals;
    protected $format;
    protected $currency = 'PEN';
    protected $showSomeColumns = false;
    protected $user_id;

    public function showSomeColumns($showSomeColumns) {
        $this->showSomeColumns = $showSomeColumns;
        return $this;
    }

    public function currency($currency) {
        $this->currency = $currency;
        return $this;
    }

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

    public function format($format) {
        $this->format = $format;

        return $this;
    }

    public function totals($totals) {
        $this->totals = $totals;
        return $this;
    }
    public function user_id($user_id) {
        $this->user_id = $user_id;
        return $this;
    }

    public function view(): View {

        return view('inventory::reports.inventory.report_excel', [
            'records' => $this->records,
            'company' => $this->company,
            'establishment' => $this->establishment,
            'format' => $this->format,
            'currency' => $this->currency,
            'totals' => $this->totals,
            'showSomeColumns' => $this->showSomeColumns,
            'user_id' => $this->user_id,
        ]);
    }
}
