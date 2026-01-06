<?php

namespace Modules\Suscription\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Class ItemExportWp
 *
 * @package App\Exports
 */
class ClientSuscriptionExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $records;
    protected $currentYear;

    public function records($records) {
        $this->records = $records;
        return $this;
    }

    public function currentYear($currentYear) {
        $this->currentYear = $currentYear;
        return $this;
    }

    public function view(): View {
        return view('suscription::clients.export', [
            'records'=> $this->records,
            'currentYear' => $this->currentYear 
        ]);
    }


}
