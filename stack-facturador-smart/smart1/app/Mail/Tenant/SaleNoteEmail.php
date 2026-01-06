<?php

namespace App\Mail\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\BusinessTurn\Models\BusinessTurn;

class SaleNoteEmail extends Mailable
{
    use Queueable, SerializesModels;
    use StorageDocument;

    public $customer;
    public $document;
    public $localmessage;
    public $isIntegrateSystem;

    public function __construct($customer, $document, $localmessage = null)
    {
        $this->customer = $customer;
        $this->document = $document;
        $this->localmessage = $localmessage;
        $this->isIntegrateSystem = BusinessTurn::isIntegrateSystem();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $pdf = $this->getStorage($this->document->filename, 'sale_note');

        return $this->subject('Envio de Nota de Venta')
                    ->from(config('mail.username'), 'Nota de Venta')
                    ->view('tenant.templates.email.sale_note')
                    ->attachData($pdf, $this->document->filename.'.pdf');
    }
}