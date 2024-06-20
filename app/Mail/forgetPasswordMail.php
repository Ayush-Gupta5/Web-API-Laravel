<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class forgetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token, $name;
    /**
     * Create a new message instance.
     */
    public function __construct($token,$name)
    {
        $this->token=$token;
        $this->name=$name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.ResetPassowordEmail')
                    ->subject('Reset Password')
                    ->with(['token' => $this->token,'name'=>$this->name]);
    }
}
