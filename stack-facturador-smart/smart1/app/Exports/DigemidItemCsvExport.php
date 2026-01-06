<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

/**
 * Class DigemidItemExport
 *
 * @package App\Exports
 */
class DigemidItemCsvExport implements FromView, ShouldAutoSize
{

    protected $records;
    protected $company_cod_digemid;
    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecords()
    : \Illuminate\Database\Eloquent\Collection {
        if(empty($this->records)){
            $this->records = new \Illuminate\Database\Eloquent\Collection();
        }
        return $this->records;
    }

 

  

    /**
     * @param \Illuminate\Database\Eloquent\Collection $records
     *
     * @return DigemidItemExport
     */
    public function setRecords(\Illuminate\Database\Eloquent\Collection $records)
    : DigemidItemCsvExport {
        $this->records = $records;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompanyCodDigemid()
    : string {
        if(empty( $this->company_cod_digemid)){
            $this->company_cod_digemid = '';
        }
        return $this->company_cod_digemid;
    }

    /**
     * @param string $company_cod_digemid
     *
     * @return DigemidItemExport
     */
    public function setCompanyCodDigemid(string $company_cod_digemid)
    : DigemidItemCsvExport {
        $this->company_cod_digemid = $company_cod_digemid;
        return $this;
    }
    use Exportable;



    public function view(): View {
        return view('tenant.items.exports.items-digemid-csv', [
            'records'=> $this->getRecords(),
            'company_cod_digemid'=> $this->getCompanyCodDigemid(),
        ]);
    }


}
