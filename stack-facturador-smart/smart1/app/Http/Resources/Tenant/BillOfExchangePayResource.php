<?php

namespace App\Http\Resources\Tenant;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BillOfExchangePayResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request
	 * @return array
	 */
	public function toArray($request)
	{
		return [
			'id' => $this->id,
			'date_of_due' => Carbon::parse($this->date_of_due)->format('Y-m-d'),
			'number' => $this->number,
			'series' => $this->series,
			'establishment' => $this->establishment,
			'establishment_id' => $this->establishment_id,
			'number_full' => "{$this->series}-{$this->number}",
			'user' => $this->user,
			'total' => $this->total,
		];
	}
}
