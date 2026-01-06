<?php

namespace Modules\Item\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ItemMigrationExport implements FromView, ShouldAutoSize
{
    use Exportable;
    protected $records;
    protected $warehouse_id;
    protected $v2;
    public function records($records)
    {
        $this->records = $records;

        return $this;
    }
    public function v2($v2)
    {
        $this->v2 = $v2;
        return $this;
    }
    public function warehouse_id($warehouse_id)
    {
        $this->warehouse_id = $warehouse_id;
        return $this;
    }
    public function view(): View
    {
        $view = $this->v2 ? 'item::item-migration.report_excel_v2' : 'item::item-migration.report_excel';
        return view($view, [
            'records' => $this->records,
            'warehouse_id' => $this->warehouse_id,
        ]);
    }
}
