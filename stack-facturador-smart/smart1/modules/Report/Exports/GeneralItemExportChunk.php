<?php

namespace Modules\Report\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\Tenant\Configuration;
use App\Services\ItemLotsGroupService;
use Illuminate\Support\Facades\Log;
use App\CoreFacturalo\Helpers\Template\ReportHelper;
use App\CoreFacturalo\Helpers\Template\TemplateHelper;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Models\Tenant\{Document, Item, Person, Purchase, PurchaseItem, SaleNote, User};
use App\Models\Tenant\Catalogs\DocumentType;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class GeneralItemExportChunk implements FromCollection, WithHeadings, ShouldAutoSize, WithChunkReading
{
    use Exportable;

    protected $records;
    protected $type;
    protected $document_type_id;
    protected $request_apply_conversion_to_pen;
    protected $configuration;
    protected $totals = [
        'qty_general' => 0,
        'total_general' => 0,
        'purchase_total_general' => 0,
        'gain_general' => 0
    ];

    public function __construct()
    {
        $this->configuration = Configuration::first();
    }

    public function records($records)
    {

        $this->records = $records;
        return $this;
    }

    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    public function document_type_id($type)
    {
        $this->document_type_id = $type;
        return $this;
    }

    public function request_apply_conversion_to_pen($request_apply_conversion_to_pen)
    {
        $this->request_apply_conversion_to_pen = $request_apply_conversion_to_pen;
        return $this;
    }

    private function processSaleNoteRecords()
    {
        return $this->records->filter(function ($value) {

            return $value->document_type_id == '80';
        })->map(function ($value) {
            // $value = \App\Models\Tenant\SaleNoteItem::find($value->id);
            return $this->processRecord($value, 'sale_note');
        })->filter();
    }

    private function processPurchaseRecords()
    {
        return $this->records->map(function ($value) {
            return $this->processRecord($value, 'purchase');
        })->filter();
    }

    private function processDocumentRecords()
    {
        return $this->records->filter(function ($value) {

            return $value->document_type_id !== '80' && $value->document_type_id !== 'COT';
        })->map(function ($value) {
            return $this->processRecord($value, 'document');
        })->filter();
    }

    private function processQuotationRecords()
    {
        return $this->records->filter(function ($value) {
            return $value->document_type_id == 'COT';
        })->map(function ($value) {
            // $value = \App\Models\Tenant\QuotationItem::find($value->id);
            $to_return = $this->processRecord($value, 'quotation');
            return $to_return;
        })->filter();
    }

    private function processRecord($value, $document_type)
    {
        try {



            $series = $this->getItemSeries($value);
            $total_item_purchase = $this->calculatePurchaseTotal($value);
            $utility_item = $this->calculateUtility($value, $total_item_purchase, $value->currency_type_id, $value->exchange_rate_sale);

            $this->updateTotals($value, $total_item_purchase, $value->exchange_rate_sale);
            if ($this->type == 'sale') {
                $stablihsment = $this->getLocationData($value->customer);
            } else {
                $stablihsment = $this->getLocationData($value->supplier);
            }
            return [
                'value' => $value,
                'series' => $series,
                'total_item_purchase' => $total_item_purchase,
                'utility_item' => $utility_item,
                'stablihsment' => $stablihsment
            ];
        } catch (\Exception $e) {
            Log::error("Stack trace: " . $e->getTraceAsString());
            Log::error("Error procesando registro: " . $e->getMessage());
            return null;
        }
    }

    private function getItemSeries($value)
    {
        if (!isset($value->item->lots)) return '';

        return collect($value->item->lots)
            ->where('has_sale', 1)
            ->pluck('series')
            ->implode(' - ');
    }

    private function getPurchaseUnitPrice($currency_type_id, $item_id, $date_of_issue, $purchase_unit_price_item)
    {
        $purchase_unit_price = 0;
        // Se busca la compra del producto en el dia o antes de su venta,
        // para sacar la ganancia correctamente

        // La tabla purchase items parece eliminar due of date
        $purchase_item = PurchaseItem::where('item_id', $item_id)
            ->latest('id')->get()->pluck('purchase_id');
        // para ello se busca las compras
        $purchase = Purchase::wherein('id', $purchase_item)
            ->where('date_of_issue', '<=', $date_of_issue)
            ->latest('id')->first();

        if ($purchase) {
            $purchase_item = PurchaseItem::where([
                'purchase_id' => $purchase->id,
                'item_id' => $item_id
            ])
                ->latest('id')
                ->first();

            $purchase_unit_price = $purchase_item->unit_price;
            $purchase = Purchase::find($purchase_item->purchase_id);
            $exchange_rate_sale = $purchase->exchange_rate_sale * 1;
            // Si la venta es en soles, y la compra del producto es en dolares, se hace la transformcaion
            if ($currency_type_id === 'PEN') {
                if ($purchase->currency_type_id !== $currency_type_id) {
                    $purchase_unit_price = $purchase_unit_price * $exchange_rate_sale;
                }
            } else {
                // Si la venta es en dolares, y la compra del producto es en soles, se hace la transformcaion
                if ($purchase->currency_type_id !== $currency_type_id && $exchange_rate_sale !== 0) {
                    try {
                        $purchase_unit_price = $purchase_unit_price / $exchange_rate_sale;
                    } catch (\Exception $e) {
                        $purchase_unit_price = 0;
                    }
                }
            }
        }
        // TODO: revisar esta linea: Eliminando esta linea porque el precio de compra no puede ser igual al precio de venta,
        // en conculusión esta condición nunca será 0, para los productos que no tienen una compra luego de registrarse
        // $purchase_unit_price = ($purchase_item) ? $purchase_item->unit_price : $record->unit_price;

        if ($purchase_unit_price == 0 && $purchase_unit_price_item > 0) {
            $purchase_unit_price = $purchase_unit_price_item;
        }


        // if ($record->relation_item->purchase_unit_price > 0) {
        //     $purchase_unit_price = $record->relation_item->purchase_unit_price;
        // } else {
        //     $purchase_item = PurchaseItem::select('unit_price')->where('item_id', $record->item_id)->latest('id')->first();
        //     $purchase_unit_price = ($purchase_item) ? $purchase_item->unit_price : $record->unit_price;
        // }
        return $purchase_unit_price;
    }
    private function calculatePurchaseTotal($value)
    {
        // $total = \Modules\Report\Http\Resources\GeneralItemCollection::getPurchaseUnitPrice($value);
        $total = $this->getPurchaseUnitPrice($value->currency_type_id, $value->item_id, $value->document_date, $value->purchase_unit_price_item);

        if (isset($value->item->presentation) && is_object($value->item->presentation)) {
            $quantity_unit = $value->item->presentation->quantity_unit;
            $total *= $quantity_unit * $value->quantity;
        }

        return $total;
    }

    private function calculateUtility($value, $total_item_purchase, $currency_type_id, $exchange_rate_sale)
    {
        $apply_conversion = $this->request_apply_conversion_to_pen == 'true';

        if (!$apply_conversion && $currency_type_id === 'USD') {
            $total_item_purchase /= ($exchange_rate_sale ?: 1);
        }

        return $value->total - $total_item_purchase;
    }

    private function updateTotals($value, $total_item_purchase, $exchange_rate_sale)
    {
        $this->totals['qty_general'] += $value->quantity;
        $this->totals['total_general'] += round($value->total * $exchange_rate_sale, 2);
        $this->totals['purchase_total_general'] += round($total_item_purchase, 2);
        $this->totals['gain_general'] += round($value->total * $exchange_rate_sale - $total_item_purchase, 2);
    }

    private function getLocationData($customer)
    {
        $district = '';
        $department = '';
        $province = '';



        $customer = json_decode($customer);
        if ($customer != null) {
            if ($customer->district && $customer->district->description) {
                $district = $customer->district->description;
            }
            if ($customer->department && $customer->department->description) {
                $department = $customer->department->description;
            }
            if ($customer->province && $customer->province->description) {
                $province = $customer->province->description;
            }
        }

        return [
            'district' => $district,
            'department' => $department,
            'province' => $province,
        ];
    }

    public function collection()
    {
        if ($this->type == 'sale') {
            $processed_records = collect()
                ->merge($this->processSaleNoteRecords())
                ->merge($this->processDocumentRecords())
                ->merge($this->processQuotationRecords());

            return $this->transformToExcelCollection($processed_records);
        } else {
            $processed_records = collect()
                ->merge($this->processPurchaseRecords());
            return $this->transformToExcelCollection($processed_records);
        }

        return collect();
    }

    private function transformToExcelCollection($records)
    {
        $rows = collect();
        $item_lots_group_service = new ItemLotsGroupService();
        $multi_companies = $this->configuration->multi_companies;

        $records->chunk(1000)->each(function ($chunk) use (&$rows, $item_lots_group_service, $multi_companies) {
            foreach ($chunk as $key => $record) {
                try {

                    $value = $record['value'];
                    $series = $record['series'];
                    $total_item_purchase = $record['total_item_purchase'];
                    $utility_item = $record['utility_item'];
                    $stablihsment = $record['stablihsment'];


                    $seller = User::select('name')->find($value->seller_id ?? $value->user_id);

                    $payments = [];
                    if ($value->document_id || $value->sale_note_id || $value->quotation_id) {
                        $type_document = $value->document_id ? 'document' : ($value->sale_note_id ? 'sale_note' : 'quotation');
                        $id = $value->document_id ? $value->document_id : ($value->sale_note_id ? $value->sale_note_id : $value->quotation_id);
                        $payments = TemplateHelper::getDetailedPaymentById($id, $type_document);
                    }

                    $payment_method = collect($payments)->map(function ($payment) {
                        return collect($payment)->map(function ($pay) {
                            return $pay['description'];
                        })->implode("\n");
                    })->implode("\n");
                    $warehouse_description = $value->warehouse_description;

                    $id_lote_selected = $value->item->IdLoteSelected ?? [];
                    $lots_group = $item_lots_group_service->getItemLotGroupLineBreak($id_lote_selected);

                    $row = [];
                    $row[] = $key + 1; // #

                    if ($multi_companies) {
                        $row[] = $value->company_name ?? '';
                    }

                    $row[] = $value->document_date;
                    $row[] = $seller ? $seller->name : ''; // USUARIO/VENDEDOR

                    if ($this->type == 'sale') {
                        $row[] = $stablihsment['district']; // DIST
                        $row[] = $stablihsment['department']; // DPTO
                        $row[] = $stablihsment['province']; // PROV
                    }
                    $document_type = DocumentType::find($value->document_type_id);
                    $row[] = $document_type ? strtoupper($document_type->description) : 'NOTA DE VENTA'; // TIPO DOCUMENTO
                    $row[] = "";
                    $row[] = $value->document_series ?? $value->document_prefix; // SERIE
                    $row[] = $value->document_number ?? $value->document_id; // NÚMERO

                    if ($this->type == 'sale') {
                        $row[] = $value->purchase_order ?? ''; // ORDEN DE COMPRA
                        $row[] = $value->platform_name ?? '';    // Plataforma

                    }

                    $row[] = $value->document_state_type_id == '11' ? 'SI' : 'NO'; // ANULADO
                    if ($this->type == 'sale') {
                        $person = Person::with(['person_type'])->select('id', 'person_type_id', 'zone_id')->find($value->customer_id);
                        $customer = json_decode($value->customer);
                    } else {
                        $person = Person::with(['person_type'])->select('id', 'person_type_id', 'zone_id')->find($value->supplier_id);
                        $customer = json_decode($value->supplier);
                    }

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error("Error transformando registro {$key}: " . json_last_error_msg());
                        return null;
                    }
                    $row[] = optional($customer->identity_document_type)->description ?? ''; // DOC ENTIDAD TIPO DNI RUC
                    $row[] = $customer->number ?? ''; // DOC ENTIDAD NÚMERO
                    $row[] = $customer->name ?? ''; // DENOMINACIÓN ENTIDAD
                    $zone = $person->getZone();
                    $row[] = optional($person->person_type)->description ?? ''; // TIPO ENTIDAD
                    $row[] = isset($zone) ? $zone->name : ''; // ZONA

                    if ($this->type == 'sale') {
                        $user_id = $value->user_id ?? $value->seller_id;
                        $user = User::select('name')->find($user_id);
                        $row[] = $user->name ?? ''; // VENDEDOR
                        $row[] = $value->observation ?? ''; // OBSERVACIÓN
                    }
                    $item = json_decode($value->item);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error("Error transformando registro {$key}: " . json_last_error_msg());
                        return null;
                    }
                    $row[] = $value->currency_type_id; // MONEDA
                    $row[] = $item->unit_type_id; // UNIDAD DE MEDIDA
                    $row[] = $item->internal_id; // CÓDIGO INTERNO

                    if ($this->type == 'sale') {
                        $row[] = '-';
                        // $row[] = $document->reference_data ?? ''; // DATOS DE REFERENCIA
                    }

                    $row[] = $item->description; // DESCRIPCIÓN
                    $row[] = $value->quantity; // CANTIDAD

                    if ($this->type == 'sale') {
                        $row[] = $payment_method; // MÉTODO DE PAGO
                    }

                    $row[] = $series; // SERIES

                    if ($this->type == 'sale') {
                        $row[] = $lots_group; // LOTES
                    }

                    $row[] = $value->purchase_unit_price ?? 0; // COSTO UNIDAD
                    $row[] = $value->unit_value; // VALOR UNITARIO
                    $row[] = $value->unit_price; // PRECIO UNITARIO
                    $row[] = $value->total_discount ?? 0; // DESCUENTO
                    $row[] = $value->total_value; // SUBTOTAL
                    $row[] = $value->affectation_igv_type_id ?? ''; // TIPO DE IGV
                    $row[] = $value->total_igv; // IGV
                    $row[] = $value->system_isc_type_id ?? ''; // TIPO DE ISC
                    $row[] = $value->total_isc ?? 0; // ISC
                    $row[] = $value->total_plastic_bag_taxes ?? 0; // IMPUESTO BOLSAS
                    $row[] = $value->total; // TOTAL
                    if (!$value->unit_price) {
                        return false;
                    }
                    if ($this->type == 'sale') {
                        $row[] = $total_item_purchase; // TOTAL COMPRA
                        $row[] = $utility_item; // GANANCIA

                        $row[] = $value->item_model ?? '';                // Modelo
                        $row[] = $value->brand_name ?? '';                // Marca
                        $row[] = $value->category_name ?? '';             // Categoría
                    }

                    $row[] = $value->exchange_rate_sale ?? ''; // TIPO CAMBIO
                    $row[] = $warehouse_description; // ALMACÉN

                    $rows->push($row);
                } catch (\Exception $e) {
                    Log::error("Stack trace: " . $e->getTraceAsString());
                    Log::error("Error transformando registro {$key}: " . $e->getMessage());
                    return false;
                }
            }
        });

        return $rows;
    }

    public function headings(): array
    {
        $headers = ['#'];

        if ($this->configuration->multi_companies) {
            $headers[] = 'EMPRESA';
        }

        $headers = array_merge($headers, [
            'FECHA DE EMISIÓN',
            'USUARIO/VENDEDOR'
        ]);

        if ($this->type == 'sale') {
            $headers = array_merge($headers, [
                'DIST',
                'DPTO',
                'PROV'
            ]);
        }

        $headers = array_merge($headers, [
            'TIPO DOCUMENTO',
            'ID TIPO',
            'SERIE',
            'NÚMERO'
        ]);

        if ($this->type == 'sale') {
            $headers = array_merge($headers, [
                'ORDEN DE COMPRA',
                'PLATAFORMA'
            ]);
        }

        $headers = array_merge($headers, [
            'ANULADO',
            'DOC ENTIDAD TIPO DNI RUC',
            'DOC ENTIDAD NÚMERO',
            'DENOMINACIÓN ENTIDAD',
            'TIPO ENTIDAD',
            'ZONA'
        ]);

        if ($this->type == 'sale') {
            $headers = array_merge($headers, [
                'VENDEDOR',
                'OBSERVACIÓN'
            ]);
        }

        $headers = array_merge($headers, [
            'MONEDA',
            'UNIDAD DE MEDIDA',
            'CÓDIGO INTERNO'
        ]);

        if ($this->type == 'sale') {
            $headers[] = 'DATOS DE REFERENCIA';
        }

        $headers = array_merge($headers, [
            'DESCRIPCIÓN',
            'CANTIDAD'
        ]);

        if ($this->type == 'sale') {
            $headers[] = 'MÉTODO DE PAGO';
        }

        $headers[] = 'SERIES';

        if ($this->type == 'sale') {
            $headers = array_merge($headers, [
                'LOTES',
                'MODELO'
            ]);
        }

        $headers = array_merge($headers, [
            'COSTO UNIDAD',
            'VALOR UNITARIO',
            'PRECIO UNITARIO',
            'DESCUENTO',
            'SUBTOTAL',
            'TIPO DE IGV',
            'IGV',
            'TIPO DE ISC',
            'ISC',
            'IMPUESTO BOLSAS',
            'TOTAL'
        ]);

        if ($this->type == 'sale') {
            $headers = array_merge($headers, [
                'TOTAL COMPRA',
                'GANANCIA',
                'MARCA',
                'CATEGORÍA'
            ]);
        }

        $headers = array_merge($headers, [
            'TIPO CAMBIO',
            'ALMACÉN'
        ]);

        return $headers;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
