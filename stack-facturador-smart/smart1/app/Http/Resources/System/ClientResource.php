<?php

namespace App\Http\Resources\System;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request
     *
     * @return array
     */
    public function toArray($request)
    {

        // $all_modules = Module::orderBy('description')->get();
        // $modules_in_user = $this->modules->pluck('module_id')->toArray();
        // dd($all_modules,$modules_in_user);
        // $modules = [];
        // foreach ($all_modules as $module)
        // {
        //     $modules[] = [
        //         'id' => $module->id,
        //         'description' => $module->description,
        //         'checked' => (bool) in_array($module->id, $modules_in_user)
        //     ];
        // }

        return [
            'pse' => (bool)$this->pse,
            'is_rus'  => (bool)$this->is_rus,
            'id' => $this->id,
            'hostname' => $this->hostname->fqdn,
            'cert_smart' => (bool)$this->cert_smart,
            'name' => $this->name,
            'email' => $this->email,
            'token' => $this->token,
            'number' => $this->number,
            'plan_id' => $this->plan_id,
            'locked_items' => (bool)$this->locked_items,
            'active' => (bool)$this->active,
            'identity_document_type_id' => $this->identity_document_type_id,
            'locked' => (bool)$this->locked,
            'locked_emission' => (bool)$this->locked_emission,
            'modules' => $this->modules,
            'apps' => $this->apps,
            'levels' => $this->levels,
            'create_restaurant' => $this->create_restaurant,
            'cert_pem' => $this->cert_pem,
            'cert_pfx' => $this->cert_pfx,
            'config_system_env' => $this->config_system_env,
            //'count_doc' => $this->count_doc,
            // 'max_documents' => (int) $this->plan->limit_documents,
            //'count_user' => $this->count_user,
            //'max_users' => (int) $this->plan->limit_users,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'cert_smart' => (bool)$this->cert_smart,
            'soap_send_id' => (bool)$this->cert_smart  ? '03' : $this->soap_send_id,
            'soap_type_id' => $this->soap_type_id,
            'soap_username' => $this->soap_username,
            'soap_password' => $this->soap_password,
            'soap_url' => $this->soap_url,
            'config_system_env' => (bool)$this->config_system_env,
            'certificate' => $this->certificate,
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'smtp_user' => $this->smtp_user,
            'smtp_password' => null, // dont show smtp password
            'smtp_encryption' => $this->smtp_encryption,
            'users' => $this->users,
            'password' => $this->password,
            'password_cdt' => $this->password_cdt,
        ];
    }
}
