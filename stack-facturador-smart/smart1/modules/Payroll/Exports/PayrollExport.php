<?php

namespace Modules\Payroll\Exports;

use App\Models\Tenant\Company;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class PayrollExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        return collect($this->records);
    }

    public function headings(): array
    {
        $company = Company::first();
        $currentDate = Carbon::now()->format('Y-m-d H:i:s');

        return [
            [$company->name],
            [$company->number],
            ['Fecha: ' . $currentDate],
            [],
            [
                'Código',
                'Nombre',
                'Apellido',
                'Edad',
                'Sexo',
                'Puesto',
                'Fecha de ingreso',
                'Fecha de cese',
            ]
        ];
    }

    public function map($record): array
    {
        return [
            $record['code'],
            $record['name'],
            $record['last_name'],
            $record['age'],
            $record['sex'],
            $record['job_title'],
            $record['admission_date'],
            $record['cessation_date'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A2:H2')->getFont()->setBold(true);
        $sheet->getStyle('A3:H3')->getFont()->setBold(true);
        $sheet->getStyle('A5:H5')->getFont()->setBold(true);
        $sheet->getStyle('A5:H5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D3D3D3');
        $sheet->getStyle('A5:H5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getDefaultColumnDimension()->setWidth(20);
        $sheet->getDefaultRowDimension()->setRowHeight(20);
    }
}
