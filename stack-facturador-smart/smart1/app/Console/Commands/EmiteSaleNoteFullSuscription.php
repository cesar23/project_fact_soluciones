<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant\Voided;
use Illuminate\Support\Facades\DB;
use App\CoreFacturalo\Facturalo;
use App\Http\Controllers\Tenant\SaleNoteController;
use App\Http\Controllers\Tenant\SaleNotePaymentController;
use App\Http\Requests\Tenant\SaleNotePaymentRequest;
use App\Models\Tenant\SaleNotePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class EmiteSaleNoteFullSuscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emite:sale-note-full-suscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Emite las notas de credito de los planes de suscripcion';

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
        Carbon::setLocale('es');
        Carbon::setLocale('Spanish_Peru');
        setlocale(LC_ALL, 'Spanish_Peru');
        $this->info('The command was started');
        $configuration = DB::connection('tenant')->table('configurations')->where('id', 1)->first();
        $full_suscription_list_type = $configuration->full_suscription_list_type;

        if (!$full_suscription_list_type) {
            $this->info('El tipo de suscripcion no esta activo');
            return;
        }

        try {
            DB::connection('tenant')->beginTransaction();
            $today = Carbon::now()->format('Y-m-d');
            $users_rel_suscription_plans = DB::connection('tenant')->table('user_rel_suscription_plans')->where('is_individual', true)->get();
            foreach ($users_rel_suscription_plans as $user_rel_suscription_plan) {
                $cat_period_id = $user_rel_suscription_plan->cat_period_id;
                $cat_period = DB::connection('tenant')->table('cat_periods')->where('id', $cat_period_id)->first();
                $type_period = $cat_period->period;
                $customer_id = $user_rel_suscription_plan->parent_customer_id;
                $full_suscription_credit = DB::connection('tenant')->table('person_full_suscription_credit')->where('person_id', $customer_id)->first();
                $credit_amount = 0;
                if($full_suscription_credit){
                    $credit_amount = $full_suscription_credit->amount;
                }
                $user_rel_suscription_plan_id = $user_rel_suscription_plan->id;
                $quantity_period = $user_rel_suscription_plan->quantity_period;
                $sale_notes = DB::connection('tenant')->table('sale_notes')->where('customer_id', $customer_id)->where('user_rel_suscription_plan_id', $user_rel_suscription_plan_id)->count();
                $last_sale_note = DB::connection('tenant')->table('sale_notes')->select('id', 'date_of_issue')->where('customer_id', $customer_id)->where('user_rel_suscription_plan_id', $user_rel_suscription_plan_id)->orderBy('date_of_issue', 'desc')->first();
                if(!$last_sale_note){
                    continue;
                }
                Auth::loginUsingId($last_sale_note->user_id);
                if($last_sale_note->date_of_issue == $today){
                    continue;
                }
                if ($sale_notes < $quantity_period) {
                    $date_of_issue = Carbon::parse($last_sale_note->date_of_issue);
                    if ($type_period == 'Y') {
                        $date_of_issue->addYear();
                    } else {
                        $date_of_issue->addMonth();
                    }
                    $date_of_due = $date_of_issue->clone();
                    if($type_period == 'Y'){
                        $date_of_due->addYear();
                    }else{
                        $date_of_due->addMonth();
                    }

                    if ($date_of_issue->format('Y-m-d') == $today) {

                        $request = new Request();
                        $id = $last_sale_note->id;
                        $request->merge([
                            'id' => $id,
                            'is_from_suscription' => true,
                            'date_of_issue' => $date_of_issue->format('Y-m-d'),
                            'date_of_due' => $date_of_issue->format('Y-m-d')
                        ]);
                        $payment_method_type_id = DB::connection('tenant')->table('payment_method_types')->where('description', 'like', '%efectivo%')->first();
                        $response = (new SaleNoteController())->duplicate($request);
                        if ($response['success']) {
                            $data = $response['data'];
                            $sale_note_id = $data['id'];
                            if($credit_amount > 0 && $payment_method_type_id){
                                $request_body = [
                                    'sale_note_id' => $sale_note_id,
                                    'payment' => $credit_amount,
                                    'payment_destination_id' => 'cash',
                                    'payment_method_type_id' => $payment_method_type_id->id,
                                    'date_of_payment' => $date_of_issue->format('Y-m-d'),
                                    'user_id' => $last_sale_note->user_id
                                ];
                                $response = (new SaleNotePaymentController())->storeFullSuscriptionPayment(new SaleNotePaymentRequest($request_body));
                                if($response['success']){
                                    DB::connection('tenant')->table('person_full_suscription_credit')->where('person_id', $customer_id)->update([
                                        'amount' => 0
                                    ]);
                                }
                            }
                            DB::connection('tenant')->table('user_rel_suscription_plans')->where('id', $user_rel_suscription_plan_id)->update([
                                'sale_notes' => DB::raw("CONCAT(sale_notes, ',', $sale_note_id)")
                            ]);
                        }
                    }
                }
            }

            DB::connection('tenant')->commit();
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->error('Error al emitir las notas de credito de los planes de suscripcion');
            return;
        }

        $this->info("Emisión de notas de venta de planes de suscripción, realizado con éxito");
    }
}
