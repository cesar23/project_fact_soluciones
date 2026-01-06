<?php

namespace App\Services\DocumentProcessors;

use App\Models\Tenant\Cash;
use App\Models\Tenant\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

abstract class BaseDocumentProcessor implements DocumentProcessorInterface
{
    /**
     * Caché de precios de compra para evitar consultas repetidas
     * @var array
     */
    protected static $items_data_cache = [];
    protected $amounts_cash = [];

    protected function formatNumber($number, $decimal = 2)
    {
        return number_format($number, $decimal, '.', '');
    }

    protected function calculateTotal($amount, $currency_type_id, $exchange_rate_sale)
    {
        if ($currency_type_id !== 'PEN') {
            return $amount * $exchange_rate_sale;
        }
        return $amount;
    }

    protected function getPaymentMethodDescription($methods_payment, $payment_method_type_id)
    {
        foreach ($methods_payment as $record) {
            if ($record->id == $payment_method_type_id) {
                return $record->description;
            }
        }
        return '';
    }
    public function removeDuplicatesItems($items)
    {
        return collect($items)->unique('key')->values();
    }
    /**
     * Calcula la ganancia total para un conjunto de items
     * Utiliza caché para evitar consultas repetidas a la base de datos
     * 
     * @param array $items Items de un documento
     * @return float Ganancia total
     */
    public function getGainByItems($items,$key)
    {
        $gain = 0;
        $comission = 0;
        $item_ids_to_query = [];

        // Identificar qué item_ids necesitamos consultar
        foreach ($items as $item) {
            $item_id = $item->item_id;
            if (!isset(self::$items_data_cache[$item_id])) {
                $item_ids_to_query[] = $item_id;
            }
        }

        // Consultar en lote todos los items nuevos
        if (!empty($item_ids_to_query)) {
            $db_items = Item::select('id', 'purchase_unit_price','commission_amount','commission_type')
                ->whereIn('id', $item_ids_to_query)
                ->get()
                ->keyBy('id');

            // Almacenar en caché
            foreach ($db_items as $item_id => $item_db) {
                self::$items_data_cache[$item_id]['purchase_unit_price'] = $item_db->purchase_unit_price;
                self::$items_data_cache[$item_id]['commission_amount'] = $item_db->commission_amount;
                self::$items_data_cache[$item_id]['commission_type'] = $item_db->commission_type;
            }

            // Para los IDs que no encontramos, guardar null en la caché para evitar nuevas consultas
            foreach ($item_ids_to_query as $item_id) {
                if (!isset(self::$items_data_cache[$item_id])) {
                    self::$items_data_cache[$item_id]['purchase_unit_price'] = 0;
                    self::$items_data_cache[$item_id]['commission_amount'] = 0;
                    self::$items_data_cache[$item_id]['commission_type'] = null;
                }
            }
        }

        // Calcular la ganancia con los datos en caché
        foreach ($items as $item) {
            $item_id = $item->item_id;
            $purchase_unit_price = self::$items_data_cache[$item_id]['purchase_unit_price'] ?? 0;
            $commission_amount = self::$items_data_cache[$item_id]['commission_amount'] ?? 0;
            $commission_type = self::$items_data_cache[$item_id]['commission_type'] ?? null;
            $presentationQuantity = (!empty($item->item->presentation)) ? $item->item->presentation->quantity_unit : 1;
            $gain += ($item->unit_price - $purchase_unit_price) * $item->quantity * $presentationQuantity;
            if($commission_type == 'amount'){
                $comission += $commission_amount * $item->quantity * $presentationQuantity;
            }else{
                $comission += $item->unit_price * $commission_amount * $item->quantity * $presentationQuantity / 100;
            }
        }
        return ['gain' => $gain, 'comission' => $comission];
    }

    protected function getGainItems($items, $doc)
    {
        $doc_items = collect();
        foreach ($items as $item) {
            $presentationQuantity = (!empty($item->item->presentation)) ? $item->item->presentation->quantity_unit : 1;
            $format_item = [
                'key' => $item->id . '_' . $doc,
                'quantity' => $item->quantity * $presentationQuantity,
                'item_id' => $item->item_id,
                'unit_price' => floatval($item->unit_price),
            ];
            $doc_items->push($format_item);
        }
        return $doc_items->toArray();
    }

    protected function updateMethodsPayment(&$methods_payment, $payment_method_type_id, $amount)
    {

        foreach ($methods_payment as $record) {
            if ($record->id == $payment_method_type_id) {

                $record->sum += $amount;
                if($record->is_cash){
                    $this->amounts_cash[] = $amount;
                }
                break;
            }
        }
    }

    /**
     * Procesa la información del vendedor y la agrega a los resultados
     * 
     * @param array &$result Array de resultados donde se guardará la información
     * @param int $seller_id ID del vendedor (seller_id o user_id del documento)
     * @param object $document Documento (Document o SaleNote)
     * @param float $payment_amount Monto del pago
     * @param array $items Items vendidos
     * @param string|null $document_type_id Tipo de documento (opcional)
     * @param float $gain Ganancia calculada del documento (opcional)
     */
    public function processSeller(&$result, $seller_id, $document, $payment_amount, $items, $document_type_id = null, $gain = 0)
    {
        if (!$seller_id) {
            return;
        }
    
        // Inicializar colección de vendedores si no existe
        if (!isset($result['sellers'])) {
            $result['sellers'] = collect();
        }

        // Determinar si es una nota de venta o no
        Log::info($document_type_id);
        $is_sale_note = $document_type_id == 'sale_note';
        $is_quotation = $document_type_id == 'quotation';
        // Determinar si es a crédito o contado
        $is_credit = $document->payment_condition_id === '02';

        // Definir un ID único para el documento
        $document_unique_id = ($is_sale_note ? 'note_' : ($is_quotation ? 'quotation_' : 'doc_')) . $document->id;

        // Buscar si ya existe el vendedor en los resultados
        $seller = $result['sellers']->firstWhere('id', $seller_id);

        if ($seller) {
            // Actualizar vendedor existente
            $index = $result['sellers']->search(function ($item) use ($seller_id) {
                return $item['id'] === $seller_id;
            });

            $seller = $result['sellers'][$index];

            // Verificar si este documento ya fue procesado para este vendedor
            $doc_exists = false;
            $doc_index = -1;

            if (isset($seller['processed_documents'])) {
                $doc_index = array_search($document_unique_id, $seller['processed_documents']);
                $doc_exists = $doc_index !== false;
            } else {
                $seller['processed_documents'] = [];
            }

            // Si el documento ya fue procesado, solo actualizamos el monto del pago
            if ($doc_exists) {
                // Si el documento ya fue procesado, solo actualizamos el pago
                for ($i = 0; $i < count($seller['documents']); $i++) {
                    if (
                        $seller['documents'][$i]['id'] == $document->id &&
                        $seller['documents'][$i]['is_sale_note'] == $is_sale_note
                    ) {
                        $seller['documents'][$i]['payment_amount'] += $payment_amount;
                        break;
                    }
                }

                // Actualizamos solo los montos totales
                $seller['payments'] += $payment_amount;

                // No necesitamos agregar nuevos items ni documentos, solo actualizar totales
            } else {
                // Registramos que hemos procesado este documento
                $seller['processed_documents'][] = $document_unique_id;

                // Actualizar totales
                $seller['total'] += $payment_amount;
                $seller['payments'] += $payment_amount;

                // Actualizar por tipo de pago
                if ($is_credit) {
                    $seller['total_credit'] += $payment_amount;
                } else {
                    $seller['total_cash'] += $payment_amount;
                }

                // Actualizar por tipo de documento
                if ($is_sale_note) {
                    $seller['total_sale_notes'] += $payment_amount;
                } else {
                    $seller['total_documents'] += $payment_amount;
                }

                // Actualizar ganancia total
                $seller['total_gain'] = ($seller['total_gain'] ?? 0) + $gain;
    
                // Añadir documento a la lista de documentos del vendedor
                $seller['documents'][] = [
                    'id' => $document->id,
                    'number' => $document->number_full ?? $document->number,
                    'date' => $document->date_of_issue,
                    'customer_name' => $document->customer->name,
                    'total' => $document->total,
                    'payment_amount' => $payment_amount,
                    'is_credit' => $is_credit,
                    'is_sale_note' => $is_sale_note,
                    'is_quotation' => $is_quotation,
                    'document_type_id' => $document_type_id,
                    'gain' => $gain, // Agregar ganancia al documento
                ];

                // Añadir items a la lista de items del vendedor
                if (!isset($seller['items'])) {
                    $seller['items'] = [];
                }
                $seller['items'] = array_merge($seller['items'], $items);
            }

            // Actualizar en la colección
            $result['sellers'][$index] = $seller;
        } else {
            // Crear nuevo vendedor
            // Intentar obtener nombre del vendedor
            $seller_name = '';
            if (class_exists('App\Models\Tenant\User')) {
                $user = \App\Models\Tenant\User::find($seller_id);
                if ($user) {
                    $seller_name = $user->name;
                }
            }

            $seller = [
                'id' => $seller_id,
                'name' => $seller_name,
                'total' => $payment_amount,
                'payments' => $payment_amount,
                'total_credit' => $is_credit ? $payment_amount : 0,
                'total_cash' => !$is_credit ? $payment_amount : 0,
                'total_sale_notes' => $is_sale_note ? $payment_amount : 0,
                'total_documents' => !$is_sale_note && !$is_quotation ? $payment_amount : 0,
                'total_gain' => $gain, // Agregar ganancia al nuevo vendedor
                'processed_documents' => [$document_unique_id], // Registro de documentos procesados
                'documents' => [
                    [
                        'id' => $document->id,
                        'number' => $document->number_full ?? $document->number,
                        'date' => $document->date_of_issue,
                        'customer_name' => $document->customer->name,
                        'total' => $document->total,
                        'payment_amount' => $payment_amount,
                        'is_credit' => $is_credit,
                        'is_sale_note' => $is_sale_note,
                        'is_quotation' => $is_quotation,
                        'document_type_id' => $document_type_id,
                        'gain' => $gain, // Agregar ganancia al documento
                    ]
                ],
                'items' => $items
            ];
        
            // Añadir a la colección
            $result['sellers']->push($seller);
        }
    }

    public function processBatch($payment_ids, $status_type_id, &$methods_payment, &$result)
    {
        // Implementación por defecto - procesa uno por uno
        // Los procesadores específicos pueden sobrescribir este método para optimización
        $processed_documents = collect();

        foreach ($payment_ids as $payment_id) {
            $cash_document = (object)['payment_id' => $payment_id, 'payment_type' => get_class($this)];
            if ($document_data = $this->process($cash_document, $status_type_id, $methods_payment, $result)) {
                $processed_documents->push($document_data);
            }
        }

        return $processed_documents;
    }
}
