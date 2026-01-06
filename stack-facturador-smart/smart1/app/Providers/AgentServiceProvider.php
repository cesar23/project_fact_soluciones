<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Jenssegers\Agent\Agent;
use Illuminate\Support\ServiceProvider;

class AgentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $agent = new Agent();

        View::share('agent', $agent);
    }

    public function register()
    {
        //
    }
}