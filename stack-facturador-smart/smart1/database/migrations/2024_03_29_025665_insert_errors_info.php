<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertErrorsInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
            if(Schema::hasTable('errors')){
                DB::table('errors')->insert([
                    'img' => 'error.png',
                    'titulo' => '¡Atención!',
                    'comentario2' => 'Tu cuenta ha sido temporalmente desactivada debido a una deuda pendiente. Para reactivarla, comunícate al 936051451.',
                    'adm' => '¡No pierdas más tiempo y vuelve a operar con normalidad!',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
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