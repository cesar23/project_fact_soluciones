<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class ConfigurationEcommerceResource extends JsonResource
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
            'image_payment_methods' => $this->image_payment_methods ? asset('storage/uploads/ecommerce') . "/" . $this->image_payment_methods : null,
            'copyrigth_text' => $this->copyrigth_text,
            'image_complaints' => $this->image_complaints ? asset('storage/uploads/ecommerce') . "/" . $this->image_complaints : null,
            'url_complaints' => $this->url_complaints,
            'information_contact_name' => $this->information_contact_name,
            'information_contact_email' =>  $this->information_contact_email,
            'information_contact_phone' =>  $this->information_contact_phone,
            'information_contact_address' =>  $this->information_contact_address,
            'script_paypal' => $this->script_paypal,
            'token_private_culqui' => $this->token_private_culqui,
            'token_public_culqui' => $this->token_public_culqui,
            'columns_virtual_store' => $this->columns_virtual_store,
            'logo' => $this->logo,
            'favicon' => $this->favicon,
            'link_youtube' => $this->link_youtube,
            'link_twitter' => $this->link_twitter,
            'link_facebook' => $this->link_facebook,
            'tag_shipping' => $this->tag_shipping,
            'tag_dollar' => $this->tag_dollar,
            'tag_support' => $this->tag_support,
            'phone_whatsapp' => $this->phone_whatsapp,
            'image_yape' => $this->yape_qr ? asset('storage/uploads/ecommerce') . "/" . $this->yape_qr : null,
            'image_plin' => $this->plin_qr ? asset('storage/uploads/ecommerce') . "/" . $this->plin_qr : null, 
            'yape_number' => $this->yape_number,
            'plin_number' => $this->plin_number,
            'yape_name' => $this->yape_name,
            'plin_name' => $this->plin_name,

        ];
    }
}
