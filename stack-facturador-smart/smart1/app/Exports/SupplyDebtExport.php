<?php

namespace App\Exports;

use App\Models\Tenant\SupplyDebt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class SupplyDebtExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    /** @var int|null */
    protected $sectorId;
    /** @var int|null */
    protected $viaId;
    /** @var string|null */
    protected $column;
    /** @var string|int|null */
    protected $value;

    public function __construct($sectorId = null, $viaId = null, $column = null, $value = null)
    {
        $this->sectorId = $sectorId;
        $this->viaId = $viaId;
        $this->column = $column;
        $this->value = $value;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        $query = SupplyDebt::query()
            ->select([
                'supply_debt.supply_id',
                'supplies.number as supply_number',
                'supplies.old_code as old_code',
                'supplies.optional_address',
                'persons.name as person_name',
                'persons.number as person_number',
                // Dirección base
                'supply_via.name as via_name',
                // Tarifa desde plan activo
                DB::raw('MAX(sp.total) as tarifa_total'),
                // Agregados
                DB::raw('SUM(CASE WHEN supply_debt.serie_receipt IS NOT NULL AND supply_debt.correlative_receipt IS NOT NULL THEN 1 ELSE 0 END) as months_consumption'),
                DB::raw('SUM(CASE WHEN supply_debt.serie_receipt IS NOT NULL AND supply_debt.correlative_receipt IS NOT NULL THEN supply_debt.amount ELSE 0 END) as consumption_amount'),
                DB::raw('SUM(CASE WHEN supply_debt.supply_concept_id = 11 THEN supply_debt.amount ELSE 0 END) as aporte_total'),
                DB::raw('SUM(CASE WHEN supply_debt.supply_concept_id IN (1,45,37,54) THEN supply_debt.amount ELSE 0 END) as inscripcion_agua_total'),
                DB::raw('SUM(CASE WHEN supply_debt.supply_concept_id IN (38,50,51,2) THEN supply_debt.amount ELSE 0 END) as inscripcion_desague_total'),
                DB::raw('SUM(CASE WHEN supply_debt.supply_concept_id IN (34,27,12,29,32,26,56,25) THEN supply_debt.amount ELSE 0 END) as multas_total'),
                DB::raw('SUM(CASE WHEN supply_debt.supply_concept_id = 4 THEN supply_debt.amount ELSE 0 END) as reconexion_total'),
                DB::raw('SUM(CASE WHEN supply_debt.supply_concept_id = 3 THEN supply_debt.amount ELSE 0 END) as cambio_nombre_total'),
                DB::raw('SUM(CASE WHEN supply_debt.supply_concept_id IN (5,6,7,8,9,10) THEN supply_debt.amount ELSE 0 END) as otros_conceptos_total'),
                DB::raw('SUM(supply_debt.amount) as total_deuda'),
            ])
            ->join('supplies', 'supplies.id', '=', 'supply_debt.supply_id')
            ->leftJoin('persons', 'persons.id', '=', 'supply_debt.person_id')
            ->leftJoin('supply_via', 'supply_via.id', '=', 'supplies.supply_via_id')
            ->leftJoin('supplies_plans_registered as spr', function ($join) {
                $join->on('spr.supply_id', '=', 'supplies.id')
                    ->where('spr.active', '=', 1);
            })
            ->leftJoin('supplie_plans as sp', 'sp.id', '=', 'spr.supplie_plan_id')
            ->where('supply_debt.active', '=', 0)
            ->orderBy('supply_debt.supply_id');

        if ($this->sectorId) {
            $query->where('supplies.sector_id', $this->sectorId);
        }
        if ($this->viaId) {
            $query->where('supplies.supply_via_id', $this->viaId);
        }

        if ($this->column && $this->value) {
            switch ($this->column) {
                case 'person.id':
                    $query->where('supply_debt.person_id', $this->value);
                    break;
                case 'supply.id':
                    $query->where('supply_debt.supply_id', $this->value);
                    break;
                case 'correlative_receipt':
                    $query->where('supply_debt.correlative_receipt', 'like', "%{$this->value}%");
                    break;
            }
        }

        return $query->groupBy([
            'supply_debt.supply_id',
            'supplies.number',
            'supplies.old_code',
            'supplies.optional_address',
            'persons.name',
            'persons.number',
            'supply_via.name',
        ]);
    }

    public function headings(): array
    {
        return [
            'CÓDIGO USUARIO',
            'NOMBRES Y APELLIDOS',
            'DIRECCIÓN',
            'TARIFA',
            'MESES DE DEUDA DE CONSUMO',
            'MONTO',
            'APORTE',
            'INSCRIPCIÓN AGUA',
            'INSCRIPCIÓN DESAGÜE',
            'MULTAS',
            'RECONEXIÓN',
            'CAMBIO DE NOMBRE',
            'OTROS CONCEPTOS',
            'TOTAL DE DEUDA',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        $address = $row->optional_address ?: trim(($row->via_name ?? '') . ' ' . ($row->supply_number ?? ''));

        return [
            $row->old_code ?? '',
            $row->person_name ?? '',
            $address,
            (float) ($row->tarifa_total ?? 0),
            (int) ($row->months_consumption ?? 0),
            (float) ($row->consumption_amount ?? 0),
            (float) ($row->aporte_total ?? 0),
            (float) ($row->inscripcion_agua_total ?? 0),
            (float) ($row->inscripcion_desague_total ?? 0),
            (float) ($row->multas_total ?? 0),
            (float) ($row->reconexion_total ?? 0),
            (float) ($row->cambio_nombre_total ?? 0),
            (float) ($row->otros_conceptos_total ?? 0),
            (float) ($row->total_deuda ?? 0),
        ];
    }
    // Export agrupado: sin descripción por fila

    public function chunkSize(): int
    {
        return 1000;
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER_00,
            'F' => NumberFormat::FORMAT_NUMBER_00,
            'G' => NumberFormat::FORMAT_NUMBER_00,
            'H' => NumberFormat::FORMAT_NUMBER_00,
            'I' => NumberFormat::FORMAT_NUMBER_00,
            'J' => NumberFormat::FORMAT_NUMBER_00,
            'K' => NumberFormat::FORMAT_NUMBER_00,
            'L' => NumberFormat::FORMAT_NUMBER_00,
            'M' => NumberFormat::FORMAT_NUMBER_00,
            'N' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $ws = $sheet->getDelegate();
                $highestRow = $ws->getHighestRow();

                // Encabezados en negrita
                $ws->getStyle('A1:N1')->getFont()->setBold(true);

                // Calcular totales en servidor (evita 0 por falta de recálculo en cliente)
                $sumCols = ['D','E','F','G','H','I','J','K','L','M','N'];
                $totals = array_fill_keys($sumCols, 0.0);
                for ($r = 2; $r <= $highestRow; $r++) {
                    foreach ($sumCols as $col) {
                        $cell = $ws->getCell($col.$r);
                        $value = $cell->getCalculatedValue();
                        if (!is_numeric($value)) {
                            $raw = (string) $cell->getValue();
                            $normalized = str_replace([',', ' '], ['.', ''], $raw);
                            $value = is_numeric($normalized) ? (float) $normalized : 0;
                        }
                        $totals[$col] += (float) $value;
                    }
                }

                // Fila de totales
                $totalRow = $highestRow + 1;
                $ws->setCellValue('A'.$totalRow, 'TOTAL');
                foreach ($sumCols as $col) {
                    $ws->setCellValueExplicit($col.$totalRow, $totals[$col], DataType::TYPE_NUMERIC);
                }
                $ws->getStyle('A'.$totalRow.':N'.$totalRow)->getFont()->setBold(true);

                // Bordes a toda la tabla incluyendo totales
                $ws->getStyle('A1:N'.$totalRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
}


