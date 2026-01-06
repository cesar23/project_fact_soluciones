<?php

namespace Modules\Inventory\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ValuedKardexFormatSunatExport implements FromView, ShouldAutoSize
{
    use Exportable;
    protected $init_cost_total;
    protected $init_cost_unit;
    protected $init_stock;
    protected $show_init;
    protected $company;
    protected $records;
    protected $establishment;
    protected $additionalData;
    public function init_cost_unit($init_cost_unit)
    {
        $this->init_cost_unit = $init_cost_unit;
        return $this;
    }
    public function init_cost_total($init_cost_total)
    {
        $this->init_cost_total = $init_cost_total;
        return $this;
    }
    public function init_stock($init_stock)
    {
        $this->init_stock = $init_stock;
        return $this;
    }
    public function records($records)
    {
        $this->records = $records;
        return $this;
    }

    public function show_init($show_init)
    {

        $this->show_init = $show_init;
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

    public function additionalData($additionalData)
    {
        $this->additionalData = $additionalData;
        return $this;
    }

    public function view(): View
    {

        return view('inventory::reports.valued_kardex.report_excel_sunat', [
            'records' => $this->records,
            'company' => $this->company,
            'establishment' => $this->establishment,
            'additionalData' => $this->additionalData,
            'init_stock' => $this->init_stock ?? 0,
            'show_init' => $this->show_init ?? false,
            'init_cost_unit' => $this->init_cost_unit ?? 0,
            'init_cost_total' => $this->init_cost_total ?? 0,
        ]);
    }
}
