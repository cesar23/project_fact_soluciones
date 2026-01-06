<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `users` 
        MODIFY COLUMN `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL AFTER `name`,
        MODIFY COLUMN `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL AFTER `email_verified_at`,
        MODIFY COLUMN `permission_force_send_by_summary` tinyint(1) NULL DEFAULT 0 AFTER `type`,
        MODIFY COLUMN `permission_edit_cpe` tinyint(1) NULL DEFAULT 0 AFTER `permission_force_send_by_summary`,
        MODIFY COLUMN `create_payment` tinyint(1) NULL DEFAULT 1 AFTER `permission_edit_cpe`,
        MODIFY COLUMN `delete_purchase` tinyint(1) NULL DEFAULT 1 AFTER `create_payment`,
        MODIFY COLUMN `annular_purchase` tinyint(1) NULL DEFAULT 1 AFTER `delete_purchase`,
        MODIFY COLUMN `edit_purchase` tinyint(1) NULL DEFAULT 1 AFTER `annular_purchase`,
        MODIFY COLUMN `delete_payment` tinyint(1) NULL DEFAULT 1 AFTER `edit_purchase`,
        MODIFY COLUMN `recreate_documents` tinyint(1) NULL DEFAULT 0 AFTER `delete_payment`,
        MODIFY COLUMN `locked` tinyint(1) NULL DEFAULT 0 AFTER `recreate_documents`,
        MODIFY COLUMN `multiple_default_document_types` tinyint(1) NULL DEFAULT 0 AFTER `locked`;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
