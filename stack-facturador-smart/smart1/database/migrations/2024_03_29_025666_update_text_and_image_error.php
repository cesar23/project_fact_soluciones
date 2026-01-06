<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTextAndImageError extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('errors')) {
            //encuentra el primer error y actualiza el comentario2
            DB::table('errors')->where('id', 1)->update([
                'comentario2' => 'Tu cuenta ha sido temporalmente desactivada debido a una deuda pendiente. Para reactivarla, comunícate por WhatsApp.',
                'img' => 'images/fondos/payments.jpeg',
            ]);
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
