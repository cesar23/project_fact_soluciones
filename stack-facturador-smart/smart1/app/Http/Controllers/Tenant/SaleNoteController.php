<?php

namespace App\Http\Controllers\Tenant;

use Exception;
use Mpdf\Mpdf;
use Carbon\Carbon;
use ErrorException;
use GuzzleHttp\Client;
use Mpdf\HTMLParserMode;
use App\Models\Tenant\Cash;
use App\Models\Tenant\Item;
use App\Models\Tenant\User;
use Illuminate\Support\Str;
use App\Traits\OfflineTrait;
use App\Traits\PrinterTrait;
use Illuminate\Http\Request;
use App\Models\Tenant\Kardex;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use App\Models\Tenant\Company;
use Mpdf\Config\FontVariables;
use App\CoreFacturalo\Template;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\GuideFile;
use Modules\Item\Models\ItemLot;
use Mpdf\Config\ConfigVariables;
use App\Models\Tenant\ItemSeller;
use App\Mail\Tenant\SaleNoteEmail;
use App\Models\Tenant\BankAccount;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\CashDocument;
use App\Models\Tenant\SaleNoteItem;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\ItemWarehouse;
use Modules\Restaurant\Models\Orden;
use App\CoreFacturalo\HelperFacturalo;
use App\Models\Tenant\SaleNotePayment;
use Modules\Item\Models\ItemLotsGroup;
use App\Models\Tenant\DispatchSaleNote;
use Modules\Inventory\Models\Warehouse;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\SaleNoteMigration;
use Modules\Document\Traits\SearchTrait;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Sale\Helpers\SaleNoteHelper;
use App\Models\Tenant\CashDocumentCredit;
use App\Models\Tenant\Catalogs\PriceType;
use GuzzleHttp\Exception\RequestException;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\DocumentType;
use Modules\Finance\Traits\FilePaymentTrait;
use Modules\Inventory\Traits\InventoryTrait;
use App\Http\Requests\Tenant\SaleNoteRequest;
use App\Models\Tenant\Catalogs\AttributeType;
use App\Models\Tenant\Catalogs\OperationType;
use App\Models\Tenant\Catalogs\SystemIscType;
// use App\Http\Resources\Tenant\SaleNoteGenerateDocumentResource;
// use App\Models\Tenant\Warehouse;
use App\Models\Tenant\MigrationConfiguration;
use App\Http\Controllers\SearchItemController;
use App\Http\Resources\Tenant\SaleNoteResource;
use App\CoreFacturalo\Requests\Inputs\Functions;
use App\Http\Resources\Tenant\SaleNoteResource2;
use Modules\Document\Models\SeriesConfiguration;
use App\Http\Resources\Tenant\SaleNoteCollection;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\ChargeDiscountType;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use Modules\Suscription\Models\Tenant\SuscriptionPayment;
use App\CoreFacturalo\Requests\Inputs\Common\EstablishmentInput;
use App\Exports\SaleNoteExport;
use App\Exports\SaleNoteTransportFormatExport;
use App\Http\Requests\Tenant\SaleNotePaymentRequest;
use App\Http\Resources\Tenant\PersonLiteCollection;
use App\Http\Resources\Tenant\SaleNoteToDeleteCollection;
use App\Mail\Tenant\IntegrateSystemEmail;
use App\Models\System\Client as SystemClient;
use App\Models\Tenant\Catalogs\UnitType;
use App\Models\Tenant\ConditionBlockPaymentMethod;
use App\Models\Tenant\DispatchOrder;
use Modules\Item\Models\ItemProperty;
use App\Models\Tenant\ItemUnitType;
use App\Models\Tenant\ItemWarehousePrice;
use App\Models\Tenant\MessageIntegrateSystem;
use App\Models\Tenant\NoStockDocument;
use App\Models\Tenant\PaymentWithCreditNote;
use App\Models\Tenant\PersonDispatcher;
use App\Models\Tenant\PersonPacker;
use App\Models\Tenant\PlateNumberDocument;
use App\Models\Tenant\ProductionOrder;
use App\Models\Tenant\SaleNoteFee;
use App\Models\Tenant\SaleNoteOrderState;
use App\Models\Tenant\TransportFormat;
use App\Models\Tenant\TransportFormatItem;
use App\Models\Tenant\WarrantyDocument;
use App\Services\DebtReversalService;
use App\Traits\Auditable;
use App\Traits\PaymentWithNote;
use Barryvdh\DomPDF\Facade\Pdf;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\BusinessTurn\Models\SaleNoteTransport;
use Modules\Dispatch\Models\Dispatcher;
use Modules\Hotel\Models\HotelRent;
use Modules\Hotel\Models\HotelRentDocument;
use Modules\Hotel\Models\HotelRentItem;
use Modules\Inventory\Models\InventoryConfiguration;
use Modules\Inventory\Services\ItemCostHistoryService;
use Modules\Restaurant\Traits\OrderTrait;

class SaleNoteController extends Controller
{

    use FinanceTrait;
    use InventoryTrait;
    use SearchTrait;
    use StorageDocument;
    use OfflineTrait;
    use FilePaymentTrait;
    use PrinterTrait;
    use OrderTrait;
    use PaymentWithNote;
    protected $sale_note;
    protected $company;
    protected $apply_change;
    protected $document;

    public function changeStateDelivery($sale_note_id, $state_delivery_id)
    {
        $sale_note = SaleNote::find($sale_note_id);
        $sale_note->state_delivery_id = $state_delivery_id;
        $sale_note->save();
        return [
            'success' => true,
            'message' => 'Estado de entrega actualizado'
        ];
    }
    public function deletes(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            $ids = $request->ids;
            foreach ($ids as $id) {
                $document = SaleNote::findOrFail($id);

                //GuideFile
                $document->guide_files()->delete();
                HotelRent::where('document_id', $id)->delete();
                //DocumentPayment
                SaleNotePayment::where('sale_note_id', $id)->delete();
                //Dispatch
                Dispatch::where('reference_sale_note_id', $id)->delete();
                //DocumentFee
                SaleNoteFee::where('sale_note_id', $id)->delete();
                // CashDocument
                CashDocument::where('sale_note_id', $id)->delete();
                // CashDocumentCredit
                CashDocumentCredit::where('sale_note_id', $id)->delete();
                // Kardex
                Kardex::where('sale_note_id', $id)->delete();



                SaleNoteTransport::where('sale_note_id', $id)->delete();
                // SuscriptionPayment
                SuscriptionPayment::where('sale_note_id', $id)->delete();

                $items = SaleNoteItem::where('sale_note_id', $id)->get();
                foreach ($items as $item) {
                    $item->restoreStock();
                    ItemSeller::where('sale_note_item_id', $item->id)->delete();
                    $item->delete();
                }



                $document->inventory_kardex()->delete();
                $document->delete();
            }
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => 'Documentos eliminados con éxito'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function changeStateSaleNoteOrder($id, $state_sale_note_order_id)
    {
        $sale_note_order_state = SaleNoteOrderState::where('sale_note_id', $id)->first();
        if (!$sale_note_order_state) {
            $sale_note_order_state = new SaleNoteOrderState();
            $sale_note_order_state->sale_note_id = $id;
        }
        $sale_note_order_state->state_sale_note_orders_id = $state_sale_note_order_id;
        $sale_note_order_state->save();
        return [
            'success' => true,
            'message' => 'Estado de la orden de venta actualizado'
        ];
    }
    public function excel(Request $request)
    {
        if ($request->column == 'customer_id') {
            $records = SaleNote::where($request->column, '=', $request->value)
                ->latest('id');
        } else if ($request->column == 'date_of_issue') {
            if ($request->end != null) {
                $records = SaleNote::whereBetween($request->column, [$request->value, $request->end])->latest('id');
            } else {
                $records = SaleNote::where($request->column, 'like', "%{$request->value}%")->latest('id');
            }
        } else {
            $records = SaleNote::where($request->column, 'like', "%{$request->value}%")->latest('id');
        }




        if ($request->series) {
            $records = $records->where('series', 'like', '%' . $request->series . '%');
        }
        $records = $records->get();
        $company = Company::active();
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        return (new SaleNoteExport)
            ->records($records)
            ->company($company)
            ->download('Reporte_Nota_de_Venta_' . Carbon::now() . '.xlsx');
    }
    public function message_whatsapp($document_id)
    {
        $document = SaleNote::find($document_id);
        $message = "Estimd@: *" . $document->customer->name . "* \n";
        $message .= "Informamos que su comprobante electrónico ha sido emitido exitosamente.\n";
        $message .= "Los datos de su comprobante electrónico son:\n";
        $message .= "Razón social: *" . $document->customer->name . "* \n";
        $message .= "Fecha de emisión: *" . \Carbon\Carbon::parse($document->date_of_issue)->format('d-m-Y') . "* \n";
        $message .= "Nro. de comprobante: *" . $document->series . "-" . $document->number . "* \n";
        $message .= "Total: *" . number_format($document->total, 2, ".", "") . "*";
        return [
            "message" => $message,
            "success" => true,
        ];
    }
    public function changePersonPacker($document_id, $person_packer_id)
    {
        $document = SaleNote::find($document_id);
        $document->person_packer_id = $person_packer_id;
        $document->save();
        return [
            'success' => true,
            'message' => 'Empaquetador asignado'
        ];
    }
    public function changePersonDispatcher($document_id, $person_dispatcher_id)
    {
        $document = SaleNote::find($document_id);
        $document->person_dispatcher_id = $person_dispatcher_id;
        $document->save();
        return [
            'success' => true,
            'message' => 'Repartidor asignado'
        ];
    }
    public function index()
    {
        $is_comercial  = auth()->user()->integrate_user_type_id == 2;
        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        $company = Company::select('soap_type_id')->first();
        $soap_company  = $company->soap_type_id;
        $configuration = Configuration::select('ticket_58')->first();
        $sellers = User::where('type', 'seller')->get(); // Obtener lista de vendedores

        return view('tenant.sale_notes.index', compact(
            'soap_company',
            'is_comercial',
            'configuration',
            'is_integrate_system',
            'sellers' // Pasar la lista de vendedores a la vista
        ));
    }



    public function changeStatePayment($sale_note_id, $state_payment_id)
    {
        $sale_note = SaleNote::find($sale_note_id);
        $sale_note->state_payment_id = $state_payment_id;
        $sale_note->save();

        $response = [
            'success' => true,
            'message' => 'Se ha cambiado el estado de pago'
        ];

        // Si el estado es '02' (Aprobado), retornar los datos para enviar el correo
        if ($state_payment_id == '02') {
            $customer = Person::find($sale_note->customer_id);
            $response['send_email'] = true;
            $response['email_data'] = [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email,
                'sale_note_id' => $sale_note->id
            ];
        }

        return $response;
    }

    public function sendStatePaymentEmail(Request $request)
    {
        try {
            $customer_id = $request->input('customer_id');
            $customer_email = $request->input('customer_email');
            $sale_note_id = $request->input('sale_note_id');

            $customer = Person::find($customer_id);
            if (!$customer || !$customer_email) {
                return [
                    'success' => false,
                    'message' => 'No se pudo enviar el correo. Cliente no encontrado o sin email.'
                ];
            }

            $message = MessageIntegrateSystem::getMessage('sale_note.02');
            $mailable = new IntegrateSystemEmail($customer, $message);

            EmailController::SendMail($customer_email, $mailable, $sale_note_id, 6);

            return [
                'success' => true,
                'message' => 'Correo enviado correctamente'
            ];
        } catch (\Exception $e) {
            \Log::error("Error al enviar correo de aprobación de pago: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ];
        }
    }
    public function create($id = null)
    {
        $cashid = null;
        if ($id != null) {
            $salenote = SaleNote::find($id);
            $cash_open = Cash::where('user_id', $salenote->user_id)->where('state', true)->first();
            if ($cash_open != null) {
                $cashid = $cash_open->id;
            }
        } else {
            $cash_open = Cash::where('user_id', User::getUserCashId())->where('state', true)->first();
            if ($cash_open != null) {
                $cashid = $cash_open->id;
            }
        }
        return view('tenant.sale_notes.form', compact('id', 'cashid'));
    }

    public function killDocument($id)
    {
        $sale_note = SaleNote::find($id);
        CashDocument::where('sale_note_id', $id)->delete();
        CashDocumentCredit::where('sale_note_id', $id)->delete();
        Dispatch::where('reference_sale_note_id', $id)->delete();
        HotelRentItem::whereHas('hotelRent', function ($query) use ($id) {
            $query->where('sale_note_id', $id);
        })->delete();
        HotelRentDocument::whereHas('rent', function ($query) use ($id) {
            $query->where('sale_note_id', $id);
        })->delete();
        HotelRent::where('sale_note_id', $id)->delete();
        DispatchSaleNote::where('sale_note_id', $id)->delete();
        Document::where('sale_note_id', $id)->update(['sale_note_id' => null]);
        GuideFile::where('sale_note_id', $id)->delete();
        Kardex::where('sale_note_id', $id)->delete();
        SaleNotePayment::where('sale_note_id', $id)->delete();
        Orden::where('sale_note_id', $id)->delete();
        SuscriptionPayment::where('sale_note_id', $id)->delete();
        $items = SaleNoteItem::where('sale_note_id', $id)->get();
        foreach ($items as $item) {
            $item->restoreStock();
            ItemSeller::where('sale_note_item_id', $item->id)->delete();
            $item->delete();
        }
        $sale_note->inventory_kardex()->delete();

        $sale_note->delete();
        return [
            'success' => true,
            'message' => 'Documento eliminado'
        ];
    }


    public function getToDelete(Request $request)
    {
        $date_start = $request->date_start;
        $date_end = $request->date_end;
        $records = SaleNote::whereBetween('date_of_issue', [$date_start, $date_end])->orderBy('id', 'desc');
        return new SaleNoteToDeleteCollection($records->paginate(50));
    }
    public function sendDataToOtherSite($saleNoteId)
    {
        $dataSend = [
            'sale_note_id' => $saleNoteId,
            'success' => false,
        ];

        if (auth()->user()->type !== 'admin') {
            $dataSend['message'] = 'Solo los administradores pueden realizar esta accion';
            return $dataSend;
        }
        $configuration = Configuration::first();
        if ($configuration->isSendDataToOtherServer() != true) {
            $dataSend['message'] = 'La configuracion no esta habilitada para el envio';
            return $dataSend;
        }


        $migrationConfiguration = MigrationConfiguration::first();

        if ($migrationConfiguration === null || empty($migrationConfiguration->url) || empty($migrationConfiguration->api_key)) {
            $dataSend['message'] = 'No hay datos configurados para la migracion';
            return $dataSend;
        };
        $token = $migrationConfiguration->getApiKey();
        $web = $migrationConfiguration->getUrl();
        /*
        $token = 'TESTING_TOKEN_mmmddasdadasd';
        $web = 'testing.url';
        */

        $alreadySendit = SaleNoteMigration::where([
            'sale_notes_id' => $saleNoteId,
            'success' => 1,
            'url' => $web,
        ])->first();
        // ya se envio, no hacer nada
        if ($alreadySendit !== null) {
            $dataSend['message'] = "Ya se ha enviado al servidor $web. " . $alreadySendit->getNumber();
            return $dataSend;
        };

        $sale_note = SaleNote::find($saleNoteId);

        if ($sale_note === null) {
            $dataSend['message'] = "No se ha encontrado la NV";
            return $dataSend;
        };

        // Hace ping para validar puertos, Implementado para testing de conexion
        $this->pingSite($web);
        $data_note = $sale_note->getDataToApiExport();


        $alreadySendit = new SaleNoteMigration([
            'sale_notes_id' => $saleNoteId,
            'user_id' => auth()->user()->id,
            'success' => 1,
            'url' => $web,
            'data' => json_encode($data_note),
        ]);
        $web = "https://$web";
        $web_Url = "$web/api/sale-note";


        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
        $client = new  Client([
            'base_uri' => $web,
            'verify'          => false,
            'headers'          => $headers,

        ]);

        try {
            $send = [
                'form_params' => $data_note,
                'headers' => $headers,
            ];
            $ype_send = 'POST';
            self::ExtraLog(__FILE__ . "::" . __LINE__ . "  \n Enviando por >$ype_send<  a la url $web_Url \n\n" . __FUNCTION__ . " \n" . json_encode($send) . "\n\n\n\n");
            $response = $client->request($ype_send, $web_Url, $send);
        } catch (RequestException $e) {
            $code = $e->getCode();
            $responsea = $e->getResponse();
            if (empty($responsea)) {
                $dataSend['message'] = 'No se ha obtenido respuesta del sitio ' . $web;
                return $dataSend;
            }
            $responseBodyAsString = $responsea->getBody()->getContents();
            $response = json_decode($responseBodyAsString);
            try {
                if (property_exists($response, 'success')) {
                    $success = $response->success;
                    $alreadySendit->setSuccess();
                    if (property_exists($response, 'data')) {
                        $data = $response->data;
                        if ($success == true) {
                            $alreadySendit
                                ->setSuccess(true)
                                ->setNumber($data->number)
                                ->setRemoteId($data->id);
                        }
                    } else {
                        if (property_exists($response, 'message')) {
                            $message = $response->message;
                            $err_gen = 'NV-GEN-';
                            if ($this->searchInString('SQLSTATE[23000]', $message)) {
                                $err_gen = 'NV-SQL-';
                                if ($this->searchInString('`persons`', $message)) {
                                    $err_gen .= "001";
                                    $dataSend['message'] = 'Problemas insertando datos del cliente. ' . $err_gen;
                                } else {
                                    $err_gen .= "003";
                                    $dataSend['message'] = 'Problemas insertando datos' . $err_gen;
                                }
                            } else {
                                if (
                                    $this->searchInString("Trying to get property 'description' of non-object", $message) &&
                                    $this->searchInString("sale_note_a4.blade.php", $message)
                                ) {
                                    $err_gen = "NV-FILE-001";
                                    $dataSend['message'] = 'Problemas generando los atributos del item en el pdf ' . $err_gen;
                                    $err_gen .= '\n\n\nPosiblemente sea el atributo en parte del siguiente codigo   @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                    @endforeach
                @endif \n\n\n';
                                } else {
                                    $err_gen .= "004";
                                    $dataSend['message'] = "Error desconocido. Codigo $err_gen";
                                    $dataSend['extra'] = $response->message;
                                }
                            }
                            Log::channel('facturalo')->error(__FILE__ . "::" . __LINE__ . " \n $err_gen: No se ha podido determinar el fallo. La respuesta es \n" .
                                var_export($response->message, true));
                            return $dataSend;
                        }
                    }
                    $alreadySendit->push();
                    $dataSend['message'] = 'Se ha generado correctamente bajo el numero ' . $alreadySendit->getNumber();
                    $dataSend['success'] = true;
                }
            } catch (ErrorException $er) {
                Log::channel('facturalo')->error(__FILE__ . "::" . __LINE__ . " \n NV-M-501: No se ha podido determinar el fallo. La respuesta es \n" .
                    $responseBodyAsString . "\n");
            }

            Log::channel('facturalo')->error(__FILE__ . "::" . __LINE__ . " \n NV-M-500: No se ha podido determinar el fallo. La respuesta es \n" .
                var_export($response, true));
            $dataSend['message'] = 'Error desconocido. Codigo : NV-M-500';
            return $dataSend;
        }

        self::ExtraLog(__FILE__ . "::" . __LINE__ . "  \n Datos de RESPUESTA " . __FUNCTION__ . " \n" . var_export($response, true) . "\n\n\n\n");

        if ($response == false) {
            Log::channel('facturalo')->error(__FILE__ . "::" . __LINE__ . " \n NV-M-404: La respuesta ha sido falsa, posiblemente no se encuentre la web $web_Url \n" .
                var_export($response, true));
            $dataSend['message'] = 'Problemas de conexion con el servidor. Revise la configuracion. Codigo : NV-M-404';

            return $dataSend;
        }

        $responseBodyAsString = $response->getBody()->getContents();
        $response = json_decode($responseBodyAsString);

        if (property_exists($response, 'success')) {
            $success = $response->success;
            $alreadySendit->setSuccess();

            if (property_exists($response, 'data')) {
                $data = $response->data;
                if ($success == true) {
                    $alreadySendit
                        ->setSuccess(true)
                        ->setNumber($data->number)
                        ->setRemoteId($data->id);
                }
            } else {
                if (property_exists($response, 'message')) {
                    $message = $response->message;
                    $err_gen = 'NV-GEN-';
                    if ($this->searchInString('SQLSTATE[23000]', $message)) {
                        $err_gen = 'NV-SQL-';
                        if ($this->searchInString('`persons`', $message)) {
                            $err_gen .= "001";
                            $dataSend['message'] = 'Problemas insertando datos del cliente. ' . $err_gen;
                        } else {
                            $err_gen .= "003";
                            $dataSend['message'] = 'Problemas insertando datos' . $err_gen;
                        }
                    } else {
                        $err_gen .= "004";
                        $dataSend['message'] = "Error desconocido. Codigo $err_gen";
                        $dataSend['extra'] = $response->message;
                    }
                    Log::channel('facturalo')->error(__FILE__ . "::" . __LINE__ . " \n $err_gen: No se ha podido determinar el fallo. La respuesta es \n" .
                        var_export($response->message, true));
                    return $dataSend;
                }
            }
            $alreadySendit->push();
            $dataSend['message'] = 'Se ha generado correctamente bajo el numero ' . $alreadySendit->getNumber();
            $dataSend['success'] = true;
        } else {
            Log::channel('facturalo')->error(__FILE__ . "::" . __LINE__ . " \n NV-M-500: No se ha podido determinar el fallo. La respuesta es \n" .
                var_export($response, true));
            $dataSend['message'] = 'Error desconocido. Codigo : NV-M-500';
            return $dataSend;
        }

        return $dataSend;
    }

    /**
     * Evalua la forma de enviar la nv al servidor.
     *
     * @param Request $request
     * @return array
     */
    public function EnviarOtroSitio(Request $request)
    {
        $proccesed = [];
        $text = '';
        $success = false;
        $extra = '';
        if ($request->has('sale_note_id')) {
            // para una NV
            $saleNoteId = $request->sale_note_id;
            return $this->sendDataToOtherSite($saleNoteId);
        } elseif ($request->has('sale_notes_id')) {
            // multiples NV
            foreach ($request->sale_notes_id as $saleNoteId) {
                $temp = $this->sendDataToOtherSite($saleNoteId);
                $proccesed[] = $temp;
                $proccesed[] = $temp;
                $proccesed[] = $temp;
                if ($success == false) {
                    $success = $temp['success'];
                }
                $extra .= $temp['extra'] . " | " ?? null;
                $sms = $temp['message'] ?? null;
                $text .= ($sms !== null) ? $sms . "<br>" : null;
            }
        }
        $data['success'] = $success;
        $data['message'] = $text;
        $data['extra_info'] = $extra;
        $data['proccesed'] = $proccesed;
        return $data;
    }

    /**
     * Obtiene la url del servidor de destino configurada en la migracion.
     *
     * @return mixed|string|null
     */
    public function getSaleNoteToOtherSiteUrl()
    {
        $e = MigrationConfiguration::first();
        return $e !== null ? $e->url : '';
    }

    /**
     * Obtiene la lista de nota de ventas que pueden ser migradas a otro servidor.
     *
     * @param Request $request
     * @return SaleNote[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection|mixed
     */
    public function getSaleNoteToOtherSite(Request $request)
    {


        $saleNoteAlready = SaleNoteMigration::where('success', 1)
            ->select('sale_notes_id')
            ->get()
            ->pluck('sale_notes_id');
        $configuration = Configuration::first();
        $saleNote = SaleNote::whereNotIn('id', $saleNoteAlready);
        if ($request->has('params')) {
            $param = $request->params;
            if (isset($param['client_id'])) {
                $saleNote->where('customer_id', $param['client_id']);
            }
            if (isset($param['date_of_issue'])) {
                $saleNote->where('date_of_issue', $param['date_of_issue']);
            }
        }

        $saleNote = $saleNote->where('state_type_id', '!=', '11')
            ->get()
            ->transform(function ($row) use ($configuration) {
                /** @var SaleNote $row */
                return $row->getCollectionData($configuration);
            });

        return $saleNote;
    }
    /**
     * Busca el texto $search en la cadena de caracteres $text
     * @param $search
     * @param $text
     * @return bool
     */
    public function searchInString($search, $text)
    {
        return !(strpos($text, $search) === false);
    }

    public function columns()
    {
        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        if ($is_integrate_system) {
            return [
                'customer_id' => 'Cliente',
                'date_of_issue' => 'Fecha de emisión',
                'quotation_number' => 'N° Cotización',
            ];
        }
        return [
            'number' => 'N° Nota',
            'date_of_issue' => 'Fecha de emisión',
            'customer' => 'Cliente',
            'time_of_issue' => 'Hora de emisión',
            'seller' => 'Vendedor',
        ];
    }

    public function columns2()
    {
        $locations = [];
        $locations = func_get_locations();
        $locations_filter = array_values(array_filter($locations, function ($location) {
            return $location['value'] != '07';
        }));
        $location_15 = array_values(array_filter($locations_filter, function ($location) {
            return $location['value'] == '15';
        }));

        // Eliminar el primer hijo del elemento con el valor 15
        if (!empty($location_15) && !empty($location_15[0]['children'])) {
            array_shift($location_15[0]['children']);
        }

        // Reconstruir el array original con el elemento modificado
        $locations_filter = array_map(function ($location) {
            if ($location['value'] == '15' && !empty($location['children'])) {
                array_shift($location['children']);
            }
            return $location;
        }, $locations_filter);



        $location_15 = array_values(array_filter($locations, function ($location) {
            return $location['value'] == '15';
        }));
        $location_07 =  array_values(array_filter($locations, function ($location) {
            return $location['value'] == '07';
        }));
        $location_15 = $location_15[0]['children'][0];
        $location_07 = $location_07[0]['children'][0];
        $locations_filter2 = [
            "value" => "1",
            "label" => "Lima y Callao",
            "children" => [
                $location_15,
                $location_07
            ]
        ];
        $locations_filter3 = [
            'value' => '2',
            'label' => 'Provincias',
            'children' => $locations_filter
        ];
        $all_filters = [
            $locations_filter2,
            $locations_filter3

        ];

        return [
            'series' => Series::whereIn('document_type_id', ['80'])->get(),
            'locations' => $all_filters,

        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\Tenant\SaleNoteCollection
     */
    public function records(Request $request)
    {

        try {
            $records = $this->getRecordsSaleNote($request);
            return new SaleNoteCollection($records->paginate(config('tenant.items_per_page')));
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getTraceAsString(),
            ];
        }
    }

    private function getRecordsSaleNote($request)
    {
        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        $configuration = Configuration::getConfig();
        $records = SaleNote::query();
        $records = $records->whereTypeUser();
        $location_id = $request->location_id;
        $destination_id = $request->destination_id;
        $payment_method_id = $request->payment_method_id;
        $state_sale_note_order_id = $request->state_sale_note_order_id;
        $state_payment_id = $request->state_payment_id;
        $item_id = $request->item_id;
        $web_platform_id = $request->web_platform_id;
        $state_delivery_id = $request->state_delivery_id;
        if ($request != null && $request->has('onlySuscription') && (bool)$request->onlySuscription == true) {
            $records->whereNotNull('grade')->whereNotNull('section');
        }

        if ($request != null && $request->has('onlyFullSuscription') && (bool)$request->onlyFullSuscription == true) {
            $records->whereNotNull('user_rel_suscription_plan_id')
                ->whereNull('grade')->whereNull('section');
        }

        if ($request->column == 'customer') {
            $records->whereHas('person', function ($query) use ($request) {
                $query
                    ->where('name', 'like', "%{$request->value}%")
                    ->orWhere('number', 'like', "%{$request->value}%");
            })
                ->latest();
        } else if ($request->column == 'time_of_issue') {
            $time = $request->value;
            $explode_time = explode("-", $time);
            $start_time = trim($explode_time[0]);
            $end_time = trim($explode_time[1]);

            $records->whereBetween('time_of_issue', [$start_time, $end_time])->latest();
        } else if ($request->column == 'seller') {
            $records->whereHas('seller', function ($query) use ($request) {
                $query
                    ->where('name', 'like', "%{$request->value}%")
                    ->orWhere('number', 'like', "%{$request->value}%");
            })
                ->latest();
        } else if ($request->column == 'customer_id' && $request->value != null) {
            $records->where('customer_id', $request->value)
                ->latest();
        } else if ($request->column == 'quotation_number' && $request->value != null) {
            $records->whereHas('quotation', function ($query) use ($request) {
                $query->where('number', 'like', "%{$request->value}%");
            })
                ->latest();
        } else if ($request->column == 'user_name') {
            $records->whereHas('person', function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->value}%")
                    ->orWhere('number', 'like', "%{$request->value}%");
            })
                ->latest();
        } else {
            $records->where($request->column, 'like', "%{$request->value}%")
                ->latest('id');
        }
        if ($location_id) {
            $records->whereHas('customer', function ($query) use ($location_id) {
                $query->whereHas('dispatch_addresses', function ($query) use ($location_id) {
                    if ($location_id == '1') {
                        // Lima y Callao
                        $query->where(function ($q) {
                            // department_id = '07' (Callao) en location_id[0]
                            $q->whereRaw("JSON_EXTRACT(location_id, '$[0]') = ?", ['07'])
                                ->orWhere(function ($q2) {
                                    // department_id = '15' (Lima) y province_id = '1501' (Lima) en location_id[0] y location_id[1]
                                    $q2->whereRaw("JSON_EXTRACT(location_id, '$[0]') = ?", ['15'])
                                        ->whereRaw("JSON_EXTRACT(location_id, '$[1]') = ?", ['1501']);
                                });
                        });
                    } else {
                        // Provincias (todo lo que no sea Lima y Callao)
                        $query->where(function ($q) {
                            $q->whereRaw("JSON_EXTRACT(location_id, '$[0]') != ?", ['07'])
                                ->where(function ($q2) {
                                    $q2->whereRaw("JSON_EXTRACT(location_id, '$[0]') != ?", ['15'])
                                        ->orWhere(function ($q3) {
                                            $q3->whereRaw("JSON_EXTRACT(location_id, '$[0]') = ?", ['15'])
                                                ->whereRaw("JSON_EXTRACT(location_id, '$[1]') != ?", ['1501']);
                                        });
                                });
                        });
                    }
                });
            });
        }
        if ($destination_id) {
            $records->whereHas('payments', function ($query) use ($destination_id) {
                $query->whereHas('global_payment', function ($q) use ($destination_id) {
                    $q->where('destination_id', $destination_id)
                        ->where('destination_type', BankAccount::class);
                });
            });
        }
        if ($payment_method_id) {
            $records->whereHas('payments', function ($query) use ($payment_method_id) {
                $query->where('payment_method_type_id', $payment_method_id);
            });
        }
        if ($state_sale_note_order_id) {
            $records->whereHas('person', function ($query) use ($state_sale_note_order_id) {
                $query->whereHas('dispatch_addresses', function ($query) use ($state_sale_note_order_id) {
                    $query->where('reason', $state_sale_note_order_id);
                });
            });
        }
        if ($state_payment_id) {
            $records->where('state_payment_id', $state_payment_id);
        }
        if ($web_platform_id) {
            $records->whereHas('items', function ($query) use ($web_platform_id) {
                $query->whereHas('relation_item', function ($q) use ($web_platform_id) {
                    $q->where('web_platform_id', $web_platform_id);
                });
            });
        }
        if ($state_delivery_id) {
            if ($state_delivery_id == 1) {
                $records->where(function ($query) {
                    $query->where('state_delivery_id', 1)
                        ->orWhere('state_delivery_id', null);
                });
            } else {
                $records->where('state_delivery_id', $state_delivery_id);
            }
        }
        if ($request->series) {
            $records->where('series', 'like', '%' . $request->series . '%');
        }
        if ($item_id) {
            $records->whereHas('items', function ($query) use ($item_id, $configuration) {
                $query->where('item_id', $item_id)
                    ->orWhereHas('relation_item', function ($q) use ($item_id) {
                        $q->whereHas('sets', function ($q2) use ($item_id) {
                            $q2->where('individual_item_id', $item_id);
                        });
                    });
            });
        }

        if ($request->number) {
            $records->where('number', 'like', '%' . $request->number . '%');
        }

        if ($request->total_canceled != null) {
            if ($is_integrate_system) {
                if ($request->total_canceled == 2) {
                    $records
                        ->where(
                            function ($query) {
                                $query->where('state_payment_id', '03')
                                    ->orWhere('state_type_id', '11');
                            }
                        );
                } else {
                    $records
                        ->where('total_canceled', $request->total_canceled)
                        ->where('state_type_id', '<>', '11');
                }
            } else {
                $records->where('total_canceled', $request->total_canceled);
            }
        }

        if ($request->purchase_order) {
            $records->where('purchase_order', $request->purchase_order);
        }

        if ($request->license_plate) {
            if ($configuration->plate_number_config) {
                $records->whereHas('plateNumberDocument', function ($query) use ($request) {
                    $query->whereHas('plateNumber', function ($query) use ($request) {
                        $query->where('description', 'like', "%{$request->license_plate}%");
                    });
                });
            } else {
                $records->where('license_plate', $request->license_plate);
            }
        }

        if ($request->has('d_start') && $request->has('d_end')) {
            $records->whereBetween('date_of_issue', [$request->d_start, $request->d_end]);
        }

        if ($request->has('seller_id') && $request->seller_id != null) {
            $records->where('seller_id', $request->seller_id);
        }

        $bindings = $records->getBindings();

        $records = $records->with([
            'fee:id,date,sale_note_id',
            'items:id,sale_note_id,item_id,attributes',
            'items.relation_item.web_platform:id,name',
            'dispatch:id,series,number',
            'state_type:id,description',
            'user:id,name,email',
            'payments:id,sale_note_id,payment,date_of_payment',
        ]);



        return $records;
    }
    public function reportIntegrateSystem(Request $request)
    {
        $sale_notes = SaleNote::where('state_payment_id', '01');
        if ($request->has('d_start') && $request->has('d_end')) {
            $sale_notes->whereBetween('date_of_issue', [$request->d_start, $request->d_end]);
        }
        $records = $sale_notes->get();
        $company = Company::first();
        $pdf = PDF::loadView('tenant.sale_notes.report_pdf_s', compact("records", "company"))
            ->setPaper('a4', 'landscape');;

        $filename = 'Reporte_Cuentas_Por_Cobrar_' . date('YmdHis');

        // return $pdf->download($filename . '.pdf');
        return $pdf->stream($filename . '.pdf');
    }
    /**
     * @param $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getRecords($request)
    {
        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        $user = auth()->user();
        $configuration = Configuration::getConfig();
        $seller_establishments_all = $configuration->seller_establishments_all;
        $records = SaleNote::whereTypeUser();
        $location_id = $request->location_id;
        $item_id = $request->item_id;
        $web_platform_id = $request->web_platform_id;
        $state_delivery_id = $request->state_delivery_id;
        if ($request != null && $request->has('onlySuscription') && (bool)$request->onlySuscription == true) {
            $records->whereNotNull('grade')->whereNotNull('section');
        }

        if ($request != null && $request->has('onlyFullSuscription') && (bool)$request->onlyFullSuscription == true) {
            $records->whereNotNull('user_rel_suscription_plan_id')
                ->whereNull('grade')->whereNull('section');
        }

        if ($request->column == 'customer') {
            $records->whereHas('person', function ($query) use ($request) {
                $query
                    ->where('name', 'like', "%{$request->value}%")
                    ->orWhere('number', 'like', "%{$request->value}%");
            })
                ->latest();
        } else if ($request->column == 'time_of_issue') {
            $time = $request->value;
            $explode_time = explode("-", $time);
            $start_time = trim($explode_time[0]);
            $end_time = trim($explode_time[1]);

            $records->whereBetween('time_of_issue', [$start_time, $end_time])->latest();
        } else if ($request->column == 'seller') {
            $records->whereHas('seller', function ($query) use ($request) {
                $query
                    ->where('name', 'like', "%{$request->value}%")
                    ->orWhere('number', 'like', "%{$request->value}%");
            })
                ->latest();
        } else if ($request->column == 'customer_id' && $request->value != null) {
            $records->where('customer_id', $request->value)
                ->latest();
        } else if ($request->column == 'quotation_number' && $request->value != null) {
            $records->whereHas('quotation', function ($query) use ($request) {
                $query->where('number', 'like', "%{$request->value}%");
            })
                ->latest();
        } else if ($request->column == 'user_name') {
            $records->whereHas('person', function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->value}%")
                    ->orWhere('number', 'like', "%{$request->value}%");
            })
                ->latest();
        } else {
            $records->where($request->column, 'like', "%{$request->value}%")
                ->latest('id');
        }
        if ($location_id) {
            $records->whereHas('customer', function ($query) use ($location_id) {
                if ($location_id == '1') {
                    // Lima y Callao
                    $query->where(function ($q) {
                        $q->where('department_id', '07')
                            ->orWhere(function ($q2) {
                                $q2->where('department_id', '15')
                                    ->where('province_id', '1501');
                            });
                    });
                } else {
                    // Provincias
                    $query->where(function ($q) {
                        $q->where('department_id', '!=', '07')
                            ->where(function ($q2) {
                                $q2->where('department_id', '!=', '15')
                                    ->orWhere(function ($q3) {
                                        $q3->where('department_id', '15')
                                            ->where('province_id', '!=', '1501');
                                    });
                            });
                    });
                }
            });
        }
        if ($web_platform_id) {
            $records->whereHas('items', function ($query) use ($web_platform_id) {
                $query->whereHas('relation_item', function ($q) use ($web_platform_id) {
                    $q->where('web_platform_id', $web_platform_id);
                });
            });
        }
        if ($state_delivery_id) {
            if ($state_delivery_id == 1) {
                $records->where(function ($query) {
                    $query->where('state_delivery_id', 1)
                        ->orWhere('state_delivery_id', null);
                });
            } else {
                $records->where('state_delivery_id', $state_delivery_id);
            }
        }
        if ($request->series) {
            $records->where('series', 'like', '%' . $request->series . '%');
        }
        if ($item_id) {
            $records->whereHas('items', function ($query) use ($item_id, $configuration) {
                $query->where('item_id', $item_id)
                    ->orWhereHas('relation_item', function ($q) use ($item_id) {
                        $q->whereHas('sets', function ($q2) use ($item_id) {
                            $q2->where('individual_item_id', $item_id);
                        });
                    });
            });
        }

        if ($request->number) {
            $records->where('number', 'like', '%' . $request->number . '%');
        }

        if ($request->total_canceled != null) {
            if ($is_integrate_system) {
                if ($request->total_canceled == 2) {
                    $records
                        ->where(
                            function ($query) {
                                $query->where('state_payment_id', '03')
                                    ->orWhere('state_type_id', '11');
                            }
                        );
                } else {
                    $records
                        ->where('total_canceled', $request->total_canceled)
                        ->where('state_type_id', '<>', '11');
                }
            } else {
                $records->where('total_canceled', $request->total_canceled);
            }
        }

        if ($request->purchase_order) {
            $records->where('purchase_order', $request->purchase_order);
        }

        if ($request->license_plate) {
            if ($configuration->plate_number_config) {
                $records->whereHas('plateNumberDocument', function ($query) use ($request) {
                    $query->whereHas('plateNumber', function ($query) use ($request) {
                        $query->where('description', 'like', "%{$request->license_plate}%");
                    });
                });
            } else {
                $records->where('license_plate', $request->license_plate);
            }
        }

        if ($request->has('d_start') && $request->has('d_end')) {
            $records->whereBetween('date_of_issue', [$request->d_start, $request->d_end]);
        }

        if ($request->has('seller_id') && $request->seller_id != null) {
            $records->where('seller_id', $request->seller_id);
        }


        return $records;
    }
    public function searchCustomersLimit(Request $request)
    {
        $driver = filter_var($request->driver ?? "false", FILTER_VALIDATE_BOOLEAN);
        $customers = Person::query();
        if ($driver) {
            $customers = Person::where('barcode', 'like', "%{$request->input}%");
        } else {
            $customers = Person::where('number', 'like', "%{$request->input}%")
                ->orWhere('name', 'like', "%{$request->input}%")
                ->orWhere('internal_code', 'like', "%{$request->input}%");
        }
        $customers = $customers->whereType('customers')->orderBy('name')
            ->whereIsEnabled()
            ->where('is_driver', $driver)
            ->limit(20)
            ->get()->transform(function (Person $row) {
                return [
                    'id' => $row->id,
                    'description' => $row->number . ' - ' . $row->name,
                    'seller_id' => $row->seller_id,
                    'seller' => $row->seller,
                    'person_type_id' => $row->person_type_id,
                    'name' => $row->name,
                    'number' => $row->number,
                    'barcode' => $row->barcode,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code,
                    'all_addresses' => $row->all_addresses(),
                ];
            });

        return compact('customers');
    }


    public function searchCustomers(Request $request)
    {

        $driver = filter_var($request->driver ?? "false", FILTER_VALIDATE_BOOLEAN);
        $customers = Person::query();
        if ($driver) {
            $customers = Person::where('barcode', 'like', "%{$request->input}%");
        } else {
            $customers = Person::where('number', 'like', "%{$request->input}%")
                ->orWhere('name', 'like', "%{$request->input}%")
                ->orWhere('internal_code', 'like', "%{$request->input}%");
        }
        $customers = $customers->whereType('customers')->orderBy('name')
            ->whereIsEnabled()
            ->where('is_driver', $driver)
            ->get()->transform(function (Person $row) {
                return [
                    'id' => $row->id,
                    'description' => $row->number . ' - ' . $row->name,
                    'seller_id' => $row->seller_id,
                    'seller' => $row->seller,
                    'person_type_id' => $row->person_type_id,
                    'name' => $row->name,
                    'number' => $row->number,
                    'barcode' => $row->barcode,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code,
                    'all_addresses' => $row->all_addresses(),
                ];
            });

        return compact('customers');
    }
    public function paymentdestinations($user_id)
    {
        $payment_destinations = $this->getPaymentDestinations($user_id);
        return compact('payment_destinations');
    }
    public function tablesCompany($id)
    {
        $company = Company::where('website_id', $id)->first();
        $company_active = Company::active();
        $document_number = $company->document_number;
        $website_id = $company->website_id;
        $user = auth()->user()->id;
        $user_to_save = User::find($user);
        $user_to_save->company_active_id = $website_id;
        $user_to_save->save();
        $key = "cash_" . $company_active->name . "_" . $user;
        Cache::put($key, $website_id, 60);
        $payment_destinations = $this->getPaymentDestinations();
        if ($website_id && $company->id != $company_active->id) {
            $hostname = Hostname::where('website_id', $website_id)->first();
            $client = SystemClient::where('hostname_id', $hostname->id)->first();
            $tenancy = app(Environment::class);
            $tenancy->tenant($client->hostname->website);
        }
        $establishment = Establishment::find(1);
        $establishment_info = EstablishmentInput::set($establishment->id);
        $series = Series::where('establishment_id', $establishment->id)->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'contingency' => (bool)$row->contingency,
                    'document_type_id' => $row->document_type_id,
                    'establishment_id' => $row->establishment_id,
                    'number' => $row->number,
                ];
            });
        // $series = Series::FilterSeries(1)
        //     ->get()
        //     ->transform(function ($row)  use ($document_number) {
        //         /** @var Series $row */
        //         return $row->getCollectionData2($document_number);
        //     })->where('disabled', false);
        return [
            'success' => true,
            'data' => $company,
            'payment_destinations' => $payment_destinations,
            'series' => $series,
            'establishment' => $establishment_info,
        ];
    }
    public function tables($user_id = null)
    {
        $user = new User();
        if (Auth::user()) {
            $user = Auth::user();
        }
        $currentWarehouseId = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first()->id;
        $configuration = Configuration::select('destination_sale', 'ticket_58', 'multi_companies', 'seller_establishments_all')->first();
        $dispatchers = Dispatcher::where('is_active', true)->get();
        $person_dispatchers = PersonDispatcher::all();
        $person_packers = PersonPacker::all();
        $business_turns = BusinessTurn::where('active', true)->get();
        $establishment_id =  $user->establishment_id;
        $userId =  $user->id;
        $customers = $this->table('customers');
        $all_establishments = Establishment::whereActive()->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description
            ];
        });

        $is_admin_or_superadmn = $user->type == 'admin' || $user->type == 'superadmin';
        if ($is_admin_or_superadmn || $configuration->seller_establishments_all) {
            $establishments = Establishment::whereActive()->get();
        } else {

            $establishments = Establishment::where('id', auth()->user()->establishment_id)->get();
        }
        $currency_types = CurrencyType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $global_charge_types = ChargeDiscountType::whereIn('id', ['50'])->get();
        $company = Company::active();
        // $payment_method_types = PaymentMethodType::where('active', true)->get();
        //obtiene los payment_method_types que en su descripcion  no tengan la palabra "Factura"
        // $payment_method_types = PaymentMethodType::where('description', 'not like', '%Factura%')->get();
        $payment_method_types = ConditionBlockPaymentMethod::getCashPaymentMethods();
        $payment_method_types_credit = ConditionBlockPaymentMethod::getCreditPaymentMethods();

        $series = collect(Series::all())->transform(function ($row) {
            return [
                'id' => $row->id,
                'contingency' => (bool) $row->contingency,
                'document_type_id' => $row->document_type_id,
                'establishment_id' => $row->establishment_id,
                'number' => $row->number
            ];
        });
        $payment_destinations = $this->getPaymentDestinations();
        // $sellers = User::GetSellers(false)->get();
        $sellers = User::getSellersToNvCpe($establishment_id, $userId);
        $global_discount_types = ChargeDiscountType::getGlobalDiscounts();
        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        $companies = [];
        if ($configuration->multi_companies) {
            $companies = Company::all();
        }
        $unit_types = UnitType::where('active', true)->get();
        $affectation_igv_types = AffectationIgvType::all();
        return compact(
            'unit_types',
            'all_establishments',
            'currentWarehouseId',
            'affectation_igv_types',
            'dispatchers',
            'person_dispatchers',
            'person_packers',
            'companies',
            'business_turns',
            'is_integrate_system',
            'customers',
            'establishments',
            'currency_types',
            'discount_types',
            'configuration',
            'charge_types',
            'company',
            'payment_method_types',
            'payment_method_types_credit',
            'series',
            'payment_destinations',
            'sellers',
            'global_charge_types',
            'global_discount_types',
        );
    }

    public function changed($id)
    {
        $sale_note = SaleNote::find($id);
        $sale_note->changed = true;
        $sale_note->save();
    }


    public function item_tables()
    {
        // $items = $this->table('items');
        $items = SearchItemController::getItemsToSaleNote();
        $categories = [];
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $system_isc_types = SystemIscType::whereActive()->get();
        $price_types = PriceType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $attribute_types = AttributeType::whereActive()->orderByDescription()->get();
        $currentWarehouseId = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first()->id;
        $operation_types = OperationType::whereActive()->get();
        $is_client = $this->getIsClient();

        return compact(
            'currentWarehouseId',
            'items',
            'categories',
            'affectation_igv_types',
            'system_isc_types',
            'price_types',
            'discount_types',
            'charge_types',
            'attribute_types',
            'operation_types',
            'is_client'
        );
    }

    public function record($id)
    {
        $record = new SaleNoteResource(SaleNote::findOrFail($id));

        return $record;
    }

    public function record2($id)
    {
        $record = new SaleNoteResource2(SaleNote::findOrFail($id));

        return $record;
    }

    private function validCustomerDebts($customer_id){
        $waiting_payment = DB::connection('tenant')->table('sale_notes')
        ->where('customer_id', $customer_id)
        ->where('state_payment_id',  '01') // Exclude voided notes
        ->exists();
        if($waiting_payment){
            return false;
        }
        $pending_debt_notes = SaleNote::with(['sale_note_payments'])
            ->where('customer_id', $customer_id)
            ->where('state_type_id', '!=', '11') // Exclude voided notes
            ->get()
            ->filter(function($sale_note) {
                $total_paid = $sale_note->sale_note_payments->sum('payment');
                return $sale_note->total > $total_paid;
            });

        $is_valid = $pending_debt_notes->isEmpty();

        return $is_valid;
    }

    public function store(SaleNoteRequest $request)
    {
        $from_api = false;

        $configuration = Configuration::first();
        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        if($is_integrate_system){
            $is_valid = $this->validCustomerDebts($request->customer_id);
        }else{
            $is_valid = true;
        }
        if(!$is_valid){
            return [
                'success' => false,
                'message' => 'El cliente tiene deudas pendientes o por confirmar'
            ];
        }
        if ($configuration->multi_companies) {
            if (request()->route()) {
                $url = request()->url();
                $parsedUrl = parse_url($url);
                $path = $parsedUrl['path'];
                $from_api = str_contains($path, 'api');
                $request->merge(['from_api' => $from_api]);
            }
        }
        $type_user = auth()->user()->type;
        $id  = $request->input('id');
        if ($configuration->block_seller_sale_note_edit && $type_user === 'seller' && $id) {
            return [
                'success' => false,
                'message' => 'No tiene permisos para editar Notas de Venta'
            ];
        }
        $this->checkPurchaseOrden($request->purchase_order,  $id, SaleNote::class, $request->items);
        return $this->storeWithData($request->all());
    }

    function updateDispatchProductionOrdens($sale_note_id) {}
    public function storeWithData($inputs)
    {
        DB::connection('tenant')->beginTransaction();
        try {
            if (!isset($inputs['id'])) {
                $inputs['id'] = false;
            }
            $is_integrate_system = BusinessTurn::isIntegrateSystem();
            $configuration = Configuration::first();
            $company_id = Functions::valueKeyInArray($inputs, 'company_id');
            $plate_number_id = Functions::valueKeyInArray($inputs, 'plate_number_id');
            $data = $this->mergeData($inputs);
            if ($configuration->multi_companies && $company_id == null && isset($inputs['from_api']) && $inputs['from_api']) {
                $first_company = Company::select('website_id')->first();
                if ($first_company) {
                    $company_id = $first_company->website_id;
                }
            }
            if ($company_id) {
                $company_found = Company::where('website_id', $company_id)->first();

                $data['website_id'] = $company_found->website_id;
                $data['company'] = $company_found->name;
                $alter_establishment = Functions::valueKeyInArray($inputs, 'establishment');
                if ($alter_establishment) {
                    $data['establishment'] = $alter_establishment;
                }

                $alter_number = Functions::valueKeyInArray($inputs, 'number');
                if ($alter_number) {
                    $data['number'] = $alter_number;
                }
                if (!$alter_number || $alter_number == "#") {
                    $document_found = SaleNote::where('series', $data['series'])
                        ->where('website_id', $company_found->website_id)
                        ->orderBy('number', 'desc')
                        ->first();
                    if ($document_found) {
                        $document_number = $document_found->number;
                        $document_number = $document_number + 1;
                        if ($document_number) {
                            $data['number'] = $document_number;
                        }
                    } else {
                        $first_company = Company::active();
                        $is_same_website = $first_company->website_id == $company_id;
                        if ($is_same_website) {
                            $document_found = SaleNote::where('series', $data['series'])
                                ->whereNull('website_id')
                                ->orderBy('number', 'desc')
                                ->first();
                            if ($document_found) {
                                $document_number = $document_found->number;
                                $document_number = $document_number + 1;
                                if ($document_number) {
                                    $data['number'] = $document_number;
                                }
                            }
                        } else {
                            $data['number'] = 1;
                        }
                    }
                }
            }
            $payment_condition_id = Functions::valueKeyInArray($data, 'payment_condition_id', "01");
            if ($payment_condition_id == "03") {
                $data['payment_condition_id'] = "02";
            }
            $this->sale_note = SaleNote::query()->updateOrCreate(['id' => $inputs['id']], $data);
            $configuration = Configuration::first();
            if ($configuration->plate_number_config) {
                PlateNumberDocument::where('sale_note_id', $this->sale_note->id)->delete();
                if ($plate_number_id) {
                    PlateNumberDocument::create([
                        'plate_number_id' => $plate_number_id,
                        'sale_note_id' => $this->sale_note->id,
                        'km' => Functions::valueKeyInArray($inputs, 'km'),
                    ]);
                }
            }
            SaleNoteFee::where('sale_note_id', $this->sale_note->id)->delete();
            $this->deleteAllPayments($this->sale_note->payments);
            $this->deleteAllAdvanceSaleNote($this->sale_note->id);
            $this->deleteAllItems($this->sale_note->items);

            $fee = Functions::valueKeyInArray($inputs, 'fee', []);
            $this->saveFees($this->sale_note, $fee);

            $configuration = Configuration::first();
            foreach ($data['items'] as $row) {
                $item_id = isset($row['record_id']) ? $row['record_id'] : null;
                $update_price = Functions::valueKeyInArray($row, 'update_price', false);
                $sale_note_item = SaleNoteItem::query()->firstOrNew(['id' => $item_id]);
                if ($update_price) {
                    try {
                        $price = floatval($row["unit_price"]);
                        if (isset($row["item"]["presentation"]["id"])) {
                            $id = $row["item"]["presentation"]["id"];
                            $unit_type = ItemUnitType::find($id);
                            if ($unit_type) {
                                switch ($unit_type->price_default) {
                                    case 1:
                                        $unit_type->price1 = $price;
                                        break;
                                    case 2:
                                        $unit_type->price2 = $price;
                                        break;
                                    default:
                                        $unit_type->price3 = $price;
                                        break;
                                }

                                $unit_type->save();
                            }
                        } else {

                            $item_id = $row["item_id"];
                            Item::where('id', $item_id)->update(["sale_unit_price" => $price]);
                            if (isset($row["warehouse_id"]) && $row["warehouse_id"] != null) {
                                ItemWarehousePrice::where('item_id', $item_id)
                                    ->where('warehouse_id', $row["warehouse_id"])
                                    ->update(["sale_unit_price" => $price]);
                            }
                        }
                    } catch (Exception $e) {
                        Log::error($e->getMessage());
                    }
                }
                // Obtener el ítem desde la tabla 'items'
                $item = Item::find($row['item_id']);
                $unit_price_to_save = isset($row['unit_price']) ? $row['unit_price'] : null;

                // Asegurar que $row['item'] sea un array o convertirlo en array
                $item_data = is_string($row['item']) ? json_decode($row['item'], true) : $row['item'];
                if (is_array($item_data)) {
                    // Incluir 'id_cupones' en el campo 'item'
                    $item_data['id_cupones'] = $item ? $item->id_cupones : null;
                    $item_data['item_complements_selected'] = isset($row['item_complements_selected']) ? $row['item_complements_selected'] : null;

                    // Actualizar el valor del campo 'item' en $row
                    $row['item'] = json_encode($item_data);
                } else {
                    throw new Exception("Error al decodificar el campo 'item' en el ítem con ID {$row['item_id']}");
                }

                if (isset($item_data['lots'])) {
                    $item_data['lots'] = isset($row['lots']) ? $row['lots'] : $item_data['lots'];
                }
                $row['item'] = $item_data;


                $this->setIdLoteSelectedToItem($row);
                $this->setSizesSelectedToItem($row);
                if (isset($row['warehouse_id'])) {
                    $row["warehouse_id"] = ($row["warehouse_id"] == 0) ? null : $row["warehouse_id"];
                }

                $sale_note_item->fill($row);
                $sale_note_item->sale_note_id = $this->sale_note->id;
                if ($unit_price_to_save) {
                    $igv = $row['percentage_igv'];
                    $original_unit_price = $item->sale_unit_price;
                    $has_igv =  (bool)$item->has_igv;
                    if (!$has_igv) {
                        $original_unit_price = $original_unit_price * (1 + ($igv / 100));
                    }
                    $original_unit_price = floatval($original_unit_price);
                    $unit_price_to_save = floatval($unit_price_to_save);
                    if ($original_unit_price != $unit_price_to_save) {
                        $sale_note_item->modify_sale_unit_price = true;
                    } else {
                        $sale_note_item->modify_sale_unit_price = false;
                    }
                }
                $sale_note_item->save();
                if (isset($row['item']['idAttributeSelect'])) {
                    ItemProperty::where('id', $row['item']['idAttributeSelect'][0]["id"])->update([
                        'has_sale' => true,
                    ]);
                }
                if ($configuration->multi_sellers) {
                    $seller_id = isset($row['seller_id']) ? $row['seller_id'] : $this->sale_note->seller_id;
                    if ($seller_id == null) {
                        $seller_id = auth()->user()->id;
                    }
                    ItemSeller::create([
                        'seller_id' => $seller_id,
                        'sale_note_item_id' => $sale_note_item->id,
                    ]);
                }
                if (isset($row['lots'])) {
                    foreach ($row['lots'] as $lot) {
                        $record_lot = ItemLot::query()->findOrFail($lot['id']);
                        $record_lot->has_sale = true;
                        $record_lot->update();
                    }
                }
                // control de lotes
                $id_lote_selected = $this->getIdLoteSelectedItem($row);
                // si tiene lotes y no fue generado a partir de otro documento (pedido...)
                if ($id_lote_selected && !$this->sale_note->isGeneratedFromExternalRecord()) {
                    if (is_array($id_lote_selected)) {
                        // presentacion - factor de lista de precios
                        $quantity_unit = isset($sale_note_item->item->presentation->quantity_unit) ? $sale_note_item->item->presentation->quantity_unit : 1;
                        $inventory_configuration = InventoryConfiguration::first();
                        $inventory_configuration->stock_control;
                        foreach ($id_lote_selected as $item) {
                            $lot = ItemLotsGroup::query()->find($item['id']);
                            $lot->quantity = $lot->quantity - ($quantity_unit * $item['compromise_quantity']);
                            if ($inventory_configuration->stock_control) {
                                $this->validateStockLotGroup($lot, $sale_note_item);
                            }
                            $lot->save();
                        }
                    } else {
                        $quantity_unit = 1;
                        if (isset($row['item']) && isset($row['item']['presentation']) && isset($row['item']['presentation']['quantity_unit'])) {
                            $quantity_unit = $row['item']['presentation']['quantity_unit'];
                        }
                        $lot = ItemLotsGroup::find($id_lote_selected);
                        $lot->quantity = ($lot->quantity - ($row['quantity'] * $quantity_unit));
                        $lot->save();
                    }
                }
                $configuration = Configuration::first();
                if ($configuration->college) {
                    $sale_note_id = $this->sale_note->id;
                    $periods = Functions::valueKeyInArray($data, 'months');
                    $client_id = Functions::valueKeyInArray($data, 'customer_id');
                    $child_id = Functions::valueKeyInArray($data, 'child_id');
                    if ($client_id && $child_id && $periods) {
                        SuscriptionPayment::where('sale_note_id', $sale_note_id)->delete();
                        foreach ($periods as  $period) {
                            $date = Carbon::createFromDate($period['year'], $period['value'], 1);
                            SuscriptionPayment::create([
                                'child_id' => $child_id,
                                'client_id' => $client_id,
                                'sale_note_id' => $sale_note_id,
                                'period' => $date,
                            ]);
                        }
                    }
                }

                $warranty = Functions::valueKeyInArray($row, 'warranty');
                if ($warranty) {
                    $item_warranty = new WarrantyDocument;
                    $item_warranty->sale_note_id = $this->sale_note->id;
                    $item_warranty->total = $warranty['amount'];
                    $item_warranty->quantity = $warranty['quantity'];
                    $item_warranty->comment = $warranty['comments'];
                    $item_warranty->save();
                }
            }

            if (isset($data['no_stock']) && $data['no_stock']) {
                $exist_no_stock = NoStockDocument::where('sale_note_id', $this->sale_note->id)->first();
                if (!$exist_no_stock) {
                    NoStockDocument::create([
                        'sale_note_id' => $this->sale_note->id,
                        'completed' => false
                    ]);
                }
            }
            //pagos
            $this->savePayments($this->sale_note, $data['payments'], $data['cash_id']);
            if (isset($data['transport']) && $data['transport']) $this->sale_note->transport()->create($data['transport']);
            if (isset($data['transport_dispatch']) && $data['transport_dispatch']) $this->sale_note->transport_dispatch()->create($data['transport_dispatch']);
            $this->setFilename();
            DB::connection('tenant')->commit();
            $this->regularizePayments($data['payments']);
            $base_url = url('/');
            $external_id = $this->sale_note->external_id;
            $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
            $print_format = $establishment->print_format ?? 'ticket';
            $url_print = "{$base_url}/sale-notes/print/{$external_id}/$print_format";

            if ($inputs['id'] && $is_integrate_system) {
                $exist_production_order = ProductionOrder::where('sale_note_id', $inputs['id'])->first();
                if ($exist_production_order) {
                    $request = new Request();
                    (new ProductionOrderController)->generateFromSaleNote($request, $inputs['id']);

                    $exist_dispatch_order = DispatchOrder::where('production_order_id', $exist_production_order->id)->first();
                }
            }
            // $this->createPdf($this->sale_note, "a4", $this->sale_note->filename);
            if (isset($inputs['rent_id'])) {
                HotelRentDocument::create([
                    'rent_id' => $inputs['rent_id'],
                    'sale_note_id' => $this->sale_note->id,
                    'is_advance' => true
                ]);
            }
            if (isset($inputs['orden_id']) && $inputs['orden_id'] != null) {
                $orden_id = $inputs['orden_id'];
                $this->processOrder($orden_id, $this->sale_note->id, $this->sale_note->customer_id, true);
            }
            if(isset($inputs['id']) && $inputs['id'] != null && $inputs['id'] != false && $inputs['id'] != 0 ){
                $this->sale_note->auditUpdated(null, $this->sale_note->total, $this->sale_note->total);
            }else{
                $quotation_id = $this->sale_note->quotation_id;
                if($quotation_id){
                    $this->sale_note->auditConvertedTo('quotation', $quotation_id);
                }
            }
            return [
                'success' => true,
                'data' => [
                    'id' => $this->sale_note->id,
                    'printer'  => $this->printerName(auth()->user()->id),
                    'number_full' => $this->sale_note->number_full,
                    'url_print' => $url_print,
                ],
            ];
        } catch (Exception $e) {
            $this->generalWriteErrorLog($e);

            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }



    /**
     *
     * Obtener lote seleccionado
     *
     * @todo regularizar lots_group, no se debe guardar en bd, ya que tiene todos los lotes y no los seleccionados, reemplazar por IdLoteSelected
     *
     * @param  array $row
     * @return array
     */
    private function getIdLoteSelectedItem($row)
    {
        $id_lote_selected = null;

        if (isset($row['IdLoteSelected'])) {
            $id_lote_selected = $row['IdLoteSelected'];
        } else {
            if (isset($row['item']['lots_group'])) {
                $id_lote_selected = collect($row['item']['lots_group'])->where('compromise_quantity', '>', 0)->toArray();
            }
        }

        return $id_lote_selected;
    }


    /**
     *
     * Asignar lote a item (regularizar propiedad en json item)
     *
     * @param  array $row
     * @return void
     */
    private function setIdLoteSelectedToItem(&$row)
    {
        if (isset($row['IdLoteSelected'])) {
            $row['item']['IdLoteSelected'] = $row['IdLoteSelected'];
        } else {
            $row['item']['IdLoteSelected'] = isset($row['item']['IdLoteSelected']) ? $row['item']['IdLoteSelected'] : null;
        }
    }
    private function setSizesSelectedToItem(&$row)
    {
        if (isset($row['sizes_selected'])) {
            $row['item']['sizes_selected'] = $row['sizes_selected'];
        } else {
            $row['item']['sizes_selected'] = isset($row['item']['sizes_selected']) ? $row['item']['sizes_selected'] : null;
        }
    }

    private function regularizePayments($payments)
    {

        $total_payments = collect($payments)->sum('payment');

        $balance = $this->sale_note->total - $total_payments;

        if ($balance <= 0) {

            $this->sale_note->total_canceled = true;
            $this->sale_note->save();
        } else {

            $this->sale_note->total_canceled = false;
            $this->sale_note->save();
        }
    }


    public function destroy_sale_note_item($id)
    {
        $item = SaleNoteItem::findOrFail($id);

        if (isset($item->item->lots)) {

            foreach ($item->item->lots as $lot) {
                $record_lot = ItemLot::findOrFail($lot->id);
                $record_lot->has_sale = false;
                $record_lot->update();
            }
        }

        $item->delete();

        return [
            'success' => true,
            'message' => 'eliminado'
        ];
    }

    public function mergeData($inputs)
    {

        $this->company = Company::active();

        $cash_id = Functions::valueKeyInArray($inputs, 'cash_id');
        if ($cash_id == null) {

            try {
                $cash_id = optional(Cash::where([['user_id', User::getUserCashId()], ['state', 1]]))->first()->id;
            } catch (Exception $e) {
                throw new Exception('Abra una caja para poder registrar la nota de venta');
            }
        }
        // Para matricula, se busca el hijo en atributos
        $attributes = $inputs['attributes'] ?? [];
        $children = $attributes['children_customer_id'] ?? null;
        $type_period = isset($inputs['type_period']) ? $inputs['type_period'] : null;
        $quantity_period = isset($inputs['quantity_period']) ? $inputs['quantity_period'] : null;
        $d_of_issue = new Carbon($inputs['date_of_issue']);
        $automatic_date_of_issue = null;

        if ($type_period && $quantity_period > 0) {

            $add_period_date = ($type_period == 'month') ? $d_of_issue->addMonths($quantity_period) : $d_of_issue->addYears($quantity_period);
            $automatic_date_of_issue = $add_period_date->format('Y-m-d');
        }

        if (key_exists('series_id', $inputs)) {
            $series = Series::query()->find($inputs['series_id'])->number;
        } else {
            $series = $inputs['series'];
        }

        $number = null;

        if ($inputs['id']) {
            $number = $inputs['number'];
        } else {

            if (
                SaleNote::where('soap_type_id', $this->company->soap_type_id)
                ->where('series', $series)
                ->count() == 0
            ) {
                $series_configuration = SeriesConfiguration::where([['document_type_id', "80"], ['series', $series]])->first();
                $number = $series_configuration->number ?? 1;
            } else {
                $document = SaleNote::query()
                    ->select('number')->where('soap_type_id', $this->company->soap_type_id)
                    ->where('series', $series)
                    ->orderBy('number', 'desc')
                    ->first();

                if ($document) {
                    $number = $document->number + 1;
                } else {
                    $series_configuration = SeriesConfiguration::where([['document_type_id', "80"], ['series', $series]])->first();
                    if ($series_configuration) {
                        $number = $series_configuration->number ?? 1;
                    } else {
                        $number = 1;
                    }
                }
                // $number = ($document) ? $document->number + 1 : 1;
            }
        }
        $seller_id = isset($inputs['seller_id']) ? (int)$inputs['seller_id'] : 0;
        if ($seller_id == 0) {
            // $seller_id = $inputs['seller_id'];
        }
        $additional_information = isset($inputs['additional_information']) ? $inputs['additional_information'] : '';
        $address_id = isset($inputs['address_id']) ? $inputs['address_id'] : null;
        $customer = PersonInput::set($inputs['customer_id'], $address_id !== 'main' ? $address_id : null);
        if ($address_id) {
            $customer['address_id'] = $address_id;
        }




        $values = [
            'additional_information' => $additional_information,
            'automatic_date_of_issue' => $automatic_date_of_issue,
            'user_id' => $seller_id == 0 ? auth()->user()->id : $seller_id,
            'seller_id' => $seller_id,
            'external_id' => Str::uuid()->toString(),
            'customer' => $customer,
            'establishment' => EstablishmentInput::set($inputs['establishment_id']),
            'soap_type_id' => $this->company->soap_type_id,
            'state_type_id' => '01',
            'series' => $series,
            'number' => $number,
            'cash_id' => $cash_id
        ];

        if (!empty($children)) {
            $customer = PersonInput::set($inputs['customer_id']);
            $customer['children'] = PersonInput::set($children);
            $values['customer'] = $customer;
        }

        $this->setDataPointSystemToValues($values, $inputs);


        unset($inputs['series_id']);
        $inputs = array_merge($inputs, $values);

        return $inputs;
    }


    /**
     * Configuración de sistema por puntos
     *
     * @param  array $values
     * @param  array $inputs
     * @return void
     */
    private function setDataPointSystemToValues(&$values, $inputs)
    {
        $configuration = Configuration::getDataPointSystem();

        $created_from_pos = $inputs['created_from_pos'] ?? false;

        if ($created_from_pos && $configuration->enabled_point_system) {
            $values['point_system'] = $configuration->enabled_point_system;
            $values['point_system_data'] = [
                'point_system_sale_amount' => $configuration->point_system_sale_amount,
                'quantity_of_points' => $configuration->quantity_of_points,
                'round_points_of_sale' => $configuration->round_points_of_sale,
            ];
        }
    }


    //    public function recreatePdf($sale_note_id)
    //    {
    //        $this->sale_note = SaleNote::find($sale_note_id);
    //        $this->createPdf();
    //    }

    private function setFilename()
    {
        $name = [$this->sale_note->series, $this->sale_note->number, date('Ymd')];
        if ($this->sale_note->website_id) {
            $company = Company::where('website_id', $this->sale_note->website_id)->first();
            if ($company) {
                $name[] = $company->number;
            }
        }
        $this->sale_note->filename = join('-', $name);

        $this->sale_note->unique_filename = $this->sale_note->filename;

        $this->sale_note->save();
    }

    public function toPrint($external_id, $format)
    {

        $sale_note = SaleNote::where('external_id', $external_id)->first();

        if (!$sale_note) throw new Exception("El código {$external_id} es inválido, no se encontro la nota de venta relacionada");

        $this->reloadPDF($sale_note, $format, $sale_note->filename);
        $temp = tempnam(sys_get_temp_dir(), 'sale_note');

        file_put_contents($temp, $this->getStorage($sale_note->filename, 'sale_note'));

        /*
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$sale_note->filename.'"'
        ];
        */

        return response()->file($temp, $this->generalPdfResponseFileHeaders($sale_note->filename));
    }

    private function reloadPDF($sale_note, $format, $filename)
    {
        $this->createPdf($sale_note, $format, $filename);
    }


    /**
     * 
     * Obtener el ancho del ticket dependiendo del formato
     *
     * @param  string $format_pdf
     * @return int
     */
    public function getWidthTicket($format_pdf)
    {
        $width = 0;

        if (config('tenant.enabled_template_ticket_80')) {
            $width = 76;
        } else {
            switch ($format_pdf) {
                case 'ticket_58':
                    $width = 56;
                    break;
                case 'ticket_50':
                    $width = 45;
                    break;
                default:
                    $width = 78;
                    break;
            }
        }

        return $width;
    }


    public function changeValuesPdfTicket50(&$pdf_margin_right, &$pdf_margin_left, &$base_height)
    {
        $pdf_margin_right = 2;
        $pdf_margin_left = 2;
        $base_height = 90;
    }


    public function createPdf($sale_note = null, $format_pdf = null, $filename = null, $output = 'pdf')
    {
        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();
        $pdf->shrink_tables_to_fit = 1;
        $this->company = ($this->company != null) ? $this->company : Company::active();
        $this->document = ($sale_note != null) ? $sale_note : $this->sale_note;
        $configuration = Configuration::first();
        $this->configuration = $configuration;
        if ($configuration->multi_companies &&  $this->document->website_id) {
            $company = Company::where('website_id', $this->document->website_id)->first();
            if ($company) {
                $this->company = $company;
            }
        }
        // $configuration = $this->configuration->formats;
        $establishment = Establishment::find($this->document->establishment_id);
        if ($establishment->template_sale_notes) {
            $base_template = $establishment->template_sale_notes;
        } else {
            $base_template = $establishment->template_pdf;
        }



        if (($format_pdf === 'ticket') or
            ($format_pdf === 'ticket_58') or
            ($format_pdf === 'ticket_50')
        ) {
            // $base_template = Establishment::find($this->document->establishment_id)->template_ticket_pdf;
            if ($establishment->template_sale_notes_ticket) {
                $base_template = $establishment->template_sale_notes_ticket;
            } else {
                $base_template = $establishment->template_ticket_pdf;
            }
        }
        $html = $template->pdf($base_template, "sale_note", $this->company, $this->document, $format_pdf);


        $pdf_margin_top = 2;
        $pdf_margin_right = 5;
        $pdf_margin_bottom = 0;
        $pdf_margin_left = 5;

        // if (($format_pdf === 'ticket') OR ($format_pdf === 'ticket_58'))
        if (in_array($format_pdf, ['ticket', 'ticket_58', 'ticket_50'])) {
            // $width = ($format_pdf === 'ticket_58') ? 56 : 78 ;
            // if(config('tenant.enabled_template_ticket_80')) $width = 76;
            $width = $this->getWidthTicket($format_pdf);

            $company_logo      = ($this->company->logo) ? 40 : 0;
            $company_name      = (strlen($this->company->name) / 20) * 10;
            $company_address   = (strlen($this->document->establishment->address) / 30) * 10;
            $company_number    = $this->document->establishment->telephone != '' ? '10' : '0';
            $customer_name     = strlen($this->document->customer->name) > '25' ? '10' : '0';
            $customer_address  = (strlen($this->document->customer->address) / 200) * 10;
            $p_order           = $this->document->purchase_order != '' ? '10' : '0';

            $total_exportation = $this->document->total_exportation != '' ? '10' : '0';
            $total_free        = $this->document->total_free != '' ? '10' : '0';
            $total_unaffected  = $this->document->total_unaffected != '' ? '10' : '0';
            $total_exonerated  = $this->document->total_exonerated != '' ? '10' : '0';
            $total_taxed       = $this->document->total_taxed != '' ? '10' : '0';
            $quantity_rows     = count($this->document->items);
            $payments     = $this->document->payments()->count() * 2;
            $discount_global = 0;
            $extra_by_item_description = 0;
            foreach ($this->document->items as $it) {
                if (strlen($it->item->description) > 100) {
                    $extra_by_item_description += 24;
                }
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
            }
            $legends = $this->document->legends != '' ? '10' : '0';
            $bank_accounts = BankAccount::count() * 6;
            $base_height = 120;
            if ($base_template == 'ticket_kichi') {
                $pdf_margin_right = 1;
                $pdf_margin_left = 1;
                $pdf_margin_top = 1;
                $pdf_margin_bottom = 0;
            }

            if ($format_pdf === 'ticket_50') $this->changeValuesPdfTicket50($pdf_margin_right, $pdf_margin_left, $base_height);

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    $width,
                    $base_height +
                        ($quantity_rows * 8) +
                        ($discount_global * 3) +
                        $company_logo +
                        $payments +
                        $company_name +
                        $company_address +
                        $company_number +
                        $customer_name +
                        $customer_address +
                        $p_order +
                        $legends +
                        $bank_accounts +
                        $total_exportation +
                        $total_free +
                        $total_unaffected +
                        $total_exonerated +
                        $extra_by_item_description +
                        $total_taxed
                ],
                'margin_top' => $pdf_margin_top,
                'margin_right' => $pdf_margin_right,
                'margin_bottom' => $pdf_margin_bottom,
                'margin_left' => $pdf_margin_left
            ]);
        } else if ($format_pdf === 'a5') {

            $company_name      = (strlen($this->company->name) / 20) * 10;
            $company_address   = (strlen($this->document->establishment->address) / 30) * 10;
            $company_number    = $this->document->establishment->telephone != '' ? '10' : '0';
            $customer_name     = strlen($this->document->customer->name) > '25' ? '10' : '0';
            $customer_address  = (strlen($this->document->customer->address) / 200) * 10;
            $p_order           = $this->document->purchase_order != '' ? '10' : '0';

            $total_exportation = $this->document->total_exportation != '' ? '10' : '0';
            $total_free        = $this->document->total_free != '' ? '10' : '0';
            $total_unaffected  = $this->document->total_unaffected != '' ? '10' : '0';
            $total_exonerated  = $this->document->total_exonerated != '' ? '10' : '0';
            $total_taxed       = $this->document->total_taxed != '' ? '10' : '0';
            $quantity_rows     = count($this->document->items);
            $discount_global = 0;
            foreach ($this->document->items as $it) {
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
            }
            $legends           = $this->document->legends != '' ? '10' : '0';


            $alto = ($quantity_rows * 8) +
                ($discount_global * 3) +
                $company_name +
                $company_address +
                $company_number +
                $customer_name +
                $customer_address +
                $p_order +
                $legends +
                $total_exportation +
                $total_free +
                $total_unaffected +
                $total_exonerated +
                $total_taxed;
            $diferencia = 148 - (float)$alto;

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    210,
                    $diferencia + $alto
                ],
                'margin_top' => 2,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);
        } else {


            if (in_array($base_template, ['proforma_matricial'])) {

                $pdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => [
                        116,
                        140
                    ],
                    'margin_top' => 2,
                    'margin_right' => 3,
                    'margin_bottom' => 0,
                    'margin_left' => 3
                ]);
            }


            $pdf_font_regular = config('tenant.pdf_name_regular');
            $pdf_font_bold = config('tenant.pdf_name_bold');

            if ($pdf_font_regular != false) {
                $defaultConfig = (new ConfigVariables())->getDefaults();
                $fontDirs = $defaultConfig['fontDir'];

                $defaultFontConfig = (new FontVariables())->getDefaults();
                $fontData = $defaultFontConfig['fontdata'];

                $pdf = new Mpdf([
                    'fontDir' => array_merge($fontDirs, [
                        app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                            DIRECTORY_SEPARATOR . 'pdf' .
                            DIRECTORY_SEPARATOR . $base_template .
                            DIRECTORY_SEPARATOR . 'font')
                    ]),
                    'fontdata' => $fontData + [
                        'custom_bold' => [
                            'R' => $pdf_font_bold . '.ttf',
                        ],
                        'custom_regular' => [
                            'R' => $pdf_font_regular . '.ttf',
                        ],
                    ]
                ]);
            }
        }

        $path_css = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
            DIRECTORY_SEPARATOR . 'pdf' .
            DIRECTORY_SEPARATOR . $base_template .
            DIRECTORY_SEPARATOR . 'style.css');

        $stylesheet = file_get_contents($path_css);

        // para impresion automatica
        if ($output == 'html') return $this->getHtmlDirectPrint($pdf, $stylesheet, $html);

        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        if (config('tenant.pdf_template_footer') && $base_template != 'ticket_kichi_lite') {
            /* if (($format_pdf != 'ticket') AND ($format_pdf != 'ticket_58') AND ($format_pdf != 'ticket_50')) */
            if ($base_template != 'full_height') {
                $html_footer = $template->pdfFooter($base_template, $this->document);
            } else {
                $html_footer = $template->pdfFooter('default', $this->document);
            }
            $html_footer_legend = "";
            if ($base_template != 'legend_amazonia') {
                if ($this->configuration->legend_footer) {
                    $html_footer_legend = $template->pdfFooterLegend($base_template, $this->document);
                }
            }

            if (($format_pdf === 'ticket') || ($format_pdf === 'ticket_58') || ($format_pdf === 'ticket_50')) {
                $pdf->WriteHTML($html_footer . $html_footer_legend, HTMLParserMode::HTML_BODY);
            } else {
                $pdf->SetHTMLFooter($html_footer . $html_footer_legend);
            }
        }

        if ($base_template === 'brand') {

            if (($format_pdf === 'ticket') || ($format_pdf === 'ticket_58') || ($format_pdf === 'ticket_50')) {
                $pdf->SetHTMLHeader("");
                $pdf->SetHTMLFooter("");
            }
        }

        $helper_facturalo = new HelperFacturalo();

        if ($helper_facturalo->isAllowedAddDispatchTicket($format_pdf, 'sale-note', $this->document)) {
            $helper_facturalo->addDocumentDispatchTicket($pdf, $this->company, $this->document, [
                $template,
                $base_template,
                $width,
                ($quantity_rows * 8) + $extra_by_item_description
            ]);
        }


        $this->uploadFile($this->document->filename, $pdf->output('', 'S'), 'sale_note');
    }

    public function getHtmlDirectPrint(&$pdf, $stylesheet, $html)
    {
        $path_html = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'ticket_html.css');
        $ticket_html = file_get_contents($path_html);
        $pdf->WriteHTML($ticket_html, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        return "<style>" . $ticket_html . $stylesheet . "</style>" . $html;
    }


    /**
     * 
     * Impresión directa en pos
     *
     * @param  int $id
     * @param  string $format
     * @return string
     */
    public function toTicket($id, $format = 'ticket')
    {
        $document = SaleNote::find($id);

        if (!$document) throw new Exception("El código {$id} es inválido, no se encontro documento relacionado");

        return $this->createPdf($document, $format, $document->filename, 'html');
    }


    public function uploadFile($filename, $file_content, $file_type)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }
    public function receipt($id)
    {
        $data = SaleNote::findOrFail($id);

        $company = Company::active();
        $establishment = $data->establishment;
        $total_ =  250;
        $items = count($data->items) * 20;
        $total_ = $total_ + $items;
        $pdf = Pdf::loadView('tenant.package_handler.receipt', compact("data", "company", "establishment"))
            ->setPaper(array(0, 0, 180, $total_), 'portrait');
        $filename = "Recibo de caja";

        return $pdf->stream($filename . '.pdf');
    }
    public function table($table)
    {
        switch ($table) {
            case 'customers':

                $customers = Person::whereType('customers')
                    ->whereIsEnabled()->orderBy('name')->take(20)->get()->transform(function (Person $row) {
                        return [
                            'id' => $row->id,
                            'description' => $row->number . ' - ' . $row->name,
                            'seller' => $row->seller,
                            'seller_id' => $row->seller_id,
                            'name' => $row->name,
                            'number' => $row->number,
                            'person_type_id' => $row->person_type_id,
                            'barcode' => $row->barcode,
                            'is_driver' => (bool) $row->is_driver,
                            'identity_document_type_id' => $row->identity_document_type_id,
                            'identity_document_type_code' => $row->identity_document_type->code,
                            'all_addresses' => $row->all_addresses(),

                        ];
                    });

                return $customers;

                break;

            case 'items':

                return SearchItemController::getItemsToSaleNote();
                $establishment_id = auth()->user()->establishment_id;
                $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
                // $warehouse_id = ($warehouse) ? $warehouse->id:null;

                $items_u = Item::whereWarehouse()->whereIsActive()->whereNotIsSet()->orderBy('description')->take(20)->get();

                $items_s = Item::where('unit_type_id', 'ZZ')->whereIsActive()->orderBy('description')->take(10)->get();

                $items = $items_u->merge($items_s);

                return collect($items)->transform(function ($row) use ($warehouse) {

                    /** @var Item $row */
                    return $row->getDataToItemModal($warehouse);
                    /* Movido al modelo */
                    $detail = $this->getFullDescription($row, $warehouse);
                    $id_cupones = $row->id_cupones;  //cupones

                    return [
                        'id' => $row->id,
                        'full_description' => $detail['full_description'],
                        'brand' => $detail['brand'],
                        'category' => $detail['category'],
                        'stock' => $detail['stock'],
                        'description' => $row->description,
                        'currency_type_id' => $row->currency_type_id,
                        'currency_type_symbol' => $row->currency_type->symbol,
                        'sale_unit_price' => round($row->sale_unit_price, 2),
                        'purchase_unit_price' => $row->purchase_unit_price,
                        'unit_type_id' => $row->unit_type_id,
                        'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                        'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                        'has_igv' => (bool) $row->has_igv,
                        'lots_enabled' => (bool) $row->lots_enabled,
                        'series_enabled' => (bool) $row->series_enabled,
                        'is_set' => (bool) $row->is_set,
                        'warehouses' => collect($row->warehouses)->transform(function ($row) use ($warehouse_id) {
                            return [
                                'warehouse_id' => $row->warehouse->id,
                                'warehouse_description' => $row->warehouse->description,
                                'stock' => $row->stock,
                                'checked' => ($row->warehouse_id == $warehouse_id) ? true : false,
                            ];
                        }),
                        'item_unit_types' => $row->item_unit_types,
                        'lots' => [],
                        // 'lots' => $row->item_lots->where('has_sale', false)->where('warehouse_id', $warehouse_id)->transform(function($row) {
                        //     return [
                        //         'id' => $row->id,
                        //         'series' => $row->series,
                        //         'date' => $row->date,
                        //         'item_id' => $row->item_id,
                        //         'warehouse_id' => $row->warehouse_id,
                        //         'has_sale' => (bool)$row->has_sale,
                        //         'lot_code' => ($row->item_loteable_type) ? (isset($row->item_loteable->lot_code) ? $row->item_loteable->lot_code:null):null
                        //     ];
                        // }),
                        'lots_group' => collect($row->lots_group)->transform(function ($row) {
                            return [
                                'id'  => $row->id,
                                'code' => $row->code,
                                'quantity' => $row->quantity,
                                'date_of_due' => $row->date_of_due,
                                'checked'  => false,
                                'warehouse_id' => $row->warehouse_id,
                            ];
                        }),
                        'lot_code' => $row->lot_code,
                        'date_of_due' => $row->date_of_due,
                        'id_cupones' => $row->id_cupones ///cupones

                    ];
                });


                break;
            default:

                return [];

                break;
        }
    }


    public function searchItems(Request $request)
    {

        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        $warehouse_id = ($warehouse) ? $warehouse->id : null;
        $items = SearchItemController::getItemsToSaleNote($request);

        return compact('items');
    }


    public function searchItemById($id)
    {
        return  SearchItemController::getItemsToSaleNote(null, $id);
        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        $search_item = $this->getItemsNotServicesById($id);

        if (count($search_item) == 0) {
            $search_item = $this->getItemsServicesById($id);
        }

        $items = collect($search_item)->transform(function ($row) use ($warehouse) {
            $detail = $this->getFullDescription($row, $warehouse);
            return [
                'id' => $row->id,
                'full_description' => $detail['full_description'],
                'brand' => $detail['brand'],
                'category' => $detail['category'],
                'stock' => $detail['stock'],
                'description' => $row->description,
                'currency_type_id' => $row->currency_type_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => round($row->sale_unit_price, 2),
                'purchase_unit_price' => $row->purchase_unit_price,
                'unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'has_igv' => (bool)$row->has_igv,
                'lots_enabled' => (bool)$row->lots_enabled,
                'series_enabled' => (bool)$row->series_enabled,
                'is_set' => (bool)$row->is_set,
                'warehouses' => collect($row->warehouses)->transform(function ($row) use ($warehouse) {
                    return [
                        'warehouse_id' => $row->warehouse->id,
                        'warehouse_description' => $row->warehouse->description,
                        'stock' => $row->stock,
                        'checked' => ($row->warehouse_id == $warehouse->id) ? true : false,
                    ];
                }),
                'item_unit_types' => $row->item_unit_types,
                'lots' => [],
                'lots_group' => collect($row->lots_group)->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'code' => $row->code,
                        'quantity' => $row->quantity,
                        'date_of_due' => $row->date_of_due,
                        'checked' => false,
                        'warehouse_id' => $row->warehouse_id,
                        'warehouse' => $row->warehouse_id ? $row->warehouse->description : null,
                    ];
                }),
                'lot_code' => $row->lot_code,
                'date_of_due' => $row->date_of_due
            ];
        });

        return compact('items');
    }


    public function getFullDescription($row, $warehouse)
    {

        $desc = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
        $category = ($row->category) ? "{$row->category->name}" : "";
        $brand = ($row->brand) ? "{$row->brand->name}" : "";

        if ($row->unit_type_id != 'ZZ') {
            $warehouse_stock = ($row->warehouses && $warehouse) ? number_format($row->warehouses->where('warehouse_id', $warehouse->id)->first() != null ? $row->warehouses->where('warehouse_id', $warehouse->id)->first()->stock : 0, 2) : 0;
            $stock = ($row->warehouses && $warehouse) ? "{$warehouse_stock}" : "";
        } else {
            $stock = '';
        }


        $desc = "{$desc} - {$brand}";

        return [
            'full_description' => $desc,
            'brand' => $brand,
            'category' => $category,
            'stock' => $stock,
        ];
    }


    public function searchCustomerById($id)
    {
        return $this->searchClientById($id);
    }

    public function option_tables($sale_note_id = null)
    {
        $sellers = User::GetSellers(false)->get();
        $configuration = Configuration::select(['multi_companies', 'restrict_sale_items_cpe', 'global_discount_type_id', 'seller_establishments_all'])->first();
        $global_discount_types = ChargeDiscountType::getGlobalDiscounts();
        $company_id = null;
        $payment_destinations = $this->getPaymentDestinations();

        $establishment_info = null;
        $sale_note = SaleNote::find($sale_note_id);
        $website_id  = $sale_note ? $sale_note->website_id : null;
        if ($configuration->multi_companies && $sale_note_id && $website_id) {
            $company_alter = Company::where('website_id', $website_id)->first();
            $document_number = $company_alter->document_number;
            $key = 'cash_' . auth()->user()->id;
            $company_active_id = Cache::put($key, $website_id, 60);
            User::find(auth()->user()->id)->update(['company_active_id' => $website_id]);
            $company_id = $company_alter->website_id;
            $hostname = Hostname::where('website_id', $website_id)->first();
            $client = SystemClient::where('hostname_id', $hostname->id)->first();
            $tenancy = app(Environment::class);
            $tenancy->tenant($client->hostname->website);
            $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
            $establishment_info = EstablishmentInput::set($establishment->id);
            $series = Series::where('establishment_id', $establishment->id)->get();
        } else {
            $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
            if ($configuration->seller_establishments_all) {
                $series = Series::where('contingency', false)->get();
            } else {
                $series = Series::where('establishment_id', $establishment->id)->get();
            }
        }
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])->where('active', true)->get();
        $payment_method_types = PaymentMethodType::all();


        return compact(
            'establishment_info',
            'company_id',
            'series',
            'document_types_invoice',
            'payment_method_types',
            'payment_destinations',
            'sellers',
            'configuration',
            'global_discount_types'
        );
    }

    public function sendEmail(Request $request)
    {
        // $company = Company::active();
        $record = SaleNote::find($request->input('id'));
        $customer = Person::find($record->customer_id);
        $customer_email = $request->input('customer_email');
        $email = $customer_email;
        $message = $request->input('message');
        $mailable = new SaleNoteEmail($customer, $record, $message);
        $id = (int) $request->id;
        $sendIt = EmailController::SendMail($email, $mailable, $id, 2);

        return [
            'success' =>  $sendIt
        ];
    }
    public function email(Request $request)
    {
        $company = Company::active();
        $record = SaleNote::find($request->input('id'));
        $customer_email = $request->input('customer_email');
        $sent_all = $request->input('send_all');
        $email = $customer_email;
        $mailable = new SaleNoteEmail($company, $record);
        $id = (int) $request->id;
        $sendIt = EmailController::SendMail($email, $mailable, $id, 2);
        /*
        Configuration::setConfigSmtpMail();
        $array_email = explode(',', $customer_email);
        if (count($array_email) > 1) {
            foreach ($array_email as $email_to) {
                $email_to = trim($email_to);
                if(!empty($email_to)) {
                    Mail::to($email_to)->send(new SaleNoteEmail($company, $record));
                }
            }
        } else {
            Mail::to($customer_email)->send(new SaleNoteEmail($company, $record));
        }*/

        return [
            'success' => $sendIt,
            'message' => ($sendIt) ? 'Email enviado con éxito' : 'Ocurrió un error al enviar el email'
        ];
    }


    public function dispatches(Request $request)
    {
        $input =  $request->input;

        if ($input) {
            $dispatches = Dispatch::latest()
                ->when(strpos($input, '-') !== false, function ($query) use ($input) {
                    [$series, $number] = explode('-', $input);
                    return $query->where('series', $series)
                        ->where('number', $number);
                })
                ->when(strpos($input, '-') === false, function ($query) use ($input) {
                    return $query->where('number', 'like', "%{$input}%");
                })
                ->take(20)
                ->get(['id', 'series', 'number'])
                ->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'series' => $row->series,
                        'number' => $row->number,
                        'number_full' => "{$row->series}-{$row->number}",
                    ];
                });
        } else {
            $dispatches = Dispatch::latest()
                ->take(20)
                ->get(['id', 'series', 'number'])
                ->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'series' => $row->series,
                        'number' => $row->number,
                        'number_full' => "{$row->series}-{$row->number}",
                    ];
                });
        }



        return $dispatches;
    }

    public function enabledConcurrency(Request $request)
    {

        $sale_note = SaleNote::findOrFail($request->id);
        $sale_note->enabled_concurrency = $request->enabled_concurrency;
        $sale_note->update();

        return [
            'success' => true,
            'message' => ($sale_note->enabled_concurrency) ? 'Recurrencia activada' : 'Recurrencia desactivada'
        ];
    }

    public function anulate($id)
    {
        $obj = null;

        DB::connection('tenant')->transaction(function () use ($id, &$obj) {

            $obj = SaleNote::find($id);
            $obj->state_type_id = 11;
            $obj->save();

            \App\Services\DebtReversalService::reverseDebtPayments($obj->id, 'sale_note');
            if ($obj->no_stock == false) {
                // $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
                $warehouse = Warehouse::where('establishment_id', $obj->establishment_id)->first();

                foreach ($obj->items as $sale_note_item) {

                    // voided sets
                    $this->voidedSaleNoteItem($sale_note_item, $warehouse);
                    // voided sets

                    //habilito las series
                    // ItemLot::where('item_id', $item->item_id )->where('warehouse_id', $warehouse->id)->update(['has_sale' => false]);
                    $this->voidedLots($sale_note_item);
                    $this->voidedItemSize($sale_note_item);
                }
            }
        });

        if (BusinessTurn::isIntegrateSystem()) {
            ProductionOrder::where('sale_note_id', $id)->update(['production_order_state_id' => 6]);
            DispatchOrder::where('sale_note_id', $id)->update(['dispatch_order_state_id' => 7]);
        }

        // Auditar anulación - Usar el modelo, no el controller
        if ($obj) {
            $obj->auditVoided('Anulado');
        }

        return [
            'success' => true,
            'message' => 'N. Venta anulada con éxito'
        ];
    }

    public function voidedSaleNoteItem($sale_note_item, $warehouse)
    {

        $warehouse_id = ($sale_note_item->warehouse_id) ? $sale_note_item->warehouse_id : $warehouse->id;

        if (!$sale_note_item->item->is_set) {

            $presentationQuantity = (!empty($sale_note_item->item->presentation)) ? $sale_note_item->item->presentation->quantity_unit : 1;

            $sale_note_item->sale_note->inventory_kardex()->create([
                'date_of_issue' => date('Y-m-d'),
                'item_id' => $sale_note_item->item_id,
                'warehouse_id' => $warehouse_id,
                'quantity' => $sale_note_item->quantity * $presentationQuantity,
            ]);

            $wr = ItemWarehouse::where([['item_id', $sale_note_item->item_id], ['warehouse_id', $warehouse_id]])->first();

            if ($wr) {
                $wr->stock =  $wr->stock + ($sale_note_item->quantity * $presentationQuantity);
                $wr->save();
            }
        } else {

            $item = Item::findOrFail($sale_note_item->item_id);

            foreach ($item->sets as $it) {

                $ind_item  = $it->individual_item;
                $item_set_quantity  = ($it->quantity) ? $it->quantity : 1;
                $presentationQuantity = 1;
                $warehouse = $this->findWarehouse($sale_note_item->sale_note->establishment_id);
                $this->createInventoryKardexSaleNote($sale_note_item->sale_note, $ind_item->id, (1 * ($sale_note_item->quantity * $presentationQuantity * $item_set_quantity)), $warehouse->id, $sale_note_item->id);
                if (!$sale_note_item->sale_note->order_note_id) $this->updateStock($ind_item->id, (1 * ($sale_note_item->quantity * $presentationQuantity * $item_set_quantity)), $warehouse->id);
            }
        }

        (new ItemCostHistoryService)->insertPendingItemCostReset($sale_note_item->item_id, $warehouse->id, $sale_note_item->sale_note->date_of_issue);
    }


    /**
     * 
     * Totales de nota venta, se visualiza en el listado
     *
     * @param  Request $request
     * @return array
     */
    public function totals(Request $request)
    {
        $baseQuery = $this->getRecords($request)->whereStateTypeAccepted()->whereFilterWithOutRelations()->filterCurrencyPen();

        $result = DB::connection('tenant')->selectOne("
            SELECT
                SUM(sn.total) as total_pen,
                COALESCE(SUM(snp.payment), 0) as total_paid_pen
            FROM ({$baseQuery->toSql()}) as sn
            LEFT JOIN sale_note_payments snp ON sn.id = snp.sale_note_id
        ", $baseQuery->getBindings());

        $total_pen = $result->total_pen ?? 0;
        $total_paid_pen = $result->total_paid_pen ?? 0;
        $total_pending_paid_pen = $total_pen - $total_paid_pen;

        return [
            'total_pen' => number_format($total_pen, 2, ".", ""),
            'total_paid_pen' => number_format($total_paid_pen, 2, ".", ""),
            'total_pending_paid_pen' => number_format($total_pending_paid_pen, 2, ".", "")
        ];
    }



    public function downloadExternal($external_id, $format = null)
    {
        $configuration = Configuration::first();
        if ($format == null && $configuration->paper_size_modal_documents) {
            $format = $configuration->paper_size_modal_documents;
        } else if ($format == null) {
            $format = 'a4';
        }
        $document = SaleNote::where('external_id', $external_id)->first();
        $this->reloadPDF($document, $format, null);
        return $this->downloadStorage($document->filename, 'sale_note');
    }


    public function saveFees($sale_note, $fees)
    {
        foreach ($fees as $row) {
            $sale_note->fee()->create($row);
        }
    }
    public function savePayments($sale_note, $payments, $cash_id = null)
    {
        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        $total = $sale_note->total;
        $total_canceled_by_quotation = false;
        $quotation = $sale_note->quotation;
        if ($quotation) {
            $payments_quotation = $quotation->payments->sum('payment');
            $total_canceled_by_quotation = $payments_quotation >= $quotation->total;
            if ($total_canceled_by_quotation && !$is_integrate_system) {
                $quotation->payments()->delete();
            }
        }
        $balance = $total - collect($payments)->sum('payment');

        $search_cash = ($balance < 0) ? collect($payments)->firstWhere('payment_method_type_id', '01') : null;

        $this->apply_change = false;

        if ($balance < 0 && $search_cash) {

            $payments = collect($payments)->map(function ($row) use ($balance) {

                $change = null;
                $payment = $row['payment'];

                if ($row['payment_method_type_id'] == '01' && !$this->apply_change) {
                    if ($balance != 0) {
                        $change = abs($balance);
                        // Verificar si el cambio excede el límite de la columna (10,2)
                        if ($change > 9999999999.99) {
                            $change = null;
                        }
                        $payment = $row['payment'] - abs($balance);
                        $this->apply_change = true;
                    }
                }


                return [
                    "id" => null,
                    "document_id" => null,
                    'filename' => isset($row['filename']) ? $row['filename'] : null,
                    'temp_path' => isset($row['temp_path']) ? $row['temp_path'] : null,
                    'filename_quotation' => isset($row['filename_quotation']) ? $row['filename_quotation'] : null,
                    "sale_note_id" => null,
                    "note_credit_id" => isset($row['note_credit_id']) ? $row['note_credit_id'] : null,
                    "date_of_payment" => $row['date_of_payment'],
                    "payment_method_type_id" => $row['payment_method_type_id'],
                    "reference" => $row['reference'],
                    "glosa" => isset($row['glosa']) ? $row['glosa'] : null,
                    "payment_destination_id" => isset($row['payment_destination_id']) ? $row['payment_destination_id'] : null,
                    "payment_filename" => isset($row['payment_filename']) ? $row['payment_filename'] : null,
                    "change" => $change,
                    "payment" => $payment
                ];
            });
        }



        foreach ($payments as $row) {

            if ($balance < 0 && !$this->apply_change) {
                $change = abs($balance);
                // Verificar si el cambio excede el límite de la columna (10,2)
                if ($change > 9999999999.99) {
                    $change = null;
                }
                $row['change'] = $change;
                $row['payment'] = $row['payment'] - abs($balance);
                $this->apply_change = true;
            }


            $record_payment = $sale_note->payments()->create($row);
            if (isset($row['note_credit_id'])) {
                $this->savePaymentWithNote('sale_note', $record_payment->id, $row['note_credit_id'], $row['payment']);
            }

            if (isset($row['payment_destination_id'])) {

                $this->createGlobalPayment($record_payment, $row);
            }

            if (isset($row['payment_filename'])) {
                $record_payment->payment_file()->create([
                    'filename' => $row['payment_filename']
                ]);
            }
            if (!$record_payment->hasGlobalPayment()) {
                $user_id = isset($row['user_id']) ? $row['user_id'] : $record_payment->sale_note->user_id;
                $user_tmp = User::find($user_id);
                if ($user_tmp->user_cash_id) {
                    $user_id = $user_tmp->user_cash_id;
                }
                $custom_row = [
                    'user_id' => $user_id,
                    'payment_destination_id' => 'cash'
                ];
                $this->createGlobalPayment($record_payment, $custom_row);
            }

            // para carga de voucher
            $this->saveFilesFromPayments($row, $record_payment, 'sale_notes');
        }
    }
    public function voidedItemSize($sale_note_item)
    {
        $item = $sale_note_item->item;
        $item_id = $sale_note_item->item_id;
        $sizes = isset($item->sizes_selected) ? $item->sizes_selected : [];
        $to_update = [];
        foreach ($sizes as $size) {
            $to_update[] = [
                'item_id' => $item_id,
                'size' => $size->size,
                'stock' => $size->qty
            ];
        }
        foreach ($to_update as $update_data) {
            DB::connection('tenant')->table('item_sizes')
                ->where('item_id', $update_data['item_id'])
                ->where('size', $update_data['size'])
                ->increment('stock', $update_data['stock']);
        }
    }

    private function voidedLots($item)
    {

        $i_lots_group = isset($item->item->lots_group) ? $item->item->lots_group : [];
        $lot_group_selecteds_filter = collect($i_lots_group)->where('compromise_quantity', '>', 0);
        $lot_group_selecteds =  $lot_group_selecteds_filter->all();

        if (count($lot_group_selecteds) > 0) {

            foreach ($lot_group_selecteds as $lt) {
                $lot = ItemLotsGroup::find($lt->id);
                $lot->quantity = $lot->quantity + $lt->compromise_quantity;
                $lot->save();
            }
        }

        if (isset($item->item->lots)) {
            foreach ($item->item->lots as $it) {
                if ($it->has_sale == true) {
                    $ilt = ItemLot::find($it->id);
                    $ilt->has_sale = false;
                    $ilt->save();
                }
            }
        }
        $this->recalculateStock($item->item_id);
    }
    function recalculateStock($item_id)
    {
        $total = 0;
        $item_warehouses = ItemWarehouse::where('item_id', $item_id)->get();
        foreach ($item_warehouses as $item_warehouse) {
            $total += $item_warehouse->stock;
        }
        $item = Item::find($item_id);
        $item->stock = $total;
        $item->save();
    }

    public function saleNotesByClientDispatch(Request $request)
    {
        $request->validate([
            'client_id' => 'required|numeric|min:1',
        ]);
        $clientId = $request->client_id;
        $records = SaleNote::without(['user', 'soap_type', 'state_type', 'currency_type', 'payments'])
            ->select('series', 'number', 'id', 'date_of_issue', 'total')
            ->where('customer_id', $clientId)
            ->whereNull('document_id')
            ->whereIn('state_type_id', ['01', '03', '05'])
            ->orderBy('number', 'desc');

        $dateOfIssue = $request->date_of_issue;
        $dateOfDue = $request->date_of_due;
        if ($dateOfIssue && !$dateOfDue) {
            $records = $records->where('date_of_issue', $dateOfIssue);
        }

        if ($dateOfIssue && $dateOfDue) {
            $records = $records->whereBetween('date_of_issue', [$dateOfIssue, $dateOfDue]);
        }
        $sum_total = 0;
        $records = $records->take(20)
            ->get();
        $sum_total = number_format($records->sum('total'), 2);
        return response()->json([
            'success' => true,
            'data' => $records,
            'sum_total' => $sum_total,
        ], 200);
    }
    public function saleNotesByTransportFormat(Request $request)
    {

        $clientId = $request->client_id;
        $sellerId = $request->seller_id;
        $records = SaleNote::without(['user', 'soap_type', 'state_type', 'currency_type', 'payments'])
            ->select('series', 'number', 'id', 'date_of_issue', 'total')
            ->whereNull('document_id')
            ->whereDoesntHave('transportFormatItems')
            ->whereIn('state_type_id', ['01', '03', '05'])
            ->orderBy('number', 'desc');


        if ($clientId) {
            $records = $records->where('customer_id', $clientId);
        }
        $dateOfIssue = $request->date_of_issue;
        $dateOfDue = $request->date_of_due;
        if ($dateOfIssue && !$dateOfDue) {
            $records = $records->where('date_of_issue', $dateOfIssue);
        }

        if ($dateOfIssue && $dateOfDue) {
            $records = $records->whereBetween('date_of_issue', [$dateOfIssue, $dateOfDue]);
        }
        if ($sellerId) {
            $records = $records->where('seller_id', $sellerId);
        }
        $sum_total = 0;
        $records = $records->take(20)
            ->get();
        $sum_total = number_format($records->sum('total'), 2);
        return response()->json([
            'success' => true,
            'data' => $records,
            'sum_total' => $sum_total,
        ], 200);
    }
    public function getSellers()
    {
        $sellers = User::whereIn('type', ['seller', 'admin'])->orderBy('name')->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->name,
                'type' => $row->type,
            ];
        });
        return response()->json(['data' => $sellers], 200);
    }
    public function saleNotesByClient(Request $request)
    {
        $request->validate([
            'client_id' => 'required|numeric|min:1',
        ]);
        $clientId = $request->client_id;
        $records = SaleNote::without(['user', 'soap_type', 'state_type', 'currency_type', 'payments'])
            ->select('series', 'number', 'id', 'date_of_issue', 'total')
            ->where('customer_id', $clientId)
            ->whereNull('document_id')
            ->whereIn('state_type_id', ['01', '03', '05'])
            ->orderBy('number', 'desc');

        $dateOfIssue = $request->date_of_issue;
        $dateOfDue = $request->date_of_due;
        if ($dateOfIssue && !$dateOfDue) {
            $records = $records->where('date_of_issue', $dateOfIssue);
        }

        if ($dateOfIssue && $dateOfDue) {
            $records = $records->whereBetween('date_of_issue', [$dateOfIssue, $dateOfDue]);
        }

        $sum_total = 0;
        $records = $records->take(20)
            ->get();
        $sum_total = number_format($records->sum('total'), 2);
        return response()->json([
            'success' => true,
            'data' => $records,
            'sum_total' => $sum_total,
        ], 200);
    }

    public function getItemsFromNotesDispatch(Request $request)
    {
        $request->validate([
            'notes_id' => 'required|array',
        ]);


        if ($request->select_all) {

            $items = SaleNoteItem::whereIn('sale_note_id', $request->notes_id)->get();
        } else {

            $items = SaleNoteItem::whereIn('sale_note_id', $request->notes_id)
                ->select('item_id', 'quantity')
                ->get();
        }


        return response()->json([
            'success' => true,
            'data' => $items,
        ], 200);
    }
    public function generateFormat(Request $request)
    {
        $request->validate([
            'notes_id' => 'required|string',
        ]);
        $notes_id = explode(',', $request->notes_id);
        $transport_format_id = $request->transport_format_id;
        $sale_note_items = SaleNoteItem::whereIn('sale_note_id', $notes_id)

            ->select('item_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('item_id')
            ->get()
            ->map(function ($row) {
                $item = Item::find($row->item_id); // Asumiendo que tienes una relación con la tabla de items
                return [
                    'item_description' => $item ? $item->description : 'Descripción no disponible',
                    'item_id' => $row->item_id,
                    'total_quantity' => $row->total_quantity,
                ];
            });

        if (!$transport_format_id) {
            $date_of_issue = Carbon::now();
            try {
                DB::connection('tenant')->beginTransaction();
                $transportFormat = TransportFormat::create([
                    'date_of_issue' => $date_of_issue
                ]);
                foreach ($notes_id as $sale_note_id) {
                    TransportFormatItem::create([
                        'transport_format_id' => $transportFormat->id,
                        'sale_note_id' => $sale_note_id,
                    ]);
                }
                DB::connection('tenant')->commit();
            } catch (\Exception $e) {
                DB::connection('tenant')->rollBack();
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        } else {
            $transportFormat = TransportFormat::find($transport_format_id);
            $date_of_issue = Carbon::parse($transportFormat->date_of_issue);
        }
        return (new SaleNoteTransportFormatExport)->records($sale_note_items)
            ->company(Company::first())
            ->seller(User::find($request->seller_id))
            ->download('FORMATO_TRANSPORTE_' . $date_of_issue->format('Y-m-d') . '.xlsx');
    }
    public function getItemsFromNotes(Request $request)
    {
        $request->validate([
            'notes_id' => 'required|array',
        ]);


        if ($request->select_all) {

            $items = SaleNoteItem::whereIn('sale_note_id', $request->notes_id)->get();
        } else {

            $items = SaleNoteItem::whereIn('sale_note_id', $request->notes_id)
                ->select('item_id', 'quantity', 'unit_price', 'affectation_igv_type_id', 'percentage_igv')
                ->get();
        }


        return response()->json([
            'success' => true,
            'data' => $items,
        ], 200);
    }


    public function getConfigGroupItems()
    {
        return [
            'group_items_generate_document' => Configuration::select('group_items_generate_document')->first()->group_items_generate_document
        ];
    }

    /**
     * Proceso de duplicar una nota de venta por post
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function duplicate(Request $request)
    {
        // return $request->id;
        try {
            DB::connection('tenant')->beginTransaction();
            $configuration = Configuration::first();
            $obj = SaleNote::find($request->id);
            $is_from_suscription = $request->is_from_suscription;
            $date_of_issue = $request->date_of_issue;
            $date_of_due = $request->date_of_due;
            $this->sale_note = $obj->replicate();
            if ($is_from_suscription) {
                $this->sale_note->date_of_issue = $date_of_issue;
                $this->sale_note->due_date = $date_of_due;
            }
            $this->sale_note->external_id = Str::uuid()->toString();
            $this->sale_note->state_type_id = '01';
            $this->sale_note->number = SaleNote::getLastNumberByModel($obj);
            $this->sale_note->unique_filename = null;
            if ($configuration->purchase_orden_in_item_set) {
                $this->sale_note->purchase_order = null;
            }
            $this->sale_note->changed = false;
            $this->sale_note->document_id = null;

            $this->sale_note->save();

            foreach ($obj->items as $row) {
                $new = $row->replicate();
                $new->sale_note_id = $this->sale_note->id;
                $new->save();
            }

            $this->setFilename();
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'data' => [
                    'id' => $this->sale_note->id,
                ],
            ];
        } catch (Exception $e) {

            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function nextPayment(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            $sale_note = SaleNote::find($request->id);
            $today = Carbon::now()->format('Y-m-d');
            $user_rel_suscription_plan_id = $sale_note->user_rel_suscription_plan_id;
            $customer_id = $sale_note->customer_id;
            $user_rel_suscription_plan = DB::connection('tenant')->table('user_rel_suscription_plans')->where('parent_customer_id', $customer_id)->where('id', $user_rel_suscription_plan_id)->first();
            $cat_period_id = $user_rel_suscription_plan->cat_period_id;
            $cat_period = DB::connection('tenant')->table('cat_periods')->where('id', $cat_period_id)->first();
            $type_period = $cat_period->period;
            $customer_id = $user_rel_suscription_plan->parent_customer_id;
            $full_suscription_credit = DB::connection('tenant')->table('person_full_suscription_credit')->where('person_id', $customer_id)->first();
            $credit_amount = 0;
            if ($full_suscription_credit) {
                $credit_amount = $full_suscription_credit->amount;
            }
            $user_rel_suscription_plan_id = $user_rel_suscription_plan->id;
            $quantity_period = $user_rel_suscription_plan->quantity_period;
            $sale_notes = DB::connection('tenant')->table('sale_notes')->where('customer_id', $customer_id)->where('user_rel_suscription_plan_id', $user_rel_suscription_plan_id)->count();
            $last_sale_note = DB::connection('tenant')->table('sale_notes')->select('id', 'date_of_issue')->where('customer_id', $customer_id)->where('user_rel_suscription_plan_id', $user_rel_suscription_plan_id)->orderBy('date_of_issue', 'desc')->first();
            if (!$last_sale_note) {
                return [
                    'success' => false,
                    'message' => 'No se encontró la última nota de venta',
                ];
            }

            if ($sale_notes < $quantity_period) {
                $date_of_issue = Carbon::parse($last_sale_note->date_of_issue);
                if ($type_period == 'Y') {
                    $date_of_issue->addYear();
                } else {
                    $date_of_issue->addMonth();
                }


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
                    if ($credit_amount > 0 && $payment_method_type_id) {
                        $request_body = [
                            'sale_note_id' => $sale_note_id,
                            'payment' => $credit_amount,
                            'payment_destination_id' => 'cash',
                            'payment_method_type_id' => $payment_method_type_id->id,
                            'date_of_payment' => $date_of_issue->format('Y-m-d')
                        ];
                        $response =    (new SaleNotePaymentController())->storeFullSuscriptionPayment(new SaleNotePaymentRequest($request_body));
                        if ($response['success']) {
                            DB::connection('tenant')->table('person_full_suscription_credit')->where('person_id', $customer_id)->update([
                                'amount' => 0
                            ]);
                        }
                    }
                    DB::connection('tenant')->table('user_rel_suscription_plans')->where('id', $user_rel_suscription_plan_id)->update([
                        'sale_notes' => DB::raw("CONCAT(sale_notes, ',', $sale_note_id)")
                    ]);
                }
                DB::connection('tenant')->commit();
                return [
                    'success' => true,
                    'message' => 'Nota de venta emitida correctamente',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'La última nota de venta del plan de suscripción ya fue emitida',
                ];
            }
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }


    /**
     * Retorna la vistsa para la configuracion de migracion avanzada en Nota de venta
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function SetAdvanceConfiguration()
    {
        $migrationConfiguration = MigrationConfiguration::getCollectionData();
        return view('tenant.configuration.sale_notes', compact('migrationConfiguration'));
    }

    /**
     * Guarda los datos para la migracion de nota de venta
     *
     * @param Request $request
     * @return array
     */
    public function SaveSetAdvanceConfiguration(Request $request)
    {

        $data = $request->all();
        $data['success'] = false;
        $data['send_data_to_other_server'] = (bool)$data['send_data_to_other_server'];

        if (auth()->user()->type !== 'admin') {
            $data['message'] = 'No puedes realizar cambios';
            return $data;
        }
        $configuration = Configuration::first();
        $migrationConfiguration = MigrationConfiguration::first();
        if (empty($migrationConfiguration)) $migrationConfiguration = new MigrationConfiguration($data);

        $migrationConfiguration->setUrl($data['url'])->setApiKey($data['apiKey'])->push();
        $configuration->setSendDataToOtherServer($data['send_data_to_other_server'])->push();

        $data['url'] = $migrationConfiguration->getUrl();
        $data['apiKey'] = $migrationConfiguration->getApiKey();
        $data['send_data_to_other_server'] = $configuration->isSendDataToOtherServer();
        $data['success'] = true;
        $data['message'] = 'Ha sido acualizado';
        return $data;
    }

    public function transformDataOrder(Request $request)
    {

        $data = SaleNoteHelper::transformForOrder($request->all());

        return [
            'data' => $data
        ];
    }


    /**
     * Retorna items para generar json en checkout de hoteles
     *
     * @param Request $request
     * @return array
     */
    public function getItemsByIds(Request $request)
    {
        return SearchItemController::TransformToModalSaleNote(Item::whereIn('id', $request->ids)->get());
    }


    /**
     * Elimina la relación con factura (problema antiguo respecto un nuevo campo en notas de venta que se envía de forma incorrecta a la factura siendo esta rechazada)
     * No se previene el error en este metodo
     *
     *
     */
    public function deleteRelationInvoice(Request $request)
    {
        try {
            $sale_note = SaleNote::find($request->id);

            $document = Document::find($sale_note->document_id);
            $document->sale_note_id = null;
            $document->save();

            $sale_note->changed = 0;
            $sale_note->document_id = null;
            $sale_note->save();
        } catch (RequestException $e) {
            return ['success' => false];
        }

        return ['success' => true];
    }
    public function saveOrUpdateBox(Request $request)
    {
        $id = $request->input('id');
        $box = $request->input('box');
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'No se encontró la nota de venta']);
        }
        $saleNote = SaleNote::findOrFail($id);
        $message = 'Box actualizado correctamente';

        $saleNote->box = $box;
        $saleNote->save();

        return response()->json(['success' => true, 'message' => $message]);
    }


    /**
     * 
     * Data para generar cpe desde nv
     *
     * @param  int $id
     * @return SaleNoteGenerateDocumentResource
     */
    // public function recordGenerateDocument($id)
    // {
    //     return new SaleNoteGenerateDocumentResource(SaleNote::findOrFail($id));
    // }


}
