<?php

namespace Modules\Account\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\AfterSheet;

class LedgerAccountTenantExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithEvents,
    WithCustomStartCell,
    WithChunkReading,
    ShouldAutoSize
{
    use Exportable;

    /**
     * Consulta base (usa Query Builder en la conexión 'tenant')
     */
    public function query()
    {
        return DB::connection('tenant')
            ->table('ledger_accounts_tenant')
            ->select(['code', 'name'])
            ->orderBy('code'); // o por id si prefieres estabilidad
    }

    /**
     * Dónde comienzan los encabezados visibles
     * (dejamos A1 para el título combinado)
     */
    public function startCell(): string
    {
        return 'A2';
    }

    /**
     * Encabezados de la fila 2
     */
    public function headings(): array
    {
        return ['CODIGO', 'DESCRIPCION'];
    }

    /**
     * Mapeo por fila
     */
    public function map($row): array
    {
        return [
            $row->code,
            $row->name,
        ];
    }

    /**
     * Lectura en chunks para no cargar todo en memoria
     */
    public function chunkSize(): int
    {
        return 1000; // ajusta a 500/2000 según recursos
    }

    /**
     * Título combinado y estilos básicos
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Escribimos el título en A1 y combinamos A1:B1
                $sheet->setCellValue('A1', 'PLAN DE CUENTAS');
                $sheet->mergeCells('A1:B1');
            },
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Obtener el último número de fila
                $lastRow = $sheet->getHighestRow();
                
                // Estilos: negrita y centrado al título
                $sheet->getStyle('A1:B1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                ]);

                // Estilos para las cabeceras
                $sheet->getStyle('A2:B2')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                ]);

                // Estilos para todo el contenido de datos
                $sheet->getStyle('A3:B' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);
            },
        ];
    }
}
