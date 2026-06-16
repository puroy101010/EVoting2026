<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $subject;
    /**
     * Create a new message instance.
     */
    public function __construct($otp, $subject)
    {
        $this->otp = $otp;
        $this->subject = $subject;
    }

    public function build()
    {
        return $this->subject($this->subject)
            ->view('emails.otp');
    }
}
