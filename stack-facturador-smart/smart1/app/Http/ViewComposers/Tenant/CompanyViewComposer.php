<?php

namespace App\Http\ViewComposers\Tenant;

use App\Models\Tenant\Order;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Tutorial;
use Illuminate\Support\Facades\DB;

class CompanyViewComposer
{
    public function compose($view)
    {
        
        $view->vc_company = Company::getVcCompany();
        $establishments = Establishment::getVcEstablishment();
        if ($establishments) {
            $logo =  $establishments->logo;
        } else {
            $logo = 'storage/uploads/logos/' . $view->vc_company->logo;
        }
        if ($logo == null || $logo == '') {
            $logo = 'storage/uploads/logos/' . $view->vc_company->logo;
        }
        $view->vc_config = Configuration::getConfig();
        $view->vc_shortcuts_right = Tutorial::getShortcutsRight();
        $view->vc_shortcuts_center = Tutorial::getShortcutsCenter();
        $view->vc_paginate = Tutorial::getShortcutsMiddle();
        $view->vc_shortcuts_left = Tutorial::getShortcutsLeft();
        $view->vc_videos = Tutorial::getVideos();
        $view->vc_logotipo = $logo;
        $view->vc_establishments = Establishment::getVcEstablishments();
        $view->vc_orders = 0;

    }
}
