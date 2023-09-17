<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FeedbackMailVersion2 extends Mailable
{
    use Queueable, SerializesModels;

    public $html ;
    public function __construct($html)
    {
        $this->html = $html;
    }

    public function build()
    {
        return $this->subject('Mail from Saladin support')
                    ->view('emails.feedbackMailVersion2');
    }
}
