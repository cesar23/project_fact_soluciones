<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

/**
 * Class ItemExport
 *
 * @package App\Exports
 */
class DispatchExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $records;
    protected $isTransport = false;
    public function records($records)
    {
        $this->records = $records;

        return $this;
    }


    public function isTransport($isTransport)
    {
        $this->isTransport = $isTransport;

        return $this;
    }

 

    public function view(): View
    {
        return view('tenant.dispatches.exports.excel', [
            'records' => $this->records,
            'isTransport' => $this->isTransport
        ]);
    }
}
