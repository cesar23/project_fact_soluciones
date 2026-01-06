<?php

namespace Modules\Report\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class DocumentProductExport implements  FromView, ShouldAutoSize
{
    use Exportable;
    public function date($date) {
        $this->date = $date;
        return $this;
    }
    public function items($items) {
        $this->items = $items;
        return $this;
    }

    public function number($number) {
        $this->number = $number;
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
        return view('report::documents.report_product_excel', [
            'items'=> $this->items,
            'company' => $this->company,
            'establishment'=>$this->establishment,
            'number'=>$this->number,
            'date' => $this->date,
        ]);
    }
}
