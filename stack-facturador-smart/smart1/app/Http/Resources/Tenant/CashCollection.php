<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\ApiPeruDev\Data\ServiceData;

class CashCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        $configuration = Configuration::first();
        $service = new ServiceData();
        $user = auth()->user();
        return $this->collection->transform(function ($row, $key) use ($configuration, $service, $user) {

            $alter_company = [];
            $company = Company::active();
            if ($row->website_id) {
                $company = Company::where('website_id', $row->website_id)->first();
            }
            $alter_company['name'] = $company->name;
            $alter_company['number'] = $company->number;
            $establishment_description = '';
            if($configuration->cash_by_establishment && $row->establishment_id){
                $establishment_description = $row->establishment->description;
            }
            $exchange_rate_sale = null;
            $exchange_rate = $service->exchange($row->date_opening);
            if($exchange_rate && isset($exchange_rate['sale'])){
                $exchange_rate_sale = floatval($exchange_rate['sale']);
            }
            $reopen_pos = $configuration->reopen_cash || $user->reopen_pos;
            $edit_pos = $user->type === 'admin' || $user->type === 'superadmin' || $user->edit_pos;
            $delete_pos = $user->type === 'admin' || $user->type === 'superadmin'|| $user->delete_pos;


            return [
                'edit_pos' => $edit_pos,
                'exchange_rate_sale_to_cash' => $row->exchange_rate_sale_to_cash,
                'delete_pos' => $delete_pos,
                'reopen_pos' => $reopen_pos,
                'currency_type_id' => $row->currency_type_id ?? 'PEN',
                'establishment_description' => $establishment_description,
                'exchange_rate_sale' => $exchange_rate_sale,
                'alter_company' => $alter_company,
                'website_id' => $row->website_id,
                'id' => $row->id,
                'user_id' => $row->user_id,
                'user' => $row->user->name,
                'counter' => $row->counter,
                'date_opening' => $row->date_opening,
                'time_opening' => $row->time_opening,
                'opening' => "{$row->date_opening} {$row->time_opening}",
                'date_closed' => $row->date_closed,
                'money_count' => $row->money_count,
                'time_closed' => $row->time_closed,
                'closed' => "{$row->date_closed} {$row->time_closed}",
                'beginning_balance' => number_format($row->beginning_balance, $configuration->decimal_quantity, '.', ''),
                'beginning_balance_sale' => number_format($row->beginning_balance * $exchange_rate_sale, $configuration->decimal_quantity, '.', ''),
                'final_balance' => number_format($row->final_balance, $configuration->decimal_quantity, '.', ''),
                'final_balance_sale' => number_format($row->final_balance * $exchange_rate_sale, $configuration->decimal_quantity, '.', ''),
                'final_balance_with_banks' => number_format($row->final_balance_with_banks, $configuration->decimal_quantity, '.', ''),
                'final_balance_with_banks_sale' => number_format($row->final_balance_with_banks * $exchange_rate_sale, $configuration->decimal_quantity, '.', ''),
                'income' => $row->income,
                'expense' => $row->expense,
                'filename' => $row->filename,
                'state' => (bool) $row->state,
                'state_description' => ($row->state) ? 'Aperturada' : 'Cerrada',
                'reference_number' => $row->reference_number === 0 ?  $row->user->name : $row->reference_number,

            ];
        });
    }
}
