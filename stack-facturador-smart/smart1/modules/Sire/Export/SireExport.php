<?php

namespace Modules\Sire\Export;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Class ItemExportWp
 *
 * @package App\Exports
 */
class SireExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $records;
    protected $type;

    public function records($records) {
        $this->records = $records;
        return $this;
    }

    public function type($type) {
        $this->type = $type;
        return $this;
    }

    public function view(): View {
        return view('sire::export', [
            'records'=> $this->records,
            'type'=> $this->type,
        ]);
    }


}
