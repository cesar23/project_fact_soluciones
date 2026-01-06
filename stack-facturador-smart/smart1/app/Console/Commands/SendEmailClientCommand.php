<?php

namespace App\Console\Commands;

use App\Http\Controllers\Tenant\DocumentController;
use App\Http\Controllers\Tenant\EmailController;
use App\Mail\Tenant\DocumentEmail;
use Illuminate\Console\Command;
use App\Traits\CommandTrait;
use App\Models\Tenant\{
    Company,
    Configuration,
    Document
};
use Illuminate\Support\Facades\Log;

class SendEmailClientCommand extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:send-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar comprobantes pdf, xml y cdr al cliente';

    /**
     * Create a new command instance.
     *
     * @return void
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
        if (Configuration::firstOrFail()->cron) {
            if ($this->isOffline()) {
                $this->info('Offline service is enabled');

                return;
            }
            $company = Company::firstOrFail();
            Document::query()
                ->where('sent_it_email', 0)
                ->whereIn('state_type_id', ['05'])
                ->where('soap_type_id', '02')
                ->join('persons', 'documents.customer_id', '=', 'persons.id')
                ->whereNotNull('persons.email')
                ->where('persons.email', '!=', '')
                ->select('documents.*', 'persons.email')
                ->chunk(100, function ($documents) use ($company) {
                    foreach ($documents as $document) {
                        if(!$document->email) {
                            continue;
                        }
                        $mailable = new DocumentEmail($company, $document);
                        $email = $document->email;
                        try {
                            $send_it =    EmailController::SendMail($email, $mailable, $document->id, 'document');
                            if ($send_it) {
                                $document->update(['sent_it_email' => 1]);
                            }
                        } catch (\Exception $e) {
                            $this->error("Error sending email to {$email}");
                            $this->error($e->getMessage());
                        }
                    }
                });
        } else {
            $this->info('The crontab is disabled');
        }

        $this->info('The command is finished');
    }
}
