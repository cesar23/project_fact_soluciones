<?php

namespace Modules\Report\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportCarrierDocumentExport implements  FromView, ShouldAutoSize
{
    use Exportable;
    protected $records;
    protected $dispatcher;
    protected $company;
    protected $date_start;
    protected $date_end;

    public function date_start($date_start) {
        $this->date_start = $date_start;
        return $this;
    }
    public function date_end($date_end) {
        $this->date_end = $date_end;
        return $this;
    }
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
        return view('tenant.reports.carrier_document.report_excel', [
            'records'=> $this->records,
            'company' => $this->company,
            'dispatcher' => $this->dispatcher,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end
        ]);
    }
}
