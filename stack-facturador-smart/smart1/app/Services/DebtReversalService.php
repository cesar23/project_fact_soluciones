<?php

namespace App\Services;

use App\Models\Tenant\SupplyPlanDocument;
use App\Models\Tenant\SupplyDebtDocument;
use App\Models\Tenant\SupplyAdvancePayment;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use Illuminate\Support\Facades\DB;
use Exception;

class DebtReversalService
{
    /**
     * Reverse debt payments when document is cancelled
     */
    public static function reverseDebtPayments($documentId, $documentType = 'document')
    {
        try {
            DB::beginTransaction();

            // Find the SupplyPlanDocument based on document type
            $planDocument = null;
            if ($documentType === 'sale_note') {
                $planDocument = SupplyPlanDocument::where('sale_note_id', $documentId)
                    ->where('is_debt_payment', true)
                    ->first();
            } else {
                $planDocument = SupplyPlanDocument::where('document_id', $documentId)
                    ->where('is_debt_payment', true)
                    ->first();
            }

            if (!$planDocument) {
                // Not a debt payment document, nothing to reverse
                DB::rollBack();
                return [
                    'success' => true,
                    'message' => 'Documento no relacionado con pagos de deuda'
                ];
            }

            // Check if already cancelled
            if ($planDocument->is_cancelled) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Los pagos de deuda ya fueron revertidos para este documento'
                ];
            }

            // Get all debt document relationships
            $debtDocuments = SupplyDebtDocument::where('supply_plan_document_id', $planDocument->id)
                ->where('is_cancelled', false)
                ->with('debt')
                ->get();

            if ($debtDocuments->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'No se encontraron relaciones de deuda para revertir'
                ];
            }

            // Reverse each debt payment
            foreach ($debtDocuments as $debtDocument) {
                $debt = $debtDocument->debt;
                $amountToReverse = $debtDocument->amount_paid;

                // Update debt amounts
                $newAmount = $debt->amount + $amountToReverse;
                $newPaidAmount = ($debt->paid_amount ?? 0) - $amountToReverse;
                $newCancelledAmount = ($debt->cancelled_amount ?? 0) + $amountToReverse;
                $newPaymentCount = max(0, ($debt->payment_count ?? 1) - 1);

                // Determine new active status
                $newActiveStatus = false; // Back to unpaid

                $debt->update([
                    'amount' => round($newAmount, 2),
                    'paid_amount' => round(max(0, $newPaidAmount), 2),
                    'cancelled_amount' => round($newCancelledAmount, 2),
                    'payment_count' => $newPaymentCount,
                    'active' => $newActiveStatus,
                ]);

                // Mark debt document relationship as cancelled
                $debtDocument->update([
                    'is_cancelled' => true,
                    'cancelled_at' => now(),
                ]);
            }

            // Reverse advance payments if this document was generated from them
            self::reverseAdvancePayments($planDocument->id);

            // Mark plan document as cancelled
            $planDocument->update([
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancellation_reason' => 'Documento anulado - reversión automática de pagos',
                'original_status' => $planDocument->status,
                'status' => 'cancelled',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pagos de deuda revertidos exitosamente',
                'data' => [
                    'plan_document_id' => $planDocument->id,
                    'reversed_debts' => $debtDocuments->count(),
                    'total_amount_reversed' => $debtDocuments->sum('amount_paid'),
                ]
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al revertir pagos de deuda: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reverse advance payments when document is cancelled
     */
    private static function reverseAdvancePayments($planDocumentId)
    {
        try {
            // Find advance payments that were used in this document (new structure)
            $advancePayments = SupplyAdvancePayment::where(function($query) use ($planDocumentId) {
                $query->where('document_id', $planDocumentId)
                      ->orWhere('used_in_document_id', $planDocumentId);
            })
            ->where('is_used', true)
            ->get();

            if ($advancePayments->isEmpty()) {
                return true; // No advance payments to reverse
            }

            // Reverse each advance payment
            foreach ($advancePayments as $payment) {
                // For new structure payments, we need to restore all debts from the periods
                if ($payment->periods && is_array($payment->periods)) {
                    // Find and restore all debts for each period
                    foreach ($payment->periods as $period) {
                        $debt = SupplyDebt::where('supply_id', $payment->supply_id)
                            ->where('year', $period['year'])
                            ->where('month', $period['month'])
                            ->where('type', 'r')
                            ->first();

                        if ($debt) {
                            // Restore debt to unpaid state
                            $debt->update([
                                'amount' => $debt->original_amount ?? $period['amount'],
                                'paid_amount' => 0,
                                'active' => false, // Back to unpaid
                                'payment_count' => 0,
                                'last_payment_date' => null,
                            ]);
                        }
                    }
                }

                // Mark advance payment as unused and available again
                $payment->update([
                    'is_used' => false,
                    'used_in_document_id' => null,
                    'used_at' => null,
                    'document_id' => null,
                    'sale_note_id' => null,
                ]);
            }

            return true;

        } catch (Exception $e) {
            throw new Exception('Error al revertir pagos adelantados: ' . $e->getMessage());
        }
    }
}