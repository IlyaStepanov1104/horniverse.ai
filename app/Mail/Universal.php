<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Universal extends Mailable
{
    use Queueable, SerializesModels;

    public $universalMessage;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($universalMessage)
    {
        $this->universalMessage = $universalMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.universal')->with(['universalMessage' => $this->universalMessage]);
    }
}
