<?php

use App\Models\Tenant\Document;
use Illuminate\Database\Migrations\Migration;

//2025_07_16_153335_adjust_fee_note_document
class AdjustFeeNoteDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Document::where('payment_condition_id', '02')
            ->whereHas('note2')
            ->where('date_of_issue', '>=', '2025-01-01')
            ->chunk(100, function ($documents) {
                foreach ($documents as $document) {

                    $document->ajustDocumentFee();
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
