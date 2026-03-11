<?php

namespace App\Mail;

use App\Models\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WebsiteDownAlert extends Mailable
{
    use Queueable, SerializesModels;

    // public $site;
    // public $statusCode;

    // public $sslValid;

    public $downSites;


    /**
     * Create a new message instance.
     */
    public function __construct($downSites)
    {
        // $this -> site = $site;
        // $this->statusCode = $statusCode;
        // $this->sslValid = $sslValid;
        $this->downSites = $downSites;
    }

    public function build()
    {
        return $this->subject('Website Monitoring Alert')
        ->view('emails.website-down')
        ->with(['downSites' => $this->downSites]);
    }

    // /**
    //  * Get the message envelope.
    //  */
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'Website Down Alert',
    //     );
    // }

    // /**
    //  * Get the message content definition.
    //  */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    // /**
    //  * Get the attachments for the message.
    //  *
    //  * @return array<int, \Illuminate\Mail\Mailables\Attachment>
    //  */
    // public function attachments(): array
    // {
    //     return [];
    // }
}
