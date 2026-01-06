<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class SaleNoteTransportFormatExport implements  FromView, ShouldAutoSize
{
    use Exportable;

    protected $records;
    protected $company;
    protected $seller;
    public function records($records) {
        $this->records = $records;
        
        return $this;
    }
    
    public function company($company) {
        $this->company = $company;
        
        return $this;
    }
    
    public function seller($seller) {
        $this->seller = $seller;
        
        return $this;
    }
    /*public function establishment($establishment) {
        $this->establishment = $establishment;
        
        return $this;
    }*/
    
    public function view(): View {
        return view('tenant.reports.sale_notes.transport_format', [
            'records'=> $this->records,
            'company' => $this->company,
            'seller' => $this->seller,
            //'establishment'=>$this->establishment
        ]);
    }
}
