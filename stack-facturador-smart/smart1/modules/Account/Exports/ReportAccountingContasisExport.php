<?php

namespace Modules\Account\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class ReportAccountingContasisExport implements  FromView
{
    use Exportable;

    protected $data;


    public function data($data)
    {
        $this->data = $data;

        return $this;
    }

    public function view(): View {
        if($this->data['type_operation'] == 'purchase') {
            return view('account::accounting.templates.excel_contasis_purchase', $this->data);
        }
        return view('account::accounting.templates.excel_contasis', $this->data);
    }
}
