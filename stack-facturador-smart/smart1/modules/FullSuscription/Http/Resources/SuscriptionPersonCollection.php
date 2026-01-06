<?php

    namespace Modules\FullSuscription\Http\Resources;

use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\Person;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;

    class SuscriptionPersonCollection extends ResourceCollection
    {
        /**
         * Transform the resource collection into an array.
         *
         * @param Request $request
         *
         * @return mixed
         */
        public function toArray($request)
        {
            $personIds = $this->collection->pluck('id')->toArray();
            $fullSuscriptionCreditData = DB::connection('tenant')->table('person_full_suscription_credit')
                ->whereIn('person_id', $personIds)
                ->get()
                ->keyBy('person_id');
            $identityDocumentTypeIds = $this->collection->pluck('identity_document_type_id')->unique()->toArray();
            $identityDocumentTypes = IdentityDocumentType::whereIn('id', $identityDocumentTypeIds)->get()->keyBy('id');
            
            // Una sola consulta optimizada para todos los IDs
            $saleNotesDebt = DB::connection('tenant')->table('sale_notes')
                ->select(
                    'sale_notes.customer_id',
                    DB::raw('SUM(sale_notes.total) as total_facturado')
                )
                ->whereIn('sale_notes.customer_id', $personIds)
                ->where('sale_notes.user_rel_suscription_plan_id', '>', 0)
                ->groupBy('customer_id')
                ->get()
                ->keyBy('customer_id');

            $totalPagado = DB::connection('tenant')->table('sale_note_payments')
                ->select(
                    'sale_notes.customer_id',
                    DB::raw('SUM(sale_note_payments.payment) as total_pagado')
                )
                ->join('sale_notes', 'sale_note_payments.sale_note_id', '=', 'sale_notes.id')
                ->whereIn('sale_notes.customer_id', $personIds)
                ->where('sale_notes.user_rel_suscription_plan_id', '>', 0)
                ->groupBy('sale_notes.customer_id')
                ->get()
                ->keyBy('customer_id');

            return $this->collection->transform(function ($row, $key) use ($identityDocumentTypes, $saleNotesDebt, $totalPagado, $fullSuscriptionCreditData) {
                /** @var Person $row */
                $saleNoteDebt = 0;
                
                $facturadoItem = $saleNotesDebt->get($row->id);
                $pagadoItem = $totalPagado->get($row->id);
                
                $totalFacturado = $facturadoItem ? $facturadoItem->total_facturado : 0;
                $totalPagado = $pagadoItem ? $pagadoItem->total_pagado : 0;
                $saldoPendiente = $totalFacturado - $totalPagado;
                
                if ($saldoPendiente > 0) {
                    $saleNoteDebt = $saldoPendiente;
                }
                
                $fullSuscriptionCredit = 0;
                $existsFullSuscriptionCredit = $fullSuscriptionCreditData->where('person_id', $row->id)->first();
                if($existsFullSuscriptionCredit){
                    $fullSuscriptionCredit = $existsFullSuscriptionCredit->amount;
                }
                
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'full_suscription_credit' => number_format($fullSuscriptionCredit, 2, '.', ','),
                    'pending' => number_format($saleNoteDebt, 2, '.', ','),
                    'parent_id' => $row->parent_id,
                    'number' => $row->number,
                    'telephone' => $row->telephone,
                    'email' => $row->email,
                    'discord_channel' => $row->discord_channel,
                    'document_type' => $identityDocumentTypes[$row->identity_document_type_id]->description,
                ];
            });
        }
    }
