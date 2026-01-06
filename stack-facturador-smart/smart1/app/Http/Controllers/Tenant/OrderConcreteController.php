<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\OrderConcreteCollection;
use App\Models\Tenant\Document;
use App\Models\Tenant\Person;
use App\Models\Tenant\SaleNote;
use Illuminate\Http\Request;
use App\Models\Tenant\OrderConcrete;
use App\Models\Tenant\OrderConcreteSupply;
use App\Models\Tenant\OrderConcreteAttention;
use App\Http\Resources\Tenant\OrderConcreteResource;
use App\Models\Tenant\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

/**
 * Class CompanyController
 *
 * @package App\Http\Controllers\Tenant
 * @mixin  Controller
 */
class OrderConcreteController extends Controller
{
    public function pdf($id)
    {
        $document = OrderConcrete::with(['customer', 'master', 'supplies', 'attentions'])->find($id);
        $company = Company::active();

        $pdf = Pdf::loadView('tenant.order_concrete.pdf', compact(
            "company",
            "document"
        ))
            ->setPaper('b5', 'landscape');
        $filename = "Orden_de_concreto_{$document->series}-{$document->number}";

        return $pdf->stream($filename . '.pdf');
    }

    public function prepareData(Request $request)
    {
        $document_type_id = $request->document_type_id;
        $document_id = $request->document_id;

        if ($document_type_id == '01' || $document_type_id == '03') {
            $document = Document::select('series', 'number', 'date_of_issue', 'customer_id')->find($document_id);
        } else {
            $document = SaleNote::select('series', 'number', 'date_of_issue', 'customer_id')->find($document_id);
        }
        $customer_id = $document->customer_id;
        $customer = Person::select('id', 'name', 'address', 'telephone', 'email')->find($customer_id);


        $data = [
            'document' => $document,
            'customer' => $customer,
        ];

        return response()->json($data);
    }

    public function index()
    {
        return view('tenant.order_concrete.index');
    }
    public function create(Request $request)
    {
        $document_type_id = $request->document_type_id;
        $document_id = $request->document_id;
        $order_concrete_id = $request->order_concrete_id;
        return view('tenant.order_concrete.create', compact('document_type_id', 'document_id', 'order_concrete_id'));
    }
    private function getNumberOrderConcrete($establishment_id)
    {
        $series = "OV" . $establishment_id;
        $last_number = OrderConcrete::query()
            ->select('number')
            ->where('series', $series)
            ->orderByRaw('number * 1 DESC')
            ->lockForUpdate()
            ->first()
            ->number ?? 0;

        $current_number = intval($last_number);
        $current_number++;
        return $current_number;
    }
    public function store(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            $user = auth()->user();
            $establishment = $user->establishment;
            $establishment_id = $establishment->id;
            $id = $request->id;
            if (!$id) {
                $series = "OV" . $establishment_id;
                $number = $this->getNumberOrderConcrete($establishment_id);
            } else {
                $order_concrete = OrderConcrete::find($id);
                $series = $order_concrete->series;
                $number = $order_concrete->number;
                $order_concrete->supplies()->delete();
                $order_concrete->attentions()->delete();
            }
            // $series = 
            // Formatear la hora usando Carbon
            $time = $request->form['hour'];
            if (strpos($time, 'T') !== false) {
                // Si viene en formato ISO, convertir a formato de hora local
                $time = \Carbon\Carbon::parse($time)->format('H:i:s');
            }

            $order_concrete = OrderConcrete::firstOrNew(['id' => $id]);
            $order_concrete->fill([
                'series' => $series,
                'number' => $number,
                'establishment_code' => $establishment->code,
                'address' => $request->form['address'],
                'master_id' => $request->form['master_id'],
                'customer_id' => $request->customer['id'],
                'user_id' => $user->id,
                'establishment_id' => $establishment->id,
                'date' => $request->form['date'],
                'hour' => $time,
                'electro' => $request->form['electro'],
                'volume' => $request->form['volume'],
                'mix_kg_cm2' => $request->form['mix_kg_cm2'],
                'type_cement' => $request->form['type_cement'],
                'pump' => $request->form['pump'],
                'other' => $request->form['other'],
                'observations' => $request->form['observations'],
                'treasury_reviewed_name' => $request->form['treasury_reviewed_name'],
                'plant_manager_reviewed_name' => $request->form['plant_manager_reviewed_name'],
                'plant_operator_reviewed_name' => $request->form['plant_operator_reviewed_name'],
                'manager_approved_name' => $request->form['manager_approved_name']
            ]);
            if (!$id) {
                $document_type_id = $request->document_type_id;
                $document_id = $request->document_id;
                if ($document_type_id == '01' || $document_type_id == '03') {
                    $order_concrete->document_id = $document_id;
                } else {
                    $order_concrete->sale_note_id = $document_id;
                }
            }
            $order_concrete->save();

            // Guardar supplies
            foreach ($request->supplies as $supply) {
                $order_concrete->supplies()->create([
                    'description' => $supply['description'],
                    'type' => $supply['type'],
                    'quantity' => $supply['quantity'],
                    'total' => $supply['total']
                ]);
            }

            // Guardar attentions
            foreach ($request->attentions as $attention) {
                $order_concrete->attentions()->create([
                    'dispatch_note' => $attention['dispatch_note'],
                    'quantity' => $attention['quantity']
                ]);
            }
            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Orden de concreto guardada correctamente',
                'data' => $order_concrete,

            ], 200);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function records()
    {
        $records = OrderConcrete::with(['customer', 'master'])
            ->orderBy('id', 'desc');


        return new OrderConcreteCollection($records->paginate(20));
    }

    public function destroy($id)
    {
        try {
            $order = OrderConcrete::findOrFail($id);
            $order->delete();

            return [
                'success' => true,
                'message' => 'Orden eliminada con éxito'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getData($id)
    {
        $order = OrderConcrete::with(['customer:id,name,address,telephone,email', 'master:id,name,telephone,number', 'supplies', 'attentions', 'document:id,series,number,date_of_issue', 'sale_note:id,series,number,date_of_issue'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
    public function edit($id)
    {
        $order = OrderConcrete::with(['customer', 'master', 'supplies', 'attentions'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $order_concrete = OrderConcrete::findOrFail($id);

            // Formatear la hora
            $time = $request->form['hour'];
            if (strpos($time, 'T') !== false) {
                $time = \Carbon\Carbon::parse($time)->format('H:i:s');
            }

            $order_concrete->fill([
                'address' => $request->form['address'],
                'master_id' => $request->form['master_id'],
                'date' => $request->form['date'],
                'hour' => $time,
                'electro' => $request->form['electro'],
                'volume' => $request->form['volume'],
                'mix_kg_cm2' => $request->form['mix_kg_cm2'],
                'type_cement' => $request->form['type_cement'],
                'pump' => $request->form['pump'],
                'other' => $request->form['other'],
                'observations' => $request->form['observations'],
                'treasury_reviewed_name' => $request->form['treasury_reviewed_name'],
                'plant_manager_reviewed_name' => $request->form['plant_manager_reviewed_name'],
                'plant_operator_reviewed_name' => $request->form['plant_operator_reviewed_name'],
                'manager_approved_name' => $request->form['manager_approved_name']
            ]);

            $order_concrete->save();

            // Actualizar supplies
            $order_concrete->supplies()->delete();
            foreach ($request->supplies as $supply) {
                $order_concrete->supplies()->create([
                    'description' => $supply['description'],
                    'type' => $supply['type'],
                    'quantity' => $supply['quantity'],
                    'total' => $supply['total']
                ]);
            }

            // Actualizar attentions
            $order_concrete->attentions()->delete();
            foreach ($request->attentions as $attention) {
                $order_concrete->attentions()->create([
                    'dispatch_note' => $attention['description'],
                    'quantity' => $attention['quantity']
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Orden de concreto actualizada correctamente',
                'data' => $order_concrete
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
