<?php

use App\Models\Tenant\DocumentColumn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_25_010964_create_information_additional_pdf
class CreateInformationAdditionalPdf extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('information_additional_pdf', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('image');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('information_additional_pdf'); 
    }
}
