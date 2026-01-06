<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Report\Exports\NoPaidExport;
use Illuminate\Http\Request;
use App\Models\Tenant\Company;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Person;
use Carbon\Carbon;
use Modules\Report\Http\Resources\QuotationCollection;
use Modules\Dashboard\Helpers\DashboardView;
use Modules\Finance\Traits\UnpaidTrait;
use Modules\Report\Exports\NoPaidDetail2Export;
use Modules\Report\Exports\NoPaidDetailAllExport;
use Modules\Report\Exports\NoPaidDetailExport;
use Modules\Report\Exports\NoPaidSExport;

class ReportUnpaidController extends Controller
{

    use UnpaidTrait;
    public function excel_s(Request $request)
    {

        $records = (new DashboardView())->getUnpaidFilterUser($request->all())->get();
        $records = $this->transformRecords_especial($records);

        $company = Company::first();
        $noPaidExport = new NoPaidSExport();
        $noPaidExport
            ->company($company)
            ->records($records);
        return $noPaidExport->download('Reporte_Cuentas_Por_Cobrar' . Carbon::now() . '.xlsx');
    }
    public function excel(Request $request)
    {

        $records = (new DashboardView())->getUnpaidFilterUser($request->all())->get();
        $records = $this->transformRecords($records);

        $company = Company::first();
        $noPaidExport = new NoPaidExport();
        $noPaidExport
            ->company($company)
            ->records($records);
        return $noPaidExport->download('Reporte_Cuentas_Por_Cobrar' . Carbon::now() . '.xlsx');
    }

    public function excel_detail(Request $request)
    {

        $records = (new DashboardView())->getUnpaidFilterUser($request->all())->get();
        $records = $this->transformRecords($records,true);
        $company = Company::first();
        $noPaidExport = new NoPaidDetailExport();
        $noPaidExport
            ->company($company)
            ->records($records);
        return $noPaidExport->download('Reporte_Cuentas_Por_Cobrar_Detallado_' . Carbon::now() . '.xlsx');
    }

    public function excel_detail_2(Request $request)
    {

        $records = (new DashboardView())->getUnpaidFilterUser($request->all())->get();
        $customer_id = $request->customer_id;
        $customer = null;
        $records = $this->transformRecords2($records);
        $company = Company::first();
        $establishment_id = Establishment::find(auth()->user()->establishment_id)->id;
        if($customer_id){
            $customer = Person::find($customer_id);
        }
        


        $pdf = Pdf::loadView('tenant.reports.no_paid.report_excel_detail_2', [
            'records'=> $records,
            'company' => $company,
            'establishment_id' => $establishment_id,
            'customer' => $customer
        ])
            ->setPaper('a4', 'landscape');
        return $pdf->stream('Reporte_Cuentas_Por_Cobrar_Detallado_' . Carbon::now() . '.pdf');
    }
    public function pdf_all(Request $request)
    {
        // Configurar límites de memoria más conservadores
        ini_set('memory_limit', '2048M');
        set_time_limit(600); // 10 minutos

        // Habilitar garbage collection agresivo
        gc_enable();

        // Obtener parámetros
        $customer_id = $request->customer_id;
        $customer = null;
        if($customer_id){
            $customer = Person::select('id', 'name')->find($customer_id);
        }

        // Cargar datos mínimos de empresa y establecimiento
        $company = Company::select('id', 'name', 'number')->first();
        $establishment_id = auth()->user()->establishment_id;

        // Limitar la cantidad de registros para evitar problemas de memoria
        $query = (new DashboardView())->getUnpaidFilterUserEspecial($request->all())
            ->orderBy('date_of_issue', 'asc');
            

        $allRecords = collect();
        $processedCount = 0;

        // Procesar en chunks más pequeños
        $query->chunk(500, function ($records) use (&$allRecords, &$processedCount) {
            $transformedRecords = $this->transformRecords3OptimizedForPdf($records);

            // Filtrar y procesar solo registros con saldo > 0
            $filteredRecords = $transformedRecords->filter(function ($record) {
                return $record && isset($record['total_to_pay']) && $record['total_to_pay'] > 0;
            });

            $allRecords = $allRecords->merge($filteredRecords);
            $processedCount += $records->count();

            // Liberar memoria cada 1000 registros
            if ($processedCount % 1000 == 0) {
                gc_collect_cycles();
            }
        });

        // Liberar memoria antes de generar PDF
        gc_collect_cycles();

        $pdf = Pdf::loadView('tenant.reports.no_paid.report_pdf_all', [
            'records'=> $allRecords,
            'company' => $company,
            'establishment_id' => $establishment_id,
            'customer' => $customer
        ])
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => false,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial',
                'dpi' => 72, // Reducir DPI para menos memoria
                'defaultPaperSize' => 'a4',
                'fontSubsetting' => false,
                'chroot' => public_path()
            ]);

        return $pdf->stream('Reporte_Cuentas_Por_Cobrar_Detallado_' . Carbon::now() . '.pdf');
    }
    public function excel_all(Request $request)
    {
        // Usar la misma lógica que pdf_all para obtener datos completos
        $allRecords = collect();
        $query = (new DashboardView())->getUnpaidFilterUserEspecial($request->all())
            ->orderBy('date_of_issue', 'asc');

        // Procesar en chunks para mejor rendimiento
        $query->chunk(500, function ($records) use (&$allRecords) {
            $transformedRecords = $this->transformRecords3OptimizedForPdf($records);

            // Filtrar solo registros con saldo > 0
            $filteredRecords = $transformedRecords->filter(function ($record) {
                return $record && isset($record['total_to_pay']) && $record['total_to_pay'] > 0;
            });

            $allRecords = $allRecords->merge($filteredRecords);
        });

        $company = Company::first();

        $noPaidExport = new NoPaidDetailAllExport();
        $noPaidExport
            ->company($company)
            ->records($allRecords);
        return $noPaidExport->download('Reporte_Cuentas_Por_Cobrar_Detallado_Todas_' . Carbon::now() . '.xlsx');
    }
}
