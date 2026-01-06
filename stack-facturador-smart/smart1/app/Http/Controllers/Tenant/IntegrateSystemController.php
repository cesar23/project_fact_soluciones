<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\IntegrateUserType;

class IntegrateSystemController extends Controller
{
   
    public function userTypes(){
        $integrate_user_types = IntegrateUserType::all();
        return compact('integrate_user_types');
    }
}