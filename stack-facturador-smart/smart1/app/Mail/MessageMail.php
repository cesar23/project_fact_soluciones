<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MessageMail extends Mailable
{
    use Queueable, SerializesModels;
    public $subject;
    public $body;
    public $files;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $body, $files)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->files = $files;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        foreach ($this->files as $file) {
            $this->attach($file->getRealPath(), [
                'as' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
            ]);
        }
        return $this->from(config('mail.username'))
            ->subject($this->subject)->view('system.email.message');
    }
}
