<?php

namespace Modules\Inventory\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class InventoryListExport implements ShouldAutoSize, FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithMapping, WithEvents
{
    use Exportable;
    
    protected $allRecords;
    protected $company_name;
    protected $company_number;
    protected $warehouse_description = null;

    public function allRecords($allRecords)
    {
        $this->allRecords = $allRecords;
        return $this;
    }

    public function companyName($company_name)
    {
        $this->company_name = $company_name;
        return $this;
    }

    public function companyNumber($company_number)
    {
        $this->company_number = $company_number;
        return $this;
    }

    public function warehouseDescription($warehouse_description)
    {
        $this->warehouse_description = $warehouse_description;
        return $this;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        // Crear colección con los datos del inventario
        $data = collect();
        
        // Agregar filas de datos
        foreach (
            $this->allRecords as $idx => $record) {
            $row = [
                'item_description' => $record['item_description'] ?? $record['item_fulldescription'] ?? '',
            ];
            if ($this->warehouse_description == null) {
                $row['warehouse_description'] = $record['warehouse_description'] ?? '';
            }
            // Si el stock es 0, ponerlo como texto '0 ' (cero + espacio no separable)
            if (!isset($record['stock']) || $record['stock'] === '' || $record['stock'] === null || (int)$record['stock'] === 0) {
                $row['stock'] = '0';
            } else {
                $row['stock'] = (double)$record['stock'];
            }
            $data->push($row);
        }
        
        return $data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headings = ['Producto'];
        
        // Agregar columna de almacén solo si no hay filtro específico
        if ($this->warehouse_description == null) {
            $headings[] = 'Almacén';
        }
        
        $headings[] = 'Stock';
        
        return $headings;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Inventario';
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        $mappedRow = [
            $row['item_description'],
        ];
        
        // Agregar columna de almacén solo si no hay filtro específico
        if ($this->warehouse_description == null) {
            $mappedRow[] = $row['warehouse_description'];
        }
        
        // Si el stock es 0, ponerlo como texto '0 '
        if (!isset($row['stock']) || $row['stock'] === '' || $row['stock'] === null || (int)$row['stock'] === 0) {
            $mappedRow[] = '0';
        } else {
            $mappedRow[] = (int)$row['stock'];
        }
        
        return $mappedRow;
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        $widths = [
            'A' => 50, // Producto
        ];
        
        // Agregar columna de almacén solo si no hay filtro específico
        if ($this->warehouse_description == null) {
            $widths['B'] = 30; // Almacén
            $widths['C'] = 15; // Stock
        } else {
            $widths['B'] = 15; // Stock
        }
        
        return $widths;
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Obtener el rango de datos
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        // Estilo para el encabezado
        $headerRange = 'A1:' . $highestColumn . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E2EFDA',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        // Estilo para todas las celdas de datos
        $dataRange = 'A2:' . $highestColumn . $highestRow;
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'font' => [
                'size' => 11,
            ],
        ]);
        
        // Alineación específica para la columna de stock
        $stockColumn = $this->warehouse_description == null ? 'C' : 'B';
        $sheet->getStyle($stockColumn . '2:' . $stockColumn . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        // Ajustar altura de filas
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(25);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $colCount = $this->warehouse_description == null ? 3 : 2;
                $lastCol = chr(64 + $colCount);
                $row = 1;
                // Título principal
                $sheet->insertNewRowBefore($row, 6);
                $sheet->setCellValue('A'.$row, 'INVENTARIO');
                $sheet->mergeCells('A'.$row.':'.$lastCol.$row);
                $sheet->getStyle('A'.$row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $row++;
                // Nombre de la empresa
                if ($this->company_name) {
                    $sheet->setCellValue('A'.$row, $this->company_name);
                    $sheet->mergeCells('A'.$row.':'.$lastCol.$row);
                    $sheet->getStyle('A'.$row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 14],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                    $row++;
                }
                // Número de la empresa
                if ($this->company_number) {
                    $sheet->setCellValue('A'.$row, $this->company_number);
                    $sheet->mergeCells('A'.$row.':'.$lastCol.$row);
                    $sheet->getStyle('A'.$row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 14],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                    $row++;
                }
                // Información del almacén si está filtrado
                if ($this->warehouse_description) {
                    $sheet->setCellValue('A'.$row, 'Almacén: ' . $this->warehouse_description);
                    $sheet->mergeCells('A'.$row.':'.$lastCol.$row);
                    $sheet->getStyle('A'.$row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                    $row++;
                }
                // Fecha y hora
                $sheet->setCellValue('A'.$row, 'Fecha: ' . date('d/m/Y'));
                $sheet->setCellValue('B'.$row, 'Hora: ' . date('H:i:s'));
                $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray([
                    'font' => ['size' => 10],
                ]);
            }
        ];
    }
}
