<?php

namespace Modules\Report\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportSalesSabanaExport implements  FromView, ShouldAutoSize
{
    use Exportable;
    protected $records;
    protected $dispatcher;
    protected $company;

    public function dispatcher($dispatcher) {
        $this->dispatcher = $dispatcher;
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


    public function view(): View {
        return view('tenant.reports.sales_sabana.report_excel', [
            'records'=> $this->records,
            'company' => $this->company,
            'dispatcher' => $this->dispatcher
        ]);
    }
}
