<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_29_2024343_add_identity_document_type_id_person
class AddIdentityDocumentTypeIdPerson extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatch_addresses', function (Blueprint $table) {
            $table->string('identity_document_type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::table('dispatch_addresses', function (Blueprint $table) {
            $table->dropColumn('identity_document_type_id');
        });
    }
}
