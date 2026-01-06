<?php

namespace Modules\BusinessTurn\Http\ViewComposers;

use App\Models\Tenant\Module;
use Modules\BusinessTurn\Models\BusinessTurn;

class BusinessTurnViewComposer
{
    public function compose($view)
    { 
        $modules = auth()->user()->modules()->pluck('value')->toArray();
        if(count($modules) > 0) {
            $view->vc_modules = $modules;
        } else {
            $view->vc_modules = Module::all()->pluck('value')->toArray();
        }
        $view->vc_business_turns = BusinessTurn::where('active',true)->pluck('value')->toArray();
    }
}