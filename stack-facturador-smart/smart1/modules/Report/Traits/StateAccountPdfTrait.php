<?php

namespace Modules\Report\Traits;

use Modules\Report\Services\StateAccountPdfService;
use Illuminate\Http\Request;

trait StateAccountPdfTrait
{
    protected $pdfService;
    
    protected function initPdfService()
    {
        if (!$this->pdfService) {
            $this->pdfService = new StateAccountPdfService();
        }
    }
    
    public function getRelationSalesForPdf(Request $request)
    {
        $this->initPdfService();
        
        $records = $this->records($request);
        $recordsCollection = $records instanceof \Illuminate\Pagination\LengthAwarePaginator ? 
            collect($records->items()) : collect($records);
        
        // Cargar información del cliente si no está ya cargada
        $recordsCollection = $recordsCollection->map(function($record) {
            // Verificar que sea un objeto antes de acceder a sus propiedades
            if (is_object($record) && property_exists($record, 'customer_id') && $record->customer_id) {
                // Solo cargar si no tiene ya las relaciones
                if (!isset($record->customer) && !isset($record->person)) {
                    // Intentar cargar como customer (para SaleNotes)
                    try {
                        $record->load('customer');
                    } catch (\Exception $e) {
                        // Si falla, cargar como person (para Documents)
                        try {
                            $record->load('person');
                        } catch (\Exception $e2) {
                            // Si ambos fallan, no hacer nada
                        }
                    }
                }
            }
            return $record;
        });
        
        return $this->pdfService->groupRecordsByClient($recordsCollection);
    }
    
    protected function formatRecordsForPdf($records)
    {
        $this->initPdfService();
        
        return $records->map(function ($clientGroup) {
            // Formatear totales del cliente
            $clientGroup['totals']['formatted'] = [
                'total_taxed' => $this->pdfService->formatCurrency($clientGroup['totals']['total_taxed']),
                'total_igv' => $this->pdfService->formatCurrency($clientGroup['totals']['total_igv']),
                'total' => $this->pdfService->formatCurrency($clientGroup['totals']['total']),
                'total_paid' => $this->pdfService->formatCurrency($clientGroup['totals']['total_paid']),
                'total_pending' => $this->pdfService->formatCurrency($clientGroup['totals']['total_pending']),
            ];
            
            // Formatear records agrupados por fecha
            $clientGroup['records_by_date'] = $clientGroup['records_by_date']->map(function ($dateGroup) {
                // Formatear totales de la fecha
                $dateGroup['totals']['formatted'] = [
                    'total_taxed' => $this->pdfService->formatCurrency($dateGroup['totals']['total_taxed']),
                    'total_igv' => $this->pdfService->formatCurrency($dateGroup['totals']['total_igv']),
                    'total' => $this->pdfService->formatCurrency($dateGroup['totals']['total']),
                    'total_paid' => $this->pdfService->formatCurrency($dateGroup['totals']['total_paid']),
                    'total_pending' => $this->pdfService->formatCurrency($dateGroup['totals']['total_pending']),
                ];
                
                // Formatear cada record individual
                $dateGroup['records'] = $dateGroup['records']->map(function ($record) {
                    // Convertir el record a array si es necesario y crear un objeto stdClass
                    if (is_array($record)) {
                        $record = (object) $record;
                    }
                    
                    // Crear una copia del record para no modificar el original
                    $formattedRecord = clone $record;
                    
                    // Total del documento
                    $documentTotal = (float) ($record->total ?? 0);
                    $formattedRecord->formatted_total = $this->pdfService->formatCurrency($documentTotal);
                    $formattedRecord->formatted_total_taxed = $this->pdfService->formatCurrency($record->total_taxed ?? 0);
                    $formattedRecord->formatted_total_igv = $this->pdfService->formatCurrency($record->total_igv ?? 0);
                    
                    // Calcular suma de pagos
                    $totalPaid = 0;
                    if (isset($record->payments) && is_array($record->payments)) {
                        foreach ($record->payments as $payment) {
                            $totalPaid += (float) ($payment['payment'] ?? 0);
                        }
                    }
                    
                    // Se debe = Total - Pagos
                    $totalPending = $documentTotal - $totalPaid;

                    // Asignar valores numéricos para acumulación
                    $formattedRecord->total_paid = $totalPaid;
                    $formattedRecord->total_pending = $totalPending;

                    // Asignar valores formateados para mostrar
                    $formattedRecord->formatted_total_paid = $this->pdfService->formatCurrency($totalPaid);
                    $formattedRecord->formatted_total_pending = $this->pdfService->formatCurrency($totalPending);

                    return $formattedRecord;
                });
                
                return $dateGroup;
            });
            
            return $clientGroup;
        });
    }
}