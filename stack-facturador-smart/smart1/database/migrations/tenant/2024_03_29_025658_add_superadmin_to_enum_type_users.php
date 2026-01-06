

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSuperadminToEnumTypeUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('users', function (Blueprint $table) {
        //     $table->enum('type', ['admin','seller','integrator','client','superadmin'])->change();
        // });
        DB::connection('tenant')->statement("ALTER TABLE users MODIFY COLUMN type enum('admin','seller','integrator','client','superadmin')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        DB::connection('tenant')->statement("ALTER TABLE users MODIFY COLUMN type enum('admin','seller','integrator','client')");
        
    }
}
