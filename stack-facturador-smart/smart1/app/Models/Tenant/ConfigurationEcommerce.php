<?php

namespace App\Models\Tenant;

class ConfigurationEcommerce extends ModelTenant
{
    protected $table = "configuration_ecommerce";

    protected $fillable = [
        'plin_number',
        'plin_name',
        'plin_qr',
        'yape_number',
        'yape_name',
        'yape_qr',
        'image_complaints',
        'url_complaints',
        'image_payment_methods',
        'copyrigth_text',
        'columns_virtual_store',
        'information_contact_name',
        'information_contact_email',
        'information_contact_phone',
        'information_contact_address',
        'script_paypal',
        'token_private_culqui',
        'token_public_culqui',
        'link_youtube',
        'link_twitter',
        'link_facebook',
        'tag_shipping',
        'tag_dollar',
        'tag_support',
        'phone_whatsapp',
        'woocommerce_api_url',
        'woocommerce_api_key',
        'woocommerce_api_secret',
        'woocommerce_api_version',
        'woocommerce_api_last_sync',
        'last_id'
    ];



    function hasWoocommerce()
    {
        return $this->woocommerce_api_url && $this->woocommerce_api_key && $this->woocommerce_api_secret;
    }
}
