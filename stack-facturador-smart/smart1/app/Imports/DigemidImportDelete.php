<?php


namespace App\Imports;

use App\Models\System\Digemid;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;


/**
 * Class CatalogImport
 *
 * @package App\Imports
 *
 */
class DigemidImportDelete implements ToCollection
{
    use Importable;

    protected $data;




    public function collection(Collection $rows)
    {
        // Recolectar todos los códigos de producto
        $codigos = $rows->slice(1)->pluck(0)->map(function($codigo) {
            return trim($codigo);
        })->filter();

        // Eliminar en lote usando whereIn
        $registered = Digemid::whereIn('cod_prod', $codigos)->delete();

        $total = count($rows) - 1;
        $this->data = compact('total', 'registered');
    }

    public function getData()
    {
        return $this->data;
    }
}
