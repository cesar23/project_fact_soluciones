<?php

namespace Modules\Report\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Modules\BusinessTurn\Models\BusinessTurn;

class SaleNoteExport implements  FromView, ShouldAutoSize
{
    use Exportable;
    
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
    
    public function filters($filters) {
        $this->filters = $filters;
        
        return $this;
    }

    public function view(): View {
        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        $view = 'report::sale_notes.report_excel';
        if($is_integrate_system){
            $view = 'report::sale_notes.report_excel_integrate_system';
        }

        
        return view($view, [
            'records'=> $this->records,
            'company' => $this->company,
            'establishment'=>$this->establishment,
            'filters'=>$this->filters
        ]);
    }
}
