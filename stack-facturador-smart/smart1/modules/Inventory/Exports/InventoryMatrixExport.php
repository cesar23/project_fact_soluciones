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
use Modules\Inventory\Models\Warehouse;
use App\Models\Tenant\Item;

class InventoryMatrixExport implements ShouldAutoSize, FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithMapping, WithEvents
{
    use Exportable;

    protected $company_name;
    protected $company_number;
    protected $warehouses;
    protected $items;
    protected $matrixData;

    public function __construct()
    {
        $this->warehouses = Warehouse::where('active', true)->orderBy('description')->get();
        $this->prepareData();
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

    protected function prepareData()
    {
        // Obtener todos los items que tienen stock en al menos un almacén
        $this->items = Item::with(['item_warehouse.warehouse'])
            ->where('unit_type_id', '!=', 'ZZ')
            ->whereNotIsSet()
            ->whereHas('item_warehouse')
            ->orderBy('description')
            ->get();

        // Preparar la matriz de datos
        $this->matrixData = collect();

        foreach ($this->items as $item) {
            $row = [
                'item_description' => $item->internal_id ? "{$item->internal_id} - {$item->description}" : $item->description,
            ];

            // Agregar stock para cada almacén
            foreach ($this->warehouses as $warehouse) {
                $itemWarehouse = $item->item_warehouse->where('warehouse_id', $warehouse->id)->first();
                $stock = $itemWarehouse ? $itemWarehouse->stock : 0;
                $row['warehouse_' . $warehouse->id] = $stock;
            }

            $this->matrixData->push($row);
        }
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->matrixData;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headings = ['Producto'];

        // Agregar nombre de cada almacén como cabecera
        foreach ($this->warehouses as $warehouse) {
            $headings[] = $warehouse->description;
        }

        return $headings;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Inventario por Almacenes';
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

        // Agregar stock de cada almacén
        foreach ($this->warehouses as $warehouse) {
            $stock = $row['warehouse_' . $warehouse->id] ?? 0;
            $mappedRow[] = (int)$stock;
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

        // Agregar ancho para cada columna de almacén
        $columnIndex = 'B';
        foreach ($this->warehouses as $warehouse) {
            $widths[$columnIndex] = 15;
            $columnIndex++;
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

        // Alineación para las columnas de stock (todas excepto la primera)
        $stockRange = 'B2:' . $highestColumn . $highestRow;
        $sheet->getStyle($stockRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Ajustar altura de filas
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(25);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $colCount = 1 + $this->warehouses->count(); // Producto + almacenes
                $lastCol = chr(64 + $colCount);
                $row = 1;

                // Título principal
                $sheet->insertNewRowBefore($row, 5);
                $sheet->setCellValue('A'.$row, 'INVENTARIO POR ALMACENES');
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

                // Fecha y hora
                $sheet->setCellValue('A'.$row, 'Fecha: ' . date('d/m/Y'));
                $sheet->setCellValue('B'.$row, 'Hora: ' . date('H:i:s'));
                $sheet->getStyle('A'.$row.':B'.$row)->applyFromArray([
                    'font' => ['size' => 10],
                ]);
                $row++;

                // Espacio en blanco
                $row++;
            }
        ];
    }
}