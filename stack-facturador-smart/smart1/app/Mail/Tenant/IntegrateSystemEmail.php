<?php

namespace App\Mail\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Models\Tenant\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IntegrateSystemEmail extends Mailable
{
    use Queueable, SerializesModels;
    use StorageDocument;


    public $customer;
    public $localMessage;
    public $image;
    public function __construct($customer, $localMessage = null, $image  = null)
    {
        $this->customer = $customer;
        $this->image = $image;
        // $this->document = $document;
        $this->localMessage = $localMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // $pdf = $this->getStorage($this->document->filename, 'sale_note');
        $company = Company::first();
        $name = $company->name;
        $name = strtoupper($name);
        //si la imagen existe, es una url, obten el archivo y obten la extensión
        if ($this->image) {
            $image = $this->image;

            //guardamos el archivo en el tenant en una carpeta llamada 'agency_dispatch
            $file = file_get_contents($image);
            $ext = pathinfo($image, PATHINFO_EXTENSION);
        }
        $mail_ = $this->subject('Actualización de pedido')
            ->from(config('mail.username'), $name);
        if ($this->image) {
            $mail_->attachData($file, 'image.' . $ext);
        }

        $mail_ = $mail_->view('tenant.templates.email.integrate_system');

        return $mail_;
        // ->attachData($pdf, $this->document->filename.'.pdf');
    }
}
