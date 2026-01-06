<?php

namespace Modules\Report\Exports;

use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\PersonRegModel;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ReportAllSalesConsolidatedExport implements FromCollection, WithEvents, ShouldAutoSize, WithTitle, WithCustomStartCell
{
    use Exportable;
    protected $records;
    protected $company;
    protected $establishment;


    public function records($records)
    {
        $this->records = $records;

        return $this;
    }

    public function company($company)
    {
        $this->company = $company;

        return $this;
    }
    public function title(): string
    {
        $title = "Reporte de ventas consolidado";
        return $title;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Agregar información de la compañía y fecha actual
                $event->sheet->setCellValue('A1', 'Compañía:');
                $event->sheet->setCellValue('B1', $this->company->name ?? 'N/A');
                $event->sheet->setCellValue('A2', 'Número:');
                $event->sheet->setCellValue('B2', $this->company->number ?? 'N/A');
                $event->sheet->setCellValue('A3', 'Fecha de exportación:');
                $event->sheet->setCellValue('B3', Carbon::now()->format('d/m/Y'));

                // Aplicar estilos a los encabezados de la compañía
                $event->sheet->getStyle('A1:B3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                ]);

                // Estilo para la primera fila de la tabla (encabezados)
                $event->sheet->getStyle('A5:Z5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFCCCCCC'],
                    ],
                ]);

                // Estilo para las filas de datos
                $event->sheet->getStyle('A6:Z' . $event->sheet->getHighestRow())->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                ]);
            },
        ];
    }

    public function establishment($establishment)
    {
        $this->establishment = $establishment;

        return $this;
    }
    private function statusDue($days_of_delay)
    {
        if ($days_of_delay >= 0 && $days_of_delay <= 15) {
            return "Por vencer";
        } else if ($days_of_delay >= 16 && $days_of_delay <= 45) {
            return "Atrasado";
        } else if ($days_of_delay > 45) {
            return "Muy atrasado";
        }
    }
    public function collection()
    {

        $apply_conversion_to_pen = true;
        $data = collect();
        $payment_methods = PaymentMethodType::all();
        $person_reg = PersonRegModel::all();

        // Espacio vacío antes de los encabezados
        $data->push(['']);
        $data->push(['']);
        $data->push(['']);
        $data->push(['']);

        // Agregar encabezados
        $data->push([
            '#', 'Fecha de emisión', 'Documento', 'RUC', 'Razón Social', 'Descripción',
            'Total (incl. IGV)', 'Detracción', 'Descuento', 'Neto a pagar', 'Pagado',
            'Pendiente', 'Días de atraso', 'Estado deuda', 'Fecha de pago', 'Método de pago',
            'Año', 'Nombre ejecutivo', 'Categoría'
        ]);

        // Agregar datos
        foreach ($this->records as $index => $row) {
            $detraction = 0;
            if ($row->detraction) {
                $detraction = json_decode($row->detraction);
                $detraction = $detraction->amount;
            }
            $days_of_delay = 0;
            $now = Carbon::now();
            $date_of_issue = Carbon::parse($row->date_of_issue);
            if ($row->total_subtraction > 0) {
                $days_of_delay = $now->diffInDays($date_of_issue);
            }

            $currency_type_id = $row->currency_type_id;
            $exchange_rate_sale = $row->exchange_rate_sale;
            $total = $row->total;
            $total_without_detraction = $row->total - $detraction;
            $total_payment = $row->total_payment;
            $pending = $row->total - $row->total_payment;
            $total_discount = $row->total_discount;

            if ($currency_type_id !== 'PEN' && $apply_conversion_to_pen) {
                $total = $total * $exchange_rate_sale;
                $total_without_detraction = $total_without_detraction * $exchange_rate_sale;
                $total_payment = $total_payment * $exchange_rate_sale;
                $pending = $pending * $exchange_rate_sale;
                $total_discount = $total_discount * $exchange_rate_sale;
                $detraction = $detraction * $exchange_rate_sale;
            }

            $add = [
                $index + 1,
                $row->date_of_issue,
                $row->number_full,
                $row->customer_number,
                $row->customer_name,
                $row->observation,
                number_format($total, 2, '.', ''),
                number_format($detraction, 2, '.', ''),
                number_format($total_discount, 2, '.', ''),
                number_format($total_without_detraction, 2, '.', ''),
                number_format($total_payment, 2, '.', ''),
                number_format($pending, 2, '.', ''),
                $days_of_delay,
                $row->total_subtraction > 0 ? $this->statusDue($days_of_delay) : null,
                $row->last_payment_date,
                $row->last_payment_method_type_id ? $payment_methods->find($row->last_payment_method_type_id)->description : null,
                Carbon::parse($row->date_of_issue)->year,
                $row->seller_name ?? $row->username,
                $row->customer_reg_id ? $person_reg->find($row->customer_reg_id)->description : null,
            ];

            $data->push($add);
        }

        return $data;
    }
}
