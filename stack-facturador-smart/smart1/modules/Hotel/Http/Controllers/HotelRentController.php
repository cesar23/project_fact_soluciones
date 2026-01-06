<?php

namespace Modules\Hotel\Http\Controllers;

use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Hotel\Models\HotelRent;
use Modules\Hotel\Models\HotelRoom;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use Modules\Hotel\Models\HotelRentItem;
use App\Models\Tenant\PaymentMethodType;
use Modules\Finance\Traits\FinanceTrait;
use App\Models\Tenant\Catalogs\DocumentType;
use Modules\Hotel\Http\Requests\HotelRentRequest;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use Illuminate\Http\Request;
use Modules\Hotel\Http\Requests\HotelRentItemRequest;
use Modules\Hotel\Models\HotelRentDocument;
use Modules\Hotel\Models\HotelReservation;

class HotelRentController extends Controller
{
	use FinanceTrait;

	public function documentEmitted($rentId){
		$id = request('id');
		$document_type_id = request('document_type_id');

		$document_type = $document_type_id !== '80' ? 'document_id' : 'sale_note_id';
		$rent = HotelRent::findOrFail($rentId);
		$items = HotelRentItem::where('hotel_rent_id', $rentId)->get();
		$rent->update([
			'arrears' => request('arrears'),
			'payment_status' => 'PAID',
		]);
		if ($id) {
			$rent->update([
				$document_type => $id,
			]);
		}
		foreach ($items as $item) {
			$item->update([
				'payment_status' => 'PAID',
			]);
		}

		$rent = HotelRent::with('room', 'room.category', 'items')->findOrFail($rentId);
		return response()->json([
			'success' => true,
			'message' => 'Información procesada de forma correcta.',
			'currentRent' => $rent
		], 200);
	}
	public function payments($rentId)
	{
		$rent = HotelRent::findOrFail($rentId);
		$total_rent = $rent->total_amount;
		$hotel_documents = HotelRentDocument::where('rent_id', $rentId)->get();
		$reservation_paid = null;
		if($rent->reservation_id){
			$reservation = HotelReservation::find($rent->reservation_id);
			$reservation_paid = $reservation->sale_note;
		}
		$documents_total = 0;
		$documents = [];
		if ($reservation_paid) {
			$documents_total = $reservation_paid->total;
			$documents[] = [
				'id' => $reservation_paid->id,
				'number' => $reservation_paid->number_full,
				'is_reservation' => true,
				'total' => $reservation_paid->total,
				'pdf' => "/sale-notes/print/{$reservation_paid->external_id}/a4"
			];
		}
		foreach ($hotel_documents as $hotel_document) {
			$document = null;
			$pdf = null;
			if ($hotel_document->document_id) {
				$document = Document::find($hotel_document->document_id);
				$pdf = "/print/document/{$document->external_id}/a4";
			} else {
				$document = SaleNote::find($hotel_document->sale_note_id);
				$pdf = "/sale-notes/print/{$document->external_id}/a4";
			}
			if ($document->state_type_id !== '11') {
				$documents_total += $document->total;
			}
			$documents[] = [
				'id' => $hotel_document->id,
				'document_id' => $hotel_document->document_id,
				'sale_note_id' => $hotel_document->sale_note_id,
				'total' => $document->total,
				'state_type_id' => $document->state_type_id,
				'number' => $document->number_full,
				'is_reservation' => false,
				'pdf' => $pdf
			];
		}

		return [
			'documents' => $documents,
			'documents_total' => $documents_total,
			'total_rent' => $total_rent
		];
	}
	public function rent(Request $request, $roomId)
	{
		$room = HotelRoom::with('category', 'rates.rate')
			->findOrFail($roomId);
		$reservation_id = $request->reservation_id;
		$affectation_igv_types = AffectationIgvType::whereActive()->get();

		return view('hotel::rooms.rent', compact('room', 'affectation_igv_types', 'reservation_id'));
	}

	public function store(HotelRentRequest $request, $roomId)
	{
		DB::connection('tenant')->beginTransaction();
		try {
			$room = HotelRoom::findOrFail($roomId);
			if ($room->status !== 'DISPONIBLE') {
				return response()->json([
					'success' => true,
					'message' => 'La habitación seleccionada no esta disponible',
				], 500);
			}
			$is_undefined_out = $request->undefined_out;
			$request->merge(['hotel_room_id' => $roomId]);
			$now = now();
			$request->merge(['input_date' => $now->format('Y-m-d')]);
			$request->merge(['input_time' => $now->format('H:i:s')]);
			if ($is_undefined_out) {
				//cambiar el valor de output_date, que sea un mes despues de la fecha de entrada
				$request->merge(['output_date' => $now->addMonth()->format('Y-m-d')]);
			}

			$rent = HotelRent::create($request->only('undefined_out', 'customer_id', 'customer', 'notes', 'towels', 'hotel_room_id', 'hotel_rate_id', 'duration', 'quantity_persons', 'payment_status', 'output_date', 'output_time', 'input_date', 'input_time', 'destiny', 'observations', 'reservation_id'));
			if ($request->reservation_id) {
				$reservation = HotelReservation::find($request->reservation_id);
				if ($reservation) {
					$reservation->update(['active' => false]);
				}
			}
			$room->status = 'OCUPADO';
			$room->save();

			// Agregando la habitación a la lista de productos
			$item = new HotelRentItem();
			$item->type = 'HAB';
			$item->hotel_rent_id = $rent->id;
			$item->item_id = $request->product['item_id'];
			$item->item = $request->product;
			$item->payment_status = $request->payment_status;
			$item->save();

			DB::connection('tenant')->commit();

			return response()->json([
				'success' => true,
				'message' => 'Habitación rentada de forma correcta.',
			], 200);
		} catch (\Throwable $th) {
			DB::connection('tenant')->rollBack();

			return response()->json([
				'success' => true,
				'message' => 'No se puede procesar su transacción. Detalles: ' . $th->getMessage(),
			], 500);
		}
	}

	public function searchCustomers()
	{
		$customers = $this->customers();

		return response()->json([
			'customers' => $customers,
		], 200);
	}

	public function showFormAddProduct($rentId)
	{
		$rent = HotelRent::with('room')
			->findOrFail($rentId);

		$establishment = Establishment::query()->find(auth()->user()->establishment_id);
		$configuration = Configuration::first();

		$products = HotelRentItem::where('hotel_rent_id', $rentId)
			->where('type', 'PRO')
			->get();

		return view('hotel::rooms.add-product-to-room', compact('rent', 'configuration', 'products', 'establishment'));
	}

	public function addProductsToRoom(HotelRentItemRequest $request, $rentId)
	{
		$idInRequest = [];
		foreach ($request->products as $product) {
			$item = HotelRentItem::where('hotel_rent_id', $rentId)
				->where('item_id', $product['item_id'])
				->first();
			if (!$item) {
				$item = new HotelRentItem();
				$item->type = 'PRO';
				$item->hotel_rent_id = $rentId;
				$item->item_id = $product['item_id'];
			}
			$item->item = $product;
			$item->payment_status = $product['payment_status'];
			$item->save();
			$idInRequest[] = $item->id;
		}

		// Borrar los items que no esten asignados con PRO
		$rent = HotelRent::find($rentId);
		$itemsToDelete = $rent->items->where('type', 'PRO')->whereNotIn('id', $idInRequest);
		foreach ($itemsToDelete as $deleteable) {
			$deleteable->delete();
		}
		return response()->json([
			'success' => true,
			'message' => 'Información actualizada.'
		], 200);
	}

	public function showFormChekout($rentId)
	{
		$rent = HotelRent::with('room', 'room.category', 'items')
			->findOrFail($rentId);
		$document_paid = null;
		if($rent->document_id){
			$document_for_rent = Document::find($rent->document_id);
			$document_paid = [
				'id' => $document_for_rent->id,
				'number' => $document_for_rent->number_full,
				'is_reservation' => false,
				'total' => $document_for_rent->total,
				'pdf' => "/print/document/{$document_for_rent->external_id}/a4",
				'is_invoice' => true
			];
		}
		if($rent->sale_note_id){
			$document_for_rent = SaleNote::find($rent->sale_note_id);
			$document_paid = [
				'id' => $document_for_rent->id,
				'number' => $document_for_rent->number_full,
				'is_reservation' => true,
				'total' => $document_for_rent->total,
				'pdf' => "/sale-notes/print/{$document_for_rent->external_id}/a4",
				'is_invoice' => false
			];
		}
		if($rent->document_emitted) return redirect()->route('hotel.reception.records');
		$diff = 0;
		$documents = [];
		$documents_total = 0;
		$reservation = HotelReservation::find($rent->reservation_id);
		if ($reservation) {
			$sale_note = $reservation->sale_note;
			if ($sale_note) {
				$documents_total = $sale_note->total;
				$documents[] = [
					'id' => $sale_note->id,
					'number' => $sale_note->number_full,
					'is_reservation' => true,
					'date_of_issue' => $sale_note->date_of_issue,
					'total' => $sale_note->total,
					'pdf' => "/sale-notes/print/{$sale_note->external_id}/a4"
				];
			}
		}

		$hotel_documents = HotelRentDocument::where('rent_id', $rentId)->get();
		foreach ($hotel_documents as $hotel_document) {
			$document = null;
			$pdf = null;
			if ($hotel_document->document_id) {
				$document = Document::find($hotel_document->document_id);
				$pdf = "/print/document/{$document->external_id}/a4";
			} else {
				$document = SaleNote::find($hotel_document->sale_note_id);
				$pdf = "/sale-notes/print/{$document->external_id}/a4";
			}
			if($document->state_type_id == '11') continue;
			if ($document) {
				$documents_total += $document->total;
				$documents[] = [
					'id' => $hotel_document->id,
					'document_id' => $hotel_document->document_id,
					'date_of_issue' => $document->date_of_issue,
					'sale_note_id' => $hotel_document->sale_note_id,
					'total' => $document->total,
					'number' => $document->number_full,
					'is_reservation' => false,
					'pdf' => $pdf
				];
			}
		}
		if ($rent->undefined_out) {
			$now = now();
			$rent->output_date = $now->format('Y-m-d');
			$rent->output_time = $now->format('H:i:s');
			$input_date = $rent->input_date . ' ' . $rent->input_time;
			$diff = $now->diffInDays($input_date);
		}
		$rent->duration = $diff;
		$rent->save();
		$room = $rent->items->firstWhere('type', 'HAB');

		$customer = Person::withOut('department', 'province', 'district')
			->findOrFail($rent->customer_id);

		$payment_method_types = PaymentMethodType::getPaymentMethodTypes();
		$payment_destinations = $this->getPaymentDestinations();
		$series = Series::where('establishment_id',  auth()->user()->establishment_id)->get();
		$document_types_invoice = DocumentType::whereIn('id', ['01', '03', '80'])->get();
		$affectation_igv_types = AffectationIgvType::whereActive()->get();

		return view('hotel::rooms.checkout', compact(
			'documents',
			'diff',
			'rent',
			'document_paid',
			'room',
			'customer',
			'payment_method_types',
			'payment_destinations',
			'series',
			'document_types_invoice',
			'affectation_igv_types'
		));
	}

	public function finalizeRentWithoutInvoice($rentId){
		$rent = HotelRent::findOrFail($rentId);
		$items = HotelRentItem::where('hotel_rent_id', $rentId)->get();
		$arrears = request('arrears');
		
		$rent->update([
			'arrears' => $arrears,
			'status' => 'FINALIZADO'
		]);
		foreach ($items as $item) {
			$item->update([
				'payment_status' => 'PAID',
			]);
		}
		HotelRoom::where('id', $rent->hotel_room_id)
			->update([
				'status' => 'LIMPIEZA'
			]);
		$rent = HotelRent::with('room', 'room.category', 'items')->findOrFail($rentId);
		return response()->json([
			'success' => true,
			'message' => 'Información procesada de forma correcta.',
			'currentRent' => $rent
		], 200);
	}
	public function finalizeRent($rentId)
	{
		$id = request('id');
		$document_type_id = request('document_type_id');

		$document_type = $document_type_id !== '80' ? 'document_id' : 'sale_note_id';
		$rent = HotelRent::findOrFail($rentId);
		$items = HotelRentItem::where('hotel_rent_id', $rentId)->get();
		$rent->update([
			'arrears' => request('arrears'),
			'payment_status' => 'PAID',
			'status'  => 'FINALIZADO'
		]);
		if ($id) {
			$rent->update([
				$document_type => $id,
			]);
		}
		foreach ($items as $item) {
			$item->update([
				'payment_status' => 'PAID',
			]);
		}
		HotelRoom::where('id', $rent->hotel_room_id)
			->update([
				'status' => 'LIMPIEZA'
			]);
		$rent = HotelRent::with('room', 'room.category', 'items')->findOrFail($rentId);
		return response()->json([
			'success' => true,
			'message' => 'Información procesada de forma correcta.',
			'currentRent' => $rent
		], 200);
	}

	public function rentItems($rentId)
	{
		return HotelRentItem::where('hotel_rent_id', $rentId)
			->where('type', 'PRO')
			->get();
	}
	private function customers()
	{
		$customers = Person::with('addresses')
			->whereType('customers')
			->whereIsEnabled()
			->whereIn('identity_document_type_id', [1, 4, 6])
			->orderBy('name');

		$query = request('input');
		$search_by_barcode = (bool)request('search_by_barcode');
		if ($query && $search_by_barcode) {

			$customers = $customers->where('barcode', 'like', "%{$query}%");
		} else {
			if (is_numeric($query)) {
				$customers = $customers->where('number', 'like', "%{$query}%");
			} else {
				$customers = $customers->where('name', 'like', "%{$query}%");
			}
		}

		$customers = $customers->take(20)
			->get()
			->transform(function ($row) {
				return [
					'id'                          => $row->id,
					'description'                 => $row->number . ' - ' . $row->name,
					'name'                        => $row->name,
					'number'                      => $row->number,
					'identity_document_type_id'   => $row->identity_document_type_id,
					'identity_document_type_code' => $row->identity_document_type->code,
					'addresses'                   => $row->addresses,
					'address'                     => $row->address,
					'internal_code'               => $row->internal_code,
					'barcode'					  => $row->barcode
				];
			});

		return $customers;
	}

	public function tables()
	{
		$customers = $this->customers();
		$configuration = Configuration::first()->getCollectionData();

		return response()->json([
			'customers' => $customers,
			'configuration' => $configuration,
		], 200);
	}
}
