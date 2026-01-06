<?php

namespace App\Console\Commands;

use App\Http\Controllers\Tenant\DocumentController;
use App\Http\Controllers\Tenant\WhatsappController;
use App\Http\Requests\Tenant\DocumentEmailRequest;
use App\Models\Tenant\Company;
use App\Models\Tenant\DocumentRecurrenceItem;
use App\Models\Tenant\Person;
use App\Models\Tenant\SaleNote;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;

/**
 * Class RecurrencyDocumentCommand
 *
 * @package App\Console\Commands
 * @mixin Command
 */
class RecurrencyDocumentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurrency:documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recurrencia de documentos';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $this->info('Emitiendo documentos recurrentes');
        DB::connection('tenant')->transaction(function () {
            // Log::info('Emitiendo documentos recurrentes');
            $today = Carbon::now()->format('Y-m-d');

            $time_todays = Carbon::now()->format('H:i');

            $document_recurrence_items = DocumentRecurrenceItem::where('emission_date', $today)
                ->where('emission_time', $time_todays)
                ->where('emitted', false)
                    ->get();
            foreach ($document_recurrence_items as $document) {
                try {
                    $document_recurrence = $document->document_recurrence;
                    $document_to_emit = $document_recurrence->document;
                    $document_id = $document_to_emit->id;
                    $customer_id = $document_to_emit->customer_id;
                    $customer = Person::find($customer_id);
                    $customer_mail = $customer->email;
                    $phone = $customer->telephone;
                    $res = (new DocumentController())->duplicate($document_id);
                    if ($res['success']) {
                        $document->emitted = true;
                        $document->save();
                        // Log::info(json_encode($res['documen']));
                        if ($customer_mail) {
                            $request_email = new DocumentEmailRequest();
                            $request_email->replace(
                                [
                                    'document_id' => $res['data']['document']['id'],
                                    'customer_email' => $customer_mail
                                ]
                            );
                            try {
                                $res_email = (new DocumentController())->email($request_email);
                                if ($res_email['success']) {
                                    $document->email_sent = true;
                                    $document->save();
                                    
                                }
                            } catch (Exception $e) {
                                // Log::error("Error al enviar email: {$document->id}");
                            }
                        }
                        if ($phone) {
                            $message = "Se ha emitido el documento recurrente {$document_to_emit->series}-{$document_to_emit->number}";
                            $file_url = $document_to_emit->download_external_pdf;
                            $company = Company::active();
                            $gekawa_1 = $company->gekawa_1;
                            $gekawa_2 = $company->gekawa_2;
                            if ($gekawa_1 && $gekawa_2) {
                                $request_whatsapp = new Request();

                                $request_whatsapp->replace(
                                    [
                                        'appkey' => $gekawa_1,
                                        'authkey' => $gekawa_2,
                                        'to' => $phone,
                                        'message' => $message,
                                        'file' => $file_url,
                                    ]
                                );
                                try {
                                    $res_whatsapp = (new WhatsappController())->sendWhatsappMessage($request_whatsapp);
                                    if ($res_whatsapp['success']) {
                                        $document->whatsapp_sent = true;
                                        $document->save();
                                    }
                                } catch (Exception $e) {
                                    Log::error("Error al enviar whatsapp: {$document->id}");
                                }
                            } else {
                                $request_whatsapp = new Request();
                                $request_whatsapp->replace(
                                    [
                                        'id' => $res['data']['document']['id'],
                                        'type_id' => $res['FACT'],
                                        'customer_telephone' => $phone,
                                        'numero' => $phone,
                                        'mensaje' => $message,
                                    ]
                                );
                                try {
                                    $res_whatsapp = (new WhatsappController())->sendwhatsapp($request_whatsapp);
                                    if ($res_whatsapp['success']) {
                                        $document->whatsapp_sent = true;
                                        $document->save();
                                    }
                                } catch (Exception $e) {
                                    Log::error("Error al enviar whatsapp: {$document->id}");
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    Log::error("Error al emitir documento: {$document->id}");
                    Log::error($e);
                }
            }
        });
        $this->info("The command is finished");
    }




    // return [
    //     'success' => true,
    //     'record' => $record
    // ];


}
