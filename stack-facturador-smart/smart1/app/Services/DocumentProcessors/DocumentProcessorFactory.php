<?php

namespace App\Services\DocumentProcessors;

class DocumentProcessorFactory
{
    // private static $processors = [
    //     'App\Models\Tenant\DocumentPayment' => DocumentPaymentProcessor::class,
    //     'App\Models\Tenant\SaleNotePayment' => SaleNotePaymentProcessor::class,
    //     'App\Models\Tenant\PackageHandlerPayment' => PackageHandlerPaymentProcessor::class,
    //     'App\Models\Tenant\TechnicalServicePayment' => TechnicalServicePaymentProcessor::class,
    //     'Modules\Expense\Models\ExpensePayment' => ExpensePaymentProcessor::class,
    //     'Modules\Finance\Models\IncomePayment' => IncomePaymentProcessor::class,
    //     'App\Models\Tenant\PurchasePayment' => PurchasePaymentProcessor::class,
    //     'Modules\Sale\Models\QuotationPayment' => QuotationPaymentProcessor::class,
    //     'Modules\Pos\Models\CashTransaction' => CashTransactionProcessor::class,
    //     'App\Models\Tenant\CashTransaction' => CashTransactionProcessor::class,
    //     // Agregar más procesadores aquí
    // ];

    public static function make($payment_type)
    {
        $processors = [
            'App\Models\Tenant\DocumentPayment' => DocumentPaymentProcessor::class,
            'App\Models\Tenant\SaleNotePayment' => SaleNotePaymentProcessor::class,
            'Modules\Sale\Models\QuotationPayment' => QuotationPaymentProcessor::class,
            'Modules\Expense\Models\ExpensePayment' => ExpensePaymentProcessor::class,
            'Modules\Sale\Models\TechnicalServicePayment' => TechnicalServicePaymentProcessor::class,
            // 'Modules\Pos\Models\CashTransaction' => CashTransactionProcessor::class,
            'App\Models\Tenant\PackageHandlerPayment' => PackageHandlerPaymentProcessor::class,
            'Modules\Finance\Models\IncomePayment' => IncomePaymentProcessor::class,
            'App\Models\Tenant\PurchasePayment' => PurchasePaymentProcessor::class,
            'App\Models\Tenant\TechnicalServicePayment' => TechnicalServicePaymentProcessor::class,
        ];

        $to_return = isset($processors[$payment_type]) ? new $processors[$payment_type] : null;
        return $to_return;
    }
} 