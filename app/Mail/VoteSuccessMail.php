<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VoteSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    /**
     * Create a new message instance.
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function build()
    {
        return $this->subject($this->subject)
            ->view('emails.send_confirmation');
    }
}
