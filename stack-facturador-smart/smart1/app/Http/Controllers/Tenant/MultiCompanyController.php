<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\System\Client;
use App\Models\System\User;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\User as TenantUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Facades\TenancyFacade;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;

/**
 * Class CompanyController
 *
 * @package App\Http\Controllers\Tenant
 * @mixin  Controller
 */
class MultiCompanyController extends Controller
{

    public function index()
    {
        return view('tenant.multi_companies.index');
    }
    public function changeDefaultCompany(Request $request, $website_id)
    {
        $default = $request->default;

        DB::transaction(function () use ($website_id, $default) {
            Company::where('website_id', $website_id)
                ->update(['default' => $default]);

            Company::where('website_id', '!=', $website_id)
                ->update(['default' => false]);
        });

        return [
            'success' => true,
            'message' => 'Empresa predeterminada cambiada correctamente'
        ];
    }
    public function removeCompany($website_id)
    {

        if ($website_id) {
            DB::connection('tenant')
                ->table('multi_companies')
                ->where('website_id', $website_id)
                ->delete();
            Company::where('website_id', $website_id)
                ->where('id', '!=', 1)
                ->delete();
            return [
                'success' => true,
                'message' => 'Datos eliminados correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'No se pudo eliminar la configuración, por favor verifique que todas las empresas tengan un dominio asignado'
            ];
        }
    }
    public function saveCompany(Request $request)
    {
        $company_id = $request->company_id;
        $host = $request->getHost();
        $partesDelHost = explode('.', $host);
        $subdominio = $partesDelHost[0];
        $client = Client::where('id', $company_id)->first();
        $hostname = Hostname::where('id', $client->hostname_id)->first();
        $website = Website::where('id', $hostname->website_id)->first();
        $hostname_id = $hostname != null ? $hostname->id : null;
        $website_id = $website != null ? $website->id : null;
        if ($hostname_id && $website_id) {
            DB::connection('tenant')
                ->table('multi_companies')
                ->insert([
                    'client_id' => $company_id,
                    'hostname_id' => $hostname_id,
                    'website_id' => $website_id,
                ]);
            $tenancy = app(Environment::class);
            $tenancy->tenant($client->hostname->website);
            $company = Company::without(['identity_document_type'])
                ->first();
            $company_to_insert = $company->toArray();
            $company_to_insert['updated_at'] = date('Y-m-d H:i:s');
            $company_to_insert['id'] = null;
            $company_to_insert['document_number'] = null;
            $company_to_insert['not_principal'] = true;
            $company_to_insert['website_id'] = $website_id;
            $subDom = $subdominio;
            $uuid = config('tenant.prefix_database') . '_' . $subDom;
            $this->restoreTenant($uuid);
            $company = DB::connection('tenant')
                ->table('companies')
                ->insert($company_to_insert);
            return [
                'success' => true,
                'message' => 'Datos guardados correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'No se pudo guardar la configuración, por favor verifique que todas las empresas tengan un dominio asignado'
            ];
        }
    }
    public function saveCompanies(Request $request)
    {
        DB::connection('tenant')
            ->table('multi_companies')
            ->delete();

        $companies = $request->companies;
        $host = $request->getHost();

        $partesDelHost = explode('.', $host);

        $subdominio = $partesDelHost[0];
        $fail = false;
        $website_ids = [];
        foreach ($companies as $company_id) {
            $found_company = Client::where('id', $company_id)->first();
            $hostname = DB::table('hostnames')->where('id', $found_company->hostname_id)->first();
            $website = DB::table('websites')->where('id', $hostname->website_id)->first();
            $hostname_id = $hostname != null ? $hostname->id : null;
            $website_id = $website != null ? $website->id : null;
            if ($hostname_id && $website_id) {
                $website_ids[] = $website_id;

                DB::connection('tenant')
                    ->table('multi_companies')
                    ->insert([
                        'client_id' => $company_id,
                        'hostname_id' => $hostname_id,
                        'website_id' => $website_id,
                    ]);

                $tenancy = app(Environment::class);
                $tenancy->tenant($found_company->hostname->website);

                $company = Company::without(['identity_document_type'])
                    ->first();
                $company_to_insert = $company->toArray();
                $company_to_insert['updated_at'] = date('Y-m-d H:i:s');
                $company_to_insert['id'] = null;
                $company_to_insert['document_number'] = null;
                $company_to_insert['not_principal'] = true;
                $company_to_insert['website_id'] = $website_id;
                $subDom = $subdominio;
                $uuid = config('tenant.prefix_database') . '_' . $subDom;
                $this->restoreTenant($uuid);
                $company = DB::connection('tenant')
                    ->table('companies')
                    ->insert($company_to_insert);
            } else {
                $fail = true;
            }
        }
        $company = Company::active();
        $website_ids[] = $company->website_id;
        $companies_to_delete = DB::connection('tenant')
            ->table('companies')
            ->whereNotIn('website_id', $website_ids)
            ->get();
        foreach ($companies_to_delete as $company_to_delete) {
            $company_to_delete = Company::find($company_to_delete->id);
            $company_to_delete->delete();
        }

        $companies_to_delete = DB::connection('tenant')
            ->table('companies')
            ->select('website_id')
            ->groupBy('website_id')
            ->havingRaw('count(*) > 1')
            ->get();
        //borrar las empresas que tengan mas de un registro y solo dejar el primero
        foreach ($companies_to_delete as $company_to_delete) {
            $company_to_delete = Company::where('website_id', $company_to_delete->website_id)
                ->orderBy('id', 'asc')
                ->get();
            $company_to_delete = $company_to_delete->slice(1);
            foreach ($company_to_delete as $company) {
                $company->delete();
            }
        }

        if ($fail) {
            return [
                'success' => false,
                'message' => 'No se pudo guardar la configuración, por favor verifique que todas las empresas tengan un dominio asignado'
            ];
        }
        return [
            'success' => true,
            'message' => 'Datos guardados correctamente'
        ];
    }
    function restoreTenant($uuid)
    {
        $website = Website::where('uuid', $uuid)->first();
        $hostname = Hostname::where('website_id', $website->id)->first();
        $client = Client::where('hostname_id', $hostname->id)->first();
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);
        $company = Company::active();
        $company->website_id = $website->id;
        $company->save();
    }
    public function saveConfiguration(Request $request)
    {
        $conf = $request->configuration;
        $multi_companies = $conf["multi_companies"];
        $configuration = Configuration::first();
        $configuration->multi_companies = $multi_companies;
        $configuration->save();
        ConfigurationController::clearCache();
        if ($configuration->multi_companies == false) {
            TenantUser::query()->update(['company_active_id' => null]);
        }
        return [
            'success' => true,
            'message' => 'Datos guardados correctamente'
        ];
    }
    public function getCompanies()
    {
        $companies = Client::all();
        $select_companies = Company::all();

        return [
            'companies' => $companies,
            'selectCompanies' => $select_companies,
        ];
    }
    public function login(Request $request)
    {
        $password = $request->password;
        $user = User::first();
        $loged = Hash::check($password, $user->password);
        $configuration = Configuration::select('multi_companies')
            ->first();

        return [
            'success' => $loged,
            'configuration' => $configuration,
        ];
    }
}
