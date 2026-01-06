<?php

namespace App\Traits;

use App\Models\Tenant\User;
use Modules\Restaurant\Models\Area;
use App\Models\Tenant\Establishment;

 use Carbon\Carbon;
 
/**
 * 
 */
trait PrinterTrait
{

 
    public function printerName($user_id)
    {
        $printer=null;
        $user=User::where('id',$user_id)->first();
        if($user!=null && $user->area_id!=null){
            $area = Area::where('id',$user->area_id)->first();
            return $area->printer;
        }else{
            $establishment =Establishment::where('id',$user->establishment_id)->first();
            if($establishment!=null){
                return $establishment->printer;
            }
        }
    }
     
}
