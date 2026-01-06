<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Report\Exports\ToPayExport;
use Modules\Report\Exports\ToPaySExport;
use Modules\Report\Exports\ToPayDetailExport;
use Modules\Report\Exports\ToPayDetailAllExport;
use Illuminate\Http\Request;
use App\Models\Tenant\Company;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Person;
use Carbon\Carbon;
use Modules\Finance\Helpers\ToPay;
use Modules\Finance\Traits\ToPayTrait;
use App\Models\Tenant\BankAccount;

class ReportToPayController extends Controller
{

    use ToPayTrait;

    public function excel_s(Request $request)
    {
        $records = ToPay::getToPay($request->all());
        $records = $this->transformRecordsToPayEspecial($records);

        $company = Company::first();
        $toPayExport = new ToPaySExport();
        $toPayExport
            ->company($company)
            ->records($records);
        return $toPayExport->download('Reporte_Cuentas_Por_Pagar_' . Carbon::now() . '.xlsx');
    }

    public function excel(Request $request)
    {
        $records = ToPay::getToPay($request->all());

        $company = Company::first();
        $toPayExport = new ToPayExport();
        $toPayExport
            ->company($company)
            ->records($records);
        return $toPayExport->download('Reporte_Cuentas_Por_Pagar_' . Carbon::now() . '.xlsx');
    }

    public function excel_detail(Request $request)
    {
        $records = ToPay::getToPay($request->all());
        $company = Company::first();
        $toPayExport = new ToPayDetailExport();
        $toPayExport
            ->company($company)
            ->records($records);
        return $toPayExport->download('Reporte_Cuentas_Por_Pagar_Detallado_' . Carbon::now() . '.xlsx');
    }

    public function excel_detail_2(Request $request)
    {
        $records = ToPay::getToPay($request->all());
        $supplier_id = $request->supplier_id;
        $supplier = null;
        $company = Company::first();
        $establishment_id = Establishment::find(auth()->user()->establishment_id)->id;
        if($supplier_id){
            $supplier = Person::find($supplier_id);
        }

        $pdf = Pdf::loadView('tenant.reports.to_pay.report_excel_detail_2', [
            'records'=> $records,
            'company' => $company,
            'establishment_id' => $establishment_id,
            'supplier' => $supplier
        ])
            ->setPaper('a4', 'landscape');
        return $pdf->stream('Reporte_Cuentas_Por_Pagar_Detallado_' . Carbon::now() . '.pdf');
    }

    public function pdf_all(Request $request)
    {
        // Configurar límites de memoria más conservadores
        ini_set('memory_limit', '2048M');
        set_time_limit(600); // 10 minutos

        // Habilitar garbage collection agresivo
        gc_enable();
        // Obtener parámetros
        $supplier_id = $request->supplier_id;
        $supplier = null;
        if($supplier_id){
            $supplier = Person::select('id', 'name')->find($supplier_id);
        }

        // Cargar datos mínimos de empresa y establecimiento
        $company = Company::select('id', 'name', 'number')->first();
        $establishment_id = auth()->user()->establishment_id;

        // Usar DashboardView para obtener la query base igual que getUnpaidFilterUserEspecial
        $query = (new \Modules\Dashboard\Helpers\DashboardView())->getToPayFilterUserEspecial($request->all())
            ->orderBy('date_of_issue', 'asc');

        $processedRecords = collect();
        $processedCount = 0;

        // Procesar en chunks más pequeños
        $query->chunk(500, function ($records) use (&$processedRecords, &$processedCount) {
            $transformedRecords = $this->transformRecords3OptimizedForPdf($records);

            // Filtrar y procesar solo registros con saldo > 0
            $filteredRecords = $transformedRecords->filter(function ($record) {
                return $record && isset($record['total_to_pay']) && $record['total_to_pay'] > 0;
            });

            $processedRecords = $processedRecords->merge($filteredRecords);
            $processedCount += $records->count();

            // Liberar memoria cada 1000 registros
            if ($processedCount % 1000 == 0) {
                gc_collect_cycles();
            }
        });

        // Liberar memoria antes de generar PDF
        gc_collect_cycles();

        $pdf = Pdf::loadView('tenant.reports.to_pay.report_pdf_all', [
            'records'=> $processedRecords,
            'company' => $company,
            'establishment_id' => $establishment_id,
            'supplier' => $supplier
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

        return $pdf->stream('Reporte_Cuentas_Por_Pagar_Detallado_' . Carbon::now() . '.pdf');
    }

    public function excel_all(Request $request)
    {
        // Usar la misma lógica que pdf_all para obtener datos completos
        $allRecords = collect();
        $query = (new \Modules\Dashboard\Helpers\DashboardView())->getToPayFilterUserEspecial($request->all())
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

        $toPayExport = new ToPayDetailAllExport();
        $toPayExport
            ->company($company)
            ->records($allRecords);
        return $toPayExport->download('Reporte_Cuentas_Por_Pagar_Detallado_Todas_' . Carbon::now() . '.xlsx');
    }
}