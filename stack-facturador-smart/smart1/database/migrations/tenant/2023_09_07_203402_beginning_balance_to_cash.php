<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BeginningBalanceToCash extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash', function (Blueprint $table) {
            $table->string('reference_number', 100)->change(); // Cambia la longitud a 100 caracteres
        });

        $cash = DB::connection('tenant')->table('cash')->get();
        foreach ($cash as $row) {
            $users = DB::connection('tenant')->table('users')->where('id',$row->user_id)->first();
            if($users!=null){
                $name = $users->name;
                if($row->reference_number==null){
                    DB::connection('tenant')->table('cash')->where('user_id',$users->id)->update([
                        'reference_number' => $name
                    ]);
                }                
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
        Schema::table('cash', function (Blueprint $table) {
            //
        });
    }
}
