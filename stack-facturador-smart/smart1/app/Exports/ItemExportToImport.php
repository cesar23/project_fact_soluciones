<?php

namespace App\Exports;

use App\Models\Tenant\Warehouse;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Class ItemExportToImport
 *
 * @package App\Exports
 */
class ItemExportToImport implements ShouldAutoSize, FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    use Exportable;

    protected $records;
    protected $warehouses;
    protected $isTransport = false;

    public function __construct()
    {
        $this->warehouses = Warehouse::all();
    }

    public function records($records)
    {
        $this->records = $records;
        return $this;
    }

    public function isTransport($isTransport)
    {
        $this->isTransport = $isTransport;
        return $this;
    }

    public function collection()
    {
        // Crear fila de ejemplo con stock dinámico para cada almacén
        $exampleRow = (object) [
            'name' => 'BILLETERA D&G',
            'internal_id' => 'A001',
            'model' => 'GA001',
            'sunat_code' => '51121703',
            'unit_type_id' => 'NIU',
            'currency_type_id' => 'PEN',
            'sale_unit_price' => 10,
            'sale_affectation_igv_type_id' => '10',
            'has_igv' => true,
            'purchase_unit_price' => 5,
            'purchase_affectation_igv_type_id' => '10',
            'stock_min' => 1,
            'category' => (object) ['name' => 'Billetera'],
            'brand' => (object) ['name' => 'D&G'],
            'description' => 'COLOR NEGRO',
            'secondary_name' => 'CON ROJO',
            'lot_code' => '',
            'date_of_due' => '',
            'barcode' => '6456456456',
            'hyperlinks' => 'https://ejemplo.com/producto'
        ];

        $records = $this->records ?? collect([]);
        return collect([$exampleRow])->merge($records);
    }

    public function headings(): array
    {
        $baseHeadings = [
            'Nombre',
            'Código Interno',
            'Modelo',
            'Código Sunat',
            'Código Tipo de Unidad',
            'Código Tipo de Moneda',
            'Precio Unitario Venta',
            'Codigo Tipo de Afectación del Igv Venta',
            'Tiene Igv',
            'Precio Unitario Compra',
            'Codigo Tipo de Afectación del Igv Compra',
        ];

        // Agregar columnas de stock para cada almacén
        $warehouseHeadings = [];
        foreach ($this->warehouses as $warehouse) {
            $warehouseHeadings[] = trim('Stock ' . $warehouse->description);
        }

        $remainingHeadings = [
            'Stock Mínimo',
            'Categoria',
            'Marca',
            'Descripcion',
            'Nombre secundario',
            'Código lote',
            'Fec. Vencimiento',
            'Cód barras',
            'Hipervínculos'
        ];

        return array_merge($baseHeadings, $warehouseHeadings, $remainingHeadings);
    }

    public function map($item): array
    {
        $baseData = [
            $item->name ?? '',
            $item->internal_id ?? '',
            $item->model ?? '',
            $item->sunat_code ?? '',
            $item->unit_type_id ?? '',
            $item->currency_type_id ?? '',
            $item->sale_unit_price ?? 0,
            $item->sale_affectation_igv_type_id ?? '',
            $item->has_igv ? 'SI' : 'NO',
            $item->purchase_unit_price ?? 0,
            $item->purchase_affectation_igv_type_id ?? '',
        ];

        // Agregar stock de 10 para cada almacén
        $warehouseData = [];
        foreach ($this->warehouses as $warehouse) {
            $warehouseData[] = 10; // Valor fijo de 10 para cada almacén
        }

        $remainingData = [
            $item->stock_min ?? 0,
            $item->category->name ?? '',
            $item->brand->name ?? '',
            $item->description ?? '',
            $item->secondary_name ?? '',
            $item->lot_code ?? '',
            $item->date_of_due ?? '',
            $item->barcode ?? '',
            $item->hyperlinks ?? ''
        ];

        return array_merge($baseData, $warehouseData, $remainingData);
    }

    public function styles(Worksheet $sheet)
    {
        $heading_style = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 11
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];

        $heading_style_no_fill = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 11
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];

        $first_row_style = [
            'font' => [
                'size' => 10,
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
        ];

        // Calcular el número total de columnas
        $totalColumns = 11 + count($this->warehouses) + 9; // base + warehouses + remaining
        $lastColumn = $this->getColumnLetter($totalColumns); // Usar la función correcta

        // Columnas que no deben tener relleno amarillo: Modelo(C), Código Sunat(D), Categoria, Marca, Descripcion, Nombre secundario, Código lote, Fec. Vencimiento, Cód barras, Hipervínculos
        $noFillColumns = [];
        
        // Modelo (columna C)
        $noFillColumns[] = 'C1';
        // Código Sunat (columna D)  
        $noFillColumns[] = 'D1';
        
        
        // Columnas restantes: Categoria, Marca, Descripcion, Nombre secundario, Código lote, Fec. Vencimiento, Cód barras, Hipervínculos
        $remainingStartIndex = 13 + count($this->warehouses); // Después de Stock Mínimo
        for ($i = 0; $i < 8; $i++) { // 8 columnas restantes
            $column = $this->getColumnLetter($remainingStartIndex + $i);
            $noFillColumns[] = $column . '1';
        }

        $styles = [
            // Estilo especial para la primera fila de datos
            "A2:{$lastColumn}2" => $first_row_style,
            
            // Estilo para el resto de las filas de datos
            "A3:{$lastColumn}" . ($sheet->getHighestRow()) => [
                'font' => [
                    'size' => 10
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ],
            
            // Alineación específica para columnas numéricas
            'G2:G' . ($sheet->getHighestRow()) => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0.00'
                ]
            ],
            'J2:J' . ($sheet->getHighestRow()) => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0.00'
                ]
            ],
        ];

        // Aplicar estilo CON relleno amarillo solo a las columnas que deben tenerlo
        $fillColumns = ['A1', 'B1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1', 'K1']; // Columnas base que SÍ tienen relleno
        
        // Agregar columnas de almacenes que también deben tener relleno
        foreach ($this->warehouses as $index => $warehouse) {
            $column = $this->getColumnLetter(12 + $index);
            $fillColumns[] = $column . '1';
        }
        
        // Agregar Stock Mínimo que también debe tener relleno
        $stockMinColumn = $this->getColumnLetter(12 + count($this->warehouses));
        $fillColumns[] = $stockMinColumn . '1';
        
        foreach ($fillColumns as $column) {
            $styles[$column] = $heading_style;
        }

        // Aplicar estilo SIN relleno a las columnas específicas
        foreach ($noFillColumns as $column) {
            $styles[$column] = $heading_style_no_fill;
        }

        return $styles;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Ajustar altura de la fila del encabezado
                $sheet->getRowDimension(1)->setRowHeight(25);

                // Calcular el número total de columnas
                $totalColumns = 11 + count($this->warehouses) + 9;
                $lastColumn = $this->getColumnLetter($totalColumns);

                // Ajustar ancho de columnas específicas
                $sheet->getColumnDimension('A')->setWidth(30); // Nombre
                $sheet->getColumnDimension('B')->setWidth(15); // Código Interno
                $sheet->getColumnDimension('C')->setWidth(15); // Modelo
                $sheet->getColumnDimension('D')->setWidth(15); // Código Sunat
                $sheet->getColumnDimension('E')->setWidth(20); // Código Tipo de Unidad
                $sheet->getColumnDimension('F')->setWidth(20); // Código Tipo de Moneda
                $sheet->getColumnDimension('G')->setWidth(18); // Precio Unitario Venta
                $sheet->getColumnDimension('H')->setWidth(25); // Código Tipo de Afectación del Igv Venta
                $sheet->getColumnDimension('I')->setWidth(12); // Tiene Igv
                $sheet->getColumnDimension('J')->setWidth(18); // Precio Unitario Compra
                $sheet->getColumnDimension('K')->setWidth(25); // Código Tipo de Afectación del Igv Compra

                // Ajustar ancho para columnas de almacenes
                $warehouseStartColumn = 'L';
                foreach ($this->warehouses as $index => $warehouse) {
                    $column = $this->getColumnLetter(12 + $index); // L, M, N, etc.
                    $sheet->getColumnDimension($column)->setWidth(15); // Stock para cada almacén
                }

                // Ajustar ancho para las columnas restantes
                $remainingStartColumn = $this->getColumnLetter(12 + count($this->warehouses));
                $sheet->getColumnDimension($remainingStartColumn)->setWidth(15); // Stock Mínimo
                $sheet->getColumnDimension($this->getColumnLetter(13 + count($this->warehouses)))->setWidth(20); // Categoria
                $sheet->getColumnDimension($this->getColumnLetter(14 + count($this->warehouses)))->setWidth(15); // Marca
                $sheet->getColumnDimension($this->getColumnLetter(15 + count($this->warehouses)))->setWidth(40); // Descripcion
                $sheet->getColumnDimension($this->getColumnLetter(16 + count($this->warehouses)))->setWidth(25); // Nombre secundario
                $sheet->getColumnDimension($this->getColumnLetter(17 + count($this->warehouses)))->setWidth(15); // Código lote
                $sheet->getColumnDimension($this->getColumnLetter(18 + count($this->warehouses)))->setWidth(15); // Fec. Vencimiento
                $sheet->getColumnDimension($this->getColumnLetter(19 + count($this->warehouses)))->setWidth(20); // Cód barras
                $sheet->getColumnDimension($this->getColumnLetter(20 + count($this->warehouses)))->setWidth(30); // Hipervínculos

                // Agregar comentarios informativos
                $this->addComment($sheet, 'A1', 'Es esencial completar este campo, ya que se definirá el nombre del producto.');
                $this->addComment($sheet, 'B1', 'El llenado de esta información es opcional, pero permitirá que los productos con código aparezcan en el aplicativo.');
                $this->addComment($sheet, 'C1', 'No es necesario.');
                $this->addComment($sheet, 'D1', 'No es necesario');
                $this->addComment($sheet, 'E1', 'Es importante completar esta casilla, ya que los productos requieren un Código de Tipo de Unidad.
Para verificar los códigos válidos, visite: https://facturaperu.com.pe/unidades-de-medida-sunat/. 
Recuerde que los códigos ingresados también deben estar agregados en su plataforma.');
                $this->addComment($sheet, 'F1', 'Es importante completar esta información correctamente. Tenga en cuenta que:
PEN = Soles
USD = Dólares');
                $this->addComment($sheet, 'G1', 'Es crucial completar esta casilla, teniendo en cuenta que el precio unitario de venta no puede ser igual a 0.');
                $this->addComment($sheet, 'H1', "Es importante completar esta casilla, ya que indica si el producto está sujeto al IGV. Por ejemplo, el código '10' corresponde a 'Gravado – Operación Onerosa'. 
Para más información, puede consultar en: https://facturaperu.com.pe/codigos-de-tipo-de-afectacion-del-igv/.");
                $this->addComment($sheet, 'I1', "Se recomienda mantener la opción 'Sí' activada, ya que esto indica que el producto ya incluye el IGV. Si se selecciona 'No', el sistema realizará una multiplicación para añadir el IGV a los productos.");
                $this->addComment($sheet, 'J1', 'IMPORTANTE:
esta casilla si desean la puedes manejar en CERO. 
Del caso que no hubiera el precio unitario de compra.');
                $this->addComment($sheet, 'K1', "Es esencial completar esta casilla, ya que indica si el producto está sujeto al IGV de compra. Por ejemplo, el código '10' corresponde a 'Gravado – Operación Onerosa'. 
Para más información, puede consultar en: https://facturaperu.com.pe/codigos-de-tipo-de-afectacion-del-igv/.");

                // Agregar comentarios para las columnas de almacenes
                foreach ($this->warehouses as $index => $warehouse) {
                    $column = $this->getColumnLetter(12 + $index); // L, M, N, etc.
                    $this->addComment($sheet, $column . '1', "Stock disponible en el almacén: {$warehouse->description}, si hay más de un almacén y se deja vacío el producto no se creará.
ATENCIÓN:
El stock del producto debe ser superior a cero y no puede ser menor que el stock mínimo requerido.");
                }
                $column = $this->getColumnLetter(12 + count($this->warehouses));
                $this->addComment($sheet, $column . '1', "IMPORTANTE:
Es crucial completar esta casilla, teniendo en cuenta que el stock no puede ser igual a CERO.");
            }
        ];
    }

    private function addComment($sheet, $cell, $text)
    {
        try {
            $comment = $sheet->getComment($cell);
            $comment->getText()->createTextRun($text);
            $comment->setVisible(false);
        } catch (\Exception $e) {
            // Si hay error al crear el comentario, simplemente lo ignoramos
            // para evitar que falle la exportación
        }
    }

    private function getColumnLetter($columnNumber)
    {
        $letter = '';
        while ($columnNumber > 0) {
            $columnNumber--;
            $letter = chr(65 + ($columnNumber % 26)) . $letter;
            $columnNumber = intval($columnNumber / 26);
        }
        return $letter;
    }
}
