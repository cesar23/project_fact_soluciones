<?php

use App\Models\Tenant\Catalogs\IdentityDocumentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddNewTypesDocument extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = 'cat_identity_document_types';

        if (Schema::hasTable($tableName)) {
            $documents = [
                [
                    'id' => '9',
                    'description' => 'CARNE SOLIC REFUGIO',
                    'active' => true,
                ],
                [
                    'id' => '22',
                    'description' => 'C.IDENT.-RREE',
                    'active' => true,
                ],
                [
                    'id' => '23',
                    'description' => 'PTP',
                    'active' => true,
                ],
                [
                    'id' => '24',
                    'description' => 'DOC.ID.EXTR.',
                    'active' => true,
                ],
                [
                    'id' => '26',
                    'description' => 'CPP',
                    'active' => true,
                ],
            ];
            IdentityDocumentType::insert($documents);

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
