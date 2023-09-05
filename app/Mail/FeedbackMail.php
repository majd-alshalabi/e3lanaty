<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FeedbackMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title , $description ;
    public function __construct($title , $description)
    {
        $this->title = $title ;
        $this->description = $description ;
    }

    public function build()
    {
        return $this->subject('Mail from Saladin support')
                    ->view('emails.feedbackMail');
    }
}
