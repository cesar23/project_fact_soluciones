

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_07_14_2024322_add_admin_lower_to_enum_type_users
class AddAdminLowerToEnumTypeUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DB::connection('tenant')->statement("ALTER TABLE users MODIFY COLUMN type enum('admin','seller','integrator','client','superadmin','admin_lower')");
        try {
            DB::connection('tenant')->statement("
                ALTER TABLE users 
                MODIFY COLUMN type ENUM('admin','seller','integrator','client','superadmin','admin_lower') 
                NOT NULL DEFAULT 'seller'
            ");
        } catch (\Exception $e) {
            // Si falla, usar una aproximación alternativa
            DB::connection('tenant')->statement("
                ALTER TABLE users 
                CHANGE COLUMN type type ENUM('admin','seller','integrator','client','superadmin','admin_lower') 
                NOT NULL DEFAULT 'seller'
            ");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')->statement("ALTER TABLE users MODIFY COLUMN type enum('admin','seller','integrator','client','superadmin')");
    }
}
