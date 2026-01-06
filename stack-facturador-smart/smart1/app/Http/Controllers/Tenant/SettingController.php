<?php

namespace App\Http\Controllers\Tenant;

use Auth;

use Illuminate\View\View;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use App\Models\Tenant\UnitMeasure;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\ColumnsToReport;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use App\Http\Requests\Tenant\UnitMeasureRequest;
use App\Http\Requests\Tenant\ColumnsToReportRequest;
/**
 * Class SettingController
 *
 * @package App\Http\Controllers\Tenant
 * @mixin Controller
 */
class SettingController extends Controller
{
    /**
     * @return Factory|Application|View
     */
    public function listBanks()
    {
        return view('tenant.settings.list_banks');
    }

    /**
     * @return Factory|Application|View
     */
    public function listAccountBanks()
    {
        return view('tenant.settings.list_account_banks');
    }
    public function documentNames()
    {
        return view('tenant.settings.document_names');
    }
    public function YaplePlinQr(){

        return view('tenant.settings.yape_plin_qr');
    }

    public function pdfAdditionalInfo()
    {
        return view('tenant.settings.pdf_additional_info');
    }
    
    public function document_quotations()
    {
        return view('tenant.settings.document_quotations');
    }
    /**
     * @return Factory|Application|View
     */
    public function listCurrencies()
    {
        return view('tenant.settings.list_currencies');
    }

    /**
     * @return Factory|Application|View
     */
    public function listCards()
    {
        return view('tenant.settings.list_cards');
    }

    /**
     * @return Factory|Application|View
     */
    public function listPlatforms()
    {
        return view('tenant.settings.list_platforms');
    }
    public function listStateDeliveries()
    {
        return view('tenant.settings.list_state_deliveries');
    }

    public function listStateTechnicalServices()
    {
        return view('tenant.settings.list_state_technical_services');
    }

    /**
     * @return Factory|Application|View
     */
    public function listAttributes()
    {
        return view('tenant.settings.list_attributes');
    }

    /**
     * @return Factory|Application|View
     */
    public function listDetractions()
    {
        return view('tenant.settings.list_detractions');
    }

    /**
     * @return Factory|Application|View
     */
    public function listUnits()
    {
        return view('tenant.settings.list_units');
    }
    public function storeUnits(UnitMeasureRequest $request)
    {

        $data = UnitMeasure::firstOrNew(['code' => $request->code]);
        $data->fill($request->all());
        $data->save();
        return response()->json([
            "success" => true,
            "message" => "Se guardo con Exito",
            "data" => $data
        ],200);

        // return [

        // ];
    }
    public function listUnitsPdf()
    {
        return view('tenant.settings.list_units', ['pdf' => true]);
    }
    /**
     * @return Factory|Application|View
     */
    public function listPaymentMethods()
    {
        return view('tenant.settings.list_payment_methods');
    }

    public function listAgenciesTransport(){
        return view('tenant.settings.list_agencies_transport');
    }
    /**
     * @return Factory|Application|View
     */
    public function listIncomes()
    {
        return view('tenant.settings.list_incomes');
    }

    /**
     * @return Factory|Application|View
     */
    public function listPayments()
    {
        return view('tenant.settings.list_payments');
    }

    /**
     * @return Factory|Application|View
     */
    public function listVouchersType()
    {
        return view('tenant.settings.list_vouchers_type');
    }

    /**
     * @return Factory|Application|View
     */
    public function listReports()
    {
        $configuration = Configuration::first();
        return view('tenant.reports.list', compact('configuration'));
    }

    /**
     * @return Factory|Application|View
     */
    public function listTransferReasonTypes()
    {
        return view('tenant.settings.list_transfer_reason_types');
    }

    /**
     * @return Factory|Application|View
     */
    public function indexSettings()
    {
        /** @var User $user */
        $user = Auth::user();
        $companyMenu = $user->levels->firstWhere('value', 'configuration_company');
        $visualMenu = $user->levels->firstWhere('value', 'configuration_visual');
        $advanceMenu = $user->levels->firstWhere('value', 'configuration_advance');

        return view('tenant.settings.list_settings', compact(
            'user',
            'companyMenu',
            'visualMenu',
            'advanceMenu'
        ));
    }

    /**
     * @return Factory|Application|View
     */
    public function listExtras()
    {
        // vista blade no vue
        $configuration = Configuration::first();
        return view('tenant.settings.list_extras')->with('apk_url', $configuration->apk_url);
    }

    /**
     * Lee o Guarda
     * @param ColumnsToReportRequest $request
     *
     * @return array
     */
    public function getColumnsToDatatable(ColumnsToReportRequest  $request)
    {

        $user = \Auth::user();
        $user_id =  (null !== $user) ? $user->id : 0;
        $report = $request->report;
        $columns = $request->columns;
        $updated = (bool)$request->updated;

        $cols =  ColumnsToReport::where([
            'user_id' => $user_id,
            'report' => $report,
        ])->first();

        if (empty($cols)) {
            // Se crea una nueva por que no existe
            $cols =  new ColumnsToReport([
                'user_id' => $user_id,
                'report' => $report,
                'columns' => $columns,

            ]);
            $cols->save();
        }
        $return = [
            'user_id' => $user_id,
            'report' => $report,
            'columns' => $columns,
            'updated' => $updated,
        ];
        if ($updated !== false) {
            $cols->columns = $columns;
            $cols->push();
            $return['saved'] = 1;
        }
        $currencCol = $cols->columns;
        $currencColDeb = (array)$cols->columns;
        $orgCOls = $request->columns;

        foreach ($columns as $index => $column) {
            // Si existe una nueva columna, se envia de regreso para prevenir error en rendering
            if (isset($currencColDeb[$index])) {
                $currentRow = (array)$currencColDeb[$index];
                $currencCol->{$index} = (isset($currentRow['title'])) ? $currentRow : $orgCOls[$index];
            } else {
                if (isset($column['title'])) {
                    $currencCol->{$index} = $column;
                } else {
                    // Si la columna nueva no existe
                    $orgCOls = $request->columns;
                    $currencCol->{$index} = $orgCOls[$index];
                }
            }
        }
        $return['columns'] = $currencCol;
        return $return;
    }
}
