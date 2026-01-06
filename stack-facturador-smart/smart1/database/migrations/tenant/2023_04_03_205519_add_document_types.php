<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;

class AddDocumentTypes extends Migration
{
    public function up()
    {
        if (!Type::hasType('char')) {
            Type::addType('char', StringType::class);
        }
        Schema::table('order_notes', function (Blueprint $table) {
            $table->char('prefix', 4)->change();
        });
        Schema::table('quotations', function (Blueprint $table) {
            $table->char('prefix', 4)->change();
        });
        DB::table('cat_document_types')->insert([
            [
                'id' => 'PD',
                'active' => 1,
                'short' => 'PD',
                'description' => 'Pedidos'
            ],
            [
                'id' => 'COT',
                'active' => 1,
                'short' => 'COT',
                'description' => 'Cotizaciones'
            ]
        ]);
    }

    public function down()
    {

        DB::table('cat_document_types')->whereIn('short', ['PD', 'COT'])->delete();
    }
}
