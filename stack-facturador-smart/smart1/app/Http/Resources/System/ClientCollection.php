<?php

namespace App\Http\Resources\System;

use App\Models\System\ClientPayment;
use App\Models\System\TrackApiPeruServices;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ClientCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        $currentDay = Carbon::now();
        return $this->collection->transform(function ($row, $key) use ($currentDay) {
            $has_debt = false;
            $apiPeruAsk = TrackApiPeruServices::where('client_id', $row->id)
                ->where('date_of_issue', '>=', $currentDay->firstOfMonth()->format('Y-m-d'))
                ->where('date_of_issue', '<=', $currentDay->lastOfMonth()->format('Y-m-d'))
                ->get()
                ->count();
            $payments = ClientPayment::where('client_id', $row->id)
                ->where('state', 0)
                ->count();
            $has_debt = ($payments > 0) ? true : false;
            return [
                'total_sale_date' => number_format($row->total_sale_date, 2, '.', ','),
                'uuid' => $row->uuid,
                'certificate_due' => $row->certificate_due,
                'comment' => $row->comment,
                'telephone' => $row->telephone,
                'monto' => $row->monto,
                'tiempo' => $row->tiempo,
                'cert_smart' => (bool) $row->cert_smart,
                'has_debt' => $has_debt,
                'id' => $row->id,
                'hostname' => $row->hostname->fqdn,
                'name' => $row->name,
                'active' => (bool) $row->active,
                'email' => $row->email,
                'token' => $row->token,
                'number' => $row->number,
                'plan' => $row->plan->name,
                'locked' => (bool) $row->locked,
                'locked_emission' => (bool) $row->locked_emission,
                'locked_users' => (bool) $row->locked_users,
                'locked_items' => (bool) $row->locked_items,
                'locked_tenant' => (bool) $row->locked_tenant,
                'count_doc' => $row->count_doc,
                'count_sales_notes' => $row->count_sales_notes,
                'max_documents' => (int) $row->plan->limit_documents,
                'count_user' => $row->count_user,
                'count_item' => $row->count_item,
                'config_system_env_tenant' => (bool) $row->config_system_env,
                'max_users' => (int) $row->plan->limit_users,
                'max_items' => (int) $row->plan->limit_items,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),

                // ciclo facturacion
                'start_billing_cycle' => ($row->start_billing_cycle) ? $row->start_billing_cycle->format('Y-m-d') : '',
                'end_billing_cycle' => ($row->end_billing_cycle) ? $row->end_billing_cycle->format('Y-m-d') : '',
                // 'init_cycle' => optional($row->init_cycle)->format('Y-m-d'),
                // 'end_cycle' => optional($row->end_cycle)->format('Y-m-d'),
                'count_doc_month' => $row->count_doc_month,
                'count_sales_notes_month' => $row->count_sales_notes_month,

                'select_date_billing' => '',
                'select_end_date_billing' => '',
                'soap_type' => $row->soap_type,
                'document_regularize_shipping' => $row->document_regularize_shipping,
                'document_not_sent' => $row->document_not_sent,
                'document_to_be_canceled' => $row->document_to_be_canceled,
                'queries_to_apiperu' => $apiPeruAsk,
                'locked_create_establishments' => $row->locked_create_establishments,
                'restrict_sales_limit' => $row->restrict_sales_limit,

                //para limite de establecimientos
                'quantity_establishments' => $row->quantity_establishments,
                'max_quantity_establishments' => $row->plan->establishments_limit,
                'establishments_unlimited' => $row->plan->establishments_unlimited,

                // para limite de ventas mensual
                'monthly_sales_total' => number_format($row->monthly_sales_total, 2, '.', ''),
                'max_sales_limit' => number_format($row->plan->sales_limit, 2, '.', ''),
                'sales_unlimited' => $row->plan->sales_unlimited,

                'users' => $row->users,
                'password' => $row->password,
                'password_cdt' => $row->password_cdt,

            ];
        });
    }
}
