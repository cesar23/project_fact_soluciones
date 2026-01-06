<?php

namespace App\Http\ViewComposers\Tenant;

use App\Http\Controllers\Tenant\ConfigurationController;
use App\Http\Resources\Tenant\ConfigurationResource;
use App\Models\Tenant\Configuration;

class ConfigurationVisualViewComposer
{
    public function compose($view)
    {
        $configuration = Configuration::getConfig();
        if(is_null($configuration->visual)) {
            $defaults = [
                'bg' => 'light',
                'header' => 'light',
                'sidebars' => 'light',
            ];
            $configuration->visual = $defaults;
            $configuration->save();
            ConfigurationController::clearCache();
        }
        $record = new ConfigurationResource($configuration);
        $view->visual = $record->visual;
    }
}
