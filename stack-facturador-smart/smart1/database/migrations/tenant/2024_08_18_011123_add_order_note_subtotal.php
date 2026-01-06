<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderNoteSubtotal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('order_notes', 'subtotal')) {
            Schema::table('order_notes', function (Blueprint $table) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('total');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        if (Schema::hasColumn('order_notes', 'subtotal')) {
            Schema::table('order_notes', function (Blueprint $table) {
                $table->dropColumn('subtotal');
            });
        }
    
    }
}
