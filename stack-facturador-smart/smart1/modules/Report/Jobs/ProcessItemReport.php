<?php

namespace Modules\Report\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Hyn\Tenancy\Models\Website;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Tenant\DownloadTray;
use Hyn\Tenancy\Environment;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Models\Tenant\Establishment;
use Modules\Inventory\Exports\InventoryExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Inventory\Models\ItemWarehouse;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Modules\Report\Exports\DocumentExport;
use App\Traits\JobReportTrait;
use Modules\Report\Exports\GeneralItemExport;
use Modules\Report\Exports\GeneralItemExportChunk;
use Modules\Report\Http\Controllers\ReportDocumentController;
use Modules\Report\Http\Controllers\ReportGeneralItemController;
use Throwable;

class ProcessItemReport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use JobReportTrait;
    use StorageDocument;

    public $tray_id;
    public $columns;
    public $filters;
    public $website_id;
    public $timeout = 1800;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $tray_id, int $website_id, array $filters)
    {
        $this->website_id = $website_id;
        $this->tray_id = $tray_id;
        $this->filters = $filters;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            ini_set("memory_limit", "2048M");
            ini_set("max_execution_time", "3600");
            
            $this->showLogInfo("ProcessDocumentReport Start WebsiteId => {$this->website_id}");

            $website = $this->findWebsite($this->website_id);
            if (!$website) {
                throw new \Exception("Website not found for id: {$this->website_id}");
            }

            $tenancy = app(Environment::class);
            $tenancy->tenant($website);

            $download_tray = $this->findDownloadTray($this->tray_id);
            if (!$download_tray) {
                throw new \Exception("Download tray not found for id: {$this->tray_id}");
            }

            $format = 'excel';
            $path = $this->getReportPath($format);
            $filename = $this->getReportFilename($download_tray->module, 'Reporte_Productos');

            if ($format === 'excel') {
                Log::debug("Render excel init");
                
                // Procesar registros en chunks
                $query = app(ReportGeneralItemController::class)->getRecordsItems2($this->filters);
                $document_type_id = $this->filters['document_type_id'];
                $request_apply_conversion_to_pen = $this->filters['apply_conversion_to_pen'];
        
                $generalItemExport = new GeneralItemExportChunk();
                $generalItemExport
                    ->records($query->cursor()) 
                    ->type($this->filters['type'])
                    ->document_type_id($document_type_id)
                    ->request_apply_conversion_to_pen($request_apply_conversion_to_pen);

                Log::debug("Render excel finish");
                Log::debug("Upload excel init");
                
                $generalItemExport->store(
                    DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $filename . '.xlsx', 
                    'tenant',
                    \Maatwebsite\Excel\Excel::XLSX
                );

                Log::debug("Upload excel finish");
            }

            $this->finishedDownloadTray($download_tray, $filename, $path);

        } catch (Throwable $e) {
            Log::error('ProcessDocumentReport Error: ' . $e->getMessage());
            Log::error('ProcessDocumentReport Stack: ' . $e->getTraceAsString());
            throw $e;
        }
    }


    /**
     * The job failed to process.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::error('ProcessItemReport Error: ' . $exception->getMessage());
        Log::error('ProcessItemReport Stack: ' . $exception->getTraceAsString());
    }
}
