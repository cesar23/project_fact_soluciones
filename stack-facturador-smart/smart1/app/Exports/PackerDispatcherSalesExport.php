<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class PackerDispatcherSalesExport implements  FromView, ShouldAutoSize
{
    use Exportable;
    protected $records;
    protected $company;
    protected $d_start;
    protected $d_end;
    protected $establishment;
    protected $type;
    public function records($records) {
        $this->records = $records;
        
        return $this;
    }
    public function type($type) {
        $this->type = $type;
        
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
        return view('report::packer_dispatcher.report_excel', [
            'records'=> $this->records,
            'company' => $this->company,
            'd_start'=>$this->d_start,
            'd_end'=>$this->d_end,
            'type' => $this->type,
        ]);
    }
}
