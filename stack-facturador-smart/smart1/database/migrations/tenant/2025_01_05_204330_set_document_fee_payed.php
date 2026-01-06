<?php

use App\Http\Controllers\Tenant\DocumentPaymentController;
use App\Models\Tenant\Configuration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204330_set_document_fee_payed
class SetDocumentFeePayed extends Migration
{
    public function up()
    {
        $tenat_connection = DB::connection('tenant');
        $configuration = $tenat_connection->table('configurations')->select('bill_of_exchange_special')->first();
        if ($configuration && $configuration->bill_of_exchange_special) {
            $documents = $tenat_connection->table('documents')
            ->leftJoin('document_payments', 'documents.id', '=', 'document_payments.document_id')
            ->where('date_of_issue', '>=', '2024-01-01')
            ->where('payment_condition_id', '02')
            ->groupBy('documents.id', 'documents.date_of_issue', 'documents.total')
            ->havingRaw('IFNULL(SUM(document_payments.payment), 0) <= documents.total AND EXISTS (SELECT 1 FROM document_fee df WHERE df.document_id = documents.id AND (df.original_amount IS NULL OR df.original_amount = 0))')
            ->select('documents.id', 'documents.date_of_issue', 'documents.total')
            ->get();
        
            foreach ($documents as $document) {
                $document_id = $document->id;
                (new DocumentPaymentController())->updateDocumentFees($document_id);
            }
        }
    }

    public function down()
    {
    }
}
