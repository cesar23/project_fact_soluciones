<?php

namespace App\Exports;

use App\Models\Tenant\Company;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SellersExport implements  FromView, ShouldAutoSize
{
    use Exportable;
    protected $records;


    public function records($records) {
        $this->records = $records;
        
        return $this;
    }

    public function view(): View {
        $company = Company::first();
        return view('tenant.mall.exports.sellers', [
            'records'=> $this->records,
            'company'=> $company,
            
        ]);
    }


}
