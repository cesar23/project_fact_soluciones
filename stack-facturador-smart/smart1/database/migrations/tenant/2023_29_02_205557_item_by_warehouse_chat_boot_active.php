<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ItemByWarehouseChatBootActive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       

      //actualiza configurations para actualizar el valor de chatboot y list_items_by_warehouse a 1
        DB::table('configurations')->where('id', 1)->update(['chatboot' => 1, 'list_items_by_warehouse' => 1]);
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('sunat_stock');
    }
}
