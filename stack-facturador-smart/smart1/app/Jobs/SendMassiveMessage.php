<?php

namespace App\Jobs;

use App\Http\Controllers\Tenant\WhatsappController;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\MassiveMessage;
use App\Models\Tenant\MassiveMessageDetail;
use App\Models\Tenant\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Environment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SendMassiveMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $detail;
    protected $website;
    protected $massiveMessageId;
    protected $ids;
    protected $company;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($massiveMessageId, $ids, Website $website)
    {
        $company = Company::first();
        $this->company = $company;
        $this->massiveMessageId = $massiveMessageId;
        $this->website = $website;
        $this->ids = $ids;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Tenant handling
            $tenancy = app(Environment::class);
            $tenancy->tenant($this->website);

            $massiveMessage = MassiveMessage::findOrFail($this->massiveMessageId);
            Log::info('Línea 59 - Massive message found', ['massive_message_id' => $this->massiveMessageId]);
            // Procesar los detalles en chunks de 100
            $persons =Person::where('type', 'customers')
            ->whereNotNull('telephone');
            if(is_array($this->ids)){
                $persons->whereIn('id', $this->ids);
            }else{
                $persons->where('name', 'like', '%' . $this->ids . '%')
                ->orWhere('number', 'like', '%' . $this->ids . '%');
            }
                $persons->chunk(100, function ($persons) use ($massiveMessage) {
                    foreach ($persons as $person) {
                        try {
                            Log::info('Línea 72 - Preparing message', ['person_id' => $person->id]);
                            $detail = new MassiveMessageDetail();
                            $detail->massive_message_id = $massiveMessage->id;
                            $detail->person_id = $person->id;
                            $detail->status = 'pending';
                            $detail->attempts = 0;
                            $detail->last_attempt_at = now();
                            $message = $this->prepareMessage($massiveMessage->body, $person);
                            Log::info('Línea 80 - Message prepared', ['message' => $message]);
                            $detail->message = $message;
                            $detail->save();
                            // Enviar el mensaje
                            // Esperar un tiempo aleatorio entre 750ms y 1860ms antes de enviar
                            $sleepTime = rand(1000, 2600);
                            usleep($sleepTime * 1000);
                            $sent = $this->sendMessage($message, $person->telephone);

                            // Actualizar estado

                            $detail->status = $sent ? 'sent' : 'failed';
                            $detail->attempts += 1;
                            $detail->last_attempt_at = now();
                            $detail->save();
                            if ($sent) {
                                Log::info('Línea 67 - Message sent successfully', ['detail_id' => $detail->id]);
                            } else {
                                Log::error('Línea 67 - Message sent failed', ['detail_id' => $detail->id]);
                            }
                        } catch (\Exception $e) {
                            $detail->status = 'failed';
                            $detail->attempts += 1;
                            $detail->last_attempt_at = now();
                            $detail->error_message = $e->getMessage();
                            $detail->save();

                            Log::error('Línea 75 - Failed to send individual message', [
                                'detail_id' => $detail->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                });

            Log::info('Línea 82 - Finished processing all chunks');
        } catch (\Exception $e) {
            Log::error('Línea 84 - Fatal error processing massive message', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function prepareMessage(string $messageTemplate, Person $person): string
    {
        $name = $person->name;
        $type = optional($person->person_type)->description;
        $observation = $person->observation;
        $seller_name = optional($person->seller)->name;
        $variables = [
            'nombre' => $name ?? '-',
            'tipo' => $type ?? '-',
            'observacion' => $observation ?? '-',
            'vendedor' => $seller_name ?? '-'
        ];
        $message = $messageTemplate;
        foreach ($variables as $key => $value) {
            $message = str_replace("{{{$key}}}", $value, $message);
        }
        return $message;
    }

    private function sendMessage(string $message, string $number): bool
    {
        try {
            $request = new Request([
                'appkey' => $this->company->gekawa_1,
                'authkey' => $this->company->gekawa_2,
                'to' => "+51" . $number,
                'message' => $message,
                'gekawa_url' => $this->company->gekawa_url,
            ]);
            $response = (new WhatsappController)->sendWhatsappMessageSimple($request);
            $data = $response->getData();
            if ($data->success) {
                return true;
            }
            Log::error('Línea 156 - Failed to send individual message', [
                'error' => $data
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Línea 135 - Failed to send individual message', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
