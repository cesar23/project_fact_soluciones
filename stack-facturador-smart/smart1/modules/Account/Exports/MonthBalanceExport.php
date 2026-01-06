<?php

namespace Modules\Account\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Class MonthDiaryExport
 *
 * @package Modules\Account\Exports
 */
class MonthBalanceExport implements ShouldAutoSize, FromCollection, WithHeadings, WithMapping, WithEvents
{
    use Exportable;

    /** @var Collection */
    protected $accounts;
    protected $records;
    protected $company;
    protected $total_debit_sum;
    protected $total_credit_sum;
    protected $total_sum_balance;
    protected $total_debit_remain;
    protected $total_credit_remain;
    protected $total_remain_balance;
    protected $total_debit_balance;
    protected $total_credit_balance;
    protected $total_balance;
    protected $total_debit_nature_sum;
    protected $total_credit_nature_sum;
    protected $total_nature_balance;
    protected $total_debit_function_sum;
    protected $total_credit_function_sum;
    protected $total_function_balance;
    protected $length;
    protected $list_nature;
    protected $list_function;
    const LIST_NATURE = ["601101", "611101", "621101", "627101", "701211", "63", "68", "69"];
    const LIST_FUNCTION = ["701211", "941101", "951101", "97", "69"];
    /**
     * Constructor
     */
    public function __construct($records = null, $company = null, $accounts = null, $length = null)
    {
        $this->records = $records;
        $this->accounts = $accounts;
        $this->company = $company;

        // Inicializar todas las variables de totales en 0
        $this->total_debit_sum = 0;
        $this->total_credit_sum = 0;
        $this->total_sum_balance = 0;
        $this->total_debit_remain = 0;
        $this->total_credit_remain = 0;
        $this->total_remain_balance = 0;
        $this->total_debit_balance = 0;
        $this->total_credit_balance = 0;
        $this->total_balance = 0;
        $this->total_debit_nature_sum = 0;
        $this->total_credit_nature_sum = 0;
        $this->total_nature_balance = 0;
        $this->total_debit_function_sum = 0;
        $this->total_credit_function_sum = 0;
        $this->total_function_balance = 0;
        $this->length = $length == 5 ? 6 : $length;
        // Transformar las listas según la longitud especificada
        $nature_codes = self::LIST_NATURE;
        $function_codes = self::LIST_FUNCTION;
        $this->list_nature = array_map(function ($code) {
            if ($this->length == 5 && strlen($code) == 6) {
                return $code;
            }
            return substr($code, 0, $this->length);
        }, $nature_codes);

        $this->list_function = array_map(function ($code) {
            if ($this->length == 5 && strlen($code) == 6) {
                return $code;
            }
            return substr($code, 0, $this->length);
        }, $function_codes);
    }
    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        if (!$this->accounts || !$this->records) {
            return collect();
        }
    
        // Filtrar solo las cuentas que tienen registros/valores
        return $this->accounts->filter(function ($account) {
            $exists = $this->records->filter(function ($record) use ($account) {
                
                return str_starts_with($record->code, $account->code);
            });
        

            return count($exists) > 0;
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'CUENTA',
            'DETALLE',
            'SUMAS',
            'SUMAS',
            'SALDOS',
            'SALDOS',
            'BALANCE',
            'BALANCE',
            'ER NATURALEZA',
            'ER NATURALEZA',
            'ER FUNCION',
            'ER FUNCION'
        ];
    }

    /**
     * @param mixed $record
     * @return array
     */
    public function map($account): array
    {
    
        
        // Usar filter en lugar de where para colecciones
        $exists = $this->records->filter(function ($record) use ($account) {
            return str_starts_with($record->code, $account->code);
        });
        

        if (count($exists) > 0) {
            $balance = $exists->sum('balance');
            $debit = $exists->sum('total_debit');
            $credit = $exists->sum('total_credit');
            $balance = abs($balance);
            $format_debit = $debit > 0 ? number_format($debit, 2, '.', '') : '';
            $format_credit = $credit > 0 ? number_format($credit, 2, '.', '') : '';

            // Función helper para formatear valores, convirtiendo 0.00 a cadena vacía
            $formatValue = function ($condition, $value) {
                if ($condition && $value > 0) {
                    return number_format($value, 2, '.', '');
                }
                return '';
            };

            return [
                $account->code,
                $account->name,
                $format_debit, // SUMAS columna 1
                $format_credit, // SUMAS columna 2
                $formatValue($debit > $credit, $balance), // SALDOS columna 1
                $formatValue($debit < $credit, $balance), // SALDOS columna 2
                $formatValue(($debit > $credit) && $this->in_array_code($account->code, 'balance'), $balance), // BALANCE columna 1
                $formatValue(($debit < $credit) && $this->in_array_code($account->code, 'balance'), $balance), // BALANCE columna 2
                $formatValue($debit > $credit && $this->in_array_code($account->code, 'nature'), $balance), // ER NATURALEZA columna 1
                $formatValue($debit < $credit && $this->in_array_code($account->code, 'nature'), $balance), // ER NATURALEZA columna 2
                $formatValue($debit > $credit && $this->in_array_code($account->code, 'function'), $balance), // ER FUNCION columna 1
                $formatValue($debit < $credit && $this->in_array_code($account->code, 'function'), $balance), // ER FUNCION columna 2
            ];
        }
    

        // No debería llegar aquí ya que collection() filtra las cuentas
        return [];
    }

    public function in_array_code($code, $type)
    {
        $pass = false;
        if ($type == 'balance') {
            $first_char = substr($code, 0, 1);
            $to_number = intval($first_char);
            if ($to_number <= 5) {
                $pass = true;
            }
        } else if ($type == 'nature') {
            // Verificar si es un código exacto O si comienza con un prefijo de 2 dígitos
            $pass = $this->checkNatureCode($code);
        } else if ($type == 'function') {
            // Verificar si es un código exacto O si comienza con un prefijo de 2 dígitos
            $pass = $this->checkFunctionCode($code);
        }

        return $pass;
    }

    /**
     * Verifica si un código coincide con la lista de naturaleza
     * Permite códigos exactos y códigos de prefijo (2 dígitos)
     */
    private function checkNatureCode($code)
    {
        foreach ($this->list_nature as $natureCode) {
            // Si es un código de 2 dígitos, verificar prefijo
            if (strlen($natureCode) == 2) {
                if (str_starts_with($code, $natureCode)) {
                    return true;
                }
            } 
            // Si es un código específico, verificar coincidencia exacta
            else {
                if ($code === $natureCode) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Verifica si un código coincide con la lista de función
     * Permite códigos exactos y códigos de prefijo (2 dígitos)
     */
    private function checkFunctionCode($code)
    {
        foreach ($this->list_function as $functionCode) {
            // Si es un código de 2 dígitos, verificar prefijo
            if (strlen($functionCode) == 2) {
                if (str_starts_with($code, $functionCode)) {
                    return true;
                }
            } 
            // Si es un código específico, verificar coincidencia exacta
            else {
                if ($code === $functionCode) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $worksheet = $event->sheet->getDelegate();

                // Calcular totales después de que se hayan procesado todos los registros
                $this->calculateTotals();

                // Insertar filas al inicio para el título
                $worksheet->insertNewRowBefore(1, 3);

                // Agregar el título principal
                $companyName = $this->company ? $this->company->name : 'EMPRESA XXX';
                $worksheet->setCellValue('A1', 'BALANCE DE COMPROBACION - ' . $companyName);

                // Combinar celdas para el título
                $worksheet->mergeCells('A1:H1');

                // Dar formato al título
                $worksheet->getStyle('A1')->getFont()->setBold(true);
                $worksheet->getStyle('A1')->getFont()->setSize(14);
                $worksheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Combinar headers que ocupan dos celdas (fila 4 después de insertar 3 filas)
                $worksheet->mergeCells('C4:D4'); // SUMAS
                $worksheet->mergeCells('E4:F4'); // SALDOS
                $worksheet->mergeCells('G4:H4'); // BALANCE
                $worksheet->mergeCells('I4:J4'); // ER NATURALEZA
                $worksheet->mergeCells('K4:L4'); // ER FUNCION

                // Centrar el texto en las celdas combinadas
                $worksheet->getStyle('C4:D4')->getAlignment()->setHorizontal('center');
                $worksheet->getStyle('E4:F4')->getAlignment()->setHorizontal('center');
                $worksheet->getStyle('G4:H4')->getAlignment()->setHorizontal('center');
                $worksheet->getStyle('I4:J4')->getAlignment()->setHorizontal('center');
                $worksheet->getStyle('K4:L4')->getAlignment()->setHorizontal('center');

                // Aplicar negrita a todos los headers
                $worksheet->getStyle('A4:L4')->getFont()->setBold(true);

                // Agregar bordes a toda la tabla de headers
                $worksheet->getStyle('A4:L4')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Obtener la última fila con datos
                $lastRow = $worksheet->getHighestRow();

                // Agregar bordes a todas las filas de datos
                if ($lastRow > 4) {
                    $worksheet->getStyle('A5:L' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }

                // Insertar fila de totales al final
                $worksheet->insertNewRowBefore($lastRow + 1, 1);
                $worksheet->setCellValue('A' . ($lastRow + 1), 'TOTAL');
                $worksheet->setCellValue('C' . ($lastRow + 1), $this->total_debit_sum);
                $worksheet->setCellValue('D' . ($lastRow + 1), $this->total_credit_sum);
                $worksheet->setCellValue('E' . ($lastRow + 1), $this->total_debit_remain);
                $worksheet->setCellValue('F' . ($lastRow + 1), $this->total_credit_remain);
                $worksheet->setCellValue('G' . ($lastRow + 1), $this->total_debit_balance);
                $worksheet->setCellValue('H' . ($lastRow + 1), $this->total_credit_balance);
                $worksheet->setCellValue('I' . ($lastRow + 1), $this->total_debit_nature_sum);
                $worksheet->setCellValue('J' . ($lastRow + 1), $this->total_credit_nature_sum);
                $worksheet->setCellValue('K' . ($lastRow + 1), $this->total_debit_function_sum);
                $worksheet->setCellValue('L' . ($lastRow + 1), $this->total_credit_function_sum);

                $total_general_array = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];

                foreach ($total_general_array as $letter) {
                    $worksheet->getStyle($letter . ($lastRow + 1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('D3D3D3'));
                }

                $totals_array = ['G', 'H', 'I', 'J', 'K', 'L'];

                foreach ($totals_array as $letter) {
                    $worksheet->getStyle($letter . ($lastRow + 2))->getFont()->setBold(true);
                    $worksheet->getStyle($letter . ($lastRow + 3))->getFont()->setBold(true);
                    $worksheet->getStyle($letter . ($lastRow + 3))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('D3D3D3'));
                    $worksheet->getStyle($letter . ($lastRow + 2))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $worksheet->getStyle($letter . ($lastRow + 3))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }

                $worksheet->setCellValue('G' . ($lastRow + 2), $this->total_debit_balance < $this->total_credit_balance ? $this->total_balance : "0.00");
                $worksheet->setCellValue('H' . ($lastRow + 2), $this->total_debit_balance > $this->total_credit_balance ? $this->total_balance : "0.00");
                $worksheet->setCellValue('I' . ($lastRow + 2), $this->total_debit_nature_sum < $this->total_credit_nature_sum ? $this->total_nature_balance : "0.00");
                $worksheet->setCellValue('J' . ($lastRow + 2), $this->total_debit_nature_sum > $this->total_credit_nature_sum ? $this->total_nature_balance : "0.00");
                $worksheet->setCellValue('K' . ($lastRow + 2), $this->total_debit_function_sum < $this->total_credit_function_sum ? $this->total_function_balance : "0.00");
                $worksheet->setCellValue('L' . ($lastRow + 2), $this->total_debit_function_sum > $this->total_credit_function_sum ? $this->total_function_balance : "0.00");


                $worksheet->setCellValue('G' . ($lastRow + 3), $this->total_debit_balance < $this->total_credit_balance ? $this->total_balance + $this->total_debit_balance : $this->total_debit_balance);
                $worksheet->setCellValue('H' . ($lastRow + 3), $this->total_debit_balance > $this->total_credit_balance ? $this->total_balance + $this->total_credit_balance : $this->total_credit_balance);
                $worksheet->setCellValue('I' . ($lastRow + 3), $this->total_debit_nature_sum < $this->total_credit_nature_sum ? $this->total_nature_balance + $this->total_debit_nature_sum : $this->total_debit_nature_sum);
                $worksheet->setCellValue('J' . ($lastRow + 3), $this->total_debit_nature_sum > $this->total_credit_nature_sum ? $this->total_nature_balance +  $this->total_credit_nature_sum : $this->total_credit_nature_sum);
                $worksheet->setCellValue('K' . ($lastRow + 3), $this->total_debit_function_sum < $this->total_credit_function_sum ? $this->total_function_balance + $this->total_debit_function_sum : $this->total_debit_function_sum);
                $worksheet->setCellValue('L' . ($lastRow + 3), $this->total_debit_function_sum > $this->total_credit_function_sum ? $this->total_function_balance + $this->total_credit_function_sum : $this->total_credit_function_sum);

                // Establecer ancho mínimo para las columnas numéricas
                $worksheet->getColumnDimension('C')->setWidth(75);
                $worksheet->getColumnDimension('D')->setWidth(75);
                $worksheet->getColumnDimension('E')->setWidth(75);
                $worksheet->getColumnDimension('F')->setWidth(75);
                $worksheet->getColumnDimension('G')->setWidth(75);
                $worksheet->getColumnDimension('H')->setWidth(75);
                $worksheet->getColumnDimension('I')->setWidth(75);
                $worksheet->getColumnDimension('J')->setWidth(75);
                $worksheet->getColumnDimension('K')->setWidth(75);
                $worksheet->getColumnDimension('L')->setWidth(75);

                // Formato de moneda para las columnas numéricas (C, D, E, F, G, H, I, J, K, L)
                $worksheet->getStyle('C5:C' . ($lastRow + 3))->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->getStyle('D5:D' . ($lastRow + 3))->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->getStyle('E5:E' . ($lastRow + 3))->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->getStyle('F5:F' . ($lastRow + 3))->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->getStyle('G5:G' . ($lastRow + 3))->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->getStyle('H5:H' . ($lastRow + 3))->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->getStyle('I5:I' . ($lastRow + 3))->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->getStyle('J5:J' . ($lastRow + 3))->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->getStyle('K5:K' . ($lastRow + 3))->getNumberFormat()->setFormatCode('#,##0.00');
                $worksheet->getStyle('L5:L' . ($lastRow + 3))->getNumberFormat()->setFormatCode('#,##0.00');
            }
        ];
    }

    /**
     * Calcular totales
     */
    private function calculateTotals()
    {

        // Usar la colección filtrada en lugar de todas las cuentas
        $filteredAccounts = $this->collection();

        foreach ($filteredAccounts as $idx => $account) {

            $exists = $this->records->filter(function ($record) use ($account) {
                return str_starts_with($record->code, $account->code);
            });
            if ($exists) {
                $balance = $exists->sum('balance');
                $debit = $exists->sum('total_debit');
                $credit = $exists->sum('total_credit');
                $balance = abs($balance);
                $this->total_debit_sum += $debit;
                $this->total_credit_sum += $credit;
                $this->total_debit_remain += $debit > $credit ? $balance : 0;
                $this->total_credit_remain += $debit < $credit ? $balance : 0;
                $this->total_debit_balance += $debit > $credit && $this->in_array_code($account->code, 'balance') ? $balance : 0;
                $this->total_credit_balance += $debit < $credit && $this->in_array_code($account->code, 'balance') ? $balance : 0;
                $this->total_debit_nature_sum += $debit > $credit && $this->in_array_code($account->code, 'nature') ? $balance : 0;
                $this->total_credit_nature_sum += $debit < $credit && $this->in_array_code($account->code, 'nature') ? $balance : 0;
                $this->total_debit_function_sum += $debit > $credit && $this->in_array_code($account->code, 'function') ? $balance : 0;
                $this->total_credit_function_sum += $debit < $credit && $this->in_array_code($account->code, 'function') ? $balance : 0;
            }
        }

        $this->total_balance = abs($this->total_debit_balance - $this->total_credit_balance);
        $this->total_nature_balance = abs($this->total_debit_nature_sum - $this->total_credit_nature_sum);
        $this->total_function_balance = abs($this->total_debit_function_sum - $this->total_credit_function_sum);
    }

    public function title(): string
    {
        return 'Balance mensual';
    }
}
