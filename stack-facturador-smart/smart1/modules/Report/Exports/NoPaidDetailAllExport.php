<?php

namespace Modules\Report\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

class NoPaidDetailAllExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithEvents
{
    use Exportable;

    protected $records;
    protected $company;

    public function records($records) {
        $this->records = $records;
        return $this;
    }

    public function company($company) {
        $this->company = $company;
        return $this;
    }

    public function collection(): Collection
    {
        // Filtrar solo registros con saldo pendiente
        return $this->records->filter(function ($record) {
            return $record['total_to_pay'] > 0;
        });
    }

    public function headings(): array
    {
        return [
            'RUC',
            'Cliente',
            'Teléfono',
            'Zona',
            'Vendedores',
            'Línea',
            'Doc. Relac.',
            'Serie-Número',
            'N° único',
            'Emisión',
            'Vencimiento',
            'Moneda',
            'Total',
            'Cobrado',
            'Saldo',
            'Estado',
            'Días atraso'
        ];
    }

    public function map($row): array
    {
        return [
            $row['customer_ruc'] ?? '-',
            $row['customer_name'] ?? '-',
            $row['customer_telephone'] ?? '-',
            $row['customer_zone'] ?? '-',
            $row['seller_name'] ?? '-',
            $row['line_credit'] ?? '0.00',
            $row['document_related'] ?? '-',
            $row['description'] ?? '-',
            $row['code'] ?? '-',
            $row['date_of_issue'] ?? '-',
            $row['date_of_due'] ?? '-',
            $row['currency_type_id'] ?? 'PEN',
            $row['total'] ?? '0.00',
            $row['total_payment'] ?? '0.00',
            $row['total_to_pay'] ?? '0.00',
            ($row['delay_payment'] > 0 ? 'Vencido' : 'Pendiente'),
            $row['delay_payment'] ?? 0
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                // Insertar filas de encabezado
                $sheet->insertNewRowBefore(1, 2);

                // Fila 1: Título centrado
                $sheet->setCellValue('A1', 'Estado de cuenta al ' . date('Y-m-d'));
                $sheet->mergeCells('A1:Q1');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                // Fila 2: Información de la empresa
                if ($this->company) {
                    $sheet->setCellValue('A2', $this->company->name);
                    $sheet->mergeCells('A2:K2');
                    $sheet->getStyle('A2')->getFont()->setBold(true);
                }

                // Fila 3: Encabezados de la tabla (se mueven automáticamente)
                $sheet->getStyle('A3:Q3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A3:Q3')->getFont()->setBold(true);

                // Calcular totales
                $filteredRecords = $this->records->filter(function ($record) {
                    return $record['total_to_pay'] > 0;
                });

                $totalLineCredit = 0;
                $totalAmount = 0;
                $totalPayment = 0;
                $totalToPay = 0;

                foreach ($filteredRecords as $record) {
                    $totalLineCredit += floatval(str_replace(',', '', $record['line_credit'] ?? '0'));
                    $totalAmount += floatval(str_replace(',', '', $record['total'] ?? '0'));
                    $totalPayment += floatval(str_replace(',', '', $record['total_payment'] ?? '0'));
                    $totalToPay += floatval(str_replace(',', '', $record['total_to_pay'] ?? '0'));
                }

                // Encontrar la última fila de datos
                $highestRow = $sheet->getHighestRow();
                $totalRow = $highestRow + 1;

                // Agregar fila de totales
                $sheet->setCellValue('A' . $totalRow, 'TOTALES');
                $sheet->mergeCells('A' . $totalRow . ':E' . $totalRow);
                $sheet->setCellValue('F' . $totalRow, number_format($totalLineCredit, 2));
                $sheet->setCellValue('M' . $totalRow, number_format($totalAmount, 2));
                $sheet->setCellValue('N' . $totalRow, number_format($totalPayment, 2));
                $sheet->setCellValue('O' . $totalRow, number_format($totalToPay, 2));

                // Aplicar estilo a la fila de totales
                $sheet->getStyle('A' . $totalRow . ':Q' . $totalRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $totalRow . ':Q' . $totalRow)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF5F5F5');
                $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('M' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('N' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('O' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Aplicar bordes a toda la tabla incluyendo la fila de totales
                $tableRange = 'A3:Q' . $totalRow;
                $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Aplicar color rojo a filas vencidas
                $dataStartRow = 4; // Primera fila de datos después de los encabezados

                $rowIndex = $dataStartRow;
                foreach ($filteredRecords as $record) {
                    if ($record['delay_payment'] > 0) {
                        // Aplicar color rojo a toda la fila
                        $sheet->getStyle('A' . $rowIndex . ':Q' . $rowIndex)->getFont()->setColor(
                            new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED)
                        );
                    }
                    $rowIndex++;
                }

                // Ajustar ancho de columnas
                $sheet->getColumnDimension('A')->setWidth(12); // RUC
                $sheet->getColumnDimension('B')->setWidth(25); // Cliente
                $sheet->getColumnDimension('C')->setWidth(12); // Teléfono
                $sheet->getColumnDimension('D')->setWidth(15); // Zona
                $sheet->getColumnDimension('E')->setWidth(20); // Vendedores
                $sheet->getColumnDimension('F')->setWidth(12); // Línea
                $sheet->getColumnDimension('G')->setWidth(15); // Doc. Relac.
                $sheet->getColumnDimension('H')->setWidth(20); // Serie-Número
                $sheet->getColumnDimension('I')->setWidth(12); // N° único
                $sheet->getColumnDimension('J')->setWidth(12); // Emisión
                $sheet->getColumnDimension('K')->setWidth(12); // Vencimiento
                $sheet->getColumnDimension('L')->setWidth(8);  // Moneda
                $sheet->getColumnDimension('M')->setWidth(12); // Total
                $sheet->getColumnDimension('N')->setWidth(12); // Cobrado
                $sheet->getColumnDimension('O')->setWidth(12); // Saldo
                $sheet->getColumnDimension('P')->setWidth(10); // Estado
                $sheet->getColumnDimension('Q')->setWidth(12); // Días atraso
            },
        ];
    }
}
