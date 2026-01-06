<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

/**
 * Class UserResource
 *
 * @package App\Http\Resources\Tenant
 */
class UserResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     *
     * @return array
     */
    public function toArray($request) {
        $modules = $this->getCurrentModuleByTenant()
                        ->pluck('module_id')
                        ->toArray();
        $levels = $this->getCurrentModuleLevelByTenant()
                       ->pluck('module_level_id')
                       ->toArray();

        //si modules y levels estan vacios, se asigna el modulo por defecto
        if(empty($modules) && empty($levels)){
            $establishment = DB::connection('tenant')->table('modules')->where('value', 'establishments')->first();
            $module_id = $establishment->id;
            $user_id = $this->id;
            //si el id es  1 ingresar todos los modules y levels, sino en  modulos no ingresar el de establecimientos y ni el de levels
            if($user_id == 1){
                $modules = DB::connection('tenant')->table('modules')->pluck('id')->toArray();
                $levels = DB::connection('tenant')->table('module_levels')->pluck('id')->toArray();
            }else{
                $modules = DB::connection('tenant')->table('modules')->where('id', '!=', $module_id)->pluck('id')->toArray();
                $levels = DB::connection('tenant')->table('module_levels')->where('module_id', '!=', $module_id)->pluck('id')->toArray();
            }

            //guardar los modulos y niveles por defecto
            $this->setModuleAndLevelModule($modules, $levels);

           
        }
        
        return [
            'edit_payment' => (bool) $this->edit_payment,
            'show_packers_in_document' => (bool) $this->show_packers_in_document,
            'show_dispatchers_in_document' => (bool) $this->show_dispatchers_in_document,
            'show_box_in_document' => (bool) $this->show_box_in_document,
            'show_packers_document' => (bool) $this->show_packers_document,
            'show_dispatchers_document' => (bool) $this->show_dispatchers_document,
            'show_box' => (bool) $this->show_box,
            'edit_pos' => (bool) $this->edit_pos,
            'reopen_pos' => (bool) $this->reopen_pos,
            'delete_pos' => (bool) $this->delete_pos,
            'create_order_delivery' => (bool) $this->create_order_delivery,
            'edit_order_delivery' => (bool) $this->edit_order_delivery,
            'voided_order_delivery' => (bool) $this->voided_order_delivery,
            'voided_cpe' => (bool) $this->voided_cpe,
            'note_cpe' => (bool) $this->note_cpe,
            'voided_sale_note' => (bool) $this->voided_sale_note,
            'pos_lite' => (bool) $this->pos_lite,
            'user_cash_id' => $this->user_cash_id,
            'auditor' => (bool)$this->auditor,
            'id'               => $this->id,
            'email'            => $this->email,
            'name'             => $this->name,
            'api_token'        => $this->api_token,
            'establishment_id' => $this->establishment_id,
            'type'             => $this->type,
            'zone_id'             => $this->zone_id,
            'modules'          => $modules,
            'init_route'      => $this->init_route,
            'levels'           => $levels,
            'locked'           => (bool)$this->locked,
            'integrate_user_type_id' => $this->integrate_user_type_id,
            'document_id'      => $this->document_id,
            'permission_edit_cpe' => $this->permission_edit_cpe,
            'recreate_documents' => $this->recreate_documents,
            'series_id'        => ($this->series_id == 0) ? null : $this->series_id,
            'create_payment' => $this->create_payment,
            'delete_payment' => $this->delete_payment,
            'edit_purchase' => $this->edit_purchase,
            'annular_purchase' => $this->annular_purchase,
            'delete_purchase' => $this->delete_purchase,

            
            'identity_document_type_id' => $this->identity_document_type_id,
            'number' => $this->number,
            'address' => $this->address,
            'names' => $this->names,
            'last_names' => $this->last_names,
            'personal_email' => $this->personal_email,
            'corporate_email' => $this->corporate_email,
            'personal_cell_phone' => $this->personal_cell_phone,
            'corporate_cell_phone' => $this->corporate_cell_phone,
            'date_of_birth' => $this->date_of_birth,
            'contract_date' => $this->contract_date,
            'position' => $this->position,
            'multiple_default_document_types' => $this->multiple_default_document_types,

            'photo_filename' => $this->photo_filename,
            'photo_temp_image' => $this->getPhotoForView(),
            'photo_temp_path' => null,
            'default_document_types' => $this->default_document_types->transform(function($row){
                return $row->getDataMultipleDocumentType();
            }),
            'permission_force_send_by_summary' => $this->permission_force_send_by_summary,
        ];
    }
}
