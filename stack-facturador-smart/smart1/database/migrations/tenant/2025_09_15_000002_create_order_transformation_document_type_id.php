<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_09_15_000002_create_order_transformation_document_type_id
class CreateOrderTransformationDocumentTypeId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('cat_document_types')->insert([   
            ['id' => 'OT', 'active' => true, 'short' => 'OT', 'description' => 'ORDEN DE TRANSFORMACIÓN'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('cat_document_types')->where('id', 'OT')->delete();
    }
}