<?php

use App\Models\Tenant\Catalogs\DocumentType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPackageHandlerCashDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_documents', function (Blueprint $table) {
            $table->unsignedInteger('package_handler_id')->nullable();
        });
        DocumentType::create([
            'id' => 'TE',
            'short' => 'TE',
            'description' => 'TICKET DE ENCOMIENDA',
            'active' => true,
        ]);
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_documents', function (Blueprint $table) {
            $table->dropColumn('package_handler_id');
        });
        
    }
}
