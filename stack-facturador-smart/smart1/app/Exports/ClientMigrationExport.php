<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ClientMigrationExport implements FromView, ShouldAutoSize
{
    use Exportable;
    protected $records;
    protected $type;
    public function records($records)
    {
        $this->records = $records;

        return $this;
    }

    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    public function view(): View
    {
        return view('tenant.persons.exports.persons_migration', [
            'records' => $this->records,
            'type' => $this->type,
        ]);
    }
}
