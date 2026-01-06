<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class SummarySalesExport implements  FromView, ShouldAutoSize
{
    use Exportable;
    protected $records;
    protected $company;
    protected $d_start;
    protected $d_end;
    protected $establishment;
    public function records($records) {
        $this->records = $records;
        
        return $this;
    }
    
    public function company($company) {
        $this->company = $company;
        
        return $this;
    }
    public function d_start($d_start) {
        $this->d_start = $d_start;
        
        return $this;
    }
    public function d_end($d_end) {
        $this->d_end = $d_end;
        
        return $this;
    }
    /*public function establishment($establishment) {
        $this->establishment = $establishment;
        
        return $this;
    }*/
    
    public function view(): View {
        return view('report::summary_sales.report_excel', [
            'records'=> $this->records,
            'company' => $this->company,
            'd_start'=>$this->d_start,
            'd_end'=>$this->d_end,
            //'establishment'=>$this->establishment
        ]);
    }
}
