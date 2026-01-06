<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Tenant\ConfigurationController;
use App\Models\System\Client;
use Closure;
use App\Models\Tenant\Configuration;
use Hyn\Tenancy\Facades\TenancyFacade;
use Hyn\Tenancy\Models\Hostname;
use Carbon\Carbon;

class LockedTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $configuration = Configuration::getConfig();
        if (null === $configuration) {
            $configuration = new Configuration();
        }
        $was_block_by_date =   $this->checkDate($configuration->locked_tenant);
        // if ($was_block_by_date
        if(!$configuration->locked_tenant && $was_block_by_date){
            $configuration->locked_tenant = $was_block_by_date;
            $configuration->save();
            ConfigurationController::clearCache();
        }
        // }
        if ($configuration->isLockedTenant()) {
            // abort(403);
            return response()->view('errors.403', [
                'message' => 'El acceso al sistema está bloqueado. Por favor, contacte con el administrador.'
            ], 403)->withHeaders([
                'Cache-Control' => 'no-cache, must-revalidate'
            ]);
        }


        return $next($request);
    }

    function checkDate($is_locked)
    {

        $tenant = TenancyFacade::tenant();

        $blocked = false;
        if ($tenant && !$is_locked) {
            $tenantId = $tenant->id;
            $hostname = Hostname::where('website_id', $tenantId)->first();
            $client = Client::where('hostname_id', $hostname->id)->first();
            if ($client) {
                if ($client->end_billing_cycle && Carbon::parse($client->end_billing_cycle)->format('Y-m-d') <= Carbon::now()->format('Y-m-d')) {
                    $client->locked_tenant = 1;
                    $client->save();
                    $blocked = true;
                }
            }
        }
        return $blocked;
    }
}
