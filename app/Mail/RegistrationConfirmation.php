<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $confirmationCode = null;
    public $notify = false;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($confirmationCode, $notify)
    {
        $this->confirmationCode = $confirmationCode;
        $this->notify = $notify;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // if $notify is true, we send only notification email, else, confirmation email is sent.
        return $this->markdown($this->notify ? 'mail.notify-user-addition' : 'mail.registration-confirmation');
    }
}
