<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2024_12_13_0120108_insert_person_reg.php

class InsertPersonReg extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $records = [
            ['short' => 'R. General', 'description' => 'Régimen General', 'active' => true],
            ['short' => 'R. Especial', 'description' => 'Régimen Especial', 'active' => true], 
            ['short' => 'R. Mype', 'description' => 'Régimen Mype', 'active' => true],
            ['short' => 'RUS', 'description' => 'RUS', 'active' => true],
        ];

        foreach($records as $row) {
            $exists = DB::table('person_reg')
                        ->where('short', $row['short'])
                        ->exists();

            if (!$exists) {
                DB::table('person_reg')->insert($row);
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
        DB::table('person_reg')->truncate();
    }
}
