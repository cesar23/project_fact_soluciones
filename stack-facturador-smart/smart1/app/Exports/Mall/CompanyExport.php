<?php

namespace App\Exports;

use App\Models\Tenant\Company;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CompanyExport implements  FromView, ShouldAutoSize
{
    use Exportable;



    public function view(): View {
        $company = Company::first();
        return view('tenant.mall.exports.company', [
            'company'=> $company,
        ]);
    }


}
