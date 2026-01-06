<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\CommandTrait;
use App\Traits\OfflineTrait;
use App\Models\Tenant\{
    Dispatch,
    Document,
};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RejectedDocumentOutOfDate extends Command
{
    use CommandTrait, OfflineTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reject:out-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cambia a rechazado los documentos/guias que estan fuera de fecha';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    protected  $days_cpe = 3;
    protected  $days_dispatch = 1;
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $company = Company::active();
        try {

            DB::connection('tenant')->transaction(function () {
                $dateNow = Carbon::now()->startOfDay(); 
                $formattedDateNow = $dateNow->format('Y-m-d');
                Document::whereIn('state_type_id', ['01'])
                    ->where('date_of_issue', '<', $dateNow->subDays($this->days_cpe)->format('Y-m-d'))
                    ->chunk(100, function ($documents) {
                        foreach ($documents as $document) {
                            $document->state_type_id = '09';
                            $document->save();
                        }
                    });
                Dispatch::whereIn('state_type_id', ['01'])
                    ->where('date_of_issue', '!=', $formattedDateNow)
                    ->chunk(100, function ($dispatches) {
                        foreach ($dispatches as $dispatch) {
                            $dispatch->state_type_id = '09';
                            $dispatch->message_ticket = 'Documento fuera de fecha';
                            $dispatch->save();
                        }
                    });
            });


            $this->info('The command is finished');
        } catch (\Exception $e) {
            $this->info('Error: ' . $e->getMessage());
        }
    }
}
