<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BillOfExchangeExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $records;
    protected $company;
    protected $date;
    public function __construct($records, $company, $date)
    {
        $this->records = $records;
        $this->company = $company;
        $this->date = $date;
    }

    public function view(): View
    {
        return view('tenant.bill_of_exchange.export_excel', [
            'records' => $this->records,
                'company' => $this->company,
            'date' => $this->date
        ]);
    }
} 