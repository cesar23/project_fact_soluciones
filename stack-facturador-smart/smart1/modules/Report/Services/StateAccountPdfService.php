<?php

namespace Modules\Report\Services;

use Illuminate\Support\Collection;

class StateAccountPdfService
{
    public function groupRecordsByClient(Collection $records): Collection
    {
        $groupedRecords = [];
        
        foreach ($records as $idx => $record) {
            
            $clientKey = $this->getClientKey($record);
            $clientName = $this->getClientName($record);
            $clientNumber = $this->getClientNumber($record);
            $clientAddress = $this->getClientAddress($record);
            $clientContactName = $this->getClientContactName($record);
            $clientContactPhone = $this->getClientContactPhone($record);

        
            if (!isset($groupedRecords[$clientKey])) {
                $groupedRecords[$clientKey] = [
                    'client_name' => $clientName,
                    'client_number' => $clientNumber,
                    'client_id' => $this->getClientId($record),
                    'client_address' => $clientAddress,
                    'client_contact_name' => $clientContactName,
                    'client_contact_phone' => $clientContactPhone,
                    'records_by_date' => [],
                    'totals' => [
                        'total_taxed' => 0,
                        'total_igv' => 0,
                        'total' => 0,
                        'total_paid' => 0,
                        'total_pending' => 0,
                    ]
                ];
            }
            
            // Agrupar por fecha dentro del cliente
            $dateKey = $this->getDateKey($record);
            if (!isset($groupedRecords[$clientKey]['records_by_date'][$dateKey])) {
                $groupedRecords[$clientKey]['records_by_date'][$dateKey] = [
                    'date' => $dateKey,
                    'records' => collect(),
                    'totals' => [
                        'total_taxed' => 0,
                        'total_igv' => 0,
                        'total' => 0,
                        'total_paid' => 0,
                        'total_pending' => 0,
                    ]
                ];
            }
            
            $groupedRecords[$clientKey]['records_by_date'][$dateKey]['records']->push($record);
            $this->updateClientTotals($groupedRecords[$clientKey]['totals'], $record);
            $this->updateClientTotals($groupedRecords[$clientKey]['records_by_date'][$dateKey]['totals'], $record);
        }
        
        // Ordenar fechas dentro de cada cliente
        foreach ($groupedRecords as &$clientData) {
            ksort($clientData['records_by_date']);
            $clientData['records_by_date'] = collect($clientData['records_by_date']);
        }
        
        return collect($groupedRecords)->sortBy('client_name');
    }
    
    private function getClientKey($record): string
    {
        $clientId = $this->getClientId($record);
        $clientName = $this->getClientName($record);
        return $clientId . '_' . str_slug($clientName);
    }
    
    private function getDateKey($record): string
    {
        if (is_object($record) && property_exists($record, 'date_of_issue')) {
            return $record->date_of_issue ?? date('Y-m-d');
        }
        
        if (is_array($record) && isset($record['date_of_issue'])) {
            return $record['date_of_issue'];
        }
        
        return date('Y-m-d');
    }
    
    private function getClientId($record)
    {
        if (is_object($record) && property_exists($record, 'customer_id')) {
            return $record->customer_id ?? null;
        }
        
        if (is_array($record) && isset($record['customer_id'])) {
            return $record['customer_id'];
        }
        
        return null;
    }

    private function getClientAddress($record): string
    {
        return $record['customer_address'] ?? '';
    }
    
    private function getClientContactName($record): string
    {
        return $record['customer_contact_name'] ?? '';
    }
    
    private function getClientContactPhone($record): string
    {
        return $record['customer_contact_phone'] ?? '';
    }

    private function getClientName($record): string
    {
        return $record['customer_name'] ?? '';
    }
    
    private function getClientNumber($record): string
    {
        return $record['customer_number'] ?? '';
    }
    
    private function updateClientTotals(array &$totals, $record): void
    {
        $total_taxed = 0;
        $total_igv = 0;
        $total = 0;
        
        if (is_object($record)) {
            $total_taxed = (float) ($record->total_taxed ?? 0);
            $total_igv = (float) ($record->total_igv ?? 0);
            $total = (float) ($record->total ?? 0);
        } elseif (is_array($record)) {
            $total_taxed = (float) ($record['total_taxed'] ?? 0);
            $total_igv = (float) ($record['total_igv'] ?? 0);
            $total = (float) ($record['total'] ?? 0);
        }
        
        $totals['total_taxed'] += $total_taxed;
        $totals['total_igv'] += $total_igv;
        $totals['total'] += $total;
        
        $totalPaid = $this->calculateTotalPaid($record);
        $totals['total_paid'] += $totalPaid;
        $totals['total_pending'] += $total - $totalPaid;
    }
    
    private function calculateTotalPaid($record): float
    {
        $totalPaid = 0;
        
        // Si es un array (ya procesado por la Collection)
        if (is_array($record) && isset($record['payments']) && is_array($record['payments'])) {
            foreach ($record['payments'] as $payment) {
                $totalPaid += (float) ($payment['payment'] ?? 0);
            }
            return $totalPaid;
        }
        
        // Si es objeto con payments cargados
        if (is_object($record) && isset($record->payments) && $record->payments) {
            foreach ($record->payments as $payment) {
                if (is_object($payment)) {
                    $totalPaid += (float) ($payment->payment ?? 0);
                } elseif (is_array($payment)) {
                    $totalPaid += (float) ($payment['payment'] ?? 0);
                }
            }
        }
        
        return $totalPaid;
    }
    
    public function formatCurrency($amount): string
    {
        return number_format((float) $amount, 2, '.', ',');
    }
}