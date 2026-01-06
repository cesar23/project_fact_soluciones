<?php

namespace Modules\Dashboard\Helpers;

use App\Models\Tenant\Document;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SunatPurchaseSale;
use Modules\Expense\Models\Expense;

class DashboardSaleSunatPurchase
{

    public function data($year, $establishment_id = null)
    {
        // Crear array base para 12 meses
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $months[] = [
                'month' => $i,
                'date' => date('Y-m-d', strtotime($year . '-' . $i . '-01')),
                'internal_sale' => 0,
                'purchase_expense' => 0,
                'sunat_sale' => 0,
            ];
        }

        $yearStart = $year . '-01-01';
        $yearEnd = $year . '-12-31';

        // 1. Cargar registros SUNAT del año completo (1 query)
        $sunatRegisters = SunatPurchaseSale::where('show', true)
            ->whereBetween('period', [$yearStart, $yearEnd])
            ->get()
            ->keyBy('period');

        // 2. Si no hay establishment_id, usar datos agregados más eficientemente
        if (!$establishment_id) {
            // Cargar datos del año completo con queries agregadas por mes
            $purchases = Purchase::without(['user', 'soap_type', 'state_type', 'document_type', 'currency_type', 'group', 'items', 'purchase_payments'])
                ->whereIn('state_type_id', ['01', '05'])
                ->whereBetween('date_of_issue', [$yearStart, $yearEnd])
                ->selectRaw('YEAR(date_of_issue) as year, MONTH(date_of_issue) as month,
                    SUM(CASE WHEN currency_type_id = "PEN" THEN total ELSE total * exchange_rate_sale END) as total_purchases')
                ->groupBy('year', 'month')
                ->get()
                ->keyBy('month');

            $expenses = Expense::whereBetween('date_of_issue', [$yearStart, $yearEnd])
                ->selectRaw('YEAR(date_of_issue) as year, MONTH(date_of_issue) as month, SUM(total) as total_expenses')
                ->groupBy('year', 'month')
                ->get()
                ->keyBy('month');

            $invoices = Document::without(['user', 'soap_type', 'state_type', 'document_type', 'currency_type', 'items', 'payments'])
                ->where('state_type_id', '05')
                ->whereIn('document_type_id', ['01', '03', '08'])
                ->whereBetween('date_of_issue', [$yearStart, $yearEnd])
                ->selectRaw('YEAR(date_of_issue) as year, MONTH(date_of_issue) as month, SUM(total) as total_invoices')
                ->groupBy('year', 'month')
                ->get()
                ->keyBy('month');

            $credits = Document::without(['user', 'soap_type', 'state_type', 'document_type', 'currency_type', 'items', 'payments'])
                ->where('state_type_id', '05')
                ->whereIn('document_type_id', ['07'])
                ->whereBetween('date_of_issue', [$yearStart, $yearEnd])
                ->selectRaw('YEAR(date_of_issue) as year, MONTH(date_of_issue) as month, SUM(total) as total_credits')
                ->groupBy('year', 'month')
                ->get()
                ->keyBy('month');

            $saleNotes = SaleNote::without(['user', 'soap_type', 'state_type', 'document_type', 'currency_type', 'items', 'payments'])
                ->where('state_type_id', '01')
                ->whereBetween('date_of_issue', [$yearStart, $yearEnd])
                ->selectRaw('YEAR(date_of_issue) as year, MONTH(date_of_issue) as month, SUM(total) as total_sale_notes')
                ->groupBy('year', 'month')
                ->get()
                ->keyBy('month');
        } else {
            // Con establishment_id, agregar filtros correspondientes
            $purchases = Purchase::without(['user', 'soap_type', 'state_type', 'document_type', 'currency_type', 'group', 'items', 'purchase_payments'])
                ->whereIn('state_type_id', ['01', '05'])
                ->where('establishment_id', $establishment_id)
                ->whereBetween('date_of_issue', [$yearStart, $yearEnd])
                ->selectRaw('YEAR(date_of_issue) as year, MONTH(date_of_issue) as month,
                    SUM(CASE WHEN currency_type_id = "PEN" THEN total ELSE total * exchange_rate_sale END) as total_purchases')
                ->groupBy('year', 'month')
                ->get()
                ->keyBy('month');

            $expenses = Expense::where('establishment_id', $establishment_id)
                ->whereBetween('date_of_issue', [$yearStart, $yearEnd])
                ->selectRaw('YEAR(date_of_issue) as year, MONTH(date_of_issue) as month, SUM(total) as total_expenses')
                ->groupBy('year', 'month')
                ->get()
                ->keyBy('month');

            $invoices = Document::without(['user', 'soap_type', 'state_type', 'document_type', 'currency_type', 'items', 'payments'])
                ->where('state_type_id', '05')
                ->where('establishment_id', $establishment_id)
                ->whereIn('document_type_id', ['01', '03', '08'])
                ->whereBetween('date_of_issue', [$yearStart, $yearEnd])
                ->selectRaw('YEAR(date_of_issue) as year, MONTH(date_of_issue) as month, SUM(total) as total_invoices')
                ->groupBy('year', 'month')
                ->get()
                ->keyBy('month');

            $credits = Document::without(['user', 'soap_type', 'state_type', 'document_type', 'currency_type', 'items', 'payments'])
                ->where('state_type_id', '05')
                ->where('establishment_id', $establishment_id)
                ->whereIn('document_type_id', ['07'])
                ->whereBetween('date_of_issue', [$yearStart, $yearEnd])
                ->selectRaw('YEAR(date_of_issue) as year, MONTH(date_of_issue) as month, SUM(total) as total_credits')
                ->groupBy('year', 'month')
                ->get()
                ->keyBy('month');

            $saleNotes = SaleNote::without(['user', 'soap_type', 'state_type', 'document_type', 'currency_type', 'items', 'payments'])
                ->where('state_type_id', '01')
                ->where('establishment_id', $establishment_id)
                ->whereBetween('date_of_issue', [$yearStart, $yearEnd])
                ->selectRaw('YEAR(date_of_issue) as year, MONTH(date_of_issue) as month, SUM(total) as total_sale_notes')
                ->groupBy('year', 'month')
                ->get()
                ->keyBy('month');
        }

        // 3. Procesar cada mes con datos precargados
        foreach ($months as $key => $m) {
            $month = $m['month'];
            $monthDate = $m['date'];

            // Usar datos SUNAT si existen
            if (isset($sunatRegisters[$monthDate])) {
                $register = $sunatRegisters[$monthDate];
                $months[$key]['internal_sale'] = $register->internal_sale;
                $months[$key]['purchase_expense'] = $register->purchase_expense;
                $months[$key]['sunat_sale'] = $register->sunat_sale;
            } else {
                // Usar datos precargados
                $monthPurchases = isset($purchases[$month]) ? $purchases[$month]->total_purchases : 0;
                $monthExpenses = isset($expenses[$month]) ? $expenses[$month]->total_expenses : 0;
                $monthInvoices = isset($invoices[$month]) ? $invoices[$month]->total_invoices : 0;
                $monthCredits = isset($credits[$month]) ? $credits[$month]->total_credits : 0;
                $monthSaleNotes = isset($saleNotes[$month]) ? $saleNotes[$month]->total_sale_notes : 0;

                $months[$key]['internal_sale'] = $monthSaleNotes;
                $months[$key]['purchase_expense'] = $monthPurchases + $monthExpenses;
                $months[$key]['sunat_sale'] = $monthInvoices - $monthCredits;
            }
        }

        return $months;
    }
}
