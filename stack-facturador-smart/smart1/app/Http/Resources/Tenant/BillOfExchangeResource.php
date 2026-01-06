<?php

namespace App\Http\Resources\Tenant;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BillOfExchangeResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request
	 * @return array
	 */
	public function toArray($request)
	{
		$bill_of_exchange_currency_type_id = $this->currency_type_id;
		return [
			'id' => $this->id,
			'code' => $this->code,
			'date_of_due' => Carbon::parse($this->date_of_due)->format('Y-m-d'),
			'number' => $this->number,
			'series' => $this->series,
			'establishment' => $this->establishment,
			'establishment_id' => $this->establishment_id,
			'number_full' => "{$this->series}-{$this->number}",
			'user' => $this->user,
			'total' => $this->total,
			'documents' => $this->items->transform(function ($item) use ($bill_of_exchange_currency_type_id) {
				$is_fee = $item->is_fee;
				$document = $item->document;
				$total = $item->total;
				return [
					'id' => $item->id,
					'number_full' => $document->number_full,
					'is_fee' => $is_fee,
					'fee_id' => $item->fee_id,
					'document_id' => $item->document_id,
					'total' => $total,
					'current_currency_type_symbol' => $bill_of_exchange_currency_type_id,
				];
			}),
		];
	}
}
