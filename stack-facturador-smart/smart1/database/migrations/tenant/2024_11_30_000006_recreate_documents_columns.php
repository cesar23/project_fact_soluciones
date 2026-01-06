<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RecreateDocumentsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = DB::connection("tenant");

        // Obtener datos actuales
        $currentColumns = $connection->table('document_columns')
            ->select(['value', 'name', 'width', 'order', 'is_visible'])
            ->get()
            ->map(function($item) {
                return (array) $item;
            })
            ->toArray();

        // Agregar nueva columna al inicio del array
        array_unshift($currentColumns, [
            'value' => 'image',
            'name' => 'Imagen',
            'width' => 12.00,
            'order' => 0,
            'is_visible' => false
        ]);

        // Eliminar datos actuales
        $connection->table('document_columns')->truncate();

        // Insertar todos los registros
        $connection->table('document_columns')->insert($currentColumns);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No es necesario un rollback ya que los datos originales se perdieron
    }
}
