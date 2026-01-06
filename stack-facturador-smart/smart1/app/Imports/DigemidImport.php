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
class DigemidImport implements ToCollection
{
    use Importable;

    protected $data;




    public function collection(Collection $rows)
    {
        // DB::table('digemid')->truncate();
        $total = count($rows);
        //divide by 6 all rows get int value
        $divided = intval($total / 6);
        unset($rows[0]);
        $registered = 0;
        $registers = [];
        foreach ($rows as $i =>$row){
        
            $Cod_Prod = trim($row[0]);
            $Nom_Prod = $row[1];
            $Concent = $row[2];
            $Nom_Form_Farm = $row[3];
            $Nom_Form_Farm_Simplif = $row[4];
            $Presentac = $row[5];
            $Fracciones = $row[6];
            $Fec_Vcto_Reg_Sanitario = $row[7];
            $Num_RegSan = $row[8];
            $Nom_Titular = $row[9];
            $Situacion = $row[10];
            if (
                $Cod_Prod !== 'Cod_Prod'
            ) {
                $exists = Digemid::where('cod_prod', $Cod_Prod)->first();
                if (!$exists) {
                    $registers[] = [
                        'cod_prod'                => $Cod_Prod,
                        'nom_prod'                => $Nom_Prod,
                        'concent'                 => $Concent,
                        'nom_form_farm'           => $Nom_Form_Farm,
                        'nom_form_farm_simplif'   => $Nom_Form_Farm_Simplif,
                        'presentac'               => $Presentac,
                        'fracciones'              => $Fracciones,
                        'fec_vcto_reg_sanitario'  => $Fec_Vcto_Reg_Sanitario,
                        'num_regsan'              => $Num_RegSan,
                        'nom_titular'             => $Nom_Titular,
                        'situacion'               => $Situacion,
                    ];
                }
            

                if(count($registers) === $divided || $i === $total - 1){
                    DB::table('digemid')->insert($registers);
                    $registers = [];
                }
                
                ++$registered;
            }
        }
        $this->data = compact('total', 'registered');
    }

    public function getData()
    {
        return $this->data;
    }
}
