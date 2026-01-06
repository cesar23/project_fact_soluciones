<?php

namespace App\Imports;

use App\Http\Controllers\SearchItemController;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\DetractionType;
use App\Models\Tenant\Item;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;



class DocumentMassiveEmitImport implements ToCollection
{
    use Importable;

    protected $data;

    protected function formatDate($date)
    {
        if (!$date) return null;
        
        try {
            // Si es una fecha de Excel (número)
            if (is_numeric($date)) {
                return Date::excelToDateTimeObject($date)->format('Y-m-d');
            }
            
            // Intentar diferentes formatos de fecha
            $formats = [
                'd/m/Y',
                'd-m-Y',
                'Y-m-d',
                'd/m/y',
                'd-m-y'
            ];
            
            foreach ($formats as $format) {
                $dateObj = \Carbon\Carbon::createFromFormat($format, $date);
                if ($dateObj !== false) {
                    return $dateObj->format('Y-m-d');
                }
            }
            
            // Si no coincide con ningún formato, intentar parsear automáticamente
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function collection(Collection $rows)
    {
        $total = count($rows);
        $warehouse_id_de = request('warehouse_id');
        $registered = 0;
        $info = [];
        $errors = [];
        $original_rows = [];
        $document_customers = [];
        $document_dates = [];
        $document_detractions = [];
        unset($rows[0]);
        
        // Guardar filas originales (excluyendo cabecera)
        foreach ($rows as $idx => $row) {
            if ($idx !== 0) { // Excluir cabecera
                $original_rows[] = array_slice($row->toArray(), 0, 9);
            }
        }
        
        foreach ($rows as $idx => $row) {
            $row_name = "Fila " . ($idx + 1);
            $invoice_number = ($row[0]) ?: null;
            $customer_number = ($row[1]) ?: null;
            $internal_id = ($row[2]) ?: null;
            $pdf_description = ($row[3]) ?: null;
            $quantity = ($row[4]) ?: null;
            $currency = ($row[5]) ?: null;
            $unit_price = ($row[6]) ?: null;
            $detraction_id = ($row[7]) ?: null;
            $date_of_due = $this->formatDate($row[8]) ?: null;
            $document_errors = [];

            // Guardar customer_number, date_of_due y detraction_id por documento
            if ($customer_number) {
                $document_customers[$invoice_number] = $customer_number;
            }
            if ($date_of_due) {
                $document_dates[$invoice_number] = $date_of_due;
            }
            if ($detraction_id) {
                $document_detractions[$invoice_number] = $detraction_id;
            }

            // Obtener valores del documento si no están en la fila actual
            $customer_number = $customer_number ?: ($document_customers[$invoice_number] ?? null);
            $date_of_due = $date_of_due ?: ($document_dates[$invoice_number] ?? null);
            $detraction_id = $detraction_id ?: ($document_detractions[$invoice_number] ?? null);

            if ($internal_id == null) {
                $document_errors[] = 'El código interno es requerido';
            }
            if ($quantity == null) {
                $document_errors[] = 'La cantidad es requerida';
            }
            if ($unit_price == null) {
                $document_errors[] = 'El precio unitario es requerido';
            }
            
            // Validar customer_number solo si no existe para este documento
            if (!isset($document_customers[$invoice_number])) {
                $document_errors[] = 'El número de cliente es requerido';
                if (count($document_errors) > 0) {
                    $errors["documento_{$invoice_number}"] = [
                        'fila' => $row_name,
                        'errores' => $document_errors
                    ];
                }
                continue;
            }

            $customer = Person::where('number', $customer_number)
                ->where('type', 'customers')
                ->first();
            if ($customer == null) {
                $document_errors[] = 'El número de cliente no existe';
            }
            if ($currency) {
                $currency = strtoupper($currency);
                $currency_type = CurrencyType::where('id', $currency)->first();
                if ($currency_type == null) {
                    $document_errors[] = 'La moneda no existe';
                }
            }
            if ($detraction_id) {
                $detraction = DetractionType::where('id', $detraction_id)->first();
                if ($detraction == null) {
                    $document_errors[] = 'La detracción no existe';
                }
            }

            $item = Item::where('internal_id', $internal_id)->first();
            if ($item == null) {
                $document_errors[] = 'El código interno no existe';
            }

            // Guardar errores si existen
            if (count($document_errors) > 0) {
                $errors["documento_{$invoice_number}"] = [
                    'fila' => $row_name,
                    'errores' => $document_errors
                ];
                continue;
            }

            // Si no hay errores, procesar el documento
            if ($item && $customer) {
                $item_id = $item->id;
                $customer_id = $customer->id;
                $item_found = (new SearchItemController)->getItemsToDocuments(null, $item_id);
                
                if ($item_found && isset($item_found[0])) {
                    $found = $item_found[0];
                    $found["description_pdf"] = $pdf_description;
                    $found["sale_unit_price"] = $unit_price;
                    $found["has_igv"] = true;
                    $found["quantity"] = $quantity;
                    $info["document_" . $invoice_number]["items"][] = $found;
                    $info["document_" . $invoice_number]["customer_id"] = $customer_id;
                    $info["document_" . $invoice_number]["date_of_due"] = $date_of_due;
                    $info["document_" . $invoice_number]["detraction_id"] = $detraction_id;
                    $info["document_" . $invoice_number]["currency_type_id"] = $currency ? $currency : 'PEN';
                    $registered += 1;
                }
            }
        }

        $establishment_id = auth()->user()->establishment_id;
        $serie = Series::where('document_type_id', '01')
            ->where('establishment_id', $establishment_id)
            ->first();
        $serie_id = $serie->id;
        
        $this->data = compact('total', 'registered', 'info', 'errors', 'serie_id', 'original_rows');
    }

    public function getData()
    {
        return $this->data;
    }
}
