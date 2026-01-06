<?php

use App\Models\Tenant\DocumentColumn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_25_010963_adjust_documents_columnas
class AdjustDocumentsColumnas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $docRecords = DocumentColumn::where('type', 'DOC')->get();

        if ($docRecords->count() > 0) {
            $exists_cot = DocumentColumn::where('type', 'COT')->exists();
            $exists_nv = DocumentColumn::where('type', 'NV')->exists();
            $types = [];
            if (!$exists_cot) {
                $types[] = 'COT';
            }
            if (!$exists_nv) {
                $types[] = 'NV';
            }


            foreach ($types as $type) {
                $newRecords = $docRecords->map(function ($docRecord) use ($type) {
                    return [
                        'value' => $docRecord->value,
                        'name' => $docRecord->name,
                        'width' => $docRecord->width,
                        'order' => $docRecord->order,
                        'is_visible' => $docRecord->is_visible,
                        'column_align' => $docRecord->column_align,
                        'column_order' => $docRecord->column_order,
                        'type' => $type,
                    ];
                })->toArray();

                DocumentColumn::insert($newRecords);
            }
        }
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
